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


returnString = ""

if len(sys.argv) == 2 and sys.argv[1]:

  locationList = []
  
  g = geopy.geocoders.GoogleV3()

  try:
    results = g.geocode(sys.argv[1].decode("utf-8"), exactly_one=False)
    locationList = [ unicode(result[0]) + u" (" \
        + unicode(result[1][0]) + u"," + unicode(result[1][1]) + u")" \
        for result in results ]

  except geopy.geocoders.googlev3.GQueryError:
    returnString = "error_querynotunderstood"

  except geopy.geocoders.googlev3.GTooManyQueriesError:
    returnString = "error_apicreditsusedup"

  except:
    returnString = "error_unknown"

  if len(locationList) > 0:
    returnString = u"$&$".join(locationList)

else:
  returnString = "error_wrong_arguments"  

sys.stdout.write(returnString.encode("utf-8"))

#Am Schluss lockfile loeschen
os.remove(lockPath)