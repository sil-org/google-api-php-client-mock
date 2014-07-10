<?php
namespace SilMock\Google\Service\Directory;


class Alias implements \ArrayAccess
{
    private $_values = array();

    public $alias;
    public $etag;
    public $id;
    public $kind;
    public $primaryEmail;

    public function initialize($properties)
    {
        foreach ($properties as $key=>$value) {
            $this->$key = $value;
            $this->offsetSet($key, $value);
        }
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setPrimaryEmail($primaryEmail)
    {
        $this->primaryEmail = $primaryEmail;
    }

    public function getPrimaryEmail()
    {
        return $this->primaryEmail;
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