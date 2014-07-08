<?php
namespace SilMock\Google\Service\Directory;


class Aliases  implements ArrayAccess
{

    protected $aliasesType = 'Google_Service_Directory_Alias';
    protected $aliasesDataType = 'array';
    public $etag;
    public $kind;

    //TODO finish this


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