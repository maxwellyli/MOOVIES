from ai_helper import AiHelper
from sklearn.externals import joblib
from datetime import datetime
from params import *

def main():
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('<label_task %s>\n' % str(datetime.now())))
    update_cnt = 0
    with AiHelper() as ai:
        filename = format('/home/ejmoore2/scikit-learn/classifier/%s_%s_%d_%s_%s.pkl' % (str(use_genres), str(use_ppl), min_app_cnt, str(use_plot), classifier_type))
        classifier = joblib.load(filename)
        ai.generate_instances(False, None, use_genres=use_genres, use_ppl=use_ppl, min_app_cnt=min_app_cnt, use_plot=use_plot)
        while True:
            ids,X,y = ai.next_batch(batch_size=batch_size)
            if X is None:
                break
            Y = classifier.predict(X)
            for _id,y_pred in zip(ids, Y):
                ai.update_predicted_rating(_id, y_pred)
        update_cnt = ai.chng_cnt
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('  %s\n  %d\n</label_task %s>\n' % (filename, update_cnt, str(datetime.now()))))

if __name__ == '__main__':
    main()
