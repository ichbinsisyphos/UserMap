<!DOCTYPE html>

<html>
  <head>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap</title>
    <!--<script language="JavaScript" src="UserMap.js" type="text/javascript"></script>-->
  </head>

  <body>
    <div id="all">
      <div id="head">UserMap</div>
      <div id="mainForm">
        <div id="map">
        <table border="0">
          <tr>
            <td>
              <form action="register.php" method="post">
                <input type="submit" class="button" value="registrieren"/>
              </form>
            </td>
            <td>
              <form action="admin.php" method="post">
                <input type="submit" class="button" value="verwalten"/>
              </form>
            </td>
            <td width="100%">
            </td>
          </tr>
          <tr>


    <?php
    $hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
    $kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
    $redirectUrl = "https://maps.google.at/maps?q="
                    . $hostName
                    . "/UserMap/"
                    . $kmlFile
                    . ".kmz";

            echo '<td width="100%" height="100%" align="center" colspan="3">
              <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . $redirectUrl . '&amp;ie=UTF8&amp;ll=48.809243,12.720624&amp;spn=12.49903,13.726243&amp;t=m&amp;output=embed">
              </iframe>
            </td>
          </tr>
          <tr>
            <td colspan="3">
              <small>
                <a href="' . $redirectUrl . '&amp;ie=UTF8&amp;ll=48.809243,12.720624&amp;spn=12.49903,13.726243&amp;t=m&amp;source=embed" target="_blank">
                  Ansicht auf maps.google.com
                </a>
              </small>
            </td>'
?>
          </tr>
        </table>
      </div>
      </div>
    </div>
  </body>
</html>