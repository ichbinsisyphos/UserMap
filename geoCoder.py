#!/usr/bin/env python
# -*- coding: utf-8 -*- 

import geopy
import sys

returnString = "None"

if len(sys.argv) == 2 and sys.argv[1]:
	g = geopy.geocoders.GoogleV3()
	locationList = [ result[0] + "%" + str(result[1][0]) + "%" + str(result[1][1]) for result in g.geocode(sys.argv[1], exactly_one=False) ]

	if len(locationList) > 0:
		returnString = "$".join(locationList)

sys.stdout.write(returnString)