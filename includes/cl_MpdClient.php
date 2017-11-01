<?php
/**
 * Kravens web based MPD interface script
 *
 * NOTE: This class assumes all songs are sorted by artist and album.
 *
 * Added: 2017-05-19
 * Modified: 2017-06-09
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
         *      This is NOT the file path on the disk.
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
        $result = $this->sendCommand('lsinfo', $uri);

        if (isset($result['error'])) {
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $title = $result[0]['Title'];

        /////
        // First check if the song is already on the current playlist
    	$result = $this->sendCommand('playlistsearch', 'title', $title);

        if (isset($result['error'])) {
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        if (empty($result[0])) {
            // Song not in current playlist

            // Add to current playlist
            $result = $this->sendCommand('add', $uri);

            if (isset($result['error'])) {
                echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
                throw new SystemExit();
            }

            // Now we can get the song ID
            $result = $this->sendCommand('playlistsearch', 'title', $title);

            if (isset($result['error'])) {
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
                        return $data['Pos'];
                    }
                }

                // No matches so play nothing
                $rv = '';

            }
        } elseif (!empty($result[0])) {
            if (count($result) == 1) {
                // Only one song named $title
                $rv = $result[0]['Pos'];
            } else {
                // Multiple songs named $title
                // Filter by artist name

                $artist = substr($uri, 0, strpos($uri, '/'));

                foreach ($result as $key => $data) {
                    if ($data['Artist'] = $artist) {
                        return $data['Pos'];
                    }
                }

                // No matches so play nothing
                $rv = '';

            }

        } else {
            // Something unexpected happened
            echo 'Received unexpected data from the command "playlistsearch title '.$title.'"<br>Data received:';
            echo '<pre>';
            print_r($results);
            echo '</pre>';
            throw new SystemExit();
        }

        return $rv;

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

        return '';

        // TODO:
        // 2017-06-16: Disabled until rewritten to handle array returned by playlistinfo

        $result = $this->sendCommand('playlistinfo');

        if (isset($result['error'])) {
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
            $result = $this->sendCommand('clear');

            // Add the filtered list to the play queue
            foreach ($list as $key => $uri) {
                $uri = substr($uri, 6);
                $result = $this->sendCommand('add', $uri);

                if (isset($result['error'])) {
                    echo $result['error'].'<br>'.$result['errorCmd'];
                    throw new SystemExit();
                }

            }
            // END foreach()
        }

    }

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

        $result = $this->sendCommand('commands');

        if (isset($result['error'])) {
            echo 'Unable to retrieve command list.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        foreach ($result as $key => $value) {
            // Save the command
            $commands[$value['command']] = true;

        }

        return $commands;
    }
    // END function getCommandList()

    public function getPlaylist($start = -1, $end = -1) {
        /**
         * Get info for a group of songs from the current playlist.
         *
         * Added: 2017-05-20
         * Modified: 2017-05-20
         *
         * @param Required integer $start The first song to get
         * @param Required integer $end The last song to get
         *
         * @return array
        **/

        if ($start > -1 && $end > 0) {
            // Get a group of songs
            $result = $this->sendCommand('playlistinfo', $start.':'.$end);
        } else {
            // Get all songs
            $result = $this->sendCommand('playlistinfo');
        }

        if (isset($result['error'])) {
            echo 'Unable to retrieve playlist.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $songlist = array();

        if (!empty($result[0])) {
            $count = -1;

            foreach ($result as $key => $value) {
                //if (count($key)
                $count++;
                $songlist[$count] = array();
                foreach ($value as $field => $data) {
                    $songlist[$count][$field] = trim($data);
                }

                if (!isset($songlist[$count]['Title'])) {
                    // Use file name for title
                    $a = explode('/', $songlist[$count]['file']);
                    $songlist[$count]['Title'] = array_pop($a);
                }
            } // END foreach ()
        }

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

    	$result = $this->sendCommand('listplaylist', $playlist);

        if (isset($result['error'])) {
            echo 'Unable to queue song for immediate playback.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $list = array();
        foreach ($result as $key => $value) {
            $list[] = $value['file'];
        }

        return $list;
    }

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
         * 2017-06-09: For some reason using $this->sendCommand('status') causes
         *      the script to lock up and apache to add a line similar to
         *           [core:notice] [pid 24213] AH00052: child pid 24243 exit signal Segmentation fault (11)
         *      to is error.log file.
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

    	$result = $this->sendCommand('listfiles', $curdir);

        if (isset($result['error'])) {
            echo 'Unable to list the directories and/or songs in "'.$curdir.'".<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $ret = array();

        $ret['directory'] = array();
        $ret['file'] = array();

        foreach($result as $key => $value) {
            foreach ($value as $k => $v) {
                if ($k == 'directory') {
                    $ret['directory'][] = $v;
                } elseif ($k == 'file') {
                    $ret['file'][] = $v;
                }
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

    	$result = $this->sendCommand('listplaylists');

        if (isset($result['error'])) {
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
         * Modified: 2017-05-22
         *
         * @param None
         *
         * @return array
        **/

    	$result = $this->sendCommand('currentsong');

        if (isset($result['error'])) {
            echo 'Unable to retrieve info about currently playing song.<br>Message from MPD: '.$result['error'];
            throw new SystemExit();
        }

        $songinfo = array();
        $result = $result[0];

        foreach ($result as $key => $value) {

            // Save to array
            $songinfo[$key] = trim($value);
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

    public function sendCommand($command, $arg1='', $arg2='') {
        /**
         * Sends a command to MPD.
         *
         * It is recommended that all commands to MPD be sent through this function.
         *
         * Added: 2017-05-20
         * Modified: 2017-06-16
         *
         * @param Required string $commmand A valid command from $this->getCommandList()
         * @param Optional string $arg1 An argument for the command
         * @param Optional string $arg2 A 2nd argument for the command
         *
         * @return boolean(false) or array
        **/

    	// Don't send the update command if a db update is currently running
     	if (isset($this->status["updating_db"]) && $command == "update") {
    		return false;
    	}

        // Intercept custom commands
        if ($command == 'playmenow') {
            $command = 'play';
            $arg1 = $this->_playMeNow($arg1);
        }

        // Command results stored here
        $ret = array();

        // Keeps fields with the same name from overwritting each other
        $count = 0;
        $ret[$count] = array();

        // Add the arguments to the end of the command
        if ($arg2 != '') {
            $command .= ' "'.$arg1.'" "'.$arg2.'"';
        } elseif ($arg1 != '') {
            $command .= ' "'.$arg1.'"';
        }

        // Send the command
        fputs($this->mpd_sock, $command."\n");

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
                $ret['errorCmd'] = 'Command received: '.$command; // The full command sent to MPD
                break;
            }

            $field = strtok($got, ":");

            if (isset($ret[$count][$field])) {
                $count++;
                $ret[$count] = array();
            }

            $ret[$count][$field] = strtok("\0");
            $ret[$count][$field] = trim($ret[$count][$field]);
        }

        if (!isset($ret['error'])) {
            // Update MPD status info
            $this->status = $this->getStatusInfo();

            if (substr($command, 0, 4) == 'load') {
                // $this->_playQueueDupCheck();
            }
        }

        // Send command results back to calling script
        return $ret;

    }
    // END function sendCommand()
}
// END class mpd()
/////

/**
 * Change log:
 *
 * 2016-06-11:
 *      -function getPlaylist() returns empty array if current play queue is empty
 *
 * 2017-06-09:
 *      -Added private function _playMeNow()
 *      -All functions now check if sendCommand() returned an error
 *
 * 2017-05-28:
 *      -Improved error handling in function listPlaylists()
 *      -Added public function getPlaylistContents()
 *
 * 2017-05-24:
 *      -Renamed function getNowPlaying() to nowPlaying()
 *      -Added public function getPlaylist()
 *      -Renamed function getSongGroup() to getPlaylist()
 *      -Added public function returnStatus()
 *      -Function nowPlaying() now returns an empty array if no song playing or paused
 *      -Function listFiles() returns an alphabetically sorted list
 *      -Added public function listPlaylists()
 *      -Removed public function commandlist()
 *      -Removed class variable $this->cmdlist
 *      -Change visibility of function getCommandList() from private to public
 *
 * 2017-05-22:
 *      -Removed public function status()
 *      -Change visibility of function getStatusInfo() from private to public
 *      -Added public function listFiles()
 *      -Function sendCommand() now returns command results
 *
 * 2017-05-21:
 *      -Function getNowPlaying() now returns the full 'Last-Modified' string
 *
 * 2017-05-20:
 *      -Added private function getCommandList()
 *      -Added public function commandlist()
 *      -Added public function sendCommand()
 *
 * 2017-05-19:
 *      -Created file
**/
?>
