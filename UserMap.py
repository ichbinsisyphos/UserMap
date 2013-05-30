#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

from lxml import etree
from pykml import parser
from pykml.factory import KML_ElementMaker as KML
import os
import sys
import time
import random
import string
import codecs
import hexgen


def coordinateString(newLat, newLng):
  """ erzeugt Koordinaten-String aus Gleitkomma-Koordinatenpaar """
  return "%0.7f, %0.7f, %0.7f" % (newLng, newLat, 0.0)

def nameSet(Placemarks):
  """ erzeugt Menge aller Namen aus Liste aller Placemark-Nodes """
  #return [ placemark.name for placemark in Placemarks ]
  names = set()
  for placemark in Placemarks:
    names.add(placemark.name)

  return names

def randString(length):
  """ erzeugt Zufalls-String mit angegebener Länge """
  return "".join(random.choice(string.ascii_lowercase + string.digits) for x in range(length))

def getKmlFilePath():
  """ erzeugt neuen KML-Dateinamen """
  return "var/UserMap_" + randString(6) + "_" + str(int(time.time())) + ".kml"


#Pfad zum lockfile
lockPath = "var/UserMap.lock"
#hier wird der Pfad zur letzten KML-Datei mitgeschrieben
kmlFilenamePath   = "var/kmlFilename.dat"
filep = open(kmlFilenamePath, "r")
#KML-Pfad aus dieser Datei auslesen
kmlFilePath = filep.read()
filep.close()

#Anzahl der Argumente checken, wenn nicht 5, dann Fehlerausgabe und Abbruch
if len(sys.argv) != 5:
  sys.stdout.write("wrong_arguments")
  exit()

#Kommandozeilenargumente auslesen: Reihenfolge Benutzername, Beschreibung, latitude- und longitude- Koordinaten
#single quotes im Eingabeformular verbieten/filtern
newName = sys.argv[1].decode("utf-8")
newDescription = sys.argv[2].decode("utf-8")
newLat = float(sys.argv[3])
newLng = float(sys.argv[4])

#die letzten beiden zum Koordinatenstring zusammenfügen, höhe wird 0 gesetzt
#Achtung: KML verlangt umgekehrte Reihenfolge
#newCoordinates = ",".join((newLng, newLat, "0.0"))

newCoordinates = coordinateString(newLat, newLng)

#wenn lockfile existiert, 1 Sekunde warten und nochmal checken, sonst weiter
while os.path.isfile(lockPath):
  time.sleep(1)

#lockfile erstellen
open(lockPath,"w").close()

#alte KML-Datei in string auslesen 
filep = open(kmlFilePath, "r")
KMLText = filep.read()
filep.close()
del filep

#KML-Baum vom string erzeugen
root = parser.fromstring(KMLText)

#alle placemarks
Placemarks  = root.Document.findall("{http://www.opengis.net/kml/2.2}Placemark")

#Benutzernamen herausfilterm
names = nameSet(Placemarks)


collision = []
for placemark in Placemarks:
  if placemark.Point.true_coordinates == newCoordinates:
    collision.append(placemark)

gen = hexgen.hexgen(newLat,newLng,1e-2)

style = "#single"
if len(collision) > 0:
  style = "#multiple"

  if len(collision) > 5:
    style = "#highdensity"

  #kollisionskorrektur
  for nr,coll in enumerate(collision):
    relocateLat, relocateLng = gen.next()

    coll.styleUrl = KML.styleUrl(style)
    coll.Point.coordinates = KML.coordinates(coordinateString(relocateLat, relocateLng))

#Abfrage ob ein Eintrag unter dem Namen bereits existiert
if newName not in names:
  styleNode = KML.styleUrl(style)
  newPoint = KML.Point(KML.coordinates(newCoordinates))
  newPoint.append(KML.true_coordinates(newCoordinates))
  descNode = etree.Element("description")
  descNode.text = etree.CDATA(newDescription)

  newPlacemark = KML.Placemark(
      KML.name(newName),
      styleNode,
      descNode,
      newPoint
      )

  #im alten Baum dazuhängen
  root.Document.append(newPlacemark)

  #neuen KML-Dateinamen erzeugen: UserMap-prefix, 6 Zufallszeichen und Zeit in Sekunden
  newKmlFilePath = getKmlFilePath()

  #diesen namen in Kontrolldatei schreiben
  filep = open(kmlFilenamePath, "w")
  filep.write(newKmlFilePath)
  filep.close()
  del filep
  
  #unter diesem Dateinamen die KML-Datei neu erzeugen und neunen Baum reinschreiben
  filep = open(newKmlFilePath,"w")
  filep.write('<?xml version="1.0" encoding="UTF-8"?>\n')
  filep.write(etree.tostring(root, pretty_print=True))
  filep.close()
  del filep

  #alte KML-Datei loeschen
  os.remove(kmlFilePath)

  #Erfolgsmeldung ausgeben
  sys.stdout.write("success")
  
else:
  #Benutzername hat schon einen Eintrag
  sys.stdout.write("name_taken")

#Am Schluss lockfile loeschen
os.remove(lockPath)