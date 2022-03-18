<?php

namespace SilMock\Google\Service\Directory;

use Google_Service_Directory_Asps;
use SilMock\Google\Service\DbClass;

class Asps extends DbClass
{
    public function __construct($dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'asps');
    }
    
    public function listAsps($userKey, $optParams = array())
    {
        return new Google_Service_Directory_Asps(array(
            'items' => array(),
        ));
    }
}
