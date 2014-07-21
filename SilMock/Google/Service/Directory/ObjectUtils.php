<?php
namespace SilMock\Google\Service\Directory;


class ObjectUtils
{

    public static function initialize($newUser, $properties)
    {
        $propArray = $properties;
        if (is_object($properties)) {
            $propArray = get_object_vars($properties);
        }

        foreach ($propArray as $key=>$value) {
            $newUser->$key = $value;
        }
    }

} 