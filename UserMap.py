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
      if action.name not in nameSet(Placemarks):
        addNewPlacemark(Placemarks, hostname, action.name, action.lat, action.lng, action.country, action.desc)
      else:
        action.error = "name_taken"

    elif action.type == Actions.Actions.overwrite:
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       action.error = "name_not_found"

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")

        Placemarks.remove(nameNode)
        correctCollision(hostname, Placemarks, float(lat), float(lng))
        addNewPlacemark(Placemarks, hostname, action.name, action.lat, action.lng, action.country, action.desc)

    elif action.type == Actions.Actions.namelist:
      sys.stdout.write("$&$".join(nameList(Placemarks)))

    elif action.type == Actions.Actions.rebuild:
      pass
    elif action.type == Actions.Actions.forname:
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       action.error = "name_not_found"

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        desc    = nameNode.description.text
        if not desc:
          desc = ""
        country = nameNode.country.text
        sys.stdout.write("$&$".join((desc, country)))
      else:
        action.error = "error"

    elif action.type == Actions.Actions.updateDescription:
      exit() #vorübergehend deaktiviert

      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       action.error = "name_not_found"

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        nameNode.description = KML.description(action.desc)

    elif action.type == Actions.Actions.removename:
      exit() #vorübergehend deaktiviert
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       action.error = "name_not_found"

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")

        Placemarks.remove(nameNode)
        correctCollision(hostname, Placemarks, float(lat), float(lng))

      else:
        action.error = "error"

    if not action.readOnly:
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      unlock()

if action.error:
  sys.stdout.write(action.error)
elif not action.readOnly:
    sys.stdout.write("success")