#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#TODO: SET ACTION UNDEFINED IF INSUFFICIENT OR WRONG TYPE OF ARGUMENTS
#AND INCLUDE A DESCRIPTIVE ERROR MESSAGE

import time

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

def coordSplit(locationString):
	place, coords = locationString.replace(")","").split("(")
	placelist = place.split(",")
	
	if len(placelist) == 1:
		countryOnly = True
	else:
		countryOnly = False

	country = (placelist[-1]).strip()
	try:
		lat,lng = ( float(coord) for coord in coords.split(",") )
		return (countryOnly, country, lat, lng)
	except:
		return [None]

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
		self.error = None
		self.locationString = None

 		self.type = cleanAction(args[1])
 		if self.type != Actions.undefined:
	 		if self.type in (Actions.add,
	 						 Actions.overwrite,
	 						 Actions.removename,
	 						 Actions.forname,
	 						 Actions.updateDescription):
	 			if len(args) > 2:
			 		self.name = args[2].decode("UTF-8")
			 	else:
			 		self.type = Actions.undefined
			 		self.error = "insufficient_arguments"
	 		if self.type in (Actions.add,
	 			 			 Actions.overwrite,
	 			 			 Actions.updateDescription):
	 			if len(args) > 3:
			 		self.desc = args[3].decode("UTF-8")
			 	else:
			 		self.type = Actions.undefined
			 		self.error = "insufficient_arguments"
			if self.type in (Actions.add, Actions.overwrite):
				if len(args) > 4:
					self.locationString = args[4].decode("UTF-8")
			 		coordList = coordSplit(self.locationString)
			 		if len(coordList) == 4:
			 			self.countryOnly, self.country, self.lat, self.lng = coordList
			 		else:
			 			self.type = Actions.undefined
			 			self.error = "location_not_understood"
			 	else:
			 		self.type = Actions.undefined
			 		self.error = "insufficient_arguments"
			if self.type in (Actions.add,
							 Actions.overwrite,
							 Actions.removename,
							 Actions.rebuild,
							 Actions.updateDescription):
				self.readOnly = False
			else:
				self.readOnly = True
		else:
			self.error = "action_undefined"

	def logMessage(self):
		logStr = time.strftime("%Y-%m-%d %H:%M:%S : ", time.localtime())
		if self.type == Actions.add:
			logStr += u"Benutzername %s hinzugefügt" % self.name
		elif self.type == Actions.overwrite:
			logStr += u"Information für Benutzername %s überschrieben" % self.name
		elif self.type == Actions.updateDescription:
			logStr += u"Beschreibung für Benutzername %s geändert" % self.name
		elif self.type == Actions.removename:
			logStr += u"Benutzername %s entfernt" % self.name
		elif self.type == Actions.rebuild:
			logStr += u"KML-Datei neu erstellt"
		elif self.type == Actions.namelist:
			logStr += u"Namensliste ausgegeben"
		elif self.type == Actions.forname:
			logStr += u"Information für Benutzername %s ausgegeben" % self.name
		elif self.type == Actions.undefined:
			logStr += u"Unbekannte Aktion angefordert"

		if self.error == None:
			logStr += u" : Erfolg"
		else:
			logStr += u" : Fehler: " + self.error

		return logStr