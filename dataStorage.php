<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'storedInfo.php';
?>
<!DOCTYPE = HTML>
<html>
<head>
	<meta charset="utf-8/">
	<title>Video Store Database</title>
</head>
<body>
	<?php


	$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "waltmanr-db", $password, "waltmanr-db");

	if($mysqli->connect_errno){
		echo "Connect failed: (" . $mysqli->connect_errono . ") " . $mysqli->connect_error;
	} else {
		echo "Connection Successful.<br>";
	}

	echo "Add new movie:<br>";
	echo "<form method='post' action='dataStorage.php'>";
	echo "Title: <input type='text' name='inputName'>  Category: <input type='text' name='inputCategory'>  Length: <input type='text' name='inputLength'>";
	echo "<input type='submit' value='Submit'><br>";
	echo "</form>";

	if(isset($_POST['inputName'])){
		addNewVideo();
	} else if (isset($_POST['deleteAll'])){
		deleteAllVideos();
	} else if (isset($_POST['Return'])){
		returnVideo();
	} else if (isset($_POST['Checkout'])){
		checkOutVideo();
	} else if (isset($_POST['Remove'])){
		removeVideo();
	} 
	createFilter();
	drawTable();
	?>

	<p><form method='post' action='dataStorage.php'>
	<button type='submit' name='deleteAll'>Delete All Videos</button>
	</form>

	<?php

	function drawTable(){
		global $mysqli;
		$res;
		echo "<table border='1' cellpadding='2'> <tr> <td>ID#</td> <td>Name</td> <td>Category</td> <td>Length</td> <td>Rented?</td> <td>Check Out/Return</td> <td>Remove</td></tr>";
		if (isset($_POST['category']) && $_POST['category'] != "all"){
			$sanitized = $mysqli->real_escape_string($_POST['category']);
			$res = $mysqli->query("SELECT * FROM vidStore WHERE category = '$sanitized'");
		} else {
			$res = $mysqli->query("SELECT * FROM vidStore WHERE 1");
		}
		
		$res->data_seek(0);
		while($row = $res->fetch_assoc()){
			if($row['rented'] == 1){
				$rentStatus = "Checked Out";
				$buttonText = "Return";
			} else {
				$rentStatus = "Available";
				$buttonText = "Checkout";
			}
			echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td><td>" 
			. $row['category'] . "</td><td>" . $row['length'] . "</td><td>" . $rentStatus 
			. "</td><td align='center'><form method='post' style='margin: 0; text-align: center;' action='dataStorage.php'>
			<button type='submit' name='" . $buttonText . "' value='" . $row['id'] . "'>" . $buttonText . "</button></td></form><td>
			<form method='post' style='margin: 0; text-align: center;' action='dataStorage.php'>
			<button type='submit' name='Remove' value='" . $row['id'] . "'>Remove</button></td></form></tr>";
		}
		echo "</table>";

	}

	function addNewVideo(){
		if($_POST['inputName'] == ""){
			echo "ERROR: New videos must have a name!<br>";
		} else if (!empty($_POST['inputLength']) && !is_numeric($_POST['inputLength'])){
			echo "ERROR: Length must be a number!<br>";
		} else if (!empty($_POST['inputLength']) && $_POST['inputLength'] < 0){
			echo "ERROR: Length must be a positive number!<br>";
		} else {
			global $mysqli;
			if (!($stmt = $mysqli->prepare("INSERT INTO vidStore(name, category, length) VALUES (?, ?, ?)"))) {
	   			 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			$stmt->bind_param('ssi', $_POST['inputName'], $_POST['inputCategory'], $_POST['inputLength']);
			$stmt->execute();
			$stmt->close();
		}
	}

	function deleteAllVideos(){
		global $mysqli;
		$stmt = $mysqli->prepare("DELETE FROM vidStore WHERE 1");
		$stmt->execute();
		$stmt->close();

	}

	function returnVideo(){
		global $mysqli;
		$stmt = $mysqli->prepare("UPDATE vidStore SET rented = NOT rented WHERE id = (?)");
		$stmt->bind_param('i', $_POST['Return']);
		$stmt->execute();
		$stmt->close();

	}

	function checkOutVideo(){
		global $mysqli;
		$stmt = $mysqli->prepare("UPDATE vidStore SET rented = NOT rented WHERE id = (?)");
		$stmt->bind_param('i', $_POST['Checkout']);
		$stmt->execute();
		$stmt->close();

	}

	function removeVideo(){
		global $mysqli;
		$stmt = $mysqli->prepare("DELETE FROM vidStore WHERE id = (?)");
		$stmt->bind_param('i', $_POST['Remove']);
		$stmt->execute();
		$stmt->close();

	}

	function createFilter(){
		global $mysqli;
		$res = $mysqli->query("SELECT DISTINCT category FROM vidStore WHERE category != ''");
		$res->data_seek(0);
		echo "<form method='post' action='dataStorage.php'><select name='category'>";
		while($row = $res->fetch_assoc()){
			echo "<option value='" . $row['category'] . "'>" . $row['category'] . "</option>";
		}
		echo "<option value='all'>All Categories</option>";
		echo "</select><button type='submit' name='filter' value='Filter'>Filter</button></form>";
	}


	// if (!$mysqli->query("DROP TABLE IF EXISTS test") || !$mysqli->query("CREATE TABLE test(id INT)")) {
	//     echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
	// }

	// /* Prepared statement, stage 1: prepare */
	// if (!($stmt = $mysqli->prepare("INSERT INTO test(id) VALUES (?)"))) {
	//     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	// }

	// /* Prepared statement, stage 2: bind and execute */
	// $id = 1;
	// if (!$stmt->bind_param("i", $id)) {
	//     echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	// }

	// if (!$stmt->execute()) {
	//     echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	//}

	?>
</body>
</html>