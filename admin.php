<!DOCTYPE html>

<html>

  <head>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <title>UserMap Admin</title>
    <!--<script language="JavaScript" src="UserMap.js" type="text/javascript"></script> -->
  </head>

  <!-- <body onload="readCookie()"> -->
    <!-- <div id="all"> -->
      <div id="head">UserMap Admin</div>
      <div id="mainForm">
  
      <table border="0">
      <?php
                $command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
                      . escapeshellarg("namelist");

                $returnString = shell_exec($command);
                $results = explode("$&$" , $returnString, "100");
                $count = 1;
                foreach($results as $result) {
                  if($count%2 == 0) { $class = "even"; } else { $class = "odd"; }
                  echo(utf8_encode("<tr align='center' class='" . $class . "'>"
                                   . "<td rowspan='2' class='counter'>"
                                       . $count
                                   . "</td>
                                      <td>"
                                      . $result .
                                     "</td>
                                      <td rowspan='2' width='100%' height='100%'>
                                        <textarea>"

                                     . "</textarea>
                                      </td>
                                      <td width=100%>
                                        <input type='submit' value='entfernen'>
                                        
                                        </input>
                                      </td>
                                    </tr>
                                    <tr align='center' class='" . $class . "'>
                                      <td class='country'>
                                        country
                                      </td>
                                      <td width=100%>
                                        <input type='submit' value='update'>
                                        
                                        </input>
                                      </td>
                                    
                                    </tr>"));
                  $count += 1;
                }

      ?>
      </table>
  
    </div>
  </body>

</html>