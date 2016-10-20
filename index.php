<?php
session_start();

if (!isset($_SESSION["auth"])) {
	$_SESSION["auth"] = false;
}

if (!$_SESSION["auth"]) {
	header("Location: log_in.php");
} else {
	require("db_open.php");
	$array = array();
	$result = mysqli_query($con, "SELECT character_id, character_name FROM characters, users 
	WHERE username = '{$_SESSION["user"]}' and characters.user_id = users.user_id;");
	if ($result) {
		while($row = mysqli_fetch_array($result)) {
			$array[$row["character_id"]] = $row["character_name"];
		}
		$_SESSION["allowed"] = $array;
	}
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="screen.css" rel="stylesheet" type="text/css" media="screen" />
		<title>Welcome!</title>
	</head>
	<body>
		<?php require("header.php"); ?>
		<h1>Welcome, <?php echo $_SESSION["user"]; ?>!</h1>
		<a href="character_form.php">Create Character</a>
		<h2>Your Characters:</h2>
		<ul>
<?php
			$array = $_SESSION["allowed"];
			foreach ($array as $key => $value) {
?>
			<li><a href="character.php?char=<?php echo $key; ?>"><?php echo $value; ?></a></li>
<?php
			}
?>
		</ul>
	</body>
</html>
<?php
}
?>