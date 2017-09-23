<?php
// Added: 2017-05-19
// Modified: 2017-05-19

// These lines tell the browser not to cache this page
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Character encoding to use
header("Content-Type: text/html; charset=UTF-8");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>MPD Client Web Interface</title>
<?php

// Even if auto-refresh is turned on, these pages will not be automatically refreshed
$norefresh = array(
    'database',
    'playlists',
    'songinfo'
);

// These pages will automatically refresh when the song changes
$nextsongrefresh = array(
    'songinfo'
);

// Anything faster than 3 seconds makes it very hard, borderline impossible, to use the interface.
if ($config['refresh_delay'] < 3) {
    $config['refresh_delay'] = 3;
}

// Initial value
$refreshdelay = -1;

// Connect to the MPD server
$mpc = new MpdClient($config['host'], $config['port'], $config['password']);

if ((!in_array($pagename, $norefresh) === true) && $refreshstate == 'on') {
    // Use refresh delay from config file
    $refreshdelay = $config['refresh_delay'];
}

if (in_array($pagename, $nextsongrefresh)) {
    // Set refresh delay to number of seconds remaining for 'now playing' song

    // Retreive info about currently playing song
    $nowplaying = $mpc->nowPlaying();

    // These values are in seconds
    list($elapsedtime, $totaltime) = explode(':',$nowplaying['Time']);

    // Set the day in seconds
    $refreshdelay = (int)(($totaltime - $elapsedtime));
}

if ($refreshdelay > -1) {
    echo '<meta http-equiv="refresh" content="'.$refreshdelay.';URL=/index.php?page='.$pagename.'">';
}
    echo '<link rel="stylesheet" href="/skins/main.css">
    <link rel="stylesheet" href="/skins/menu.css">';

// Load the page specific CSS stylesheet
echo '<link rel="stylesheet" href="/skins/'.$pagename.'.css">';

// Load the color scheme
include_once $IP.'/skins/'.$config['skin'].'/colors.php';

// Load the PHP enhanced CSS stylesheet(s)
echo "\n".'<style type="text/css" media="all">';
include_once $IP.'/skins/main.css.php';
$pagecssphp = $IP.'/skins/'.$pagename.'.css.php';
if(is_readable($pagecssphp)) {
    include_once $pagecssphp;
}
echo "</style>\n";
?>
</head>
<body>
