#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

import sys
from UserMapfunctions import *

if __name__ == "__main__":
  if sys.argv[1] == "add" and len(sys.argv) == 5: #add new user entry
    kmlFilePath = getKmlFilePath()
    hostname = getHostname()
    newName, newDescription, newCountry, newLat, newLng = parseArgs(sys.argv[2:])

    lock()

    root = parseKml(kmlFilePath)
    Placemarks = getPlacemarks(root)

    if newName not in nameSet(Placemarks):
      addNewPlacemark(Placemarks, hostname, newName, newLat, newLng, newCountry, newDescription)
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      sys.stdout.write("success")

    else:
      #Benutzername hat schon einen Eintrag
      sys.stdout.write("name_taken")

    unlock()

  elif sys.argv[1] == "updateDescription" and len(sys.argv) == 4: #add new user entry
    kmlFilePath = getKmlFilePath()
    hostname = getHostname()
    requestName = sys.argv[2].decode("UTF-8")
    newDescription = sys.argv[3].decode("UTF-8")

    lock()

    root = parseKml(kmlFilePath)
    Placemarks = getPlacemarks(root)


    nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == requestName ]
    if len(nameNodes) == 0:
     sys.stdout.write("name_not_found")

    elif len(nameNodes) == 1:
      nameNode = nameNodes[0]
      nameNode.description = KML.description(newDescription)
      #Placemarks.remove(nameNode)
      #addNewPlacemark(Placemarks, hostname, requestName, newLat, newLng, newCountry, newDescription)
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      sys.stdout.write("success")

    unlock()


  elif sys.argv[1] == "namelist" and len(sys.argv) == 2: #return a list of all names
    kmlFilePath = getKmlFilePath()

    lock()

    Placemarks = getPlacemarks(parseKml(kmlFilePath))
    sys.stdout.write("$&$".join(nameList(Placemarks)))

    unlock()

  elif sys.argv[1] == "rebuild" and len(sys.argv) == 2: #rebuild kml as it is - effectively just giving it a new name that google maps accepts
    kmlFilePath = getKmlFilePath()
    hostname = getHostname()

    lock()
    try:
      root = parseKml(kmlFilePath)
      Placemarks = getPlacemarks(root)

      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      sys.stdout.write("success")
    except:
      sys.stdout.write("error")

    unlock()

  elif sys.argv[1] == "forname" and len(sys.argv) == 3: #return a list of all names
    requestName = sys.argv[2].decode("UTF-8")
    kmlFilePath = getKmlFilePath()

    lock()

    Placemarks = getPlacemarks(parseKml(kmlFilePath))
    nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == requestName ]
    if len(nameNodes) == 0:
     sys.stdout.write("name_not_found") 

    elif len(nameNodes) == 1:
      nameNode = nameNodes[0]
      #name    = nameNode.name.text
      desc    = nameNode.description.text
      if desc == None:
        desc = ""
      country = nameNode.country.text
      coords  = nameNode.Point.true_coordinates.text
      sys.stdout.write("$&$".join((desc, country, coords)))
    else:
      sys.stdout.write("error")
    #sys.stdout.write("$&$".join(nameList(Placemarks)))

    unlock()


  elif sys.argv[1] == "removename" and len(sys.argv) == 3: #return a list of all names
    requestName = sys.argv[2].decode("UTF-8")
    kmlFilePath = getKmlFilePath()
    hostname = getHostname()

    lock()

    root = parseKml(kmlFilePath)
    Placemarks = getPlacemarks(root)
    nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == requestName ]
    if len(nameNodes) == 0:
     sys.stdout.write("name_not_found") 

    elif len(nameNodes) == 1:
      nameNode = nameNodes[0]
      Placemarks.remove(nameNode)
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      sys.stdout.write("success")
    else:
      sys.stdout.write("error")

    unlock()





  else:
    sys.stdout.write("wrong_arguments")