<?php
function cutString($str, $limit=32) {

    /**
     * Cuts the string at the space closest to the limit without going over.
     *
     * New lines (\n) within the limit are not preserved.
     *
     *  Example:
     *      Limit: 20 characters
     *      Input:
     *          This is an example string used to \r\n
     *          explain the preg_replace below.
     *      Returns:
     *          This is an example
     *      instead of
     *          This is an example s
     *
     * Added: 2017-07-18
     * Modified: 2017-07-18
     *
     * @param Required string $str The string to be cut
     * @param Optional integer $limit The length that $str will be shortened to
     *
     * @return string
    **/

    // Replace all new line characters with a space
    $str = str_replace("\r\n","\n",$str);
    $str = str_replace("\r","\n",$str);
    $str = str_replace("\n"," ",$str);

    if (strlen($str) > $limit) {

        // Find the space and split the string
        $str = preg_replace('/\s+?(\S+)?$/', '', substr($str, 0, $limit)).'...';
    }

    return $str;

}
// END function cutString()

function getScriptName() {
    /**
     * Returns the script name (including file extension) and does a sanity check
     * Strips $_GET variables from returned value
     *
     * Added: 2017-06-07
     * Modified: 2017-06-07
     *
     * @param none
     *
     * @returns string $result The path to $_SERVER['PHP_SELF'] as viewed by the browser
     *                         Example: If $_SERVER['PHP_SELF'] = /a/b/c/d/codetest.php?example=true&testing=yes
     *                                  will return /a/b/c/d/codetest.php
    **/

    $php_self = utf8_decode($_SERVER['PHP_SELF']);

    // These two lines remove anything not in $regex
    // Items that will be kept: [a-z][A-Z][0-9][_=&/.-?+]
    $regex = '/[^a-zA-Z0-9_=&\/\.\-\?\+]/';
    $result = preg_replace_callback($regex,create_function('$matches','return \'\';') ,$php_self );

    // Keeps file extention but removes anything that follows it
    $result = substr($result,0,strrpos($result,".php")+4);

    return $result;
}
// END function getScriptName()
?>
