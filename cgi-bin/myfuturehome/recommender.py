'''
@author: Emanuel Castillo Ruiz
'''
from providers import MySQLDataService
from cluster import MFHClustering
import pandas as pd
import numpy as np

from sklearn.feature_extraction import DictVectorizer
from sklearn.preprocessing import StandardScaler
from sklearn.metrics.pairwise import pairwise_distances

class MFHRecommender:
    '''
    classdocs
    '''
    PROFILE_ID = 1
    PUNISHMENT_FACTOR = 1.1
    
    def __init__(self, profile_id=PROFILE_ID):
        '''
        Constructor
        '''
        self.profile_id = profile_id
        self.cluster_id = None
        self.distances = None
        self.stringAttributes = ['balcony']
        self.numberAttributes = ['lat','lng','price','size','rooms']
        self.scaler = None
        self.vectorizer = None
    
    def run(self):
        '''
        Runs recommendation process for given profile id
        '''
        self.compute_distances()
        self.save_distances()
    
    def init_data_preprocessing(self, data):
        self.vectorizer = DictVectorizer()
        vec_data = pd.DataFrame(self.vectorizer.fit_transform(data.ix[:,self.stringAttributes].T.to_dict().values()).toarray())
        vec_data.columns = self.vectorizer.get_feature_names()
        
        std_data = data.ix[:,self.numberAttributes]
        std_data = std_data.merge(vec_data,left_index=True,right_index=True)
        
        self.scaler = StandardScaler()
        self.scaler.fit(std_data)
    
    def preprocess_data(self, data):        
        # vectorize data (categories to dummy variables)
        vec_data = pd.DataFrame(self.vectorizer.transform(data.ix[:,self.stringAttributes].T.to_dict().values()).toarray())
        vec_data.columns = self.vectorizer.get_feature_names()
        
        # merge data
        std_data = data.ix[:,self.numberAttributes]
        std_data = std_data.merge(vec_data,left_index=True,right_index=True)
        
        #standardized data
        pdata = self.scaler.transform(std_data)
        
        return pdata
    
    def compute_distances(self):
        '''
        '''
        ds = MySQLDataService()
        profile = ds.get_profile(self.profile_id)
        
        # Find cluster of the profile. Load or predict.
        cluster = ds.get_profile_cluster(self.profile_id)
        if(len(cluster.columns)<=0):
            clustering = MFHClustering()
            cluster = pd.DataFrame(clustering.predict_cluster(profile.ix[:,1:]))
            ds.insert_cluster(self.profile_id, cluster.iat[0,0])
            cluster.columns = ['cluster']
        
        self.cluster_id = cluster.iat[0,0]
        adverts = ds.get_cluster_adverts(self.cluster_id)
        self.init_data_preprocessing(adverts)
        
        padverts = self.preprocess_data(adverts)
        
        #watched = ds.get_profile_watched_adverts(self.profile_id)
        #pwatched = self.preprocess_data(watched)
        
        #favorites = ds.get_profile_favorites_adverts(self.profile_id)
        watched = ds.get_profile_watched_adverts_ordered(self.profile_id)
        if len(watched.columns) > 0:
            #pfavorites = self.preprocess_data(favorites)
            
            pwatched = self.preprocess_data(watched) 
            self.pwatched = pwatched # TODO DELETE
            distances = pairwise_distances(padverts, pwatched)
            
            
        #elif len(watched.columns) > 0:
        #    '''
        #    '''
        else:
            search = ds.get_profile_search(self.profile_id)
            psearch = self.preprocess_data(search)
            distances = pairwise_distances(padverts, psearch)
        
        distances = pd.DataFrame(distances)
        if len(distances.columns) > 1:
            # Apply punishment factor for each watched advert (keys are from 0 to n)
            for key in distances.keys():
                distances[key] = distances[key].apply(lambda x: x * np.power(self.PUNISHMENT_FACTOR,key))
            # use minimum distance values
            distances = pd.DataFrame(distances.min(axis=1))
        
        self.distances_only = distances # TODO DELETE
        self.distances = adverts
        self.distances['priority'] = distances[0]
        
        return self.distances
    
    def save_distances(self):
        '''
        '''
        ds = MySQLDataService()
        ds.update_all_priorities(self.profile_id, self.distances)
    