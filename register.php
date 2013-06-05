  <table style="width:100%;">
    <tr>
      <td style="text-align:left;">Ort</td>
      <td style="width:100%;text-align:center;"><input type="text" id="locationInput" name="locationInput"></td>
      <td style="text-align:right;"><input type="button" name="locationSearch" value="Ort suchen" class="button" onClick="codeAddress()"></td>
    </tr>
  </table>

<form name="submitForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validate();">
  <select name="locationSelect" id="locationSelect" onChange="setMarker()"></select>
  <br>
  <output name="markerposition" id="markerposition"></output>
  <br>
  <div id="name">
      <div id="nameLabel">Benutzername</div>
      <div id="nameInputArea"><input type="text" id="nameInput" name="nameInput"></div>
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
  <input name="newline" type="button" value="newline" class="formatbutton" id="newlinetext" onClick="addNewLine()">
  <input name="preview" type="button" value="Vorschau" class="button" id="preview" onClick="previewDescription()">
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

      if ($outputVar == "success") {
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=mapRedirect.php">';    
        exit;
      }
      elseif ($outputVar == "name_taken") {
        echo('<script type="text/javascript">alert("Dieser Benutzername ist bereits eingetragen.")</script>');
      }
      elseif ($outputVar == "wrong_arguments") {
        echo('<script type="text/javascript">alert("Unzulässige Anzahl an Argumenten übergeben.")</script>');
      }
      elseif ($outputVar == "lockfile_timeout") {
        echo('<script type="text/javascript">alert("timeout.")</script>');
      }
      else {
        echo('<script type="text/javascript">alert("Unbekannter Fehler.")</script>');
      }
    }
  ?>
</form>