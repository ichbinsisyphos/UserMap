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

                if ($returnString == "error_querynotunderstood")
                {
                  echo('<script language=javascript>alert("Suchanfrage nicht verstanden.")</script>');
                }
                elseif ($returnString == "error_apicreditsusedup")
                {
                  echo('<script language=javascript>alert("API-credits aufgebraucht, bitte versuche es morgen noch einmal.")</script>');
                }
                elseif ($returnString == "error_wrong_arguments")
                {
                  echo('<script language=javascript>alert("Unzulässige Anzahl an Argumenten übergeben.")</script>');
                }                
                elseif ($returnString == "error_unknown")
                {
                  echo('<script language=javascript>alert("Unbekannter Fehler.")</script>');
                }
                else
                {
                  $results = explode("$&$" , $returnString, "10");
                  foreach($results as $result)
                  {
                    if($result != "None")
                    {
                      echo(utf8_encode("<option value='" . $result . "'>" . $result . "</option>"));
                    }
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
    </div>
  </body>
</html>