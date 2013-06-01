#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

import sys
from UserMapfunctions import *

#Anzahl der Argumente checken, wenn nicht 4, dann Fehlerausgabe und Abbruch
if len(sys.argv) != 5:
  sys.stdout.write("wrong_arguments")
  exit()

kmlFilePath, kmzFilePath = getKmlKmz()
hostname = getHostname()
action, newName, newDescription, newCountry, newLat, newLng = parseArgs(sys.argv[1:])
if action == "add":
  lock()

  #KML-Baum aus altem file erzeugen
  root = parseKml(kmlFilePath)
  Placemarks = getPlacemarks(root)
  #Abfrage ob ein Eintrag unter dem Namen bereits existiert
  if newName not in nameSet(Placemarks):
    addNewPlacemark(Placemarks, hostname, newName, newLat, newLng, newCountry, newDescription)
    #neuen KML-Dateinamen erzeugen, XML reinschreiben und alte Dateien ins backup-Verzeichnis
    writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath, kmzFilePath)
    #Erfolgsmeldung ausgeben
    sys.stdout.write("success")
  else:
    #Benutzername hat schon einen Eintrag
    sys.stdout.write("name_taken")

  unlock()