<!DOCTYPE html>
<html>

  <head>

    <link rel="stylesheet" type="text/css" href="style.css" />

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >

    <title>UserMap</title>

    <script language="JavaScript">
    <!--
      var userLocation = "";
      var userFinalLocation = "";
      var userName = "";
      var userDescription = "";

      function obligatoryFieldsFilled() {
        return (window.document.mainForm.nameInput.value != "" &&
                window.document.mainForm.locationSelect.length > 0);
      }

      function searchLocation() {

      }

      function setUserLocation(l) {
        userLocation = l;
      }

      function setUserFinalLocation(lf) {
        userFinalLocation = lf;
      }

      function setUserName(n) {
        userName = n;
      }

      function setUserDescription(d) {
        userDescription = d;
      }

      function searchLocation() {
        if (window.document.mainForm.locationInput.value == "") {
          alert("bitte einen Suchbegriff für ihren Heimatort angeben");
        }
      }
      function addLink() {
          startIndex = window.document.mainForm.descriptionInput.selectionStart;
          endIndex = window.document.mainForm.descriptionInput.selectionEnd;
          selectedText = window.document.mainForm.descriptionInput.value.slice(startIndex, endIndex);
          
          urlText = "";

          if(selectedText.slice(0,7) != "http://") {
            urlText = "http://";
          }

          if (startIndex == endIndex) {
            urlText += "LINKGOESHERE";
          }

          insertDescriptionFormatting('<a href="' + urlText, '">TEXTGOESHERE</a>');
      }
      function addBold() {
          insertDescriptionFormatting("<b>","</b>");
      }
      function addUnderline() {
          insertDescriptionFormatting("<u>","</u>");
      }
      function addItalic() {
          insertDescriptionFormatting("<i>","</i>");
      }
      function addStrikethrough() {
          insertDescriptionFormatting("<strike>", "</strike>");
      }
      function addNewLine() {
          insertDescriptionFormatting("", "<br>");
      }
      function insertDescriptionFormatting(open, close) {
          len = window.document.mainForm.descriptionInput.value.length;
          startIndex = window.document.mainForm.descriptionInput.selectionStart;
          endIndex = window.document.mainForm.descriptionInput.selectionEnd;
          pre = window.document.mainForm.descriptionInput.value.slice(0,startIndex);
          selected = window.document.mainForm.descriptionInput.value.slice(startIndex,endIndex);
          post = window.document.mainForm.descriptionInput.value.slice(endIndex,len);

          window.document.mainForm.descriptionInput.value = pre + open + selected + close + post;
      }

      function previewDescription() {
        if (this.previewWindow) {
          this.previewWindow.close();
        }

        this.previewWindow = window.open("", "Textausgabe", "width=500,height=200");
        this.previewWindow.value="";
        html = window.document.mainForm.descriptionInput.value;
        this.previewWindow.document.write(html);
      }

      // function setCookie(name, value, duration) { //duration in Sekunden, also gegebenenfalls multiplizieren
      //   now = new Date();
      //   diesAt = new Date(now.getTime() + duration);
      //   document.cookie = name + "=" + value + ";expires=" + diesAt.toGMTString() + ";";
      //   delete now;
      // }

      // function readCookie() {
      //   value = "";
      //   if(document.cookie) {
      //     valueStart = document.cookie.indexOf("=") + 1;
      //     valueEnd = document.cookie.indexOf(";");
      //     if(valueEnd == -1) {
      //       valueEnd = document.cookie.length;
      //     }
      //     value = document.cookie.substring(valueStart, valueEnd);
      //   }
      //   return value;
      // }

    //-->
    </script>

  </head>

  <body>

    <div id="all">
      <div id="head">
        UserMap
      </div>
      <div id="mainForm">
        <form name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
          Ort
            <input type="text" name="locationInput">
            <input type="submit" name="locationSearch" value="Ort suchen" class="button" onClick="searchLocation()">
            <br>
            <select name="locationSelect">
              <?php
                //header('Content-type: text/html; charset=utf-8');
                if(isset($_POST['locationSearch']) AND $_POST['locationSearch'] == "Ort suchen")
                {
                  $varSearchString = escapeshellarg($_POST["locationInput"]);

                  $command = "export PYTHONIOENCODING=UTF-8; python ./geoCoder.py " . $varSearchString; #bringts ned

                  $returnString = utf8_decode(shell_exec($command));
                  #exec($command, $returnString); #bringts ned
                  #$returnString = $returnString[0];
                  echo "STRLEN: " . strlen($returnString);

                  $results = explode("$" , $returnString, "10");

                  foreach($results as $result)
                  {
                    if($result != "None")
                    {
                      $elements = explode("%" , $result);
                      $displayString = $elements[0] . " (" . $elements[1] . ", " . $elements[2] . ")";
                      $coordString = $elements[1] . ", " . $elements[2];
                      echo(utf8_encode("<option value='" . $coordString . "'>" . $displayString . "</option>"));
                    }
                  }
                }
              ?>
            </select>
          <br>
          <br>
            Benutzername <input type="text" name="nameInput">
          <br>
          <br>
          Beschreibungstext (optional, HTML möglich)
          <br>         
          <input name="link" type="button" value="link" class="plaintext" onClick="addLink()">
          <input name="bold" type="button" value="bold" class="boldtext" onClick="addBold()">
          <input name="italic" type="button" value="italic" class="italictext" onClick="addItalic()">
          <input name="underline" type="button" value="underline" class="underlinedtext" onClick="addUnderline()">
          <input name="strikethrough" type="button" value="strikethrough" class="strikethroughtext" onClick="addStrikethrough()">
          <input name="newline" type="button" value="newline" class="plaintext" onClick="addNewLine()">
          <input name="preview" type="button" value="preview" class="preview" onClick="previewDescription()">
          <br>
          <textarea name="descriptionInput" rows="10"></textarea>
          <br>
          <input type="submit" name="formSubmit" value="Abschicken" class="button">
          <br>
          <?php
            //header('Content-type: text/html; charset=utf-8');
            if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Abschicken")
            {
              $varName = $_POST['nameInput'];
              $varDescription = $_POST['descriptionInput'];

              $coords = explode(",", $_POST['locationSelect']);
              $varLat = $coords[0];
              $varLng = $coords[1];

              $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                    . escapeshellarg($varName) . " "
                    . escapeshellarg($varDescription) . " "
                    . escapeshellarg($varLat) . " "
                    . escapeshellarg($varLng);

              $outputVar = shell_exec($command);

              if ($outputVar == "success")
              {
                $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
                $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
                header("Location: https://maps.google.at/maps?source=embed&q=" 
                      . $hostName . "/UserMap/" . $kmlFile);
              }
              elseif ($outputVar == "name_taken")
              {
                echo("This username is already on the map.\n");
              }
              elseif ($outputVar == "wrong_arguments")
              {
                echo("Please fill out all forms before submitting.\n");
              }
              else
              {
                echo("unclassified error.\n");
              }
            }
          ?>
        </form>
        <?php
          //header('Content-type: text/html; charset=utf-8');
          $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
          $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
          $mapUrl = "https://maps.google.at/maps?source=embed&q=" . $hostName . "/UserMap/" . $kmlFile;
          echo "<a href='" . $mapUrl . "'>bisherige Karte</a>";
        ?>
      </div>
    </div>
  </body>
</html>