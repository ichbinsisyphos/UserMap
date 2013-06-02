<!DOCTYPE html>

<html>
  <head>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap Admin</title>
    <script language="JavaScript" src="UserMap.js" type="text/javascript"></script>
  </head>
  <body>
    <div id="head">UserMap Admin</div>
    <div id="mainForm">
      <table border="0">
        <?php
          $nameListCommand = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                . escapeshellarg("namelist");

          $nameListReturnString = shell_exec($nameListCommand);
          $nameListResults = explode("$&$" , $nameListReturnString, "100");//auch hier kann man die anzahl mehr einschränken
          $nameCount = 1;
          foreach($nameListResults as $name) {
            if(isset($_POST['updateSubmit' . $nameCount])) { //AND $_POST['updateSubmit' . $nameCount] == "Beschreibung\nspeichern") {
              $userName = escapeshellarg($name);
              $newDescription = escapeshellarg($_POST['userDescription' . $nameCount]);

              $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py updateDescription " . $userName . " " . $newDescription;
              //echo $command;
              $outputVar = shell_exec($command);
              //echo $outputVar;//Fehlerbehandlung noch einfügen
            }

            if(isset($_POST['removeSubmit' . $nameCount])) {//} AND $_POST['removeSubmit' . $nameCount] == "Benutzer\nentfernen") {
              $userName = escapeshellarg($name);
              $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py removename " . $userName;
              $outputVar = shell_exec($command);
              //echo $outputVar;//Fehlerbehandlung noch einfügen
            }

            else {
              $forNameCommand = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                        . escapeshellarg(utf8_encode("forname")) . " " . escapeshellarg($name);
              $forNameReturnString = shell_exec($forNameCommand);
              $forNameResults = explode("$&$" , utf8_encode($forNameReturnString), "10");//wieviele tatsächlich?
              $desc = utf8_decode($forNameResults[0]);
              $country = utf8_encode($forNameResults[1]);
              //$coords = utf8_encode($forNameResults[2]);
              //$coordArray = explode(",", $coords, "10");//nur 3
              //$lng = $coordArray[0];
              //$lat = $coordArray[1];
              //$coords = "lng: " . $lng . "<br>" . "lat: " . $lat;

              if($nameCount%2 == 0) { $oddEvenClass = "even"; } else { $oddEvenClass = "odd"; }

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
                 . "    <td rowspan='2' width='100%' height='100%'>\n"
                 . "      <textarea name='userDescription" . $nameCount . "'>" . $desc . "</textarea>\n"
                 . "    </td>\n"
                 . "    <td width=100%>\n"
                 . "      <input type='submit' class='button' name='updateSubmit" . $nameCount . "' value='Beschreibung\nspeichern'></input>\n"
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
            }
            $nameCount += 1;
          }
        ?>
    </table>
    </div>
  </body>
</html>