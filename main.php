<!DOCTYPE html>

<html>

  <head>

    <link rel="stylesheet" type="text/css" href="main.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap</title>
    <script type="text/javascript" src="UserMap.js"></script>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAV0Nn3tQ2R4ECUmItAruwylO-CzZrjEUQ&amp;sensor=false">
    </script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script type="text/javascript" src="register.js"></script>

  </head>

  <body onload="initialize()">

    <div id="all">
      <div id="head">UserMap</div>
      <div id="mainForm" style="overflow:hidden;">
        <table style="width:100%;height:100%">
          <tr>
            <td colspan="2">
              <div id="controls">
                  <?php
                    $varName = "";
                    $varDescription = "";
                    $varCountry = "";


                    $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
                    $kmzFile  = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));

                    echo '<script type="text/javascript">
                          kmzLink = "'. $hostName . "/UserMap/" . $kmzFile . '.kmz";
                          </script>';


                  // username kommt hier über externes Formular rein, gegebenenfalls anpassen
                    if(isset($_POST['nameSendSubmit']))
                      {
                        if(isset($_POST['nameSendInput'])) {
                          $varName = $_POST['nameSendInput'];
                      }
                    }

                    else if(isset($_POST['formSubmit']))
                      {
                        if(isset($_POST['nameInput'])) {
                          $varName = $_POST['nameInput'];
                      }
                    }

                    if($varName != "") {
                      echo '<div id="registerbutton" style="float:left;">';
                      echo '  <input type="button" class="controls_button" id="registerButton" value=" " onClick="toggleHidden()"/>';
                      echo '</div>';

                      $forNameCommand = "python ./UserMap.py "
                                . escapeshellarg("forname") . " "
                                . escapeshellarg($varName);

                      $forNameReturnString = shell_exec($forNameCommand);

                      if ($forNameReturnString != "name_not_found") {
                        $forNameResults = explode("$&$" , $forNameReturnString, "10");//wieviele tatsächlich?
                        $varLocationString = $forNameResults[0];
                        $varDescription = $forNameResults[1];
                      }
                    }

                    $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
                    $kmlFile  = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
                    $redirectUrl = "https://maps.google.com/maps?q="
                                      . $hostName
                                      . "/UserMap/"
                                      . $kmlFile
                                      . ".kmz"
                                      . "&amp;ie=UTF8"
                                      // . "&amp;ll=48.809243,12.720624"
                                      // . "&amp;spn=12.49903,13.726243"
                                      . "&amp;t=m"
                                      . "&amp;source=embed";
                    
                    $link = '<a href="'
                            . $redirectUrl
                            . '" target="_blank">Ansicht auf maps.google.com</a>';
                    
                    echo '<div id="maplink" style="float:right;">';
                    echo($link);
                    echo '</div>';
                  ?>
              </div>
            </td>
          </tr>
          <tr>
            <?php
              if ($varName != "") {
                echo '<td style="vertical-align:top;">';
                echo '  <div id="register">';
                require("./register.php");
                echo '  </div>';
                echo '</td>';
              }
            ?>
            <td style="width:100%;height:100%">
              <div id="map"></div>
            </td>
          </tr>
        </table>
      </div>
    </div>

  </body>

</html>