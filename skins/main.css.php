<?php
// This file only sets the colors for the items listed below.
// To change text size, position, borders, etc; go to main.css
?>

body {
    background-color: <?php echo $colors['currentpage']; ?>;
}

a:link {
    color: <?php echo $colors['a_link']; ?>;
}

a:visited {
    color: <?php echo $colors['a_visited']; ?>;
}

a.button {
    background: <?php echo $colors['a_button_active']; ?>;
}

span.button {
    background: <?php echo $colors['a_button_active']; ?>;
}

.disabled_button {
    background-color: <?php echo $colors['a_button_disabled']; ?> !important;
}

.maintable {
    background-color: <?php echo $colors['main_table_bg']; ?>;
}

.currentpage {
    background-color: <?php echo $colors['currentpage']; ?>;
}

.pagecontent {
    background-color: <?php echo $colors['currentpage']; ?>;
}

.generic_header_cell {
    background-color: <?php echo $colors['generic_header_cell']; ?>;
}

.button_background_div {
    background-color: <?php echo $colors['a_button_active']; ?>;
}

.button_state_marker_div {
    background-color: #000000;
}
