from ai_helper import AiHelper
from sklearn.externals import joblib
from sklearn.metrics import mean_absolute_error
from datetime import datetime
from params import *

def main():
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('<test_task %s>\n' % str(datetime.now())))
    with AiHelper() as ai:
        filename = format('/home/ejmoore2/scikit-learn/classifier/%s_%s_%d_%s_%s.pkl' % (str(use_genres), str(use_ppl), min_app_cnt, str(use_plot), classifier_type))
        classifier = joblib.load(filename)
        ai.generate_instances(True, None, use_genres=use_genres, use_ppl=use_ppl, min_app_cnt=min_app_cnt, use_plot=use_plot)
        batch_cnt = 0
        total_err = 0
        while True:
            ids,X,y_true = ai.next_batch(batch_size=batch_size)
            if X is None:
                break
            batch_cnt += 1
            y_pred = classifier.predict(X)
            total_err += mean_absolute_error(y_true, y_pred)
    with open('/home/ejmoore2/cron.log', 'a') as f:
        f.write(format('  %s\n  mean_absolute_error: %.2f\n</test_task %s>\n' % (filename, total_err/batch_cnt, str(datetime.now()))))

if __name__ == '__main__':
    main()
