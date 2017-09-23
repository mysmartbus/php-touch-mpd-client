<?php
// Added: 2017-05-21
// Modified: 2017-09-18

require 'includes/_menu.php';

display_menu('songinfo');

// Retreive info about currently playing song
$nowplaying = $mpc->nowPlaying();

// MPD status
$status = $mpc->returnStatus();

$table = new HtmlTable();

function clean_url($url) {
    /**
     * Clean the URL so it can be processed correctly by the song api.
     *
     * Added: 2017-05-26
     * Modified: 2017-05-26
     *
     * @param Required string $url The URL to be cleaned
     *
     * @return string
    **/

    $url = str_replace(" ", "%20", $url);
    $url = str_replace("#", "%23", $url);

    return $url;
}

if (!empty($nowplaying)) {
    if ($status['state'] != 'stop') {
        // Elapsed time will be included if song is playing or paused
        list($elapsedtime, $totaltime) = explode(':',$nowplaying['Time']);
    } else {
        $totaltime = $nowplaying['Time'];
    }

    // Convert seconds into minutes and seconds
    $time_min = (int)($totaltime / 60);
    $time_sec = (int)($totaltime - $time_min * 60);
    if ($time_sec < 10) {
        // Add leading zero if needed
        $time_sec = "0$time_sec";
    }

    if (!($time_min == 0 && $time_sec == "00")) {
        $timestring = $time_min.':'.$time_sec;
    } else {
        $timestring = '(Unknown)';
    }

    // Retrieve song lyrics
    $url = clean_url('http://www.kraven.rat/song_api.php?title='.$nowplaying['Title'].'&artist='.$nowplaying['Artist'].'&album='.$nowplaying['Album'].'&return=lyrics');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $lyrics = curl_exec($ch);
    curl_close($ch);
    $lyrics = json_decode($lyrics, true);

    // Retrieve album cover art
    $albumuri = str_replace('&', '%26', $nowplaying['Album']);
    $url = clean_url('http://www.kraven.rat/song_api.php?album='.$albumuri.'&artist='.$nowplaying['Artist'].'&return=coverart');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $coverart = curl_exec($ch);
    curl_close($ch);
    $coverart = json_decode($coverart, true);

} else {
    // End of play list reached
    $lyrics['error']['msg'] = '';
    $coverart['coverart'] = '';
    $timestring = '';
}

// BEGIN songinfo_maintable
$table->new_table('songinfo_maintable');

$table->new_row();

// Song name and artist/band name
$table->set_colspan(3);
$table->new_cell('song_artist_names_cell');
if (!empty($nowplaying)) {
    //echo '<span class="nowplaying_title_span">'.$nowplaying['Title'].'</span> by <span class="nowplaying_artist_span">'.$nowplaying['Artist'].'</span>';
    echo '<span class="nowplaying_title_span">'.$nowplaying['Title'].'</span> by <span class="nowplaying_artist_span"><a href="/index.php?page=database&curdir='.$nowplaying['Artist'].'" class="undecorated_href">'.$nowplaying['Artist'].'</a></span>';
} else {
    echo '<span class="nowplaying_title_span">No song playing</span>';
}

// Album
$table->new_row();
$table->new_cell('data_type_cell');
echo 'Album:';
$table->new_cell('data_cell wordwrap');
if (!empty($nowplaying)) {
    echo '<a href="/index.php?page=database&curdir='.$nowplaying['Artist'].'/'.$nowplaying['Album'].'" class="undecorated_href">'.$nowplaying['Album'].'</a>';
} else {
    echo '&nbsp;';
}

// Lyrics
$table->set_rowspan(6);
$table->new_cell('lyrics_cell');
echo '<div class="lyrics_div"><pre>';
if (isset($lyrics['lyrics'])) {
    // Display lyrics
    echo $lyrics['lyrics'];
} elseif (isset($lyrics['error'])) {
    // Display an error message
    echo $lyrics['error']['msg'];
} else {
    // Unhandled/unexpected error in the song api
    // echo 'The song API experienced an unhandled error';
    print_r($lyrics);
}
echo '</pre></div>';

// Track #
$table->new_row();
$table->new_cell('data_type_cell');
echo 'Track #:';
$table->new_cell('data_cell');
if (!empty($nowplaying)) {
    echo explode('/', $nowplaying['Track'])[0];
} else {
    echo '&nbsp;';
}

// Genre
$table->new_row();
$table->new_cell('data_type_cell');
echo 'Genre:';
$table->new_cell('data_cell');
if (!empty($nowplaying)) {
    echo $nowplaying['Genre'];
} else {
    echo '&nbsp;';
}

// Run time
$table->new_row();
$table->new_cell('data_type_cell');
echo 'Run time:';
$table->new_cell('data_cell');
echo $timestring;

// Cover art
$table->new_row();
$table->set_colspan(2);
$table->new_cell('coverart_cell');
if (isset($coverart['nocoverart'])) {
    echo '&nbsp;';
} else {
    if (isset($coverart['coverart']) && ($coverart['coverart'] != '')) {
        // Display cover art
        echo '<a href="/index.php?page=database&curdir='.$nowplaying['Artist'].'/'.$nowplaying['Album'].'" class="undecorated_href"><img class="coverart_img" src="'.$coverart['coverart'].'"></a>';
    } elseif (isset($coverart['error'])) {
        echo $coverart['error']['msg'];
    }
}

// Update button
$table->new_row();
$table->set_colspan(2);
$table->new_cell('update_info_href_cell');
//$table->new_cell();
echo '<span class="update_info_href_span"><a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'" class="button">Update Info</a></span>';

$table->end_table();
// END songinfo_maintable
?>