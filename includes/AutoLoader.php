<?php

// The autoloader will not run unless the MPD client was properly started
if (!defined('MPDCLIENT')) {
    die('This file is part of Kravens MPD client. It is not a valid entry point.');
}

// Setup the autoloader for classes
function myAutoload($className) {

    global $IP;

    $filename = false;

    /**
     * If the class is not listed here, it probably won't get loaded.
    **/
    // Used only by this section of the website
    $classlist = array(
        'MpdClient' => 'includes/cl_MpdClient.php',
        'HtmlForm' => 'includes/cl_HtmlForm.php',
        'HtmlTable' => 'includes/cl_HtmlTable.php',
        'Validate' => 'includes/cl_Validation.php'
    );

    // Workaround for PHP bug <https://bugs.php.net/bug.php?id=49143> (5.3.2. is broken, it's
    // fixed in 5.3.6). Strip leading backslashes from class names. When namespaces are used,
    // leading backslashes are used to indicate the top-level namespace, e.g. \foo\Bar. When
    // used like this in the code, the leading backslash isn't passed to the auto-loader
    // ($className would be 'foo\Bar'). However, if a class is accessed using a string instead
    // of a class literal (e.g. $class = '\foo\Bar'; new $class()), then some versions of PHP
    // do not strip the leading backlash in this case, causing autoloading to fail.
    $className = ltrim($className, '\\');

    if (isset($classlist[$className])) {
        $filename = $IP.'/'.$classlist[$className];
    }

    if (!$filename) {
        // Class not found; let the next autoloader try to find it
        return;
    }

    if(is_readable($filename)) {
        require $filename;
    } else {
        // Use PHP's built-in Exception class here because our custom exception
        // classes may not have been loaded yet.
        throw new Exception("Unable to load class $className in $filename.");
    }
}

// Tells PHP which function to use for autoloading of classes
spl_autoload_register('myAutoload');

?>
