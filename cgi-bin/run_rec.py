import cgitb
cgitb.enable()

from myfuturehome.recommender import MFHRecommender
import sys
rec = MFHRecommender(sys.argv[1])
rec.run()