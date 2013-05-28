<!DOCTYPE html>
<html>

  <head>

    <link rel="stylesheet" type="text/css" href="style.css" />

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >

    <title>UserMap</title>

    <script language="JavaScript">
    <!--
      function obligatoryFieldsFilled() {
        if (window.document.mainForm.nameInput.value != "" &&
                window.document.mainForm.locationSelect.length > 0)
        {
          window.document.mainForm.formSubmit.disabled = false;
        }
        else window.document.mainForm.formSubmit.disabled = true;

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
          window.document.mainForm.descriptionInput.focus();
          window.document.mainForm.descriptionInput.selectionStart = endIndex + open.length + close.length;
          window.document.mainForm.descriptionInput.selectionEnd = endIndex + open.length + close.length;

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

      function setCookie() { //duration in Sekunden, also gegebenenfalls multiplizieren
        duration = 60000; // milliseconds
        now = new Date();
        diesAt = new Date(now.getTime() + duration);
        name = "formValues";

        userName = window.document.mainForm.nameInput.value;
        userLocation = window.document.mainForm.locationInput.value;
        userDescription = window.document.mainForm.descriptionInput.value;

        value = userName + "$&$" + userLocation + "$&$" + userDescription;
        document.cookie = name + "=" + value + ";expires=" + diesAt.toGMTString() + ";";
        
        delete now;
        obligatoryFieldsFilled()
      }

      function readCookie() {
        value = "";

        if(document.cookie) {
          valueStart = document.cookie.indexOf("=") + 1;
          valueEnd = document.cookie.indexOf(";");
          if(valueEnd == -1) {
            valueEnd = document.cookie.length;
          }
          value = document.cookie.substring(valueStart, valueEnd);
          
          tempArray = value.split("$&$");

          window.document.mainForm.nameInput.value = tempArray[0];
          window.document.mainForm.locationInput.value = tempArray[1];
          window.document.mainForm.descriptionInput.value = tempArray[2];

          delete tempArray;
          //obligatoryFieldsFilled();
        }
        //else { alert("cookie nicht gesetzt!"); }
      }

    //-->
    </script>

  </head>

  <body onload="readCookie()">

    <div id="all">
      <div id="head">
        UserMap
      </div>
      <div id="mainForm">
        <form name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
          Ort
            <input type="text" name="locationInput" onChange="obligatoryFieldsFilled()">
            <input type="submit" name="locationSearch" value="Ort suchen" class="button" onClick="setCookie()">
            <br>
            <select name="locationSelect" onFocus="obligatoryFieldsFilled()">
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
            Benutzername <input type="text" name="nameInput" onChange="obligatoryFieldsFilled()">
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
          <input type="submit" name="formSubmit" value="Abschicken" class="button" onClicked="setCookie()">
          <br>
          <?php
            //header('Content-type: text/html; charset=utf-8');
            if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Abschicken")
            {
              if($_POST['nameInput'] != "" AND isset($_POST['locationSelect']))
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
                  $redirectUrl = "https://maps.google.at/maps?source=embed&q=" 
                                    . $hostName . "/UserMap/" . $kmlFile;

                  echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $redirectUrl . '">';    
                  exit;
                }
                elseif ($outputVar == "name_taken")
                {
                  echo('<div class="error">This username is already on the map.</div>');
                }
                elseif ($outputVar == "wrong_arguments")
                {
                  echo('<div class="error">Please fill out all forms before submitting.</div>');
                }
                else
                {
                  echo('<div class="error">unclassified error.</div>');
                }
              }
              else
              {
                echo('<div class="error">Please fill out all forms before submitting.</div>');
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
    <!--<script language="JavaScript">
      obligatoryFieldsFilled();
    </script>-->
  </body>
</html>