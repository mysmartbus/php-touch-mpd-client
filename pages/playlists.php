<?php
// Added: 2017-05-19
// Modified: 2017-05-28

require 'includes/_menu.php';

display_menu('playlists');

// Connect to the MPD server
$mpc = new MpdClient($config['host'], $config['port'], $config['password']);

// Send a command to MPD if there is one
$command = $valid->get_value('command');
$arg1 = $valid->get_value('arg1');
$arg2 = $valid->get_value('arg2');
if ($command != '') {
    $rv = $mpc->sendCommand($command, $arg1, $arg2);
    // TODO:
    //  Replace this with a message box similar to the one
    //  mediawiki uses after saving changes to a page
    if (count($rv) > 1) {
        echo '<pre>hi';
        print_r($rv);
        echo '</pre>';
    }
}

// Get a list of all playlists
$playlists = $mpc->listPlaylists();

$letters = array();
// Create the <a href=""></a> links
foreach($playlists['sorted'] as $key => $name) {
    $fl = strtoupper(substr($name, 0, 1));
    if (!array_key_exists($fl, $letters)) {
        $letters[$fl] = '<a href="#'.$fl.'">'.$fl.'</a>';
    }
}

// Show contents of this playlist
$showplaylist = $valid->get_value('showplaylist');

// Get playlist contents
if ($showplaylist != '') {
    $contents = $mpc->getPlaylistContents($showplaylist);
} else {
    $contents = array();
}

$table = new HtmlTable();
$innertable = new HtmlTable();

$table->new_table('playlists_maintable');

// Name of selected playlist
$table->new_row();
$table->set_colspan(2);
$table->new_cell('playlist_name_cell');
if ($showplaylist != '') {
    echo $showplaylist;
} else {
    echo '&nbsp;';
}

// Playlist column header
$table->new_row();
$table->new_cell('generic_header_cell');
echo 'Playlists (';
if (is_array($playlists)) {
    echo count($playlists['sorted']);
} else {
    echo '0';
}
echo ')';

// Playlist content column header
$table->new_cell('generic_header_cell');
$innertable->new_table('file_count_table');
$innertable->new_row();
$innertable->new_cell('file_count_cell');
echo 'Files (';
echo count($contents);
echo ')';
$innertable->new_cell();
// Add all option
if ($showplaylist != '') {
    $uri = str_replace('&', '%26', $showplaylist);
    echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&command=load&arg1='.$uri.'" title="Add all files listed below" class="button add_all_button">Add All</a>';
} else {
    echo '&nbsp;';
}
$innertable->end_table();

// First letter shortcuts
$table->new_row();
$table->set_colspan(2);
if (!empty($letters)) {
    $table->new_cell();
    foreach ($letters as $key => $value) {
        echo $value.'&nbsp;';
    }
} else {
    $table->blank_cell();
}

$table->new_row();

// List the playlists in the left column
$table->new_cell('column_cell');
echo '<div class="column_scroll_div">';
if (isset($playlists['sorted'])) {
    // Display playlist names
    $curletter = '';
    foreach ($playlists['sorted'] as $key => $name) {
        // Create the <a name=""></a> anchors
        $fl = strtoupper(substr($name, 0, 1));
        if ($curletter != $fl) {
            echo '<a name="'.$fl.'"></a>';
            $curletter = $fl;
        }
        $uri = str_replace('&', '%26', $name);
        echo '<div class="name_div"><a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&showplaylist='.$uri.'" title="View playlist contents">'.$name.'</a></div>';
    }
}
echo '</div>';

// List the playlist contents in the right column
$table->new_cell('column_cell');
echo '<div class="column_scroll_div">';
foreach ($contents as $key => $name) {
    echo '<div class="name_div" style="background-color:'.$colors['playlist_row_colors'][($key % 2)].';">'.$name.'</div>';
}
echo '</div>';

$table->new_row();
$table->new_cell('update_playlists_cell');
echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'" title="Refresh List" class="button">Refresh List</a>';

$table->blank_cell();

$table->end_table();
?>
