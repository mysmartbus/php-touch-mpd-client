<?php
// Added: 2017-05-19
// Modified: 2018-01-13

require 'includes/_menu.php';

display_menu('database');

// Directory to work in
$curdir = $valid->get_value('curdir', '/');
$curdir = str_replace('//', '/', $curdir);

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

// List all directories and files inside $curdir
$dirlist = $mpc->listFiles($curdir);
$dirs = $dirlist['directory'];
$files = $dirlist['file'];

$letters = array();

// Create the <a href=""></a> links
foreach($dirs as $key => $dir) {
    if ($dir != 'Parent...') {
        $fl = substr($dir, 0, 1);
        if (!array_key_exists($fl, $letters)) {
            $letters[$fl] = '<a href="#'.$fl.'">'.$fl.'</a>';
        }
    }
}

$table = new HtmlTable();
$innertable = new HtmlTable();

$table->new_table('database_maintable');

// Current directory
$table->new_row();
$table->set_colspan(2);
$table->new_cell('directory_name_cell');
echo $curdir;

// Column headers
$table->new_row();
$table->new_cell('generic_header_cell');
echo 'Directories (';
if (is_array($dirs)) {
    if ($dirs[0] == 'Parent...') {
        echo (count($dirs) - 1); // Subtract 1 for parent directory
    } else {
        echo count($dirs);
    }
} else {
    echo '0';
}
echo ')';

$table->new_cell('generic_header_cell');

// Files and file count table
$innertable->new_table('file_count_table');
$innertable->new_row();
$innertable->new_cell('file_count_cell');
echo 'Files (';
if (is_array($files)) {
    echo count($files);
} else {
    echo '0';
}
echo ')';
$innertable->new_cell();
// Add all option
if (is_array($files) && count($files) > 0) {
    $curdir = str_replace('&', '%26', $curdir);
    $uri = substr($curdir, 1); // Remove leading slash so MPD can find the files
    echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&command=add&arg1='.$uri.'&curdir='.$curdir.'" title="Add all files listed below" class="button add_all_button">Add All</a>';
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

// List the directories in the left column
$table->new_cell('column_cell');
echo '<div class="column_scroll_div dir_colum_div">';
$innertable->new_table('column_contents_table');

$curletter = '';
foreach ($dirs as $key => $dir) {
    $display = $dir; // Unmodified copy to display

    $dir = str_replace('&', '%26', $dir);

    $innertable->new_row();
    $innertable->new_cell();

    // Create the <a name=""></a> anchors
    if ($dir != 'Parent...') {
        $fl = substr($dir, 0, 1);
        if ($curletter != $fl) {
            echo '<a name="'.$fl.'"></a>';
            $curletter = $fl;
        }
    }

    if ($dir == 'Parent...') {
        $updir = explode('/', $curdir);
        array_pop($updir); // Removes the current directory name from $updir
        $updir = implode('/', $updir);
        if ($updir == '') {
            $updir = '/';
        }
        echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&curdir='.str_replace('&', '%26', $updir).'" title="Go up one directory">'.$display.'</a>';
    } else {
        echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&curdir='.str_replace('&', '%26', $curdir).'/'.$dir.'" title="Select directory">'.$display.'</a>';
    }
}

$innertable->end_table();
echo '</div>';
// END directory listing
/////

// List the files in the right column
$table->new_cell('column_cell');
echo '<div class="column_scroll_div">';
if (is_array($files) && count($files) > 0) {
    $innertable->new_table('file_contents_table');

    // Remove leading forward slash (/) if any
    if (substr($curdir, 0, 1) == '/') {
        $uri = substr($curdir, 1);
    } else {
        $uri = $curdir;
    }

    $curdir = str_replace('&', '%26', $curdir);
    foreach ($files as $key => $file) {

        $innertable->new_row();
        $innertable->new_cell('wordwrap');
        echo '<div>';
        echo '<div class="file_name_div wordwrap">'.$file.'</div>';

        // The buttons are added in reverse order of what they are shown on screen due to float:right

        // Play button
        echo '<div style="float:right;"><a href="/index.php?page='.$pagename.'&amp;refreshstate='.$refreshstate.'&amp;command=playmenow&amp;arg1='.$uri.'/'.$file.'&curdir='.$curdir.'" title="Play now"><img src="skins/'.$config['skin'].'/ButtonPlay.png"></a></div>';

        // Plus button
        echo '<div style="float:right;"><a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&command=add&arg1='.$uri.'/'.$file.'&curdir='.$curdir.'" title="Add to current playlist"><img src="skins/'.$config['skin'].'/ButtonPlus.png"></a></div>';

        echo '</div>';
    }
    $innertable->end_table();
}
echo '</div>';
// END file listing
/////


$table->new_row('root_update_row');
$table->new_cell();
echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&curdir=/" title="Go to root directory" class="button">Root Directory</a>';
$table->new_cell();
echo '<a href="/index.php?page='.$pagename.'&refreshstate='.$refreshstate.'&command=update" title="Update database" class="button">Update database</a>';
$table->end_table();
?>
