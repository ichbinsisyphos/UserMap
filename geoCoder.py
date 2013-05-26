#!/usr/bin/env python
# -*- coding: utf-8 -*- 

import geopy
import sys
import os
import time

#Pfad zum lockfile
lockPath = "var/geoCoder.lock"

#wenn lockfile existiert, 1 Sekunde warten und nochmal checken, sonst weiter
while os.path.isfile(lockPath):
	time.sleep(1)

#lockfile erstellen
open(lockPath,"w").close()


returnString = "None"

if len(sys.argv) == 2 and sys.argv[1]:
	g = geopy.geocoders.GoogleV3()
	locationList = [ result[0] + "%" + str(result[1][0]) + "%" \
			+ str(result[1][1]) for result in g.geocode(sys.argv[1], \
			exactly_one=False) ]

	if len(locationList) > 0:
		returnString = "$".join(locationList)

sys.stdout.write(returnString)

#Am Schluss lockfile loeschen
os.remove(lockPath)