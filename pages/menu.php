<?php
// Added: 2018-01-12
// Modified: 2018-01-12

require 'includes/_menu.php';

display_menu('menu');

// Send a command to MPD if there is one
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

//echo '<pre>';
//print_r($nowplaying);
//echo '</pre>';

// This flag is used to hide/show menu items.
if (empty($nowplaying)) {
    $songplaying = false;
} else {
    $songplaying = true;
}

$table = new HtmlTable();
$innertable = new HtmlTable();

// set_colspan value
$numcols = 2;

$table->new_table('menu_maintable');

// Display title and artist for currently playing song
$table->new_row();
$table->set_colspan($numcols);
$table->new_cell('centertext title_artist_cell');
if ($songplaying) {
    echo '<span class="nowplaying_title_span"><a href="/index.php?page=songinfo" class="undecorated_href">'.$nowplaying['Title'].'</a></span> by <span class="nowplaying_artist_span"><a href="/index.php?page=database&curdir='.$nowplaying['Artist'].'" class="undecorated_href">'.$nowplaying['Artist'].'</a></span>';
} else {
    echo '<span class="nowplaying_title_span">No song playing</span>';
}

$table->new_row();
$table->new_cell('toptext');

// Refresh On/Off button
$innertable->new_table('refreshonoff_table');
$innertable->new_row();
$innertable->new_cell('centertext');
echo '<b>Refresh State: ';
if ($valid->get_value('refreshstate', 'on') == 'on') {
    echo 'On</b><br><br><a href="/index.php?page='.$pagename.'&refreshstate=off" title="Click to turn off" class="button">Turn Off</a>';
} else {
    echo 'Off</b><br><br><a href="/index.php?page='.$pagename.'&refreshstate=on" title="Click to turn on" class="button">Turn On</a>';
}
$innertable->end_table();

$table->new_cell('centertext toptext');

// Current song options
if ($songplaying) {

    $innertable->new_table('currentsong_table');

    $innertable->new_row();
    $innertable->set_colspan(2);
    $innertable->new_cell();
    echo '<b>Current Song Options</b>';

    if ($mpc->inCurrentPlayQueue($nowplaying['Title'])) {
        $innertable->new_row();
        $innertable->new_cell('button_cell');
        echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&command=deleteid&arg1='.$nowplaying['Id'].'" class="button">Remove from play queue</a>';
        $innertable->new_cell('text_cell');
        echo ' Removal will imediately begin playing<br>the next song in the play queue.';
    }

    $innertable->new_row();
    $innertable->new_cell('button_cell');
    $uri = str_replace('&', '%26', $nowplaying['file']);
    echo '<a href="/index.php?page=playlists_edit&refreshstate='.$refreshstate.'&uri='.$uri.'" class="button">Assign to playlist(s)</a>';
    $innertable->blank_cell();

    $innertable->end_table();
}
// END Current song options
/////

$table->end_table();
?>
