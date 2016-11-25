<?php
session_start();

if (!isset($_SESSION["auth"])) {
	$_SESSION["auth"] = false;
}

if (!$_SESSION["auth"]) {
	header("Location: log_in.php");
}

$edit = false;
if (isset($_GET["mode"]) && $_GET["mode"] == "edit")
{
	$edit = true;
}	

require("db_open.php");
require("character_utils.php");

$class_array = array();
$race_array = array();

// Get the array of classes
$class_array = mysqli_fetch_all(mysqli_query($con, "SELECT class_id, class_name FROM classes;"), MYSQLI_ASSOC);
// Get the length of the class array
$class_length = count($class_array);

// Get the array of races
$race_array = mysqli_fetch_all(mysqli_query($con, "SELECT race_id, race_name FROM races;"), MYSQLI_ASSOC);
// Get the length of the race array
$race_length = count($race_array);

/*
	Need:
		Name:
			Random from names file based on race
		Level:
			1
		Strength, Dexterity, Constitution, Intelligence, Wisdom, Charisma:
			Roll 4d6, drop lowest. (Can be optimized later but for base, this works)
		Weight:
			Based on race and gender
		Height:
			Based on race and gender
		Age:
			Based on race and class
		Religion:
			Random from deities file.
		Gender:
			Male of Female.
		Class:
			Random class from the array.
		Race:
			Random race from the arraay.
		Hit Points:
			Based on class, add const modifier.
		Alignment: (not in database, have to manually set name.)
			Base pure random, future based on table. Maybe put restrictions.
		Money:
			Based on class. Add to the database. Page #111 in the pdf.

	Order:
		Level,
		Stats,
		Religion,
		Gender,
		Class,
		Race,
		Name,
		Weight,
		Height,
		Age,
		Hit Points,
		Alignment,
		Money
*/

// Level
$level = 1;

// Stats
// Roll the stats by rolling 4d6 and dropping the lowest.
function roll_stats(){
	$array = [0, 0, 0, 0];
	$sum = 0;
	// Roll 4d6
	for($i = 0; $i < 4; $i++)
	{
		$array[i] = rand(1, 6);
		// Add to the sum
		$sum += $array[i];
	}
	// Drop the lowest
	$sum -= min($array);
	return $sum;
}

// Strength
$str = roll_stats();
// Dexterity
$dex = roll_stats();
// Constitution
$con = roll_stats();
// Intelligence
$int = roll_stats();
// Wisdom
$wis = roll_stats();
// Charisma
$cha = roll_stats();

// Religion
// Read the religions into an array
$religion_array = file("heities.txt", FILE_IGNORE_NEW_LINES);
// Grab a random index in the array
$rand_index = rand(0, count($religion_array));
$religion = $religion_array[$rand_index];

// Gender
$rand_index = rand(0, 1);
if($rand_index == 0){
	// Male
	$gender = "Male";
}else{
	// Female
	$gender = "Female";
}

// Class
// Grab a random class from the array
$rand_index = rand(0, $class_length);
$class = class_array[$rand_index];

// Race
// Grab a random class from the array
$rand_index = rand(0, $race_length);
$race = race_array[$rand_index];

// Name - may be a bit difficult


// Weight


// Height


// Age


// Hit Points


// Alignment


// Money









// check if all form data exists
// TODO Either allow nullable fields to be unset or change nullable fields to nonullable fields
$is_form_full = !empty($_POST["character_name"])
	&& isset($_POST["character_level"])
	&& isset($_POST["str_attr"])
	&& isset($_POST["int_attr"])
	&& isset($_POST["cha_attr"])
	&& isset($_POST["con_attr"])
	&& isset($_POST["dex_attr"])
	&& isset($_POST["wis_attr"])
	&& isset($_POST["weight"])
	&& isset($_POST["height"])
	&& isset($_POST["age"])
	&& !empty($_POST["religion"])
	&& !empty($_POST["gender"])
	&& isset($_POST["char_class"])
	&& isset($_POST["race"])
	&& isset($_POST["hit_points"])
	&& !empty($_POST["alignment"])
	&& isset($_POST["money"]);

if ($edit) {
	if (isset($_GET["char"])) {
		$charId = $_GET["char"];
	} else {
		header("Location: error.php");
	}

	if (isset($_SESSION["allowed"][$charId])) {
		require("db_open.php");
		$result = mysqli_query($con, "SELECT * FROM characters WHERE character_id='$charId'");
		$row = mysqli_fetch_array($result);

		$result_class = mysqli_query($con, "SELECT class_name FROM classes WHERE class_id='{$row["char_class"]}' ;");
		if ($result_class) {
			$class_row = mysqli_fetch_array($result_class);
			$_SESSION["class"] = $class_row["class_name"];
		}

		$result_race = mysqli_query($con, "SELECT race_name FROM races WHERE race_id='{$row["race"]}' ;");
		if ($result_race) {
			$race_row = mysqli_fetch_array($result_race);
			$_SESSION["race"] =  $race_row["race_name"];
		}
	} else {
		header("Location: error.php");
	}
}
	
if ($is_form_full) {
	$form_array = array();
	$form_array["character_name"] = mysqli_real_escape_string($con, $_POST["character_name"]);
	$form_array["character_level"] = intval($_POST["character_level"]);
	$form_array["str_attr"] = intval($_POST["str_attr"]);
	$form_array["int_attr"] = intval($_POST["int_attr"]);
	$form_array["cha_attr"] = intval($_POST["cha_attr"]);
	$form_array["con_attr"] = intval($_POST["con_attr"]);
	$form_array["dex_attr"] = intval($_POST["dex_attr"]);
	$form_array["wis_attr"] = intval($_POST["wis_attr"]);
	$form_array["weight"] = intval($_POST["weight"]);
	$form_array["height"] = intval($_POST["height"]);
	$form_array["age"] = intval($_POST["age"]);
	$form_array["religion"] = mysqli_real_escape_string($con, $_POST["religion"]);
	$form_array["gender"] = mysqli_real_escape_string($con, $_POST["gender"]);
	$form_array["char_class"] = intval($_POST["char_class"]);
	$form_array["race"] = intval($_POST["race"]);
	$form_array["hit_points"] = intval($_POST["hit_points"]);
	$form_array["alignment"] = mysqli_real_escape_string($con, $_POST["alignment"]);
	$form_array["money"] = floatval($_POST["money"]);

	$res = mysqli_query($con, "SELECT user_id FROM users WHERE username = '{$_SESSION["user"]}' ;");
	if ($res) {
		$row = mysqli_fetch_array($res);
		$form_array["user_id"] = $row["user_id"];
	} else {
		header("Location: index.php");
	}

	if (is_valid($con, $form_array)) {
		if ($edit)
		{
			$set_str = "";
			foreach ( $form_array as $key => $value ) {
				if ( $key === "user_id" ) {
					$set_str = $set_str . $key . "='" . $value . "' ";
				} else {
					$set_str = $set_str . $key . "='" . $value . "', ";
				}
			}
			mysqli_query($con, "UPDATE characters SET $set_str WHERE character_id=$charId;");
			header("Location: character.php?" . http_build_query($_GET)); # TODO Fix to not add edit to URL
		} else {
			$insert_str = "INSERT INTO characters (";
			$values_str = "VALUES (";

			foreach ( $form_array as $key => $value ) {
				if ( $key === "user_id" ) {
					$insert_str = $insert_str . $key . ")";
					$values_str = $values_str . "'" . $value . "')";
				} else {
					$insert_str = $insert_str . $key . ",";
					$values_str = $values_str . "'" . $value . "',";
				}
			}
			$newChar = mysqli_multi_query($con, "$insert_str $values_str");
			header("Location: index.php");
		}
	} # doesn't do anything if invalid because invalid form data would require user to subvert html form
}
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="screen.css" rel="stylesheet" type="text/css" media="screen" />
		<title><?php echo ($edit ? "Edit " . $row["character_name"] : "Create Character") ?></title>
		<script type="text/javascript">
		//<![CDATA[
//			function validateForm() {
//			}
		//]]>
		</script>
	</head>
	<body>
		<?php require("header.php"); ?>
		<h1><?php echo ($edit ? "Edit " . $row["character_name"] : "Create Character") ?></h1>

		<form name="form" method="post">

			<label for="character_name">Name:</label>
			<input type="text" name="character_name" placeholder="Name" maxlength="50" required="required" value="<?php echo ($edit ? $row["character_name"] : "") ?>">
			
			<label for="character_level">Level:</label>
			<input type="number" name="character_level" required="required" value="<?php echo ($edit ? $row["character_level"] : 1) ?>" min="1" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="str_attr">Strength:</label>
			<input type="number" name="str_attr" value="<?php echo ($edit ? $row["str_attr"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="dex_attr">Dexterity:</label>
			<input type="number" name="dex_attr" value="<?php echo ($edit ? $row["dex_attr"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="con_attr">Constitution:</label>
			<input type="number" name="con_attr" value="<?php echo ($edit ? $row["con_attr"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="int_attr">Intelligence:</label>
			<input type="number" name="int_attr" value="<?php echo ($edit ? $row["int_attr"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="wis_attr">Wisdom:</label>
			<input type="number" name="wis_attr" required="required" value="<?php echo ($edit ? $row["wis_attr"] : 1) ?>" min="1" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="cha_attr">Charisma:</label>
			<input type="number" name="cha_attr" required="required" value="<?php echo ($edit ? $row["cha_attr"] : 1) ?>" min="1" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="weight">Weight (pounds):</label>
			<input type="number" name="weight" value="<?php echo ($edit ? $row["weight"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="height">Height (inches):</label>
			<input type="number" name="height" value="<?php echo ($edit ? $row["height"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="age">Age:</label>
			<input type="number" name="age" value="<?php echo ($edit ? $row["age"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>">
			
			<label for="religion">Religion:</label>
			<input type="text" name="religion" placeholder="Religion" maxlength="20" required="true" value="<?php echo ($edit ? $row["religion"] : "") ?>">

			<label for="gender">Gender:</label>
			<input type="text" name="gender" placeholder="Gender" maxlength="10" required="true" value="<?php echo ($edit ? $row["gender"] : "") ?>">

			<label for="char_class">Class:</label>
			<select name="char_class" required="required">
				<?php
					$array = $_SESSION["classes"];
					foreach ($array as $key => $value) {
				?>
					<option value="<?php echo $key; ?>" <?php echo ($key == $row["char_class"] ? "selected=\"selected\"" : "") ?>><?php echo $value; ?></option>
				<?php
					}
				?>
			</select>

			<label for="race">Race:</label>
			<select name="race" required="required">
				<?php
					$array = $_SESSION["races"];
					foreach ($array as $key => $value) {
				?>
					<option value="<?php echo $key; ?>" <?php echo ($key == $row["race"] ? "selected=\"selected\"" : "") ?>><?php echo $value; ?></option>
				<?php
					}
				?>
			</select>

			<label for="hit_points">Hit Points:</label>
			<input type="number" name="hit_points" required="required" value="<?php echo ($edit ? $row["hit_points"] : 0) ?>" min="<?php echo PHP_INT_MIN ?>" max="<?php echo PHP_INT_MAX ?>">

			<label for="alignment">Alignment:</label>
			<select name="alignment" required="required">
				<option value="LG" <?php echo ("LG" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Lawful Good</option>
				<option value="NG" <?php echo ("NG" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Neutral Good</option>
				<option value="CG" <?php echo ("CG" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Chaotic Good</option>
				<option value="LN" <?php echo ("LN" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Lawful Neutral</option>
				<option value="N" <?php echo ("N" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Neutral</option>
				<option value="CN" <?php echo ("CN" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Chaotic Neutral</option>
				<option value="LE" <?php echo ("LE" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Lawful Evil</option>
				<option value="NE" <?php echo ("NE" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Neutral Evil</option>
				<option value="CE" <?php echo ("CE" == $row["alignment"] ? "selected=\"selected\"" : "") ?>>Chaotic Evil</option>
			</select>

			<label for="money">Money:</label>
			<input type="number" name="money" required="required" value="<?php echo ($edit ? $row["money"] : 0) ?>" min="0" max="<?php echo PHP_INT_MAX ?>" step="0.01">
			<input type="submit" value="Submit" />

		</form>
	</body>
</html>
<?php
mysqli_close($con);
?>