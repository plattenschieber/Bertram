#!/usr/bin/env python
# -*- coding: UTF-8 -*-

# enable debugging
import cgitb
cgitb.enable()


from myfuturehome.cluster import MFHClustering
cluster = MFHClustering()
cluster.run()

print "Content-Type: text/plain;charset=utf-8"
print

print "OK!"