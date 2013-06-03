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
        sys.stdout.write("name_taken")

    elif action.type == Actions.Actions.overwrite:
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       sys.stdout.write("name_not_found") 

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")

        Placemarks.remove(nameNode)
        correctCollision(hostname, Placemarks, float(lat), float(lng))
        addNewPlacemark(Placemarks, hostname, action.name, action.lat, action.lng, action.country, action.desc)

        sys.stdout.write("success")

    elif action.type == Actions.Actions.namelist:
      sys.stdout.write("$&$".join(nameList(Placemarks)))

    elif action.type == Actions.Actions.rebuild:
        sys.stdout.write("success")

    elif action.type == Actions.Actions.forname:
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       sys.stdout.write("name_not_found") 

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        desc    = nameNode.description.text
        if not desc:
          desc = ""
        country = nameNode.country.text
        sys.stdout.write("$&$".join((desc, country)))
      else:
        sys.stdout.write("error")

    elif action.type == Actions.Actions.updateDescription:
      exit() #vorübergehend deaktiviert

      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       sys.stdout.write("name_not_found")

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        nameNode.description = KML.description(action.desc)
        sys.stdout.write("success")

    elif action.type == Actions.Actions.removename:
      exit() #vorübergehend deaktiviert
      nameNodes  = [ placemark for placemark in Placemarks if unicode(placemark.name.text) == action.name ]
      if len(nameNodes) == 0:
       sys.stdout.write("name_not_found") 

      elif len(nameNodes) == 1:
        nameNode = nameNodes[0]
        lng,lat,alt = nameNode.Point.true_coordinates.text.split(",")

        Placemarks.remove(nameNode)
        correctCollision(hostname, Placemarks, float(lat), float(lng))

        sys.stdout.write("success")
      else:
        sys.stdout.write("error")

    if not action.readOnly:
      writeNewKmlKmz(hostname, root, Placemarks, kmlFilePath)
      unlock()

  else:
    sys.stdout.write("wrong_arguments")