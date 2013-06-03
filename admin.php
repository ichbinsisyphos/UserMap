<!DOCTYPE html>

<!-- TODO: PROGRAMM-AUSGABEN VERWERTEN INKL. FEHLERMELDUNGEN -->
<!-- TODO: UNCODE-UMGANG ENDGÜLTIG ABKKLÄREN -->
<!-- TODO: ACHTUNG NACH LÖSCHEN GIBTS PROBLEME - MEHRERE USER LÖSCHEN NUR NACH NEULADEN -->

<html>
  <head>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap Admin</title>
    <script language="JavaScript" src="UserMap.js" type="text/javascript"></script>
  </head>
  <body>
    <div id="all">
    <div id="head">UserMap Admin</div>
    <div id="mainForm">
      <table border="0">
        <?php
          $nameListCommand = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                . escapeshellarg("namelist");

          $nameListReturnString = shell_exec($nameListCommand);
          $nameListResults = explode("$&$" , $nameListReturnString, "100");//auch hier kann man die anzahl mehr einschränken
          $nameCount = 1;
          $done = false;
          foreach($nameListResults as $name) {
            if(isset($_POST['updateSubmit' . $nameCount]) AND $done == false) { //AND $_POST['updateSubmit' . $nameCount] == "Beschreibung\nspeichern") {
              $userName = escapeshellarg($name);
              $newDescription = escapeshellarg($_POST['userDescription' . $nameCount]);

              $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py updateDescription " . $userName . " " . $newDescription;
              //echo $command;
              $outputVar = shell_exec($command);
              //echo $outputVar;//Fehlerbehandlung noch einfügen
              $done = true;
            }

            if(isset($_POST['removeSubmit' . $nameCount]) AND $done == false) {//} AND $_POST['removeSubmit' . $nameCount] == "Benutzer\nentfernen") {
              $userName = escapeshellarg($name);
              $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py removename " . $userName;
              $outputVar = shell_exec($command);
              //echo $outputVar;//Fehlerbehandlung noch einfügen
              $done = true;
            }

            else {
              $forNameCommand = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                        . escapeshellarg(utf8_encode("forname")) . " "
                        . escapeshellarg($name);

              $forNameReturnString = shell_exec($forNameCommand);
              $forNameResults = explode("$&$" , $forNameReturnString, "10");//wieviele tatsächlich?
              $desc = $forNameResults[0];
              $country = $forNameResults[1];

              if($nameCount%2 == 0) {
                $oddEvenClass = "even";
              }
              else {
                $oddEvenClass = "odd";
              }

              echo("<tr align='center' class='" . $oddEvenClass . "'>\n"
                 . "  <form name='updateForm' action='" . $_SERVER['PHP_SELF'] . "' method='post' onSubmit='return confirmUpdate();'>\n"
                 . "    <td rowspan='2' class='counter'>\n"
                 . "      " . $nameCount . "\n"
                 . "    </td>\n"
                 . "    <td>\n"
                 . "      <text class='largeFont' name='userName'>\n"
                 . "        " . $name . "\n"
                 . "      </text>\n"
                 . "    </td>\n"
                 . "    <td rowspan='2' width='100%' height='100%' align='center'>\n"
                 . "      <textarea name='userDescription" . $nameCount . "'>" . $desc . "</textarea>\n"
                 . "    </td>\n"
                 . "    <td width=100%>\n"
                 . "      <input type='submit' class='button' name='updateSubmit" . $nameCount . "' value='speichern'></input>\n"
                 . "    </td>\n"
                 . "  </form>\n"
                 . "</tr>\n"
                 . "<tr align='center' class='" . $oddEvenClass . "'>\n"
                 . "  <td class='country'>\n"
                 . "    <text name='userCountry'>\n"
                 . "      " . $country . "\n"
                 . "    </text>\n"
                 . "  </td>\n"
                 . "  <form name='removeForm' action='" . $_SERVER['PHP_SELF'] . "' method='post' onSubmit='return confirmRemove(".'"'. $name .'"'.");'>\n"
                 //. "    <td>\n"
                 //. "      <text class='smallFont' name='userCoords'>\n"
                 //. "        " . $coords . "\n"
                 //. "      </text>\n"
                 //. "    </td>\n"
                 . "    <td width=100%>\n"
                 . "  <input type='submit' class='button' name='removeSubmit" . $nameCount . "' value='Benutzer\nentfernen'></input>\n"
                 . "    </td>\n"
                 . "  </form>\n"
                 . "</tr>\n"
                 //. "<tr align='center' class='" . $oddEvenClass . "'>\n"
                 // . "  <td class='country'>\n"
                 // . "    <text name='userCountry'>\n"
                 // . "      " . $country . "\n"
                 // . "    </text>\n"
                 // . "  </td>\n"
                 //. "</tr>\n"
              );
              $nameCount += 1;
            }
//            $nameCount += 1;
          }
        ?>
    </table>
    </div>
    </div>
  </body>
</html>