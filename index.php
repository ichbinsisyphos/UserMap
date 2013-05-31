<!-- TODO: SERVERSEITIGE ÜBERPRÜFUNG UND FEHLER ABFANGEN
      - AUCH WENN, WENN ALLES GUTGEHT, JAVASCRIPT SOLCHE 
        ANFRAGEN GAR NICHT ERST RAUS LASSEN SOLLTE
-->

<!DOCTYPE html>

<html>

  <head>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap</title>
    <script language="JavaScript" src="UserMap.js" type="text/javascript"></script>
  </head>

  <body onload="readCookie()">
    <div id="all">
      <div id="head">UserMap</div>
      <div id="mainForm">

        <form name="locationForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validateLocationString();">
          <table border="0" width="100%">
            <tr>
              <td align="left">Ort</td>
              <td width="100%" align="center"><input type="text" id="locationInput" name="locationInput"></td>
              <td align="right"><input type="submit" name="locationSearch" value="Ort suchen" class="button"></td>
            </tr>
          </table>
        </form>

        <form name="submitForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validate();">
          <select name="locationSelect">
            <?php
              if(isset($_POST['locationSearch']) AND $_POST['locationSearch'] == "Ort suchen")
              {
                $varSearchString = escapeshellarg($_POST["locationInput"]);

                $command = "export PYTHONIOENCODING=UTF-8; python ./geoCoder.py " . $varSearchString;

                $returnString = utf8_decode(shell_exec($command));

                $results = explode("$&$" , $returnString, "10");

                foreach($results as $result)
                {
                  if($result != "None")
                  {
                    #$elements = explode("%" , $result);
                    #$displayString = $elements[0] . " (" . $elements[1] . ", " . $elements[2] . ")";
                    #$coordString = $elements[1] . ", " . $elements[2];
                    echo(utf8_encode("<option value='" . $result . "'>" . $result . "</option>"));
                  }
                }
              }
            ?>
          </select>
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

                <!-- <?php
                  #$hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
                  #$kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
                  #$mapUrl = "https://maps.google.at/maps?source=embed&q=" . $hostName . "/UserMap/" . $kmlFile;
                  #echo "<a href='" . $mapUrl . "'>bisherige Karte</a>";
                ?> -->

              </td>
              <td align="right"><input type="submit" name="formSubmit" value="Abschicken" class="button"></td>
            </tr>
          </table>

          <?php
            if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Abschicken")
            {
                $varName = $_POST['nameInput'];
                $varDescription = $_POST['descriptionInput'];

                #$coords = explode(",", $_POST['locationSelect']);
                #$varLat = $coords[0];
                #$varLng = $coords[1];
                $varLocation = $_POST['locationSelect'];

                $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                      . escapeshellarg($varName) . " "
                      . escapeshellarg($varDescription) . " "
                      . escapeshellarg($varLocation);
                      #. escapeshellarg($varLat) . " "
                      #. escapeshellarg($varLng);

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
    </div>
  </body>
</html>