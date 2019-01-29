<?php
/**
 * MPD interface script written in PHP.
 *
 * This script imitates a telnet session with the MPD server.
 *
 * NOTE: This class assumes all songs are sorted by artist and album.
 *
 * Added: 2017-05-19
 * Modified: 2018-11-05 1249
 *
**/

/**
 * MPD command reference at http://www.musicpd.org/doc/protocol/index.html
**/

class MpdClient {

    private $isConnected = false;
    private $mpd_sock;
    private $protocol_version = '(unknown)';
    private $status;

    function __construct($server, $port, $password) {

        // Attempt to connect to the MPD server
        $this->mpd_sock = @fsockopen($server, $port, $errno, $errstr, 10);

        if (!$this->mpd_sock) {
            // Connection failed
            echo '<br>Unable to connect to the MPD server at '.$server.':'.$port.'<br>';
            echo 'Error Message: '.$errstr.'<br>Error Number: '.$errno;
            return;
        }

        // Connection established
        $this->isConnected = true;

        // Get MPD protocol version
    	while (!feof($this->mpd_sock)) {
	    	$got = fgets($this->mpd_sock, "1024");
	    	if (strncmp("OK", $got, strlen("OK")) == 0) {
                $this->protocol_version = preg_replace( "/^OK MPD /", "", $got);
                break;
	    	}
	    	if (strncmp("ACK", $got, strlen("ACK")) == 0) {
	    		break;
	    	}
	    }

        // Update MPD status info
        $this->status = $this->getStatusInfo();
    }
    // END function __construct()

    private function _playMeNow($uri) {
        /**
         * Adds the selected file to the current play queue and tells MPD to play it.
         *
         * Added: 2017-06-09
         * Modified: 2017-06-09
         *
         * @param Required string $uri Full path to song in MPDs database
         *      This is NOT the file path on the servers hard drive.
         *
         *      If "music_directory" in mpd.conf is set to /home/mymusic/music,
         *      then the correct $uri would be "artist/album/song_name.mp3". The
         *      incorrect $uri would be "/home/mymusic/music/artist/album/song_name.mp3"
         *
         * @return integer
        **/

        // 
        $rv = '';

        // Removes the leading forward slash if any
        $uri = ltrim($uri, "/");

        // Need to get the songs title according to MPD
        $result = $this->_sendCommand('lsinfo', $uri);

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        foreach ($result as $key => $value) {
            # Get the songs title
            if ($value['field'] == 'Title') {
                $title = trim($value['data']);
                break;
            }
        }

        /////
        // First check if the song is already on the current playlist
    	$result = $this->_sendCommand('playlistsearch', 'title', $title);

        if (empty($result) || isset($result['error'])) {
            // Song not in current playlist or the file was renamed/moved/deleted since the playlist was loaded
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $songlist = array();
        $count = -1;

        foreach ($result as $key => $value) {

            if ($value['field'] == 'file') {

                // Make sure the previous song has a title to display
                if (!isset($songlist[$count]['Title']) && isset($songlist[$count]['file'])) {
                    // Use file name for title
                    $a = explode('/', $songlist[$count]['file']);
                    $songlist[$count]['Title'] = array_pop($a);
                }

                // Increment $count for each song
                $count++;

                $songlist[$count] = array();

            }

            $songlist[$count][$value['field']] = trim($value['data']);

        } // END foreach ()

        if (empty($songlist[0])) {

            // Add to current playlist
            // - The result is ignored unless it indicates an error
            $result = $this->_sendCommand('add', $uri);

            if (empty($result) || isset($result['error'])) {
                echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
                throw new SystemExit();
            }

            // Now we can get the song ID
            $result = $this->_sendCommand('playlistsearch', 'title', $title);

            if (empty($result) || isset($result['error'])) {
                echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
                throw new SystemExit();
            }

            if (count($result) == 1) {
                // Only one song in playlist named $title
                $rv = $result[0]['Pos'];
            } else {
                // Multiple songs in playlist named $title
                // Filter by artist name

                $artist = substr($uri, 0, strpos($uri, '/'));

                foreach ($result as $key => $data) {
                    if ($data['Artist'] = $artist) {

                        // Play first song that matches
                        $rv = $data['Pos'];
                        break;
                    }
                }

            }
        } elseif (!empty($songlist[0])) {
            if (count($result) == 1) {
                // Only one song named $title
                $rv = $result[0]['Pos'];
            } else {
                // Multiple songs named $title
                // Filter by artist name

                $artist = substr($uri, 0, strpos($uri, '/'));

                foreach ($result as $key => $data) {
                    if ($data['Artist'] = $artist) {

                        // Play first song that matches
                        $rv = $data['Pos'];
                        break;
                    }
                }
            }

        } else {
            // Something unexpected happened
            echo 'Received unexpected data from the command "playlistsearch title '.$title.'"<br>Data received:';

            // Display the data received
            echo '<pre>';
            print_r($songlist);
            echo '</pre>';
            throw new SystemExit();
        }

        if ($rv != '') {
            // Play the song
            return $this->_sendCommand('play', $rv);
        }

        // No song to play
        return false;
    }

    private function _playQueueDupCheck() {
        /**
         * Checks for and removes duplicate songs from the play queue
         *
         * Added: 2017-06-16
         * Modified: 2017-06-16
         *
         * @param None
         *
         * @return None
        **/

        // TODO:
        // 2017-06-16: Disabled until rewritten to handle array returned by playlistinfo
        return '';

        $result = $this->_sendCommand('playlistinfo');

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to retrieve play queue.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $list = $result[0];
        $countpre = count($list);

        // TODO:
        // 1) Need to run nowPlaying().
        // 2) Add currently playing song to $list if not already in $list
        // 3) Resume playback of song from nowPlaying() at time index reported by $this->status['elapsed']

        $list = array_unique($list);
        $countpost = count($list);

        if ($countpost < $countpre) {

            // Clear the current play queue
            $result = $this->_sendCommand('clear');

            // Add the filtered list to the play queue
            foreach ($list as $key => $uri) {
                $uri = substr($uri, 6);
                $result = $this->_sendCommand('add', $uri);

                if (empty($result) || isset($result['error'])) {
                    echo $result['error'].'<br>'.$result['errorCmd'];
                    throw new SystemExit();
                }

            }
            // END foreach()
        }
    }
    // END function _playQueueDupCheck()

    public function _sendCommand($command, $arg1='', $arg2='') {
        /**
         * Sends a command to MPD.
         *
         * Added: 2017-05-20
         * Modified: 2018-11-05 1320
         *
         * @param Required string $commmand A valid command from $this->getCommandList()
         *                                  or a custom command to do something MPD can't
         *                                  do on its own.
         * @param Optional string $arg1 An argument for the command
         * @param Optional string $arg2 A 2nd argument for the command
         *
         * @return array
        **/

        // Command results stored here
        $ret = array();

    	// Don't send the update command if a db update is currently running
     	if (isset($this->status["updating_db"]) && $command == "update") {
    		return $ret;
    	}

        // Add the arguments to the end of the command
        if ($arg2 != '') {
            $runcommand = $command . ' "' . $arg1 . '" "' . $arg2 . '"';
        } elseif ($arg1 != '') {
            $runcommand = $command . ' "' . $arg1 . '"';
        } else {
            $runcommand = $command;
        }

        // Send the command
        fputs($this->mpd_sock, $runcommand."\n");

        $count = 0;

        // Receive status info
        while (!feof($this->mpd_sock)) {

            $got = fgets($this->mpd_sock, "1024");
            $got = str_replace("\n", "", $got);

            if (strncmp("OK", $got, strlen("OK")) == 0) {
                break;
            }

            if (strncmp("ACK", $got, strlen("ACK")) == 0) {
                // Didn't get what we wanted
                $ret['error'] = $got; // Error message from MPD
                $ret['errorCmd'] = 'Command received: '.$runcommand; // The full command sent to MPD
                break;
            }

            /*
                Using strok() instead of explode() because some fields have a colon (:)
                in their value.
                See: https://secure.php.net/manual/en/function.strtok.php
             */

            // This gets the field name
            $ret[$count]['field'] = strtok($got, ":");

            // This gets the field value and removes leading and trailing whitespace " \t\n\r\0\x0B"
            // - The "/0" tells strtok() to return whatever is left of $got
            $ret[$count]['data'] = trim(strtok("\0"));

            $count++;
        }

        if (!isset($ret['error'])) {
            // Update MPD status info
            $this->status = $this->getStatusInfo();

            //if (substr($command, 0, 4) == 'load') {
            //    $this->_playQueueDupCheck();
            //}
        }

        // Send command results back to calling script
        return $ret;

    }
    // END function _sendCommand()

    private function _updatePlaylists($arg1, $songuri) {
        /**
         * Adds and removes a song as needed to match
         * the playlists selected for the song.
         *
         * Added: 2018-01-13 1808
         * Modified: 2018-01-17 1518
         *
         * @param Required $arg1 List of playlists to add the song to
         * @param Required $songuri The songs uri (from MPD)
         *
         * @return boolean
        **/

        $assignedplaylists = explode(',', $arg1);

        // Get a list of all of the playlists
        $lists = $this->listPlaylists();
        $lists = $lists['sorted'];

        // Check each playlist to see if it needs to be updated
        foreach($lists as $key => $playlist) {

            // List of songs currently on $playlist
            $contents = $this->getPlaylistContents($playlist);

            if (in_array($playlist, $assignedplaylists) && !in_array($songuri, $contents)) {
                // $playlist is in $assignedplaylists but $songuri is not on $playlist
                //
                // Add the song to the playlist
                $rv = $this->_sendCommand('playlistadd', $playlist, $songuri);

                // Go to next playlist
                continue;
            }

            if (!in_array($playlist, $assignedplaylists) && in_array($songuri, $contents)) {
                // $playlist is NOT in $assignedplaylists but $songuri IS on $playlist
                //
                // Remove song from playlist

                // Find all of the occurances of $songuri in $contents
                $keys = array_keys($contents, $songuri);

                // Remove each occurance
                foreach ($keys as $k => $v) {
                    unset($contents[$v]);
                }

                // Since MPD does not have a command to easily add a song to a
                // playlist, the entire playlist will need to be deleted and rewritten.

                // Delete the contents of the playlist file.
                // The playlist file itself is not deleted.
                $rv = $this->_sendCommand('playlistclear', $playlist);

                // Recreate the playlist without $songuri
                foreach ($contents as $k => $uri) {
                    $rv = $this->_sendCommand('playlistadd', $playlist, $uri);
                }

            }
        }
        // END foreach($lists as $key => $playlist)

        return true;
    }
    // END function _updatePlaylists

    /*****
     * BEGIN public functions
    *****/

    public function assignedPlaylists($uri) {
        /**
         * Creates a list of playlists that the current song is assigned to.
         *
         * Added: 2018-01-13 1445
         * Modified: 2018-01-13 1445
         *
         * @param Required string $uri Full path to song in MPDs database
         *      This is NOT the file path on the servers hard drive.
         *
         *      If "music_directory" in mpd.conf is set to /home/mymusic/music,
         *      then the correct $uri would be "artist/album/song_name.mp3". The
         *      incorrect $uri would be "/home/mymusic/music/artist/album/song_name.mp3"
         *
         * @return array
        **/

        // Removes the leading forward slash if any
        $uri = ltrim($uri, "/");

        // Store all of MPD's playlists
        $allplaylists = array();

        // The song referenced by $uri was found in
        // these playlists
        $assignedplaylists = array();

        // Get a list of MPD's playlists and put the names
        // into an array.
        $temp = $this->_sendCommand('listplaylists');
        foreach($temp as $key => $value) {
            $allplaylists[] = $value['playlist'];
        }

        foreach($allplaylists as $key => $playlist) {
            $rv = $this->_sendCommand('listplaylist', $playlist);

            foreach($rv as $key => $value) {
                if (array_key_exists('file', $value) && ($value['file'] == "$uri")) {
                    $assignedplaylists[] = $playlist;
                }
            }
        }

        return $assignedplaylists;
    }
    // END function assignedPlaylists()

    public function getCommandList() {
        /**
         * Retreive the list of commands supported by the MPD server
         *
         * Added: 2017-05-20
         * Modified: 2017-05-20
         *
         * @param None
         *
         * @return array
        **/

        $commands = array();

        $result = $this->_sendCommand('commands');

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to retrieve command list.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        foreach ($result as $key => $value) {

            // Save the command
            $commands[$value['data']] = true;

        }

        return $commands;
    }
    // END function getCommandList()

    public function getPlaylist($start = -1, $end = -1) {
        /**
         * Get info for a group of songs from the current playlist.
         *
         * Added: 2017-05-20
         * Modified: 2018-11-05 1345
         *
         * @param Required integer $start The first song to get
         * @param Required integer $end The last song to get
         *
         * @return array
        **/

        if ($start > -1 && $end > 0) {
            // Get a group of songs
            $result = $this->_sendCommand('playlistinfo', $start.':'.$end);
        } else {
            // Get all songs
            $result = $this->_sendCommand('playlistinfo');
        }

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to retrieve playlist.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $songlist = array();

        $count = -1;

        foreach ($result as $key => $value) {

            if ($value['field'] == 'file') {

                // Make sure the previous song has a title to display
                if (!isset($songlist[$count]['Title']) && isset($songlist[$count]['file'])) {
                    // Use file name for title
                    $a = explode('/', $songlist[$count]['file']);
                    $songlist[$count]['Title'] = array_pop($a);
                }

                // Increment $count for each song
                $count++;

                $songlist[$count] = array();

            }

            $songlist[$count][$value['field']] = trim($value['data']);

        } // END foreach ()

        return $songlist;
    }
    // END function getPlaylist()

    public function getPlaylistContents($playlist) {
        /**
         * Get the contents of a playlist
         *
         * Added: 2017-05-28
         * Modified: 2017-05-28
         *
         * @param Required string $playlist Name of playlist
         *
         * @return array
        **/

        // The contents of the playlist
        $list = array();

    	$result = $this->_sendCommand('listplaylist', $playlist);

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        foreach ($result as $key => $value) {
            if (!array_key_exists('file', $value)) {
                echo '$playlist = '.$playlist;
                echo '<pre>$result = ';
                print_r($result);
                echo '</pre>';
                break;
            }
            $list[] = $value['file'];
        }

        return $list;
    }
    // END function getPlaylistContents()

    public function getStatusInfo() {
        /**
         * Get current status of MPD
         *
         * Includes:
         *    current volume
         *    Repeat/random/single/consume state
         *    playlist #
         *    current state (play/pause/stop)
         *    info for current song
         *
         * Added: 2017-05-19
         * Modified: 2017-05-19
         *
         * @param None
         *
         * @return array
        **/

        $ret = array();

        /**
         * 2017-06-09: For some reason using $this->_sendCommand('status') causes
         *      the script to lock up and apache to add a line similar to
         *           [core:notice] [pid 24213] AH00052: child pid 24243 exit signal Segmentation fault (11)
         *      to the error.log file.
        **/

        // Request status info
        fputs($this->mpd_sock, "status\n");

        // Receive status info
        while (!feof($this->mpd_sock)) {
            $got = fgets($this->mpd_sock, "1024");
            $got = str_replace("\n", "", $got);

            if (strncmp("OK", $got, strlen("OK")) == 0) {
                break;
            }

            if (strncmp("ACK", $got, strlen("ACK")) == 0) {

                echo 'Unable to get the status from MPD.<br>Message from MPD: '.$result['error'];
                throw new SystemExit();
            }

            $field = strtok($got, ":");
            $ret[$field] = strtok("\0");
            $ret[$field] = ltrim($ret[$field]);
        }

        return $ret;
    }
    // END function getStatusInfo()

    public function inCurrentPlayQueue($uri) {
        /**
         * Checks if the song is in the play queue.
         *
         * Added: 2018-01-12
         * Modified: 2018-01-12
         *
         * @param Required string $uri Full path to song in MPDs database
         *      This is NOT the file path on the servers hard drive.
         *
         *      If "music_directory" in mpd.conf is set to /home/mymusic/music,
         *      then the correct $uri would be "artist/album/song_name.mp3". The
         *      incorrect $uri would be "/home/mymusic/music/artist/album/song_name.mp3"
         *
         * @return integer
        **/

        // Removes the leading forward slash if any
        $uri = ltrim($uri, "/");

        // Need to get the songs title according to MPD
        $result = $this->_sendCommand('playlistsearch', 'title', $uri);

        if (empty($result) || isset($result['error'])) {
            echo 'Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        if (count($result[0]) > 0) {
            // Is in current play queue
            return true;
        } else {
            // Not in current play queue
            return false;
        }

    }

    public function listFiles($curdir) {
        /**
         * List all directories and files inside $curdir
         *
         * Added: 2017-05-22
         * Modified: 2017-05-24
         *
         * @param Required string $curdir The directory to list
         *
         * @return array
        **/

    	$result = $this->_sendCommand('listfiles', $curdir);

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to list the directories and/or songs in "'.$curdir.'".<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $ret = array();

        $ret['directory'] = array();
        $ret['file'] = array();

        foreach($result as $key => $value) {
            if ($value['field'] == 'directory') {
                $ret['directory'][] = $value['data'];
            } elseif ($value['field'] == 'file') {
                $ret['file'][] = $value['data'];
            }
        }

        // Sort the inner arrays alphabetically
        sort($ret['directory']);
        sort($ret['file']);

        // Add "link" to parent directory if not in the root directory
        if ($curdir != '/') {
            array_unshift($ret['directory'], 'Parent...');
        }

        return $ret;
    }
    // END public function listFiles()
    /////

    public function listPlaylists() {
        /**
         * List all playlists in MPD's playlist directory
         *
         * Added: 2017-05-24
         * Modified: 2017-05-28
         *
         * @param None
         *
         * @return array
        **/

    	$result = $this->_sendCommand('listplaylists');

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to retrieve playlists.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $list = array();
        $list['original'] = $result;
        $list['sorted'] = array();

        foreach ($result as $key => $value) {
            foreach ($value as $type => $data) {
                if ($type == 'playlist') {
                    $list['sorted'][] = $data;
                }
            }
        }

        if (!empty($list['sorted'])) {
            sort($list['sorted']);
        }

        return $list;
    }
    // END function listPlaylists()
    /////

    public function nowPlaying() {
        /**
         * Get info on the currently playing song
         *
         * Added: 2017-05-20
         * Modified: 2018-11-05 1249
         *
         * @param None
         *
         * @return array
        **/

    	$result = $this->_sendCommand('currentsong');

        if (empty($result) || isset($result['error'])) {
            echo 'Unable to retrieve info about currently playing song.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $songinfo = array();

        foreach ($result as $key => $value) {

            // Save to array
            $songinfo[$value['field']] = trim($value['data']);
        }

        if (!empty($songinfo)) {

            // Update MPD status info
            $this->status = $this->getStatusInfo();

            // The time returned by currentsong does not include elapsed time.
            if (($this->status['state'] == 'play') || ($this->status['state'] == 'pause')) {
                $songinfo['Time'] = $this->status['time'];
            }

            // Make sure these array keys exist
            if (!isset($songinfo['Title'])) {
                $temp = explode('/',$songinfo['file']);
                $songinfo['Title'] = array_pop($temp);
            }
            if (!isset($songinfo['Album'])) {
                $songinfo['Album'] = 'Unknown Album';
            }
            if (!isset($songinfo['Track'])) {
                $songinfo['Track'] = '';
            }
            if (!isset($songinfo['Genre'])) {
                $songinfo['Genre'] = '';
            }
        }

        return $songinfo;
    }
    // END function nowPlaying()

    public function processCommand($command, $arg1='', $arg2='') {
        /**
         * Process a command before sending it to MPD.
         *
         * All commands must be sent through this function.
         *
         * Added: 2017-05-20
         * Modified: 2017-06-16
         *
         * @param Required string $commmand A valid command from $this->getCommandList()
         *                                  or a custom command to do something MPD can't
         *                                  do on its own.
         * @param Optional string $arg1 An argument for the command
         * @param Optional string $arg2 A 2nd argument for the command
         *
         * @return boolean(false) or array
        **/

        // Intercept custom commands
        if ($command == 'playmenow') {
            $rv = $this->_playMeNow($arg1);
        } elseif ($command == 'updateplaylists') {
            $rv = $this->_updatePlaylists($arg1, $arg2);
        } else {
            // $command is an MPD command or a typo
            $rv = $this->_sendCommand($command, $arg1, $arg2);
        }

        return $rv;
    }
    // END function processComamand()

    public function returnStatus() {
        /**
         * Allow info in $this->status to be used outside this class
         *
         * Added: 2017-05-24
         * Modified: 2017-05-24
         *
         * @param None
         *
         * @return array
        **/

    	return $this->status;
    }
    // END function returnStatus()
    /////
}
// END class mpd()
/////
?>
