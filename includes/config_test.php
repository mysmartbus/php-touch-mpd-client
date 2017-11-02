<?php
/**
 * Use this file to configure the web mpd client.
**/

$config = array(

    /**
     * Hostname or IP address of the computer where MPD is running
     *
     * Default: localhost
    **/
    'host' => 'localhost',

    /**
     * The port number specified in MPD's mpd.conf file.
     *
     * Default port number for MPD is 6600.
    **/
    'port' => 6600,

    /**
     * This specifies a password for access to mpd.
     *
     * When set to NULL, no password will be sent. What happens next depends on
     * how MPD has been configured. If there are no passwords listed in mpd.conf,
     * you will get full control of MPD. If there is a password listed in mpd.conf,
     * you will be denied access to MPD and the connection will be closed
     *
     * Default: NULL
    **/
    'password' => NULL,

    /**
     * Set to true to show time elapsed.
     * Set to false to show time remaining.
     *
     * Default: true
    **/
    'display_time_elapsed' => true,

    /**
     * Update the info on screen after this many seconds have elapsed since last update.
     *
     * Anything faster than 3 seconds makes it very hard, borderline impossible, to use the interface.
     *
     * There is a check in _PageTop.php that forces this delay to be at least 3 seconds.
     *
     * Default delay: 3
    **/
    'refresh_delay' => 3,

    /**
     * Every press of the volume up/down buttons will change the volume by this amount.
     *
     * MPD sees the volume as a percentage from 0-100.
     *
     * If the current song volume is at 42% and this setting has a value of 3, pressing the volume up
     * button will increase the song volume to 45%. If the volume down button is pressed, the song
     * volume will change to 39%.
     *
     * Default: 3
    **/
    'volchange' => 3,

    /**
     * Specify the skin to use
     *
     * This is the name of a folder in /skins
     *
     * Default Skin: default
    **/
    'skin' => 'default',

    /**
     * The URL to use to retrieve the song lyrics.
     *
     * Leave blank to disable.
     *
     * To use this feature, you will need to setup your own database server and website to
     * host the song_api.php file. There does not appear to be any publicly available websites that will
     * allow you to automatically download the full song lyrics.
     *
     * Default URL: <blank>
     * Example URL: http://www.example.com/song_api.php?title={title}&artist={artist}&album={album}&return=lyrics
    **/
    'get_lyrics_url' => '',

    /**
     * The URL to use to retrieve the album cover art.
     *
     * Leave blank to disable.
     *
     * To use this feature, you will need to setup your own database server and website to
     * host the song_api.php file. There does not appear to be any publicly available websites that will
     * allow you to automatically download the full song lyrics.
     *
     * Default URL: <blank>
     * Example URL: http://www.example.com/song_api.php?title={title}&artist={artist}&album={album}&return=coverart
    **/
    'get_coverart_url' => '',

    /**
     * Default volume level to set MPD to.
     *
     * Must be an integer from 0 to 100. Setting to 0 will mute the audio, while a value of 100 will set
     * the volume to maximum.
     *
     * Default Level: 45
    **/
    'default_volume' => 45
);
?>
