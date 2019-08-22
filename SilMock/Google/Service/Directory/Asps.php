<?php

namespace SilMock\Google\Service\Directory;

use Google_Service_Directory_Asps;

class Asps
{
    /** @var string - The path (with file name) to the SQLite database. */
    private $dbFile;
    
    /** @var string - The 'type' field to use in the database. */
    private $dataType = 'directory';
    
    /** @var string - The 'class' field to use in the database */
    private $dataClass = 'asps';
    
    public function __construct($dbFile = null)
    {
        $this->dbFile = $dbFile;
    }
    
    public function listAsps($userKey, $optParams = array())
    {
        return new Google_Service_Directory_Asps(array(
            'items' => array(),
        ));
    }
}
