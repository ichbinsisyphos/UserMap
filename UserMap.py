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


def coordinateString((lat, lng)):
  """ erzeugt Koordinaten-String aus Gleitkomma-Koordinatenpaar """
  return "%0.7f, %0.7f, %0.7f" % (lng, lat, 0.0)

def nameSet(Placemarks):
  """ erzeugt Menge aller Namen aus Liste aller Placemark-Nodes """
  #return [ placemark.name for placemark in Placemarks ]
  names = set()
  for placemark in Placemarks:
    names.add(placemark.name)

  return names

def countrySet(Placemarks):
  """ erzeugt Menge aller Länder aus Liste aller Placemark-Nodes """
  #return [ placemark.name for placemark in Placemarks ]
  countries = set()
  for placemark in Placemarks:
    countries.add(placemark.country.text)

  return countries

def randString(length):
  """ erzeugt Zufalls-String mit angegebener Länge """
  return "".join(random.choice(string.ascii_lowercase + string.digits) for x in range(length))

def getKmlFilePath():
  """ erzeugt neuen KML-Dateinamen """
  return "var/UserMap_" + randString(6) + "_" + str(int(time.time())) + ".kml"


#Anzahl der Argumente checken, wenn nicht 5, dann Fehlerausgabe und Abbruch
if len(sys.argv) != 4:
  sys.stdout.write("wrong_arguments")
  exit()

#Pfad zum lockfile
lockPath = "var/UserMap.lock"
#hier wird der Pfad zur letzten KML-Datei mitgeschrieben
kmlFilenamePath   = "var/kmlFilename.dat"
filep = open(kmlFilenamePath, "r")
#KML-Pfad aus dieser Datei auslesen
kmlFilePath = filep.read()
filep.close()

#hostname und markers
hostnamePath = "hostname.conf"
filep = open(hostnamePath, "r")
hostname = filep.read()
filep.close()
markersBaseUrl = os.path.join(hostname, "UserMap", "markers")
singleMarkerUrl = os.path.join(markersBaseUrl, "single.png")
multipleMarkerUrl = os.path.join(markersBaseUrl, "multiple.png")
highdensityMarkerUrl = os.path.join(markersBaseUrl, "highdensity.png")

#Kommandozeilenargumente auslesen: Reihenfolge Benutzername, Beschreibung, latitude- und longitude- Koordinaten
#single quotes im Eingabeformular verbieten/filtern
newName = sys.argv[1].decode("utf-8")
newDescription = sys.argv[2].decode("utf-8")
newLocation = sys.argv[3].decode("utf-8")

place, coords = newLocation.split("(")
coords = coords.replace(")","")
newLat, newLng = coords.split(",")
newLat = float(newLat)
newLng = float(newLng)
newCountry = place.split(",")[-1].replace(" ","")

#die letzten beiden zum Koordinatenstring zusammenfügen, höhe wird 0 gesetzt
#Achtung: KML verlangt umgekehrte Reihenfolge
#newCoordinates = ",".join((newLng, newLat, "0.0"))

newCoordinates = coordinateString((newLat, newLng))

#wenn lockfile existiert, 1 Sekunde warten und nochmal checken, sonst weiter
loopcount = 0
while os.path.isfile(lockPath):
  if loopcount > 15:
    sys.stdout.write("lockfile_timeout")
    exit()
  time.sleep(1)
  loopcount += 1

#lockfile erstellen
open(lockPath,"w").close()

#alte KML-Datei in string auslesen 
filep = open(kmlFilePath, "r")
KMLText = filep.read()
filep.close()
del filep

#KML-Baum vom string erzeugen
root = parser.fromstring(KMLText)
#print root.Document.getchildren()
#alle placemarks
Placemarks  = root.Document.findall("{http://www.opengis.net/kml/2.2}Placemark")
#Abfrage ob ein Eintrag unter dem Namen bereits existiert
if newName not in nameSet(Placemarks):
  styleNode = KML.styleUrl("#single")
  newPoint = KML.Point(KML.coordinates(newCoordinates))
  newPoint.append(KML.true_coordinates(newCoordinates))
  descNode = etree.Element("description")
  descNode.text = etree.CDATA(newDescription)
  #CDATA überlebt das spätere einlesen und neu schreiben nicht! mal Richtung XML schauen
  countryNode = KML.country(newCountry)
  newPlacemark = KML.Placemark(
      KML.name(newName),
      styleNode,
      countryNode,
      descNode,
      newPoint
      )

  Placemarks.append(newPlacemark)
  
  collision = [ placemark for placemark in Placemarks if placemark.Point.true_coordinates and placemark.Point.true_coordinates == newCoordinates ]

  if len(collision) > 1:
    style = "#multiple"

    if len(collision) > 5:
      style = "#highdensity"

    #kollisionskorrektur
    coordinateGen = hexgen.hexgen(newLat,newLng,1e-2)
    for placemark in collision:
      placemark.styleUrl = KML.styleUrl(style)
      placemark.Point.coordinates = KML.coordinates(coordinateString(coordinateGen.next()))

  #unter diesem Dateinamen die KML-Datei neu erzeugen und neunen Baum reinschreiben
  mapTitle  = (root.Document.findall("{http://www.opengis.net/kml/2.2}name")[0].text).decode("UTF-8")
  mapDescription  = (root.Document.findall("{http://www.opengis.net/kml/2.2}description")[0].text).encode("UTF-8")
  root.Document.clear()
  root.Document.append(KML.name(mapTitle))
  root.Document.append(KML.description(mapDescription.decode("UTF-8")))
  
  #problem: bei eigenen styles wird das Markerbild am Ort zentriert
  # singleStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href(singleMarkerUrl))), id = "single")
  # multipleStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href(multipleMarkerUrl))), id = "multiple")
  # highdensityStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href(highdensityMarkerUrl))), id = "highdensity")
  singleStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href("http://maps.gstatic.com/mapfiles/ms2/micons/blue-dot.png"))), id = "single")
  multipleStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href("http://maps.gstatic.com/mapfiles/ms2/micons/purple-dot.png"))), id = "multiple")
  highdensityStyle = KML.Style(KML.IconStyle(KML.Icon(KML.href("http://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png"))), id = "highdensity")

  root.Document.append(singleStyle)
  root.Document.append(multipleStyle)
  root.Document.append(highdensityStyle)

  Placemarks.sort(key=lambda placemark: placemark.name.text)
  for placemark in Placemarks:
    root.Document.append(placemark)


  # countryNames = countrySet(Placemarks)
  # print countryNames

  # filep = open("countries.kml", "r")
  # countryText = filep.read()
  # filep.close()
  # del filep

  # countryRoot = parser.fromstring(countryText)
  # countryPlacemarks = countryRoot.Document.findall("{http://www.opengis.net/kml/2.2}Placemark")
  # for countryPlacemark in countryPlacemarks:
  #   if countryPlacemark.name in countryNames:
  #     polycolor = KML.color("19222288")
  #     linecolor = KML.color("19222288")

  #     oldColor = countryPlacemark.Style.PolyStyle.findall("{http://www.opengis.net/kml/2.2}color")[0]
  #     countryPlacemark.Style.PolyStyle.remove(oldColor)
  #     countryPlacemark.Style.PolyStyle.append(polycolor)

  #     oldColor = countryPlacemark.Style.LineStyle.findall("{http://www.opengis.net/kml/2.2}color")[0]
  #     countryPlacemark.Style.LineStyle.remove(oldColor)
  #     countryPlacemark.Style.LineStyle.append(linecolor)

  #     root.Document.append(countryPlacemark)


  #neuen KML-Dateinamen erzeugen: UserMap-prefix, 6 Zufallszeichen und Zeit in Sekunden
  newKmlFilePath = getKmlFilePath()

  #diesen namen in Kontrolldatei schreiben
  filep = open(kmlFilenamePath, "w")
  filep.write(newKmlFilePath)
  filep.close()
  del filep

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