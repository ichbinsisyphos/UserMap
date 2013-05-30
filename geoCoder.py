#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: geopy (via pip)

import geopy
import sys
import os
import time
import codecs

#Pfad zum lockfile
lockPath = "var/geoCoder.lock"

#wenn lockfile existiert, 1 Sekunde warten und nochmal checken, sonst weiter
while os.path.isfile(lockPath):
  time.sleep(1)

#lockfile erstellen
open(lockPath,"w").close()


returnString = u"None"

if len(sys.argv) == 2 and sys.argv[1]:

  locationList = []
  
  g = geopy.geocoders.GoogleV3()
  
  try:
    locationList = [ unicode(result[0]) + u"%" \
        + unicode(result[1][0]) + u"%" + unicode(result[1][1]) \
        for result in g.geocode(sys.argv[1].decode("utf-8"), \
        exactly_one=False) ]
  except geopy.geocoders.googlev3.GQueryError:
    pass

  if len(locationList) > 0:
    returnString = u"$".join(locationList)

sys.stdout.write(returnString.encode("utf-8"))

#Am Schluss lockfile loeschen
os.remove(lockPath)