<?php
$array1 = array("green", "red", "blue", "yellow", "black");

echo '<pre>';
print_r($array1);
echo '</pre>';

unset($array1[3]);

echo '<pre>';
print_r($array1);
echo '</pre>';
?>
