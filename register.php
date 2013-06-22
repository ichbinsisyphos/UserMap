<form name="submitForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onSubmit="return validate();">
  
  <table style="width:100%;">
    <tr>
      <td style="text-align:left;width:auto;"><div class="register_text">Ort</div></td>
      <td style="width:100%;" colspan="2">
        <input type="text" id="locationInput" name="locationInput" class="register_input">
      </td>
      <td style="width:auto;">
        <input type="button" name="locationSearch" value="Ort suchen" class="register_button" onClick="codeAddress()">
      </td>
    </tr>
    <tr>
      <td colspan="4">
        <select name="locationSelect" id="locationSelect" class="register_input" onChange="setMarker()">
          <?php

          if($varLocationString != "") {
            echo '<option>' . $varLocationString . '</option>';
          }

          ?>
        </select>
      </td>
    </tr>
    <tr style="height:10px;"><td colspan="4"></td>
    </tr>
    <tr>
      <td style="width:auto;">
        <div class="register_text">Benutzername</div>
      </td>
      <td colspan="3" style="width:100%;">
        <?php
          if($varName == "")
          {
            echo '<input type="text" id="nameInput" name="nameInput" class="register_input">';
          }
          else {
            echo '<input type="text" id="nameInput" name="nameInput" class="register_input" readonly="1" value="' . $varName . '">';
          }
        ?>
        <!-- <input type="text" id="nameInput" name="nameInput" class="register_input"> -->
      </td>
    </tr>
    <tr style="height:10px;"><td colspan="4"></td>
    </tr>
    <tr>
      <td colspan="3">
        <div class="register_text">Beschreibungstext (optional, HTML möglich)</div>
      </td>
      <td style="wdith:auto; vertical-align:bottom;" rowspan="2">
        <input name="preview" type="button" value="Vorschau" class="register_button" id="preview" onClick="previewDescription()">
      </td>
    </tr>
    <tr>
      <td colspan="3" style="vertical-align:bottom;">
        <input name="link" type="button" value="link" class="formatbutton" id="plaintext" onClick="addLink()">
        <input name="bold" type="button" value="bold" class="formatbutton" id="boldtext" onClick="addBold()">
        <input name="italic" type="button" value="italic" class="formatbutton" id="italictext" onClick="addItalic()">
        <input name="underline" type="button" value="underline" class="formatbutton" id="underlinedtext" onClick="addUnderline()">
        <input name="strikethrough" type="button" value="strikethrough" class="formatbutton" id="strikethroughtext" onClick="addStrikethrough()">
        <input name="newline" type="button" value="newline" class="formatbutton" id="newlinetext" onClick="addNewLine()">
      </td>
    </tr>
    <tr>
      <td colspan="4">

        <?php
          if($varDescription == "")
          {
            echo '<textarea name="descriptionInput" rows="10" class="register_input"></textarea>';
          }
          else {
            echo '<textarea name="descriptionInput" rows="10" class="register_input">'. $varDescription . '</textarea>';
          }
        ?>
        
      </td>
    </tr>
    <tr>
      <td colspan="3">
      </td>
      <td style="width:auto;">
        <input type="submit" name="formSubmit" value="Abschicken" class="register_button">
      </td>
    </tr>
  </table>

  <?php

    if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Abschicken")
    {
      $varName = $_POST['nameInput'];
      $varDescription = $_POST['descriptionInput'];
      $varLocation = $_POST['locationSelect'];
      $varAction = "overwrite";

      $command = "python ./UserMap.py "
            . escapeshellarg($varAction) . " "
            . escapeshellarg($varName) . " "
            . escapeshellarg($varDescription) . " "
            . escapeshellarg($varLocation);

      $outputVar = shell_exec($command);

      $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
      $kmzFile  = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));

      echo '<script type="text/javascript">
            kmzLink = "'. $hostName . "/UserMap/" . $kmzFile . '.kmz";
            </script>';


      if ($outputVar != "success") {
        if ($outputVar == "name_taken") {
          echo '<script type="text/javascript">alert("Dieser Benutzername ist bereits eingetragen.")</script>';
        }
        elseif ($outputVar == "wrong_arguments") {
          echo '<script type="text/javascript">alert("Unzulässige Anzahl an Argumenten übergeben.")</script>';
        }
        elseif ($outputVar == "lockfile_timeout") {
          echo '<script type="text/javascript">alert("timeout.")</script>';
        }
        else {
          echo '<script type="text/javascript">alert("Unbekannter Fehler.")</script>';
        }
      }
    }

  ?>

</form>