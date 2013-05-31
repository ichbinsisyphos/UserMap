<?php
  $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
  $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
  $redirectUrl = "https://maps.google.at/maps?q=" . $hostName . "/UserMap/" . $kmlFile;

  header("Location: " . $redirectUrl);
?>