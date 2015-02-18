'''
DataConverter class

@author: Emanuel Castillo Ruiz
'''
import pandas as pd

from sklearn.feature_extraction import DictVectorizer
from sklearn.preprocessing import StandardScaler
from sklearn.externals import joblib

class StringConverter:
    '''
    StringConverter
    '''
    def __init__(self, data, load=False):
        '''
        Constructor
        '''
        self.data = data
        self.load = load
    
    def get_converted_data(self):
        '''
        Vectorization: transforms categorical (string!) attributes to dummy variables
        '''
        if(self.load==False):
            vec = DictVectorizer()
            vec.fit(self.data.T.to_dict().values())
            joblib.dump(vec, 'vectorizer.pkl')
        else:
            vec = joblib.load('vectorizer.pkl')
                    
        vectorized_data = pd.DataFrame(vec.transform(self.data.T.to_dict().values()).toarray())
        vectorized_data.columns = vec.get_feature_names()

        return vectorized_data

class NumberConverter:
    '''
    classdocs
    '''

    def __init__(self, data,load=False):
        '''
        Constructor
        '''
        self.data = data
        self.load = load
    
    def get_converted_data(self):
        '''
        Standardization: standardizes data
        '''
        if(self.load==False):
            scaler = StandardScaler()
            scaler.fit(self.data)
            joblib.dump(scaler, 'standardizer.pkl')
        else:
            scaler = joblib.load('standardizer.pkl')
            
        standardized_data = scaler.transform(self.data)
        # TODO: allways return DataFrame and not array
        #standardized_data = pd.DataFrame(standardized_data)
        #standardized_data.columns = self.data.columns
        return standardized_data
    