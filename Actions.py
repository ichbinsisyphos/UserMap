#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#TODO: SET ACTION UNDEFINED IF INSUFFICIENT OR WRONG TYPE OF ARGUMENTS

def enum(*sequential, **named):
    enums = dict(zip(sequential, range(len(sequential))), **named)
    return type('Enum', (), enums)

Actions = enum("add",
			   "overwrite",
			   "updateDescription",
			   "removename",
			   "rebuild",
			   "namelist",
			   "forname",
			   "undefined")

def coordSplit(locationString):
	place, coords = locationString.replace(")","").split("(")
	placelist = place.split(",")
	
	if len(placelist) == 1:
		countryOnly = True
	else:
		countryOnly = False

	country = (placelist[-1]).strip().decode("UTF-8")
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
	 		if self.type in (Actions.add,
	 						 Actions.overwrite,
	 						 Actions.removename,
	 						 Actions.forname,
	 						 Actions.updateDescription):
		 		self.name = args[2].decode("UTF-8")
	 		if self.type in (Actions.add,
	 			 			 Actions.overwrite,
	 			 			 Actions.updateDescription):
		 		self.desc = args[3].decode("UTF-8")
			if self.type in (Actions.add, Actions.overwrite):
		 		self.countryOnly, self.country, self.lat, self.lng = coordSplit(args[4])
			if self.type in (Actions.add,
							 Actions.overwrite,
							 Actions.removename,
							 Actions.rebuild,
							 Actions.updateDescription):
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