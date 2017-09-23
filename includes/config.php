<?php
/**
 * Use this file to configure the web mpd client.
**/

$config = array(

    // Hostname or IP address of the computer where MPD is running
    'host' => 'localhost',

    // The port number specified in MPD's mpd.conf file.
    // Default port number for MPD is 6600.
    'port' => 6600,

    /**
     * This specifies a password for access to mpd.
     *
     * When set to NULL, no password will be sent. What happens next depends on
     * how MPD has been configured. If there are no passwords listed in mpd.conf,
     * you will get full control of MPD. If there is a password listed in mpd.conf,
     * you will be denied access to MPD and the connection will be closed
    **/
    'password' => NULL,

    // Set to true to show time elapsed. Set to false to show time remaining.
    'display_time_elapsed' => true,

    /**
     * Update the info on screen after this many seconds have elapsed since last update.
     *
     * Anything faster than 3 seconds makes it very hard, borderline impossible, to use the interface.
     *
     * There is a check in _PageTop.php that forces this delay to be at least 3 seconds.
    **/
    'refresh_delay' => 3,

    /**
     * Specify the skin to use
     *
     * This is the name of a folder in /skins
    **/
    'skin' => 'default'
);
?>
