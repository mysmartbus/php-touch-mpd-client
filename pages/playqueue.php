<?php
// Added: 2017-05-19
// Modified: 2017-09-18

require 'includes/_menu.php';

display_menu('playqueue');

// Sends a command to MPD if there is one
$command = $valid->get_value('command');
$arg1 = $valid->get_value('arg1');
$arg2 = $valid->get_value('arg2');
if ($command != '') {
    $rv = $mpc->processCommand($command, $arg1, $arg2);
    // TODO:
    //  Replace this with a popup message box so the flow
    //  of the page is not messed up
    if (count($rv) > 1) {
        echo '<pre>';
        print_r($rv);
        echo '</pre>';
    }
}

// Retreive info about currently playing song
$nowplaying = $mpc->nowPlaying();

// MPD status
$status = $mpc->returnStatus();

// List of valid commands
$commands = $mpc->getCommandList();

if (!empty($nowplaying)) {
    // Show the previous 4/next 4 songs in the current playlist
    $start = $status["song"] - 4;
    $end = $status["song"] + 5; // Why 5 instead of 4? Does MPD use zero based arrays?

    // $start must be equal to or greater than 0
    // $end can be greater than $status['playlistlength']
    if ($start < 0) {
        // $start is negative so this will add $start to $end
        $end -= $start;
        $start = 0;
    }

    $playlist = $mpc->getPlaylist($start, $end);
} else {
    // Display the first 11 songs of the playlist
    $playlist = $mpc->getPlaylist(0, 11);
}

$table = new HtmlTable();
$innertable = new HtmlTable();
$positiontable = new HtmlTable();

// Set volume levels for volume up/down buttons
$volumeup = $status['volume'] + $config['volchange'];
if ($volumeup > 100) {
    $volumeup = 100;
}
$volumedown = $status['volume'] - $config['volchange'];
if ($volumedown < 0) {
    $volumedown = 0;
}

/////
// BEGIN time string creation
if (!empty($nowplaying)) {
    if (($status['state'] == 'play') || ($status['state'] == 'pause')) {

        // These values are in seconds
        list($elapsedtime, $totaltime) = explode(':',$nowplaying['Time']);

        // Convert from seconds to minutes and seconds
        if ($config['display_time_elapsed'] === true) {
            // Show time elapsed
            $time_min = (int)($elapsedtime / 60);
            $time_sec = (int)($elapsedtime % 60);
        } else {
            // Show time remaining
            $time_min = (int)(($totaltime - $elapsedtime) / 60);
            $time_sec = (int)(($totaltime - $elapsedtime) % 60);
        }
        if ($time_sec < 0) {
            $time_sec *= -1;
            $time_min = -$time_min;
        } else if ($time_sec < 10) {
            $time_sec = "0$time_sec";
        }

        $timestring = "($time_min:$time_sec";

        // Calculate total time
        $time_min = (int)($totaltime / 60);
        $time_sec = (int)($totaltime - $time_min * 60);
        if ($time_sec < 10) {
            // Add leading zero if needed
            $time_sec = "0$time_sec";
        }

        if (!($time_min == 0 && $time_sec == "00")) {
            $timestring .= "/$time_min:$time_sec";
        }

        $timestring .= ")";
    } else {
        // Calculate total time
        $time_min = (int)($nowplaying['Time'] / 60);
        $time_sec = (int)($nowplaying['Time'] - $time_min * 60);
        if ($time_sec < 10) {
            // Add leading zero if needed
            $time_sec = "0$time_sec";
        }

        if (!($time_min == 0 && $time_sec == "00")) {
            $timestring = '(0:00/'.$time_min.':'.$time_sec.')';
        } else {
            $timestring = '(Unknown)';
        }
    }
} else {
    // End of playlist reached
    $timestring = '(0:00/0:00)';
    $elapsedtime = '0';
    $totaltime = '0';
}
// END time string creation
/////

/////
// BEGIN playqueue_maintable
$table->new_table('playqueue_maintable');

$table->new_row();

// Song name and artist/band name
$table->new_cell('song_artist_names_cell');
if (!empty($nowplaying)) {
    $cuttitle = cutString($nowplaying['Title']);
    $cutartist = cutString($nowplaying['Artist']);
    echo '<div class="nowplaying_title_artist_div">';
    echo '<span class="nowplaying_title_span">'.$cuttitle.'</span> by <span class="nowplaying_artist_span"><a href="/index.php?page=database&curdir='.$nowplaying['Artist'].'" class="undecorated_href">'.$cutartist.'</a></span>';
    echo '</div>';
} else {
    echo '&nbsp;';
}

$table->new_row();
$table->new_cell();

/////
// BEGIN time_and_seek_table
$positiontable->new_table('time_and_seek_table');
$positiontable->new_row();
$positiontable->new_cell('centertext');
if (($commands["seekid"] === true) && (!empty($nowplaying))) {
    // Restart song
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=seekid&amp;arg1='.$nowplaying['Id'].'&amp;arg2=0" title="Restart song" class="button">0:00</a>';
} else {
    echo '0:00';
}
$positiontable->new_cell();

/////
// BEGIN seekbar_table
$innertable->new_table('seekbar_table');
$innertable->new_row();
// Determines number of table cells used to create the seek bar
$time_div = 4;
$rndtimediv = round(100/$time_div);

if (($status['state'] == 'play') || ($status['state'] == 'pause')) {
    // Only add the position indicator if the song is playing or paused

    // Which cell should be highlighted?
    //$rndtimepercent = round(($elapsedtime * 100 / $totaltime) / $time_div);
    $rndtimepercent = floor(($elapsedtime * 100 / $totaltime) / $time_div);

    // Most of the seek bar will be in this color
    $color = $colors["seekbar_background"];

    for ($i = 0; $i < $rndtimediv; $i++) {
        if ($i == $rndtimepercent) {
            // Our seek bar position indicator
            $color = $colors["seekbar_foreground"];
        }

        // $seek is in seconds
        $seek = floor($i * $time_div * $totaltime / 100);

        // Display seek postion
        $min = (int)($seek / 60);
        $sec = $seek - $min * 60;

        if ($sec < 10) {
            $sec = "0".$sec;
        }

        echo "\n".'<td bgcolor="'.$color.'">';

        // Allow seeking if MPD supports it
        if ($commands["seekid"] === true) {
            echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=seekid&amp;arg1='.trim($nowplaying['Id']).'&amp;arg2='.$seek.'" title="Seek to '.$min.':'.$sec.'">';
        }

        // Use any 1x1 pixel image
        // The width="" and height="" values control the size of the seekbar_table
        echo '<img border="0" width="20" height="15" src="/skins/'.$config['skin'].'/SeekBarTransparent.gif">';

        // Close the href tag if it was started
        if ($commands["seekid"] === true) {
            echo "</a>";
        }

        echo "</td>";
        $color = $colors["seekbar_background"];
    }

} else {
    // Can't show playlist indicator because there is no song playing

    for ($i = 0; $i < $rndtimediv; $i++) {
        $innertable->new_cell('', 'bgcolor="'.$colors["seekbar_background"].'"');
        echo '<img border="0" width="20" height="15" src="/skins/'.$config['skin'].'/SeekBarTransparent.gif">';
    }
}
$innertable->new_cell();
$innertable->end_table();
// END seekbar_table
/////

$positiontable->new_cell();
echo $timestring;

$positiontable->end_table();
// END time_and_seek_table
/////

$table->new_row();
$table->new_cell();

/////
// BEGIN control_button_table
$innertable->new_table('control_button_table');
$innertable->new_row();

// Previous song
$innertable->new_cell();
echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=previous" title="Previous Song"><img src="skins/'.$config['skin'].'/ButtonPrevious.png" alt="Previous Song"></a>';

$innertable->new_cell();
if ($status['state'] == 'play') {
    // Pause playback
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=pause&amp;arg1=1" title="Pause"><div class="pause_button_div"><div class="pause_button_bar_div"><div class="pause_button_marker_left_div"></div><div class="pause_button_marker_right_div"></div></div></div></a>';
} else {
    if ($status['state'] == 'stop') {
        if (empty($nowplaying)) {
            if (empty($playlist)) {
                // Disable play button
                echo '<img src="skins/'.$config['skin'].'/ButtonPlayDisabled.png" alt="Play">';
            } else {
                // Begin playback from beginning of play list
                echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=play" title="Play"><img src="skins/'.$config['skin'].'/ButtonPlay.png" alt="Play"></a>';
            }
        } else {
            // Resume playback from beginning of song
            echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=play&amp;arg1='.$nowplaying['Pos'].'" title="Play"><img src="skins/'.$config['skin'].'/ButtonPlay.png" alt="Play"></a>';
        }
    } else {
        // Resume playback from current position in song
        echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=pause&amp;arg1=0" title="Play"><img src="skins/'.$config['skin'].'/ButtonPlay.png" alt="Play"></a>';
    }
}

// Next song
$innertable->new_cell();
echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=next" title="Next Song"><img src="skins/'.$config['skin'].'/ButtonNext.png" alt="Next Song"></a>';

// Stop button
$innertable->new_cell();
if ($status['state'] == 'stop') {
    // Disable stop button
    echo '<div class="stop_button_disabled_div"><div class="stop_button_marker_div"></div></div>';
} else {
    // Stop playing
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=stop" title="Stop"><div class="stop_button_enabled_div"><div class="stop_button_marker_div"></div></div></a>';
}

// Status cell
$innertable->new_cell('status_cell');
if ($status['state'] == 'stop') {
    echo '(Stopped)';
} elseif ($status['state'] == 'play') {
    echo '(Playing)';
} elseif ($status['state'] == 'pause') {
    echo '(Paused)';
} else {
    // What state are we in?
    echo '&nbsp;';
}

$innertable->new_cell();
echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=setvol&amp;arg1='.$volumedown.'" title="Volume Down"><img src="skins/'.$config['skin'].'/ButtonVolumeDown.png" alt="Volume Down"></a>';

// Mute/Unmute button
$innertable->new_cell();
if ($status['volume'] <= 0) {
    // Unmute audio
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=setvol&amp;arg1='.$config['default_volume'].'" title="Unmute"><img src="skins/'.$config['skin'].'/ButtonVolumeMuteActive.png" alt="Mute Off"></a>';
} else {
    // Mute audio
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=setvol&amp;arg1=0" title="Mute"><img src="skins/'.$config['skin'].'/ButtonVolumeMute.png" alt="Mute On"></a>';
}

$innertable->new_cell();
echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=setvol&amp;arg1='.$volumeup.'" title="Volume Up"><img src="skins/'.$config['skin'].'/ButtonVolumeUp.png" alt="Volume Up"></a>';

// Show volume level
$innertable->new_cell('volume_percent_cell');
echo '<span class="button" title="Volume level">Volume '.$status['volume'].'%</span>';

$innertable->end_table();
// END control_button_table
/////

$table->new_row();
$table->new_cell();

/////
// BEGIN modes_playlist_table
$positiontable->new_table();
$positiontable->new_row();
$positiontable->new_cell();

/////
// BEGIN modes_table
$onoffstring = '<br><span class="modes_on_off_span">Off&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;On</span>';

$innertable->new_table('modes_table');

// Random on/off
$innertable->new_row();
$innertable->new_cell();
echo 'Random<br><div class="button_background_div">';
if ($status['random'] == 0) {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=random&amp;arg1=1" title="Turn On"><div class="button_state_off_div"><div class="button_state_marker_div"></div></div></a>';
} else {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=random&amp;arg1=0" title="Turn Off"><div class="button_state_on_div"><div class="button_state_marker_div"></div></div></a>';
}
echo '</div>'.$onoffstring;

// Consume on/off
$innertable->new_row();
$innertable->new_cell();
echo 'Consume<br><div class="button_background_div">';
if ($status['consume'] == 0) {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=consume&amp;arg1=1" title="Turn On"><div class="button_state_off_div"><div class="button_state_marker_div"></div></div></a>';
} else {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=consume&amp;arg1=0" title="Turn Off"><div class="button_state_on_div"><div class="button_state_marker_div"></div></div></a>';
}
echo '</div>'.$onoffstring;


// Repeat on/off
$innertable->new_row();
$innertable->new_cell();
echo 'Repeat<br><div class="button_background_div">';
if ($status['repeat'] == 0) {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=repeat&amp;arg1=1" title="Turn On"><div class="button_state_off_div"><div class="button_state_marker_div"></div></div></a>';
} else {
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=repeat&amp;arg1=0" title="Turn Off"><div class="button_state_on_div"><div class="button_state_marker_div"></div></div></a>';
}
echo '</div>'.$onoffstring;

$innertable->end_table();
// END modes_table
/////

/////
// BEGIN playlist_table
$positiontable->new_cell();
$innertable->new_table('playlist_table');

// Header row
$innertable->new_row();
$innertable->new_cell();
echo '<div class="playlist_count_clear_div">';
echo '<div style="float:left;width:91%;">'; // <-- Required to get the playlist_count_div centered horizontally
echo '<div class="playlist_count_div">Playlist ('.$status['playlistlength'].' songs)</div>';
echo '</div>';
echo '<div class="clear_playqueue_button_div">';
if ($status['playlistlength'] > 0 ) {
    // Enable button to clear playlist
    echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=clear" class="button clear_playqueue_button">Clear</a>';
} else {
    // Disable button to clear playlist
    echo '<span class="button clear_playqueue_button disabled_button">Clear</span>';
}
echo '</div>';
echo "\n</div><!-- playlist_count_clear_div -->\n";

// List of songs
$innertable->new_row();
$innertable->new_cell();

echo '<div class="playlist_content_div">';
foreach ($playlist as $key => $value) {

    if (($status['state'] != 'stop') && ($value['Id'] == $nowplaying['Id'])) {
        echo '<div class="playlist_current_song_div">';
    } else {
        echo '<div style="background-color:'.$colors['playlist_row_colors'][($key % 2)].';" onclick="window.location=\'/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=play&amp;arg1='.$value['Pos'].'\'">';
    }
    echo $value['Title'].' ('.$value['Artist'].')';
    echo '</div>';
}
echo '</div>';

$innertable->end_table();
// END playlist_table
/////

$positiontable->end_table();
// END modes_playlist_table
/////

$table->end_table();
// END playqueue_maintable
/////
?>
