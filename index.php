<?php
require 'includes/_StartHere.php';

// Is auto refresh on or off?
// Defaults to on.
$refreshstate = $valid->get_value('refreshstate', 'on');

// Which page are we on?
// Defaults to playqueue
$pagename = $valid->get_value('page', 'playqueue');

require 'includes/_PageTop.php';

// $pagename comes from /includes/_PageTop.php
$pagepath = $IP.'/pages/'.$pagename.'.php';

if (is_file($pagepath)) {
    try {
        require $pagepath;
    } catch (SystemExit $e) { /* Do nothing. Error message is supposed to be displayed by $pagepath */ }
} else {
    echo 'Unable to find page: '.$pagename;
}

require 'includes/_PageBottom.php';
?>
