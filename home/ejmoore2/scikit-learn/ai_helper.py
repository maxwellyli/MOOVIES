import numpy as np
import numpy.linalg as la
import mysql.connector
import os
import re

class AiHelper:
    def __init__(self):
        # Open database connection
        self.cnx    = mysql.connector.connect(user='root', password='cs411fa2016', database='moovies')
        self.cursor = self.cnx.cursor(buffered=True)
        # Feature space helpers, uninitialized
        self.genre_cnt  = 0
        self.genre_dict = None # map from genre->idx
        self.ppl_cnt    = 0
        self.ppl_dict   = None # map from person_id->idx
        self.word_cnt   = 0
        self.word_dict  = None # map from word->idx
        # Running total of rows updated
        self.chng_cnt = 0
	self.updates = []

    def generate_instances(self, for_training, limit, use_genres=True, use_ppl=True, min_app_cnt=1, use_plot=True, extra_q=''):
        self.for_training = for_training
        # Initialize helpers for desired features
        if use_genres:
            self.load_genres()
        else:
            self.unload_genres()
        if use_ppl:
            self.load_ppl(min_app_cnt)
        else:
            self.unload_ppl()
        if use_plot:
            self.load_words()
        else:
            self.unload_words()
        # Pull movie id's, genres, cast, plot, and rating from db
        # For training sets we only use movies that have an IMDb rating
        q_for_training = 'where movie2.imdb_rating is not null' if for_training else ''
        q_limit        = format('limit %d' % limit) if limit else ''
        q = format('select movie2.id as id, movie2.genres as genres, group_concat(worked_on.person_id separator \',\') as cast, movie2.plot as plot, movie2.imdb_rating as rating from movie2 left join worked_on on movie2.id = worked_on.movie_id %s %s group by movie2.id %s' % (q_for_training, extra_q, q_limit))
        self.cursor.execute(q)
        # For each movie we will create a bit vector instance and a label (if training)
        self.feature_space_size = self.genre_cnt + self.ppl_cnt + self.word_cnt
    
    def next_batch(self, batch_size=500):
	X   = np.empty((batch_size, self.feature_space_size))
        y   = [] if self.for_training else None
        # If we are not training, we need to keep track of movie id's so we can update the database after making a prediction
        ids = [] if not self.for_training else None
        cnt = 0
        while cnt < batch_size:
            # Pull a new tuple from cursor
            row = self.cursor.fetchone()
            if not row:
                if cnt == 0:
		    # X = None means empty batch
		    return None,None,None
                break
            _id,genres_str,ppl_str,plot,rating = row
            if not self.for_training:
                ids.append(_id)
            # For each feature, we calculate an idx in the bit vector and set to 1 if this movie has that feature
            x = np.zeros(self.feature_space_size) # bit vector to fill
            if self.genre_cnt > 0 and genres_str:
                for g in genres_str.split(','):
                    if g in self.genre_dict:
                        g_idx = self.genre_dict[g]
                        x[g_idx] = 1
            if self.ppl_cnt > 0 and ppl_str:
                for p in ppl_str.split(','):
                    if p in self.ppl_dict:
                        p_idx = self.genre_cnt + self.ppl_dict[p]
                        x[p_idx] = 1
            if self.word_cnt > 0 and plot:
                for w in self.plot_to_words(plot):
                    if w in self.word_dict:
                        w_idx = self.genre_cnt + self.ppl_cnt + self.word_dict[w]
                        x[w_idx] = 1
            if self.for_training and rating:
                y.append(rating / 1.0)
            # Normalize feature vector and add to set
            mag = la.norm(x, ord=2)
	    x_norm = x #/ (mag if mag > 0 else 1)
            X[cnt,:] = x_norm
            cnt += 1
        # Done generating instances!
        if y:
            y = np.array(y)
        # If we got fewer than the batch limit we need to trim X
        X = X[:cnt,:]
        return ids,X,y

    def file_exists(self, _dir, fname):
        for fn in os.listdir(format('/home/ejmoore2/scikit-learn/%s' % _dir)):
            if fn == fname:
                return True
        return False

    def load_genres(self):
        ''' Loads genres into dictionary mapping genre string to feature space idx '''
        fname = 'genres.npy'
        _dir = 'genres'
        rows = None
        fexists = self.file_exists(_dir, fname)
        if fexists:
            rows = np.load(format('/home/ejmoore2/scikit-learn/%s/%s' % (_dir, fname))).reshape(-1, 2)
        else:
            q = 'select genre from genre'
            self.cursor.execute(q)
            rows = self.cursor.fetchall()
        self.genre_cnt = 0
        self.genre_dict = {}
        for g in rows:
            while not isinstance(g, (basestring)): # weird formatting thing...
                g = g[0]
            self.genre_dict[g] = self.genre_cnt
            self.genre_cnt += 1
        if not fexists:
            np.save(format('/home/ejmoore2/scikit-learn/%s/%s' % (_dir, fname)), self.genre_dict.items())

    def unload_genres(self):
        self.genre_cnt = 0
        self.genre_dict = None

    def load_ppl(self, min_app_cnt):
        # Check for pre-existing people file w/ given min appearance count
        fname = format('ppl-%d.npy' % min_app_cnt)
        _dir = 'ppl'
        ppl = None
        fexists = self.file_exists(_dir, fname)
        # Load from file, or db if necessary
        if fexists:
            ppl = np.load(format('/home/ejmoore2/scikit-learn/%s/%s' % (_dir, fname))).reshape(-1)
        else:
            # Query will take a LONG time...
            q = format('select worked_on.person_id from worked_on group by worked_on.person_id having count(worked_on.person_id) > %d' % min_app_cnt)
            self.cursor.execute(q)
            ppl = self.cursor.fetchall()
        # Build dictionary mapping person_id to feature space idx
        self.ppl_cnt = 0
        self.ppl_dict = {}
        for _id in ppl:
            self.ppl_dict[_id] = self.ppl_cnt
            self.ppl_cnt += 1
        # Save the people file so we avoid repeating query in the future
        if not fexists:
            np.save(format('/home/ejmoore2/scikit-learn/%s/%s' % (_dir, fname)), ppl)

    def unload_ppl(self):
        self.ppl_cnt = 0
        self.ppl_dict = None

    def load_words(self):
        fname = 'words.txt'
        _dir = 'words'
        fexists = self.file_exists(_dir, fname)
        if fexists:
            words = None
            with open(format('/home/ejmoore2/scikit-learn/%s/%s' % (_dir, fname)), 'r') as f:
                words = f.readlines()
            self.word_cnt = 0
            self.word_dict = {}
            for w in words:
                self.word_dict[w] = self.word_cnt
                self.word_cnt += 1
        else:
            # Fetch all plots from db
            q = 'select plot from movie2 where plot is not null'
            self.cursor.execute(q)
            # Load words into dictionary, and save to file so we don't have to do this again
            self.word_cnt = 0
            self.word_dict = {}
            with open(format('%s/%s' % (_dir, fname)), 'w') as f:
                while True:
                    # Split plot into words
                    plot = self.cursor.fetchone()
      		    if plot is None:
			break
		    words = self.plot_to_words(plot)
                    for w in words:
                        if w not in self.word_dict:
                            # Idx of word in feature space will be the word count when it was added to the dictionary
                            self.word_dict[w] = self.word_cnt
                            self.word_cnt += 1
                            # Save to file
                            f.write(format('%s\n' % w))

    def plot_to_words(self, plot):
        if not plot:
            return []
        while not isinstance(plot, (basestring)): # weird formatting thing...
            plot = plot[0]
        # Normalize by shifting to lower-case and replacing punctuation with spaces
        norm_plot = re.sub('[^a-z\ \']+', ' ', plot.lower())
        words = norm_plot.split()
        # Filter out short words
        words = list(filter(lambda w: len(w) >= 5, words))
        return words

    def unload_words(self):
        self.word_cnt = 0
        self.word_dict = None

    def update_predicted_rating(self, movie_id, new_predicted_rating):
        self.updates.append((movie_id, new_predicted_rating*1))
	self.chng_cnt += 1
        # Avoid memory error by flushing local changes to file
        if self.chng_cnt % 1000 == 0:
            with open('/home/ejmoore2/scikit-learn/pending_updates.dat', 'a+') as f:
                for movie_id,new_predicted_rating in self.updates:
                    f.write(format('%d,%.2f\n' % (movie_id,new_predicted_rating)))
            self.updates = []

    def update_all(self):
        ''' Executes an update query on the database '''
        # From current buffer
        for movie_id,new_predicted_rating in self.updates:
            q = format('update movie2 set predicted_rating=%s where id=%s' % (new_predicted_rating,movie_id))
            self.cursor.execute(q)
        self.updates = []
        # From local cache
        with open('/home/ejmoore2/scikit-learn/pending_updates.dat', 'r') as f:
            for line in f:
                movie_id,new_predicted_rating = line.strip().split(',')
                q = format('update movie2 set predicted_rating=%s where id=%s' % (new_predicted_rating,movie_id))
                self.cursor.execute(q)
        self.cnx.commit()
        # Clear file
        with open('/home/ejmoore2/scikit-learn/pending_updates.dat', 'w') as f:
            f.write('')

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_value, traceback):
        # Commit changes before close
        if self.chng_cnt > 0:
	    self.update_all()
        # Disconnect from db
        self.cursor.close()
        self.cnx.close()

