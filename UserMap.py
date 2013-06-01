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

  else:
    sys.stdout.write("wrong_arguments")