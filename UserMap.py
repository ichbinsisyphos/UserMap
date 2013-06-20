#!/usr/bin/env python
# -*- coding: utf-8 -*- 

#external dependencies: pykml (via pip)

import sys
from UserMapfunctions import *
import Actions

if __name__ == "__main__":
  action = Actions.Action(sys.argv)

  if action.type != Actions.Actions.undefined:
    kmlFilePath = getKmlFilePath()
    root = parseKml(kmlFilePath)
    Placemarks = getPlacemarks(root)
    if not action.readOnly:
      hostname = getHostname()
      lock()

    if action.type == Actions.Actions.add:
      if not nameExists(action.name, Placemarks):
        addNewPlacemark(Placemarks, hostname, action.name, action.locationString, action.lat, action.lng, action.country, action.desc)
      else:
        action.error = "name_taken"

    elif action.type == Actions.Actions.overwrite:
      nameNode = getNameNode(action.name, Placemarks)
      if nameNode is not None:
        # lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")
        Placemarks.remove(nameNode)
      # correctCollision(hostname, Placemarks, float(lat), float(lng))
      addNewPlacemark(Placemarks, hostname, action.name, action.locationString, action.lat, action.lng, action.country, action.desc)

    elif action.type == Actions.Actions.namelist:
      sys.stdout.write( ("$&$".join(nameList(Placemarks))).encode("UTF-8"))

    elif action.type == Actions.Actions.rebuild:
      pass #tree tatsächlich neu aufbauen (inkl. Kollisionskorrektur etc.)
           #nicht nur einlesen und schreiben

    elif action.type == Actions.Actions.forname:
      nameNode  = getNameNode(action.name, Placemarks)
      if nameNode is None:
        action.error = "name_not_found"
      else:
        desc = nameNode.description.text
        locationString = ""
        try:
          locationString = nameNode.locationString.text
        except:
          pass
        if not desc:
          desc = ""
        #country = nameNode.country.text
        sys.stdout.write( ("$&$".join((locationString, desc))).encode("UTF-8") )

    elif action.type == Actions.Actions.updateDescription:
      #wird in admin.php noch benutzt, muss aber weg -> ersetzen mit overwrite
      exit() #vorübergehend deaktiviert
      nameNode = getNameNode(action.name, Placemarks)
      if nameNode is None:
        action.error = "name_not_found"
      else:
        nameNode.description = KML.description(action.desc)

    elif action.type == Actions.Actions.removename:
      exit() #vorübergehend deaktiviert
      nameNode = getNameNode(action.name, Placemarks)
      if nameNode is None:
        action.error = "name_not_found"
      else:
        lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")
        Placemarks.remove(nameNode)
        correctCollision(hostname, Placemarks, float(lat), float(lng))

    if not action.readOnly:
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      unlock()

if action.error:
  sys.stdout.write((action.error).encode("UTF-8"))
elif not action.readOnly:
    sys.stdout.write(("success").encode("UTF-8"))

writeLog(action)