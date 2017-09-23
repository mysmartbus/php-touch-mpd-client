<?php
// This creates the "menu" that runs across the top of every page.

function display_menu($page) {
    // Every page needs to call this function immediately after the opening "<?php" tag

    global $maintable, $valid, $pagename, $refreshstate;

    ?>
    <script type="text/javascript">
        function loadpage(page, rs) {
            // Load the selected page

            window.location = '/index.php?page='+page+'&refreshstate='+rs;
        }
    </script>
    <?php

    // This table will not be closed until /includes/_PageBottom.php is loaded.
    $maintable->new_table('maintable');

    $maintable->new_row();

    // Play Queue
    if ($page == 'playqueue') {
        $css = 'tab currentpage';
    } else {
        $css = 'tab';
        $clickstring = "loadpage('playqueue', '".$refreshstate."')";
        $maintable->set_onclick($clickstring);
    }
    $maintable->new_cell($css);
    echo 'Play Queue';

    // Song info
    if ($page == 'songinfo') {
        $css = 'tab currentpage';
    } else {
        $css = 'tab';
        $clickstring = "loadpage('songinfo', '".$refreshstate."')";
        $maintable->set_onclick($clickstring);
    }
    $maintable->new_cell($css);
    echo 'Song info';

    // MPD Database
    if ($page == 'database') {
        $css = 'tab currentpage';
    } else {
        $css = 'tab';
        $clickstring = "loadpage('database', '".$refreshstate."')";
        $maintable->set_onclick($clickstring);
    }
    $maintable->new_cell($css);
    echo 'Database';

    // Playlists
    if ($page == 'playlists') {
        $css = 'tab currentpage';
    } else {
        $css = 'tab';
        $clickstring = "loadpage('playlists', '".$refreshstate."')";
        $maintable->set_onclick($clickstring);
    }
    $maintable->new_cell($css);
    echo 'Playlists';

    // Refresh on/off
    $maintable->new_cell('tab');
    if ($valid->get_value('refreshstate', 'on') == 'on') {
        echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate=off" title="Click to turn off" class="refresh_link_href">Refresh On</a>';
    } else {
        echo '<a href="/index.php?page='.$pagename.'&amp;refreshstate=on" title="Click to turn on" class="refresh_link_href">Refresh Off</a>';
    }

    $maintable->new_row();
    $maintable->set_colspan(6);
    $maintable->new_cell('pagecontent');
    // Page content goes inside this cell
    
}
// END function display_menu()
?>
