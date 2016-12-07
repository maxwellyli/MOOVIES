from sklearn.linear_model import SGDRegressor,PassiveAggressiveRegressor

use_genres = True
use_ppl    = True
min_app_cnt = 10
use_plot   = True

#classifier_type = 'SGDRegressor'
classifier_type = 'PassiveAggressiveRegressor'
#classifier = SGDRegressor()
classifier = PassiveAggressiveRegressor()

batch_size = 250

