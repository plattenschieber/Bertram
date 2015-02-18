'''
@author: Emanuel Castillo Ruiz
'''
from converters import StringConverter
from converters import NumberConverter
from providers import MySQLDataService
from sklearn.cluster import DBSCAN
from sklearn.cluster import KMeans
from sklearn.preprocessing import OneHotEncoder
from sklearn.externals import joblib
from sklearn import preprocessing
import pandas as pd

class MFHClustering:
    '''
    MyFutureHome Clustering class
    '''
    
    MIN_SIZE = 10   # MinPts for DBSCAN and threshold for k-Means
    EPSILON  = 2    # Maximal density for DBSCAN
    
    def __init__(self, min_size=MIN_SIZE, eps=EPSILON):
        '''
        Constructor
        '''
        self.min_size = min_size
        self.eps = eps
        self.dataService = MySQLDataService()
        self.profiles = None
        self.dblabels = None
        self.kmlabels = None
        self.stringAttributes = ['sex','balcony']
        self.numberAttributes = ['age','children','lat','lng','pref_lat','pref_lng','price','size','rooms']
    
    def run(self):
        '''
        Runs clustering process
        '''
        self.compute_kmeans_labels()
        self.save_kmeans_labels()
    
    def get_profiles(self):
        if(self.profiles is None):
            self.profiles = self.dataService.get_all_profiles()
        return self.profiles
    
    def compute_dbscan_labels(self):
        '''
        Computes DBSCAN clustering on data from DataService
        returns Number of estimated clusters
        '''
        profiles = self.get_profiles()
        
        stringData = profiles.ix[:,self.stringAttributes]
        stringConverter = StringConverter(stringData)
        data = stringConverter.get_converted_data()
        
        numberData = profiles.ix[:,self.numberAttributes]
        # TODO: string data should not be standardized
        numberData = numberData.merge(data,left_index=True,right_index=True) 
        numberConverter = NumberConverter(numberData)
        data = numberConverter.get_converted_data()
        
        db = DBSCAN(eps=self.eps, min_samples=self.min_size).fit(data)
        
        # Number of clusters in labels, ignoring noise if present.
        dblabels = db.labels_
        n_clusters_ = len(set(dblabels)) - (1 if -1 in dblabels else 0)
        self.dblabels = pd.DataFrame()
        self.dblabels['dblabel'] = db.labels_
        return n_clusters_
    
    def get_onehotenconded_dblabels(self):
        enc = OneHotEncoder()
        dblabels = pd.DataFrame()
        dblabels['dblabel'] = map(lambda x: x + 1, self.dblabels['dblabel'])
        dblabels = pd.DataFrame(enc.fit_transform(dblabels).toarray())
        return dblabels
    
    def get_standardazised_dblabels(self):
        '''
        '''
        return preprocessing.scale(self.get_onehotenconded_dblabels())
    
    def compute_kmeans_labels(self, dbscan=True):
        '''
        '''
        profiles = self.get_profiles()
        
        stringData = profiles.ix[:,self.stringAttributes]
        stringConverter = StringConverter(stringData)
        data = stringConverter.get_converted_data()
        
        numberData = profiles.ix[:,self.numberAttributes]
        # TODO: string data should not be standardized
        numberData = numberData.merge(data,left_index=True,right_index=True)         
        numberConverter = NumberConverter(numberData)
        data = numberConverter.get_converted_data()
        
        
        km = KMeans(init='k-means++', n_clusters=500)
        km.fit_predict(data)
        self.kmlabels = pd.DataFrame()
        self.kmlabels['kmlabel'] = km.labels_
        
        # Persist model
        joblib.dump(km, 'kmeans_model.pkl')
        
        return self.kmlabels
        
    def save_kmeans_labels(self):
        '''
        '''
        profiles = self.get_profiles()
        data = profiles.merge(self.kmlabels, left_index=True, right_index=True)
        self.dataService.update_all_clusters(data.ix[:,['id','kmlabel']])
    
    def predict_kmeans_cluster(self, data):
        '''
        '''

