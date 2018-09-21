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

    // Menu
    if ($page == 'menu') {
        $css = 'tab currentpage';
    } else {
        $css = 'tab';
        $clickstring = "loadpage('menu', '".$refreshstate."')";
        $maintable->set_onclick($clickstring);
    }
    $maintable->new_cell($css);
    echo 'Menu';

    $maintable->new_row();
    $maintable->set_colspan(6);
    $maintable->new_cell('pagecontent');
    // Page content goes inside this cell
    
}
// END function display_menu()
?>
