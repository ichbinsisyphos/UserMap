<?php
  $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
  $kmzFile  = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
  $redirectUrl = "https://maps.google.com/maps?q="
                    . $hostName
                    . "/UserMap/"
                    . $kmzFile
                    . ".kmz"
                    . "&amp;ie=UTF8";
                    // . "&amp;ll=48.809243,12.720624"
                    // . "&amp;spn=12.49903,13.726243"
                    // . "&amp;t=m";
                    // . "&amp;source=embed";
  header("Location: " . $redirectUrl );
?>