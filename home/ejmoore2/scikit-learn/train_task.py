from ai_helper import AiHelper
from sklearn.externals import joblib
from datetime import datetime
from params import *

def main():
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('<train_task %s>\n' % str(datetime.now())))
    with AiHelper() as ai:
        ai.generate_instances(True, None, use_genres=use_genres, use_ppl=use_ppl, min_app_cnt=min_app_cnt, use_plot=use_plot)
        while True:
            ids,X,y = ai.next_batch(batch_size=batch_size)
            if X is None:
                break
            classifier.partial_fit(X, y)
        filename = format('/home/ejmoore2/scikit-learn/classifier/%s_%s_%d_%s_%s.pkl' % (str(use_genres), str(use_ppl), min_app_cnt, str(use_plot), classifier_type))
	joblib.dump(classifier, filename)
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('  %s\n</train_task %s>\n' % (filename, str(datetime.now()))))

if __name__ == '__main__':
    main()
