#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

#TODO: CHAOS BESEITIGEN, EVT ZLIB STATT ZIPFILE FALLS MÖGLICH
#TODO: BACKUP MUSS MAX ANZAHL FILES UND/ODER MAX SPEICHERPLATZ EINHALTEN
#TODO: DAS SELBE FÜR DIE LOGDATEI

import math
from lxml import etree
from pykml import parser
from pykml.factory import KML_ElementMaker as KML
import os
import sys
import time
import random
import string
import codecs
import zipfile #zlib?
import shutil

hostnamePath    = "hostname.conf"
lockPath        = "var/UserMap.lock"
logPath         = "var/UserMap.log"
maxLogFilesize  = 1024*1024
maxLogLines     = 1000
kmlFilenamePath = "var/kmlFilename.dat"
countryKMLPath  = "countries.kml"
collisionDist   = 1e-2

def writeLog(action):
  """ schreibt logfile mit neuem Eintrag neu, unter Einhaltung der maximalen Anzahl an Einträgen und der maximalen Dateigröße """
  lock() #hmm... blockt auch Aktionen mit Schreibzugriff

  if os.path.isfile(logPath):
    fileobj = open(logPath, "r")
    logLines = fileobj.readlines()
    fileobj.close()
    del fileobj
  else:
    logLines = []

  if (len(logLines) >= maxLogLines) or (os.path.isfile(logPath) and (os.path.getsize(logPath) >= maxLogFilesize)):
    logLines = logLines[1:]
  logLines.append(action.logMessage().encode("UTF-8", "replace") + "\n")

  fileobj = open(logPath, "w")
  fileobj.writelines(logLines)
  fileobj.close()
  del fileobj

  unlock()

def assembleTree(hostname, root, Placemarks):
  """ erstellt den KML-tree neu, inklusive Mapnamen, -beschreibung, user- und country-placemarks """
  mapTitle = getMapTitle(root)
  mapDescription = getMapDescription(root)
  root.Document.clear()
  root.Document.append(KML.name(mapTitle.encode("UTF-8")))
  root.Document.append(KML.description(mapDescription))

  for placemark in Placemarks:
    root.Document.append(placemark)

  for countryNode in getCountryNodes(Placemarks, hostname):
    root.Document.append(countryNode)

  return root

def correctCollision(hostname, Placemarks, newLat, newLng):
  """ korrigiert Kollisionen für die angegebenen Koordinaten """
  collision = getCollision(Placemarks, newLat, newLng)
  styleID = "#single"

  if len(collision) > 1:
    styleID = "#multiple"

    if len(collision) > 5:
      styleID = "#highdensity"

  coordinateGen = hexgen(newLat, newLng, collisionDist)
  for placemark in collision:
    placemark.styleUrl = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + styleID)
    placemark.Point.coordinates = KML.coordinates(coordinateString(coordinateGen.next()))

def addNewPlacemark(Placemarks, hostname, newName, newLocationString, newLat, newLng, newCountry, newDescription):
  """ fügt neue Benutzer-placemark mit angegebenen Parametern hinzu, Kollisionscheck inklusive """
  newPlacemark = createNewPlacemark(hostname, newName, newLocationString, newLat, newLng, newCountry, newDescription)
  Placemarks.append(newPlacemark)
  Placemarks.sort(key=lambda placemark: placemark.name.text.lower())
  correctCollision(hostname, Placemarks, newLat, newLng)

def getMapTitle(root):
  """ extrahiert den Namen der Map aus KML-tree """
  return root.Document.find("{http://www.opengis.net/kml/2.2}name").text.decode("UTF-8")

def getMapDescription(root):
  """ extrahiert Kartenbeschreibung aus KML-tree """
  return root.Document.find("{http://www.opengis.net/kml/2.2}description").text

def backup(kmlFilePath):
  """ kopiert alte .kml und .kmz-Datei ins backup-Verzeichnis und löscht die Originale """
  shutil.copy(kmlFilePath + ".kml", "var/backup/" + kmlFilePath.split("/")[-1] + ".kml")
  shutil.copy(kmlFilePath + ".kmz", "var/backup/" + kmlFilePath.split("/")[-1] + ".kmz")
  os.remove(kmlFilePath + ".kml")
  os.remove(kmlFilePath + ".kmz")

def writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath):
  """ schreibt den neuen KML-tree in neue *.kml-Datei, komprimiert ihn zu *.kmz und ruft backup-Funktion auf """
  root = assembleTree(hostname, root, Placemarks)

  newKmlFilePath = getNewKmlFilePath()##hier auch
  fileobj = open(newKmlFilePath + ".kml", "w")
  xmlText = '<?xml version="1.0" encoding="UTF-8"?>\n' + etree.tostring(root, pretty_print=True)
  fileobj.write(xmlText)
  fileobj.close()
  del fileobj
  
  fileobj = zipfile.ZipFile(newKmlFilePath + ".kmz", "w")
  fileobj.write(newKmlFilePath + ".kml", compress_type=zipfile.ZIP_DEFLATED)
  fileobj.close()
  del fileobj

  backup(kmlFilePath)

def getNewKmlFilePath():
  """ erstellt neunen *.kml-Dateinamen aus Zeit und Zufallsstring """
  newKmlFilePath = "var/UserMap_%s_%i" % (randString(6), time.time())

  #diesen namen in Kontrolldatei schreiben
  fileobj = open(kmlFilenamePath, "w")
  fileobj.write(newKmlFilePath)
  fileobj.close()
  del fileobj

  return newKmlFilePath

def getCountryNodes(Placemarks, hostname):
  """ extrahiert für in der Karte verwendete Länder die Country-Placemarks (Polygone) aus countryKMLPath aus, passt sie an und liefert sie als Liste zurück """
  countryNames = countrySet(Placemarks)

  fileobj = open(countryKMLPath, "r")
  countryText = fileobj.read()
  fileobj.close()
  del fileobj

  countryNodeList = []

  countryRoot = parser.fromstring(countryText)
  countryPlacemarks = countryRoot.Document.findall("{http://www.opengis.net/kml/2.2}Placemark")
  
  for countryPlacemark in countryPlacemarks:
    if countryPlacemark.name.text in countryNames:
      point = countryPlacemark.MultiGeometry.find("{http://www.opengis.net/kml/2.2}Point")
      countryPlacemark.MultiGeometry.remove(point)
      newStyleUrl = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + "#country")
      countryPlacemark.append(newStyleUrl)
      typeNode = KML.type("country")
      countryPlacemark.append(typeNode)

      countryNodeList.append(countryPlacemark)
      
  return countryNodeList


def createNewPlacemark(hostname, newName, newLocationString, newLat, newLng, newCountry, newDescription):
  """ erzeugt neue Benutzer-placemark mit angegebenen Parametern und liefert sie zur weiteren Verwendung zurück """

  styleNode = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + "#single")
  typeNode = KML.type("user")
  newPoint =  KML.Point(
                  KML.coordinates(
                      coordinateString(
                          (newLat, newLng)
                      )
                  )
              )

  newPoint.append(KML.true_coordinates(coordinateString((newLat, newLng))))
  descNode = etree.Element("description")
  descNode.text = etree.CDATA(newDescription)
  #CDATA überlebt das spätere einlesen und neu schreiben nicht! mal Richtung XML schauen
  countryNode = KML.country(newCountry)
  locationStringNode = KML.locationString(newLocationString)

  newPlacemark = KML.Placemark(
      KML.name(newName),
      styleNode,
      typeNode,
      locationStringNode,
      countryNode,
      descNode,
      newPoint
      )

  return newPlacemark

def getCollision(Placemarks, newLat, newLng):
  """ liefert die Benutzer-placemarks zurück, die an den angegebenen Koordinaten kollidieren """
  return [ placemark for placemark in Placemarks if placemark.type.text == "user" and placemark.Point.true_coordinates == coordinateString((newLat, newLng)) ]

def parseArgs(args):
  """ sys.argv-Kommandozeilenargumente auslesen und aufbereiten, als Tupel zurückliefern """
  newName = args[0].decode("utf-8")
  newDescription = args[1].decode("utf-8")
  newLocation = args[2].decode("utf-8")

  place, coords = newLocation.split("(")
  coords = coords.replace(")","")
  newLat, newLng = coords.split(",")
  newLat = float(newLat)
  newLng = float(newLng)
  newCountry = place.split(",")[-1].replace(" ","")
  
  return (newName, newDescription, newCountry, newLat, newLng)

def getPlacemarks(root):
  """ liefert eine Liste mit allen Benutzer-placemarks """
  return [ placemark for placemark in root.Document.findall("{http://www.opengis.net/kml/2.2}Placemark") if placemark.type.text == "user" ]

def parseKml(kmlFilePath):
  """ liest *.kml-Datei aus und liefert geparsten KML-tree zurück """
  fileobj = open(kmlFilePath + ".kml", "r")
  KMLText = fileobj.read()
  fileobj.close()
  del fileobj

  return parser.fromstring(KMLText)

def getKmlFilePath():
  """ liest alten *.kml-Dateinamen as kmlFilenamePath aus und liefert ihn zurück """
  fileobj = open(kmlFilenamePath, "r")
  kmlFilePath = fileobj.read()
  fileobj.close()

  return kmlFilePath

def getHostname():
  """ liest hostname aus hostnamePath aus und lierfert ihn zurück """
  fileobj = open(hostnamePath, "r")
  hostname = fileobj.read()
  fileobj.close()
  del fileobj

  return hostname

def unlock():
  """ löscht lockfile falls vorhanden """
  #Am Schluss lockfile loeschen
  if os.path.isfile(lockPath):  
    os.remove(lockPath)

def lock():
  """ erstellt lockfile wenn es nicht existiert, sonst 1 Sekunde warten und erneut checken """
  loopcount = 0
  while os.path.isfile(lockPath):
    if loopcount > 15:
      sys.stdout.write("lockfile_timeout")
      exit()
    time.sleep(1)
    loopcount += 1

  open(lockPath,"w").close()

def coordinateString((lat, lng)):
  """ erzeugt Koordinaten-String aus Gleitkomma-Koordinatenpaar """
  return "%0.7f, %0.7f, %0.7f" % (lng, lat, 0.0)

def getNameNode(name, Placemarks):
    return next( (placemark for placemark in Placemarks if unicode(placemark.name.text) == name), None )

def nameExists(name, Placemarks):
  #return ( name in nameSet(Placemarks) )
  return (getNameNode(name, Placemarks) is not None)

def nameSet(Placemarks):
  """ erzeugt Menge aller Namen aus Liste aller Placemark-Nodes """
  return set(nameList(Placemarks))

def nameList(Placemarks):
  """ erzeugt Liste aller Namen aus Liste aller Placemark-Nodes """
  return [ unicode(placemark.name) for placemark in Placemarks if placemark.type.text == "user" ]

def countrySet(Placemarks):
  """ erzeugt Menge aller Länder aus Liste aller Placemark-Nodes """
  return set([ placemark.country.text for placemark in Placemarks if placemark.type.text == "user" ])

def randString(length):
  """ erzeugt Zufalls-String mit angegebener Länge """
  return "".join(random.choice(string.ascii_lowercase + string.digits) for x in range(length))

def hexgen(startx, starty, mindist):
  """ generator zum erzeugen von Koordinaten in einem Hexagonalgitter rund um die originalen Koordinaten im Falle von Kollisionen """
  corners = [ (round(math.cos(angle*math.pi/180.),3),round(math.sin(angle*math.pi/180.),3)) for angle in range(0,360,60) ]
  layer = 0
  interpolate = 0
  layerstart = 0
  index = 0

  while True:
    perlayer = layer*6
    if perlayer < 1: perlayer = 1

    if index == perlayer + layerstart:
      layerstart = index
      layer += 1
      perlayer = layer*6
    
    interpolate = layer - 1
    if interpolate < 0: interpolate = 0
    layerindex = index - layerstart

    if interpolate < 1 or not layerindex%(interpolate+1):
      cornerindex = layerindex/(interpolate+1)
      corner = "corner " + str(cornerindex)
      x,y = corners[cornerindex]
      x *= mindist*(2./3.)*layer
      y *= mindist*layer

    else:
      interpolateindex = layerindex%(interpolate + 1) 
      prevcornerindex = layerindex/(interpolate + 1)
      nextcornerindex = prevcornerindex + 1
      if nextcornerindex > 5:
        nextcornerindex = 0
      prevx,prevy = corners[prevcornerindex]
      nextx,nexty = corners[nextcornerindex]

      diffx = nextx - prevx
      diffy = nexty - prevy

      x = mindist*(2./3.)*layer*(prevx + diffx*interpolateindex/(interpolate+1))
      y = mindist*layer*(prevy + diffy*interpolateindex/(interpolate+1))

    index += 1
    yield (startx+x,starty+y)