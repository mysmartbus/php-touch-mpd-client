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
?>
