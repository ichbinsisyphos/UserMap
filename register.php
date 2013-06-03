<!DOCTYPE html>

<html>

  <head>
    <link rel="stylesheet" type="text/css" href="register.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap Register</title>
    <script language="JavaScript" src="UserMap.js" type="text/javascript"></script>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAV0Nn3tQ2R4ECUmItAruwylO-CzZrjEUQ&sensor=false">
    </script>
    <script language="JavaScript" type="text/javascript">
      var geocoder;
      var map;
      var marker = null;
      function initialize() {
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(47.8571,12.1181);
        var mapOptions = {
          zoom: 5,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById("map"), mapOptions);
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
      }
    </script>
  </head>

  <body onload="initialize()">
    <div id="all">
      <div id="head">UserMap Register</div>
      <div id="mainForm">
          <table border="0" width="100%">
            <tr>
              <td align="left">Ort</td>
              <td width="100%" align="center"><input type="text" id="locationInput" name="locationInput"></td>
              <td align="right"><input type="button" name="locationSearch" value="Ort suchen" class="button" onClick="codeAddress()"></td>
            </tr>
          </table>
        <form name="submitForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validate();">
          <select name="locationSelect" id="locationSelect" onChange="setMarker()"></select>
          <br>
          <br>

          <table border="0" width="100%">
            <tr>
              <td align="left">Benutzername</td>
              <td width="100%" align="right"><input type="text" id="nameInput" name="nameInput"></td>
            </tr>
          </table>

          <br>
          Beschreibungstext (optional, HTML möglich)
          <br>         
          <input name="link" type="button" value="link" class="plaintext" onClick="addLink()">
          <input name="bold" type="button" value="bold" class="boldtext" onClick="addBold()">
          <input name="italic" type="button" value="italic" class="italictext" onClick="addItalic()">
          <input name="underline" type="button" value="underline" class="underlinedtext" onClick="addUnderline()">
          <input name="strikethrough" type="button" value="strikethrough" class="strikethroughtext" onClick="addStrikethrough()">
          <input name="newline" type="button" value="newline" class="plaintext" onClick="addNewLine()">
          <input name="preview" type="button" value="Vorschau" class="preview" onClick="previewDescription()">
          <br>
          <textarea name="descriptionInput" rows="10"></textarea>
          <br>

          <table border="0" width="100%">
            <tr>
              <td align="left">
                <a href="mapRedirect.php">bisherige Karte</a>
              </td>
              <td align="right"><input type="submit" name="formSubmit" value="Abschicken" class="button"></td>
            </tr>
          </table>

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
      </div>
      <div id="map"></div>
    </div>
  </body>
</html>