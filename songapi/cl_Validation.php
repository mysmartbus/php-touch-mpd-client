<?php
/**
 * File: cl_validation.php
 *
 * Does some sanity checks on $_POST and $_GET variables.
 *
 * This class also is used to store/display custom warning and error messages
 * when something did not work the way it was supposed to.
 *
 * The something could be a bad/failed database call, a missing file called by
 * include(), a permissions error when trying to access a restricted page or some
 * other problem that the user needs to be made aware of.
 *
 * Added: 2007-03-02
 * Modified: 2017-03-26
**/
class validate {
    private $_err;
    private $_warning;

    function __construct() {
        $this->reset_errors();
        $this->reset_warnings();
    }

/**
 * BEGIN
 * Functions specifically for use with $_POST and $_GET
**/

    private function clean_tags($field) {
        $value = $this->get_value_unclean($field);
        $this->set_value($field,str_replace('<','&lt;',$value));
        $value = $this->get_value_unclean($field);
        $this->set_value($field,str_replace('>','&gt;',$value));
    }

    private function get_value_unclean($field) {
        $rv = (array_key_exists($field,$_POST) ? $_POST[$field] : '');
        if ($rv == '') {
            $rv = (array_key_exists($field,$_GET) ? $_GET[$field] : '');
        }
        return $rv;
    }
    
    public function get_value($field) {
        /**
         * Get a value from $_POST or $_GET
         *
         * Added: 2007-03-02
         * Modified: 2017-03-26
         *
         * @param Required string $field Name of field to retreive
         *
         * @return String
        **/

        $this->clean_tags($field);
        $postone = $_POST[$field];
        $getone = (array_key_exists($field,$_GET) ? $_GET[$field] : '');
        return ((!empty($_POST[$field]) || $_POST[$field]==0)?$postone:$getone);
    }

    public function get_value_numeric($field,$default=-1) {
        /**
         * Forces the value being returned to be a number
         *
         * Added: 2007-03-02
         * Modified: 2017-03-26
         *
         * @param Required string $field Name of field to retreive
         * @param Optional integer $default The number to return if $field does
         *                                  not return a number
         *
         * @return Integer
        **/

        // Make sure $default is a number
        if (!is_numeric($default)) {
            $default = -1;
        }

        $data = $this->get_value($field);

        // Make sure $data is a number
        if (!is_numeric($data)) {
            $data = $default;
        }

        return $data;
    }
    
    public function set_value($field, $value) {
        $_POST[$field] = $value;
    }
    
    public function set_value_array($array) {
        if (is_array($array)) {
            foreach($array as $field => $value) {
                $this->set_value($field, $value);
            }
        }
    }

/**
 * Functions specifically for use with $_POST and $_GET
 * END
**/

    public function isDate($date, $format = 'Y-m-d H:i:s') {
        /**
         * Checks if $date is actually a date
         *
         * Added: 2007-03-02
         * Modified: 2017-03-26
         *
         * @param Required string $date The item to test if it is a date
         * @param Optional string $format What format is $date supposed to match
         *
         * @return Boolean
        **/
        $d = DateTime::createFromFormat($format, $date);

        // Returns true if both sides of the && are true.
        // Returns false if either side of the && is false.
        return $d && $d->format($format) == $date;
    }
    
    public function is_email($email, $msg) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            $this->add_warning($msg);
            return false;
        }
    }

/**
 * BEGIN
 * Error handling functions
**/

/////
// Error messages
// Stop everything until the problem is fixed
    public function add_error($msg) {
        if($msg!='') $this->_err[] = $msg;
    }

    public function is_error() {
        if (count($this->_err) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_errors() {
        return $this->_err;
    }
    
    public function reset_errors() {
        $this->_err = array();
    }

    public function errors_table() {
        // Style is set here cause it is possible that the problem prevents loading of any stylesheets
        echo '<table border="2" bordercolor="#f00000" style="margin-left:auto;margin-right:auto;border-collapse:collapse;"><tr><td>';
        foreach($this->_err as $val) { 
            echo $val.'<br/>';
        }
        echo '</td></tr></table><br/><br/>';
        $this->reset_errors();
    }

/////
// Warning messages
// Notify and keep going
    public function add_warning($msg) {
        if($msg!='') $this->_warning[] = $msg;
    }

    public function is_warning() {
        if (count($this->_warning) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_warning() {
        return $this->_warning;
    }
    
    public function reset_warnings() {
        $this->_warning = array();
    }

    public function warnings_table() {
        // Style is set here cause it is possible that the problem prevents loading of any stylesheets
        echo '<table border="2" bordercolor="#f0f000" style="margin-left:auto;margin-right:auto;border-collapse:collapse;"><tr><td>';
        foreach($this->_warning as $val) {
            echo $val.'<br/>';
        }
        echo '</td></tr></table><br/><br/>';
        $this->reset_warnings();
    }

/**
 * Error handling functions
 * END
**/
}
?>
