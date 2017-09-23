<?php
// Report all errors
error_reporting(E_ALL);
ini_set('display_errors', 'on');

/**
 * File: _StartHere.php
 *
 * Added: 2017-05-19
 * Modified: 2017-05-19
 *
 * This is the main entry point for the web server.
**/

/**
 * Valid web server entry point, enable includes.
 *
 * Please don't move this line to /includes/Defines.php. This line essentially
 * defines a valid entry point. If you put it in includes/Defines.php, then
 * any script that includes it becomes an entry point, thereby defeating
 * its purpose.
**/
define('MPDCLIENT', true);

/**
 * Full path to working directory.
 *
 * $IP stands for Install Path.
 *
 * Using $IP instead of $_SERVER['DOCUMENT_ROOT'] will allow me to change the method
 * used to determine the working directory instead of going through a dozen scripts
 * to change a variable name.
**/
$IP = $_SERVER['DOCUMENT_ROOT'];

// Start the autoloader, so that modules can derive classes from core files
require $IP.'/includes/AutoLoader.php';

// Load the configuration file
if (is_file($IP.'/includes/config.php')) {
    require_once $IP.'/includes/config.php';
} else {
    die('Configuration file missing.');
}

require $IP.'/includes/GlobalFunctions.php';

// Allows a clean exit from the calling pages when MPD returns an error message
// When an error condition occurs, call "throw new SystemExit();"
class SystemExit extends Exception {}

// Server-side form data validation and sanity checking
// This is a global variable.
$valid = new Validate();

// This is a global variable.
// Used in _menu.php and _PageBottom.php to position the content of each page
$maintable = new HtmlTable();
?>
