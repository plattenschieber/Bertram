'''
Data service classes

@author: Emanuel Castillo Ruiz
'''
import mysql.connector
import pandas as pd

class MySQLDataService:
    '''
    MySQL data service class
    '''
    USER     = 'myfh_user'
    PASSWORD = 'nj1bK58&}laksdS123?'
    HOST     = 'myfh.storyspot.de'
    DATABASE = 'is2_myfh'

    def __init__(self, user=USER, password=PASSWORD, host=HOST, db=DATABASE):
        '''
        Constructor
        '''
        self.user = user
        self.password = password
        self.host = host
        self.db = db
        
    def get_profile(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT s.id, TIMESTAMPDIFF(YEAR,STR_TO_DATE(u.birthdate,'%m/%d/%Y'),CURDATE()) AS age, u.sex, u.children, 
                u.lat, u.lng, s.lat AS pref_lat, s.lng AS pref_lng, s.price, s.balcony, s.size, s.rooms 
                FROM users AS u, searchProfiles AS s 
                WHERE u.id = s.userId AND s.id = {0}
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_cluster(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT c.cluster FROM clusters AS c WHERE c.searchProfileId = {0}
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_search(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT s.id, s.lat, s.lng, s.price, s.balcony, s.size, s.rooms 
                FROM searchProfiles AS s 
                WHERE s.id = {0}
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_favorites_adverts(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT a.id, a.lat, a.lng, a.price, a.balcony, a.size, a.rooms
                FROM adverts AS a
                    JOIN favourites ON (a.id = favourites.advertId)
                    JOIN users ON (favourites.userId = users.id)
                    JOIN searchProfiles ON (users.id = searchProfiles.userId)
                WHERE searchProfiles.id = {0}
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_watched_adverts(self, profile_id):
        '''
        '''
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT a.id, a.lat, a.lng, a.price, a.balcony, a.size, a.rooms, COUNT(w.created) as nwatched
                FROM adverts AS a
                    JOIN watched AS w ON (a.id = w.advertId)
                    JOIN users ON (w.userId = users.id)
                    JOIN searchProfiles ON (users.id = searchProfiles.userId)
                WHERE searchProfiles.id = {0}
                GROUP BY a.id
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_watched_adverts_ordered(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT a.id, a.lat, a.lng, a.price, a.balcony, a.size, a.rooms, COUNT(w.created) as nwatched, (f.advertId IS NOT NULL) as isfavorit
                FROM adverts AS a
                    JOIN watched AS w ON (a.id = w.advertId)
                    JOIN users ON (w.userId = users.id)
                    LEFT JOIN favourites AS f ON (w.advertId = f.advertId)
                    JOIN searchProfiles ON (users.id = searchProfiles.userId)
                WHERE searchProfiles.id = {0}
                GROUP BY a.id
                ORDER BY  (f.advertId IS NOT NULL) DESC, COUNT(w.created) DESC
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_cluster_adverts(self, cluster_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT a.id, a.lat, a.lng, a.price, a.balcony, a.size, a.rooms
                FROM adverts AS a
                    JOIN RS_searchProfiles_adverts AS sa ON (a.id = sa.advertId)
                    JOIN searchProfiles AS s ON (sa.searchProfileId = s.id)
                    JOIN clusters AS c ON (c.searchProfileId = s.id)
                WHERE c.cluster = {0}
                    AND a.lat IS NOT NULL AND a.lng IS NOT NULL 
        """.format(cluster_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_profile_adverts_with_priority(self, profile_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                    SELECT sa.priority, a.* 
                    FROM adverts AS a
                        JOIN RS_searchProfiles_adverts AS sa ON (a.id = sa.advertId)
                    WHERE sa.searchProfileId = {0}
                    ORDER BY sa.priority LIMIT 5 
        """.format(profile_id))
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        if len(df.columns) > 0:
            df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def get_all_profiles(self):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT s.id, TIMESTAMPDIFF(YEAR,STR_TO_DATE(u.birthdate,'%m/%d/%Y'),CURDATE()) AS age, u.sex, u.children, 
                u.lat, u.lng, s.lat AS pref_lat, s.lng AS pref_lng, s.price, s.balcony, s.size, s.rooms 
                FROM users AS u, searchProfiles AS s 
                WHERE u.id = s.userId
                    AND u.lat IS NOT NULL AND u.lng IS NOT NULL AND s.lat IS NOT NULL AND s.lng IS NOT NULL 
                    AND TIMESTAMPDIFF(YEAR,STR_TO_DATE(u.birthdate,'%m/%d/%Y'),CURDATE()) IS NOT NULL
                    AND u.children IS NOT NULL
        """)
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def update_all_clusters(self, data):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        dbdata = pd.DataFrame()
        dbdata['searchProfileId'] = data['id']
        dbdata['cluster'] = data['kmlabel']
        dbdata.to_sql(name='clusters', con=conn, if_exists='replace', flavor='mysql', index=False)
        conn.close()
    
    def insert_cluster(self, profile_id, cluster_id):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                INSERT INTO clusters (searchProfileId,cluster) VALUES ({0}, {1});
            """.format(profile_id, cluster_id))
        conn.commit()
        conn.close()
    
    def update_all_priorities(self, profile_id, advert_priorities):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        for i, row in advert_priorities.iterrows():
            cursor.execute("""
                UPDATE RS_searchProfiles_adverts
                SET priority = {0}
                WHERE searchProfileId = {1} AND advertId = {2}
            """.format(row['priority'], profile_id, row['id']))
        conn.commit()
        conn.close()
