<!DOCTYPE html>
<html>

	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<title>UserMap</title>

	</head>

	<body>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="text" name="formSearchString" maxlength="50" size="42">
			<input type="submit" name="formSearchStringSubmit" value="Search">
			<br>
			<select name="locationSelect">

			<?php
			 	header('Content-type: text/html; charset=utf-8');
				if($_POST['formSearchStringSubmit'] == "Search")
				{
					$varSearchString = escapeshellarg($_POST["formSearchString"]);

					$command = "export PYTHONIOENCODING=UTF-8; python ./geoCoder.py " . $varSearchString; #bringts ned

					$returnString = shell_exec($command);
					#exec($command, $returnString); #bringts ned
					#$returnString = $returnString[0];
					#print_r($returnString); #bringts ned

					$returnString = utf8_decode($returnString);
					
					$results = explode("$" , $returnString, "10");

					foreach($results as $result)
					{
						if($result != "None")
						{
							$elements = explode("%" , $result);
							$displayString = $elements[0] . " (" . $elements[1] . ", " . $elements[2] . ")";
							$coordString = $elements[1] . ", " . $elements[2];
							echo(utf8_encode("<option value='" . $coordString . "'>" . $displayString . "</option>"));
						}
					}
				}
			?>

			</select>
			<br>
			Dein Benutzername
			<input type="text" name="formName" maxlength="50" size="30">
			<br>
			Beschreibungstext für deinen Eintrag
			<br>
			<textarea name="formDescription" rows="10" cols="59"></textarea>
			<br>
			<input type="submit" name="formSubmit" value="Submit">
			<br>

		    <?php
		     	header('Content-type: text/html; charset=utf-8');
				if(isset($_POST['formSubmit']) AND $_POST['formSubmit'] == "Submit")
				{
					$varName = $_POST['formName'];
					$varDescription = $_POST['formDescription'];

					$coords = explode(",", $_POST['locationSelect']);
					$varLat = $coords[0];
					$varLng = $coords[1];

					$command = "export PYTHONIOENCODING=UTF-8; python ./UserMap.py "
						. escapeshellarg($varName) . " "
						. escapeshellarg($varDescription) . " "
						. escapeshellarg($varLat) . " "
						. escapeshellarg($varLng);

					$outputVar = shell_exec($command);

					if ($outputVar == "success")
					{
						$hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
						$kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
						header("Location: https://maps.google.at/maps?source=embed&q=" . $hostName . "/UserMap/" . $kmlFile);
					}
					elseif ($outputVar == "name_taken")
					{
						echo("This username is already on the map.\n");
					}
					elseif ($outputVar == "wrong_arguments")
					{
						echo("Please fill out all forms before submitting.\n");
					}
					else
					{
						echo("unclassified error.\n");
					}
				}
			?>
		</form>
		<?php
		 	header('Content-type: text/html; charset=utf-8');
			$hostName = trim(preg_replace('/\s+/', ' ', file_get_contents("hostname.conf")));
			$kmlFile = trim(preg_replace('/\s+/', ' ', file_get_contents("var/kmlFilename.dat")));
			$mapUrl = "https://maps.google.at/maps?source=embed&q=" . $hostName . "/UserMap/" . $kmlFile;
			echo "<br>";
			echo "<a href='" . $mapUrl . "'>current map</a>";
		?>

	</body>

</html>