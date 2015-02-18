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
    HOST     = '127.0.0.1'
    DATABASE = 'is2_myfh'

    def __init__(self, user=USER, password=PASSWORD, host=HOST, db=DATABASE):
        '''
        Constructor
        '''
        self.user = user
        self.password = password
        self.host = host
        self.db = db
    
    def get_all_profiles(self):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        cursor = conn.cursor()
        cursor.execute("""
                SELECT s.id, TIMESTAMPDIFF(YEAR,STR_TO_DATE(u.birthdate,'%m/%d/%Y'),CURDATE()) AS age, u.sex, u.children, 
                u.lat, u.lng, s.lat AS pref_lat, s.lng AS pref_lng, s.price, s.balcony, s.size, s.rooms 
                FROM users AS u, searchProfiles AS s 
                WHERE u.id = s.userId
        """)
        result = cursor.fetchall()
        conn.close()
        df = pd.DataFrame(result)
        df.columns = zip(*cursor.description)[0] # gets table colum headers
        return df
    
    def update_all_clusters(self, data):
        conn = mysql.connector.connect(user=self.user,password=self.password,host=self.host,database=self.db)
        dbdata = pd.DataFrame()
        dbdata['searchProfiles_id'] = data['id']
        dbdata['cluster'] = data['kmlabel']
        dbdata.to_sql(name='clusters', con=conn, if_exists='replace', flavor='mysql', index=False)
        conn.close()
    
