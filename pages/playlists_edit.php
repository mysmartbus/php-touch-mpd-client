<?php
// Added: 2018-01-12
// Modified: 2018-01-13

require 'includes/_menu.php';

display_menu('playlists');

// Send a command to MPD if there is one
//
// If command is 'updateplaylists'
//      arg1 is the list of playlists to add the song to
//      arg2 is the list of playlists to remove the song from
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

// Get info about currently playing song
$nowplaying = $mpc->nowPlaying();

// $uri cannot be blank
$uri = $valid->get_value('uri');
if ($uri == '') {
    $uri = $nowplaying['file'];
}

// Get a list of all playlists
$playlists = $mpc->listPlaylists();

// Get a list of playlists that the current
// song is assigned to
$assignedplaylists = $mpc->assignedPlaylists($uri);

// These are the playlists the song can be added to.
$availableplaylists = array_diff($playlists['sorted'], $assignedplaylists);

$form = new HtmlForm();

$table = new HtmlTable();

?>
<script type="text/javascript">
    var arg1 = [];

    function AddToPlaylist(id) {
        // Keep track of which playlists the song should be a part of
        // If the playlist is not in this array, the song will be removed from it.

        if (document.getElementById(id).checked === true) {
            // Add to array/playlist
            arg1.push(id);

        } else {
            // Remove from array/playlist

            var index = arg1.indexOf(id);
            if (index > -1) {
                arg1.splice(index, 1);
            }
        }

    }

    function addInitial(ids) {
        // So the user does not need to click the checkbox twice
        // to keep the song on it's current playlists.

        var res = ids.split(" ");

        for (i = 0; i < res.length; i++) {
            // Skip blanks
            if ((res[i] != ' ') && (res[i] != '')) {
                arg1.push(res[i]);
            }
        }
    }

    function setArg1() {
        // Makes the playlist selections available after the page reloads.
        document.getElementById('arg1').value = arg1;

        return true;
    }
</script>
<?php

// The form used to update the playlist selections
$form->add_hidden(array(
    'command' => 'updateplaylists',
    'arg1' => '',
    'arg2' => $uri,
    'uri' => $uri,
    'page' => $pagename,
    'refreshstate' => $refreshstate
));
$form->onsubmit('return setArg1();');
$form->start_form('/index.php', 'frmupdateplaylists', 'post');

$table->new_table('playlists_edit_maintable');

// Display title and artist for currently playing song
$table->new_row();
$table->set_colspan(2);
$table->new_cell('centertext title_artist_cell');
$artist = str_replace('&', '%26', $nowplaying['Artist']);
echo '<span class="nowplaying_title_span"><a href="/index.php?page=songinfo" class="undecorated_href">'.$nowplaying['Title'].'</a></span> by <span class="nowplaying_artist_span"><a href="/index.php?page=database&curdir='.$artist.'" class="undecorated_href">'.$nowplaying['Artist'].'</a></span>';

$table->new_row();
$table->new_cell('centertext generic_header_cell');
echo 'Add To Playlist (Available: '.count($availableplaylists).')';

$table->new_cell('centertext generic_header_cell');
echo 'Remove From Playlist ('.count($assignedplaylists).' Assigned)';

// List all playlists the song is not in
$table->new_row();
$table->new_cell();
echo '<div class="column_scroll_div">';
foreach($availableplaylists as $key => $name) {
    $form->add_checkbox($name, $name, $name, false, "AddToPlaylist(this.id)");
    echo '<br>';
}
$form->end_form();
echo '</div>';

// List the playlists that the song is assigned to
$table->new_cell();
echo '<div class="column_scroll_div">';
$ids = '';
foreach($assignedplaylists as $key => $name) {
    $form->add_checkbox($name, $name, $name, true, "AddToPlaylist(this.id)");
    $ids .= ' '.$form->format_fieldid($name);
    echo '<br>';
}
echo '<script type="text/javascript">addInitial("'.$ids.'");</script>';
echo '</div>';

// The submit button
$table->new_row('update_button_row');
$table->set_colspan(2);
$table->new_cell('centertext');
$form->add_button_submit('Update Playlist Selections','submit');

$table->end_table();

$form->end_form();
?>
