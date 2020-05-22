<?php
namespace SilMock\Google\Service\Directory;


class ObjectUtils
{

    /**
     * Assigns given values to the matching properties of a Google Mock object
     *
     * @param object $newObject -- a Google mock object
     * @param object|array $properties -- object|associative array
     * @returns null
     **/
    public static function initialize($newObject, $properties)
    {
        $propArray = $properties;
        if (is_object($properties)) {
            $propArray = get_object_vars($properties);
        }

        foreach ($propArray as $key=>$value) {
            $newObject->$key = $value;
        }
    }

} 