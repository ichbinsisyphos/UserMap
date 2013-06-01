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

  elif sys.argv[1] == "namelist" and len(sys.argv) == 2: #return a list of all names
    kmlFilePath = getKmlFilePath()

    lock()

    Placemarks = getPlacemarks(parseKml(kmlFilePath))
    sys.stdout.write("$&$".join(nameList(Placemarks)))

    unlock()

  elif sys.argv[1] == "forname" and len(sys.argv) == 3: #return a list of all names
    name = sys.argv[2].decode("UTF-8")
    kmlFilePath = getKmlFilePath()

    lock()

    Placemarks = getPlacemarks(parseKml(kmlFilePath))
    #sys.stdout.write("$&$".join(nameList(Placemarks)))

    unlock()

  else:
    sys.stdout.write("wrong_arguments")