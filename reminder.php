<?php
$offset = "04:00"; // Change this in accordance with your server.
function convertType ($type) {
	switch ($type) {
		case "pbt":
			return 7;
		case "comet":
			return 28;
	}
}
function convertTypeStart ($type) {
	switch ($type) {
		case "pbt":
			return 12;
		case "comet":
			return 29;
	}
}
function nextComet ($type, $arg) {
	$current = date("j-m-Y H:i");
	$currentTime = new DateTime($current);
	$currentTime = $currentTime->getTimestamp();
	$day = convertTypeStart($type);
	$stringType = $type;
	$type = convertType($type);
	$next = (int)date("j") - $type;
	if ($next == 0 && $stringType == "comet") {
		switch ($arg) {
			case "time":
				return "NOW!";
			case "bar":
				return 118;
		}
	} else if ($next > -1 && $next < 5 && $stringType !== "comet") {
		switch ($arg) {
			case "time":
				return "NOW!";
			case "bar":
				return 118;
		}
	} else {
		if ($next > 0) {
			$mo = (int)date("m") + 1;
			$mo = (string)$mo;
		} else {
			$mo = date("m");
		}
		$moB = (int)$mo - 1;
		$start = date($day."-".$moB."-Y ".$offset);
		$start = new DateTime($start);
		$start = $start->getTimestamp();
		switch ($arg) {
			case "time":
				$nextDate = date($type."-".$mo."-Y ".$offset);
				$diff = date_diff(new DateTime($current), new DateTime($nextDate));
				return $diff->format("%dd %Hh %im");
			case "bar":
				$nextDate = date($type."-".$mo."-Y ".$offset);
				$nextDate = new DateTime($nextDate);
				$timestamp = $nextDate->getTimestamp();
				$percentage = ($currentTime - $start) / ($timestamp - $start);
				$size = floor(111 * $percentage);
				return $size + 7;
		}
	}
};
header("Content-Type: image/png");
$feeds = 0;
if ($_GET["comet"] != "false")
	$feeds++;
if ($_GET["pbt"] == "true")
	$feeds++;
if ($_GET["xpcount"] == "true") {
	$feeds += 1.2;
	if ($_GET["name"]) {
		$name = str_replace(" ", "", $_GET["name"]);
		$name = str_split($name, 12)[0];
	} else {
		$name = "INSERT NAME";
	}
	if (!$_GET["level"] && !$_GET["xp"]) {
		$xp = 0;
		$level = 0;
		$bar = 7;
	} else {
		$level = $_GET["level"];
		$xp = $_GET["xp"];
		$maxXp = 50 * (pow($level, 2) + 2);
		$percentage = $xp / $maxXp;
		if ($percentage > 1) {
			$percentage = 1;
		}
		$bar = floor(136 * $percentage) + 7;
	}
}
$im = @imagecreatetruecolor($feeds * 125, 65)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 0, 0, 0);
if ($_GET["colour"]) {
	$str = $_GET["colour"]; 
	$hex = str_replace("#", "", $str);
	switch (strlen($hex)) {
		case 6:
			$seperator = 2;
		    $rgb = str_split($hex, $seperator);
			$theme_color = imagecolorallocate($im, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]));
			break;
		default:
			$theme_color = imagecolorallocate($im, 0, 155, 0);
			break;
	}
} else {
	$theme_color = imagecolorallocate($im, 0, 155, 0);
}
imagecolortransparent($im, $background_color);
$queue = 0;
if ($_GET["comet"] != "false") {
	$offset = $queue * 125;
	imagestring($im, 2, $offset + 5, 5,  "Night of the Comet", $theme_color);
	imagestring($im, 2, $offset + 5, 18, nextComet("comet", "time"), $theme_color);
	imagerectangle($im, $offset + 5, 35, $offset + 120, 45, $theme_color);
	imagefilledrectangle($im, $offset + 7, 37, nextComet("comet", "bar") + $offset, 43, $theme_color);
	$queue++;
}
imagestring($im, 2, 5, 47,  "cernodile.com", $theme_color);
imagestring($im, 2, $feeds * 125 - 27, 47, "2017", $theme_color);
if ($_GET["pbt"] == "true") {
	$offset = $queue * 125;
	$queue++;
	imagestring($im, 2, $offset + 5, 5,  "The Grand Tournament", $theme_color);
	imagestring($im, 2, $offset + 5, 18, nextComet("pbt", "time"), $theme_color);
	imagerectangle($im, $offset + 5, 35, $offset + 120, 45, $theme_color);
	imagefilledrectangle($im, $offset + 7, 37, nextComet("pbt", "bar") + $offset, 43, $theme_color);
}
if ($_GET["xpcount"] == "true") {
	$offset = $queue * 125;
	imagestring($im, 2, $offset + 5, 5,  "Level and XP Progress", $theme_color);
	imagestring($im, 2, $offset + 5, 18, $name." - Level ".$level, $theme_color);
	imagerectangle($im, $offset + 5, 35, $offset + 145, 45, $theme_color);
	imagefilledrectangle($im, $offset + 7, 37, $bar + $offset, 43, $theme_color);
}
imagepng($im);
imagedestroy($im);
?>
