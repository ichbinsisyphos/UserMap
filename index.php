<!DOCTYPE html>

<html>
  <head>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap</title>
    <script language="JavaScript" src="UserMap.js" type="text/javascript"></script>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAV0Nn3tQ2R4ECUmItAruwylO-CzZrjEUQ&sensor=false">
    </script>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script language="JavaScript" type="text/javascript">
      var geocoder;
      var map;
      var marker = null;
      var hidden;
      var myPos;
      var myZoom;
      var kmzLink;
      var markerPosition;

      function savePos() {
          myPos = map.getCenter();
          myZoom = map.getZoom();
      }

      function restorePos() {
          map.setCenter(myPos);
          map.setZoom(myZoom);
      }

      function initialize() {
        googleLink = document.getElementsByTagName("a")[0].href;
        temp = googleLink.split("/maps?q=")[1];
        kmzLink = temp.split(".kmz")[0] + ".kmz";

        geocoder = new google.maps.Geocoder();
        hidden = true;
        $("#register").hide();

        var latlng = new google.maps.LatLng(47.8571,12.1181);
        var mapOptions = {
          zoom: 5,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById("map"), mapOptions);
        toggleButton();






  var ctaLayer = new google.maps.KmlLayer({
    url: kmzLink
  });
  ctaLayer.setMap(map);





      }

      function codeAddress() {
        var address = document.getElementById("locationInput").value;
        if (address.length < 3) {
          alert("Bitte gib einen Suchbegriff mit mindestens 3 Zeichen ein.");
        }
        else {
          var select = document.submitForm.locationSelect;

          for(i = select.options.length-1; i >= 0; i--) {
            select.remove(i);
          }

          geocoder.geocode( { 'address': address }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              for(i=0;i<results.length;i++) {
                var place = results[i].formatted_address;
                var lat = results[i].geometry.location.lat().toFixed(7);
                var lng = results[i].geometry.location.lng().toFixed(7);
                var locstring = place + " (" + lat + "," + lng + ")";
                markerPosition = locstring;

                var option = document.createElement('option');
                option.setAttribute("value", locstring);
                option.innerHTML = locstring;
                select.options[i] = option
              }
              map.setCenter(results[0].geometry.location);
              // marker = new google.maps.Marker({
              //   map: map,
              //   position: results[0].geometry.location
              // });
              setMarker();
            } else {
              alert("Geocode was not successful for the following reason: " + status);
            }
          });
        }
      }

      function setMarker() {
        if(marker != null) {
         marker.setMap(null);
        }
        locstring = document.submitForm.locationSelect.value;
        locarray = locstring.split("(");
        place = locarray[0];
        latlng = locarray[1].replace(")","").split(",");
        lat = parseFloat(latlng[0]);
        lng = parseFloat(latlng[1]);
        var latlng = new google.maps.LatLng(lat,lng);
        map.setCenter(latlng);
        marker = new google.maps.Marker({
          map: map,
          position: latlng
        });
        map.setCenter(latlng);
        marker.setDraggable(true);

        google.maps.event.addListener(marker, "drag", function() {
          setMarkerLatLng();
        });

        google.maps.event.addListener(marker, "dragend", function() {
          setMarkerPositionFull();
        });

      }

      function setMarkerLatLng() {
        if(marker) {
          lat=marker.position.lat().toFixed(7);
          lng=marker.position.lng().toFixed(7);
          document.getElementById("markerposition").value = "(" + lat + "," + lng + ")";
        }
      }

      function setMarkerPositionFull() {
        if(marker) {
          searchString = "(" + marker.position.lat().toFixed(7) + ",";
          searchString += marker.position.lng().toFixed(7) +")";
          geocoder.geocode( { 'address': searchString }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              for(i=0; i < results[0].address_components.length;++i) {
              if (results[0].address_components[i].types[0] == "country") {
                land=results[0].address_components[i].long_name;
              }
              if (results[0].address_components[i].types[0] == "locality") {
                stadt=results[0].address_components[i].long_name;
              }
            }
            locstring = stadt + ", " + land + " (" + lat + "," + lng + ")";
            document.getElementById("markerposition").value = locstring;
            markerPosition = locstring;

            var select = document.submitForm.locationSelect;

            for(i = select.options.length-1; i >= 0; i--) {
              select.remove(i);
            }
            var option = document.createElement('option');
            option.setAttribute("value", locstring);
            option.innerHTML = locstring;
            select.options[0] = option
            }
          });
        }
      }

      function toggleHidden() {
        $("#register").toggle("slide", { direction: "left" }, 600, function() { toggleButton(); });
        hidden = !hidden;
      }

      function toggleButton() {
        savePos();
        google.maps.event.trigger(map, "resize");
        restorePos();
        button = document.getElementById("registerButton");
        if (hidden) {
          button.value=">> eintragen"
        }
        else {
          button.value="<<            "
        }
      }

    </script>
  </head>

  <body onload="initialize()">
    <div id="all">

      <div id="head">UserMap</div>

      <div id="mainForm">

        <table border="0" width="100%" height="100%">
          <tr>
            <td colspan="2">

              <div id="controls">
                <div id="registerbutton">
                    <input type="button" class="button" id="registerButton" onClick="toggleHidden()"/>
                  </div>
                  <div id="maplink">
                    <?php
                      $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
                      $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
                      $redirectUrl = "https://maps.google.com/maps?q="
                                      . $hostName
                                      . "/UserMap/"
                                      . $kmlFile
                                      . ".kmz"
                                      . "&amp;ie=UTF8"
                                      . "&amp;ll=48.809243,12.720624"
                                      . "&amp;spn=12.49903,13.726243"
                                      . "&amp;t=m"
                                      . "&amp;source=embed";
                      $link = '<a href="' . $redirectUrl . '" target="_blank">Ansicht auf maps.google.com</a>';
                      echo $link;
                    ?>
                  </div>
              </div>
            </td>
            </tr>






            <tr>
              <td valign="top">
                <div id="register">
                  <table border="0" width="100%">
                    <td align="left">Ort</td>
                    <td width="100%" align="center"><input type="text" id="locationInput" name="locationInput"></td>
                    <td align="right"><input type="button" name="locationSearch" value="Ort suchen" class="button" onClick="codeAddress()"></td>
                  </table>
                <form name="submitForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validate();">
                  <select name="locationSelect" id="locationSelect" onChange="setMarker()"></select>
                  <br>
                  <output name="markerposition" id="markerposition" width="100%"></output>
                  <br>
                  <br>

                  <div id="name">
                      <div id="nameLabel">Benutzername</div>
                      <div id="nameInput"><input type="text" id="nameInput" name="nameInput"></div>
                  </div>

                  <br>
                  <div id="description">
                  Beschreibungstext (optional, HTML möglich)
                  <br>

                  <input name="link" type="button" value="link" class="formatbutton" id="plaintext" onClick="addLink()">
                  <input name="bold" type="button" value="bold" class="formatbutton" id="boldtext" onClick="addBold()">
                  <input name="italic" type="button" value="italic" class="formatbutton" id="italictext" onClick="addItalic()">
                  <input name="underline" type="button" value="underline" class="formatbutton" id="underlinedtext" onClick="addUnderline()">
                  <input name="strikethrough" type="button" value="strikethrough" class="formatbutton" id="strikethroughtext" onClick="addStrikethrough()">
                  <input name="newline" type="button" value="newline" class="formatbutton" id="plaintext" onClick="addNewLine()">
                  <input name="preview" type="button" value="Vorschau" class="button" id="preview" onClick="previewDescription()">
                  <!-- <br> -->
                  <div id="descriptionInput">
                  <textarea name="descriptionInput" rows="12"></textarea>
                  </div> <!-- descriptioninput ende -->
                  </div> <!-- description ende -->
                  <div id="submitArea">
                  <input type="submit" name="formSubmit" value="Abschicken" class="button">
                  </div>
                  <?php
                    if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Abschicken")
                    {
                      $varName = $_POST['nameInput'];
                      $varDescription = $_POST['descriptionInput'];
                      $varLocation = $_POST['locationSelect'];

                      $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                            . escapeshellarg("add") . " "
                            . escapeshellarg($varName) . " "
                            . escapeshellarg($varDescription) . " "
                            . escapeshellarg($varLocation);

                      $outputVar = shell_exec($command);

                      if ($outputVar == "success")
                      {
                        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=mapRedirect.php">';    
                        exit;
                      }
                      elseif ($outputVar == "name_taken")
                      {
                        echo('<script language=javascript>alert("Dieser Benutzername ist bereits eingetragen.")</script>');
                      }
                      elseif ($outputVar == "wrong_arguments")
                      {
                        echo('<script language=javascript>alert("Unzulässige Anzahl an Argumenten übergeben.")</script>');
                      }
                      elseif ($outputVar == "lockfile_timeout")
                      {
                        echo('<script language=javascript>alert("timeout.")</script>');
                      }
                      else
                      {
                        echo('<script language=javascript>alert("Unbekannter Fehler.")</script>');
                      }
                    }
                  ?>

                </form>
              </div> <!-- register endet hier -->
            </td>
            <td width="100%" valign="top" height="100%">
              <div id="map"></div>
            </td>
          </tr>




        </table>





      </div>
    </div>
  </body>
</html>