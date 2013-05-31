<?php
  $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
  $kmzFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmzFilename.dat")));
  $redirectUrl = "https://maps.google.at/maps?q=" . $hostName . "/UserMap/" . $kmzFile;

  header("Location: " . $redirectUrl);
?>