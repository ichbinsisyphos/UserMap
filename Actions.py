#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#TODO: SET ACTION UNDEFINED IF INSUFFICIENT OR WRONG TYPE OF ARGUMENTS

def enum(*sequential, **named):
    enums = dict(zip(sequential, range(len(sequential))), **named)
    return type('Enum', (), enums)

Actions = enum("add", "overwrite", "updateDescription", "removename", "rebuild", "namelist", "forname", "undefined")

def coordSplit(locationString):
	place, coords = locationString.replace(")","").split("(")
	placelist = place.split(",")
	
	if len(placelist) == 1:
		countryOnly = True
	else:
		countryOnly = False

	country = (placelist[-1]).strip()
	lat,lng = ( float(coord) for coord in coords.split(",") )
	
	return (countryOnly, country, lat, lng)

class Action():
 	def __init__(self, args):
		self.name = None
		self.type = None
		self.desc = None
		self.countryOnly = None
		self.country = None
		self.lat = None
		self.lng = None
		self.readOnly = None

 		self.type = cleanAction(args[1])
 		if self.type != Actions.undefined:
	 		if self.type in (Actions.add, Actions.overwrite, Actions.removename, Actions.forname, Actions.updateDescription):
		 		self.name = args[2]
	 		if self.type in (Actions.add, Actions.overwrite, Actions.updateDescription):
		 		self.desc = args[3]
			if self.type in (Actions.add, Actions.overwrite):
		 		self.countryOnly, self.country, self.lat, self.lng = coordSplit(args[4])
			if self.type in (Actions.add, Actions.overwrite, Actions.removename, Actions.rebuild, Actions.updateDescription):
				self.readOnly = False
			else:
				self.readOnly = True

def cleanAction(action):
	return {
		"add":Actions.add,
		"overwrite":Actions.overwrite,
		"updateDescription":Actions.updateDescription,
		"removename":Actions.removename,
		"rebuild":Actions.rebuild,
		"namelist":Actions.namelist,
		"forname":Actions.forname
	}.get(action, Actions.undefined)


# argv1 = ["./UserMap.py","add","ichbinsisyphos","fehsdgfjh", "Graz, Austria (115.3452345, 51.768345)"]
# argv2 = ["./UserMap.py","add","ichbinsisyphos","fehsdgfjh", "Austria (115.3452345, 51.768345)"]
# argv3 = ["./UserMap.py","overwrite","ichbinsisyphos","fehsdgfjh", "Graz, Austria (115.3452345, 51.768345)"]
# argv4 = ["./UserMap.py","removename","ichbinsisyphos"]
# argv5 = ["./UserMap.py","rebuild"]
# argv6 = ["./UserMap.py","namelist"]
# argv7 = ["./UserMap.py","forname","ichbinsisyphos"]
# argv8 = ["./UserMap.py","adsd","ichbinsisyphos","fehsdgfjh", "Graz, Austria (115.3452345, 51.768345)"]

# for argv in (argv1,argv2,argv3,argv4,argv5,argv6,argv7,argv8):
# 	a = Action(argv)
# 	print a.type, a.name, a.desc, a.countryOnly, a.country, a.lat, a.lng, a.readOnly