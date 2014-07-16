<?php
namespace SilMock\Google\Service\Directory;


class User implements \ArrayAccess
{

    protected $_values = array();

//    public $changePasswordAtNextLogin; // bool
//    public $hashFunction; // string
//    public $id; // int???
//    public $password; // string
//    public $primaryEmail; // string email
//    public $suspended; // bool
//    public $suspensionReason; // string
//    public $aliases = array();

    public function initialize($properties)
    {
        $propArray = $properties;
        if (is_object($properties)) {
            $propArray = get_object_vars($properties);
        }

        foreach ($propArray as $key=>$value) {
            $this->$key = $value;
        }

        if ( ! is_array($this->aliases)) {
            $this->aliases = array();
        }
    }

    public function encode2json() {
        $properties = array_merge(array(), $this->_values);
        return json_encode($properties);
    }

    /**
     * Get a data by property name
     *
     * @param string The key data to retrieve
     */
    public function &__get ($key) {
        return $this->_values[$key];
    }

    /**
     * Assigns a value to the specified property
     *
     * @param string The data key to assign the value to
     * @param mixed  The value to set
     */
    public function __set($key,$value) {
        $this->_values[$key] = $value;
    }

    // These are for implementing the ArrayAccess
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->_values);
    }

    public function offsetSet($offset, $value) {
        $this->_values[$offset] = $value;
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->_values[$offset]);
        }
    }

    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->_values[$offset]:NULL;
    }
} 