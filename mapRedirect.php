<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>redirect</title>
  </head>
  <body>
    <?php
    $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
    $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
    $redirectUrl = "https://maps.google.at/maps?q="
                    . $hostName
                    . "/UserMap/"
                    . $kmlFile
                    . ".kmz";

    header("Location: " . $redirectUrl);
    ?>
  </body>
</html>