#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

#TODO: CHAOS BESEITIGEN, EVT ZLIB STATT ZIPFILE FALLS MÖGLICH

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
kmzFilenamePath = "var/kmzFilename.dat"
countryKMLPath  = "countries.kml"
collisionDist   = 1e-2

def assembleTree(hostname, root, Placemarks):
  mapTitle = getMapTitle(root)
  mapDescription = getMapDescription(root)
  root.Document.clear()
  root.Document.append(KML.name(mapTitle))
  root.Document.append(KML.description(mapDescription.decode("UTF-8")))

  for placemark in Placemarks:
    root.Document.append(placemark)

  for countryNode in getCountryNodes(Placemarks, hostname):
    root.Document.append(countryNode)

  return root

def correctCollision(hostname, Placemarks, newLat, newLng):
  collision = getCollision(Placemarks, newLat, newLng)

  if len(collision) > 1:
    styleID = "#multiple"

    if len(collision) > 5:
      styleID = "#highdensity"

    #kollisionskorrektur
    coordinateGen = hexgen(newLat, newLng, collisionDist)
    for placemark in collision:
      placemark.styleUrl = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + styleID)
      placemark.Point.coordinates = KML.coordinates(coordinateString(coordinateGen.next()))

def addNewPlacemark(Placemarks, hostname, newName, newLat, newLng, newCountry, newDescription):
  newPlacemark = createNewPlacemark(hostname, newName, newLat, newLng, newCountry, newDescription)
  Placemarks.append(newPlacemark)
  Placemarks.sort(key=lambda placemark: placemark.name.text.lower())
  correctCollision(hostname, Placemarks, newLat, newLng)

def getMapTitle(root):
  return (root.Document.findall("{http://www.opengis.net/kml/2.2}name")[0].text).decode("UTF-8")

def getMapDescription(root):
  return (root.Document.findall("{http://www.opengis.net/kml/2.2}description")[0].text).encode("UTF-8")

def backup(kmlFilePath, kmzFilePath):
  shutil.copy(kmlFilePath, "var/backup/" + kmlFilePath.split("/")[-1])
  shutil.copy(kmzFilePath, "var/backup/" + kmzFilePath.split("/")[-1])
  os.remove(kmlFilePath)
  os.remove(kmzFilePath)

def writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath, kmzFilePath):
  root = assembleTree(hostname, root, Placemarks)

  newKmlFilePath, newKmzFilePath = getNewKmlKmz()
  filep = open(newKmlFilePath,"w")
  xmlText = '<?xml version="1.0" encoding="UTF-8"?>\n' + etree.tostring(root, pretty_print=True)
  filep.write(xmlText)
  filep.close()
  del filep
  
  filep = zipfile.ZipFile(newKmzFilePath, 'w')
  filep.write(newKmlFilePath, compress_type=zipfile.ZIP_DEFLATED)
  filep.close()
  del filep

  backup(kmlFilePath, kmzFilePath)

def getNewKmlKmz():
  newKmlFilePath = getKmlFilePath()
  newKmzFilePath = newKmlFilePath[:-4] + ".kmz"

  #diesen namen in Kontrolldatei schreiben
  filep = open(kmzFilenamePath, "w")
  filep.write(newKmzFilePath)
  filep.close()
  del filep

  return (newKmlFilePath, newKmzFilePath)

def getCountryNodes(Placemarks, hostname):
  countryNames = countrySet(Placemarks)

  filep = open(countryKMLPath, "r")
  countryText = filep.read()
  filep.close()
  del filep

  countryNodeList = []

  countryRoot = parser.fromstring(countryText)
  countryPlacemarks = countryRoot.Document.findall("{http://www.opengis.net/kml/2.2}Placemark")
  
  for countryPlacemark in countryPlacemarks:
    if countryPlacemark.name.text in countryNames:
      newStyleUrl = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + "#country")
      countryPlacemark.append(newStyleUrl)
      typeNode = KML.type("country")
      countryPlacemark.append(typeNode)

      countryNodeList.append(countryPlacemark)
      
  return countryNodeList


def createNewPlacemark(hostname, newName, newLat, newLng, newCountry, newDescription):
  styleNode = KML.styleUrl(hostname + "/UserMap/" + "styles.kml" + "#single")
  typeNode = KML.type("user")
  newPoint = KML.Point(KML.coordinates(coordinateString((newLat, newLng))))
  newPoint.append(KML.true_coordinates(coordinateString((newLat, newLng))))
  descNode = etree.Element("description")
  descNode.text = etree.CDATA(newDescription)
  #CDATA überlebt das spätere einlesen und neu schreiben nicht! mal Richtung XML schauen
  countryNode = KML.country(newCountry)
  newPlacemark = KML.Placemark(
      KML.name(newName),
      styleNode,
      typeNode,
      countryNode,
      descNode,
      newPoint
      )

  return newPlacemark

def getCollision(Placemarks, newLat, newLng):
  return [ placemark for placemark in Placemarks if placemark.type.text == "user" and placemark.Point.true_coordinates == coordinateString((newLat, newLng)) ]

def parseArgs(args):
  action = args[0].decode("utf-8")
  newName = args[1].decode("utf-8")
  newDescription = args[2].decode("utf-8")
  newLocation = args[3].decode("utf-8")

  place, coords = newLocation.split("(")
  coords = coords.replace(")","")
  newLat, newLng = coords.split(",")
  newLat = float(newLat)
  newLng = float(newLng)
  newCountry = place.split(",")[-1].replace(" ","")
  
  return (action, newName, newDescription, newCountry, newLat, newLng)

def getPlacemarks(root):
  return [ placemark for placemark in root.Document.findall("{http://www.opengis.net/kml/2.2}Placemark") if placemark.type.text == "user" ]

def parseKml(kmlFilePath):
  filep = open(kmlFilePath, "r")
  KMLText = filep.read()
  filep.close()
  del filep

  return parser.fromstring(KMLText)


def getKmlKmz():
  filep = open(kmzFilenamePath, "r")
  kmzFilePath = filep.read()
  filep.close()

  return (kmzFilePath[:-4] + ".kml", kmzFilePath)

def getHostname():
  filep = open(hostnamePath, "r")
  hostname = filep.read()
  filep.close()

  return hostname

def unlock():
  #Am Schluss lockfile loeschen
  os.remove(lockPath)

def lock():
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

def coordinateString((lat, lng)):
  """ erzeugt Koordinaten-String aus Gleitkomma-Koordinatenpaar """
  return "%0.7f, %0.7f, %0.7f" % (lng, lat, 0.0)

def nameSet(Placemarks):
  """ erzeugt Menge aller Namen aus Liste aller Placemark-Nodes """
  #return [ placemark.name for placemark in Placemarks ]
  names = set()
  for placemark in Placemarks:
    if placemark.type.text == "user":
      names.add(placemark.name)

  return names

def countrySet(Placemarks):
  """ erzeugt Menge aller Länder aus Liste aller Placemark-Nodes """
  #return [ placemark.name for placemark in Placemarks ]
  countries = set()
  for placemark in Placemarks:
    if placemark.type.text == "user":
      countries.add(placemark.country.text)

  return countries

def randString(length):
  """ erzeugt Zufalls-String mit angegebener Länge """
  return "".join(random.choice(string.ascii_lowercase + string.digits) for x in range(length))

def getKmlFilePath():
  """ erzeugt neuen KML-Dateinamen """
  #return "var/UserMap_%s_%i.kml" % (randString(6), time.time())
  return "var/UserMap_%s_%i.kml" % (randString(6), time.time())

def getKmzFilePath():
  """ erzeugt neuen KMZ-Dateinamen (gezippte Datei) """
  return getKmlFilePath()[:-4] + ".kmz"


def hexgen(startx, starty, mindist):
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