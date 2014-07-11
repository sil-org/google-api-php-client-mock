<?php
namespace SilMock\Google\Service\Directory;


class User implements \ArrayAccess
{

    protected $_values = array();

    public $changePasswordAtNextLogin; // bool
    public $hashFunction; // string
    public $id; // int???
    public $password; // string
    public $primaryEmail; // string email
    public $suspended; // bool
    public $suspensionReason; // string

    public function initialize($properties)
    {
        foreach ($properties as $key=>$value) {
            $this->$key = $value;
        }
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