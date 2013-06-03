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
            <td width='100%' height='100%' align='center' colspan="3">
              <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=http:%2F%2F85.127.72.181%2FUserMap%2Fvar%2FUserMap_gem6bl_1370242301.kmz&amp;ie=UTF8&amp;ll=48.809243,12.720624&amp;spn=12.49903,13.726243&amp;t=m&amp;output=embed">
              </iframe>
            </td>
          </tr>
          <tr>
            <td colspan="3">
              <small>
                <a href="https://maps.google.com/maps?q=http:%2F%2F85.127.72.181%2FUserMap%2Fvar%2FUserMap_gem6bl_1370242301.kmz&amp;ie=UTF8&amp;ll=48.809243,12.720624&amp;spn=12.49903,13.726243&amp;t=m&amp;source=embed" target="_blank">
                  Ansicht auf maps.google.com
                </a>
              </small>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </body>
</html>