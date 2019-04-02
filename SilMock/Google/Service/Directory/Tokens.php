<?php

namespace SilMock\Google\Service\Directory;

use Google_Service_Directory_Tokens;

class Tokens
{
    /** @var string - The path (with file name) to the SQLite database. */
    private $dbFile;
    
    /** @var string - The 'type' field to use in the database. */
    private $dataType = 'directory';
    
    /** @var string - The 'class' field to use in the database */
    private $dataClass = 'tokens';
    
    public function __construct($dbFile = null)
    {
        $this->dbFile = $dbFile;
    }
    
    public function listTokens($userKey, $optParams = array())
    {
        return new Google_Service_Directory_Tokens(array(
            'items' => array(),
        ));
    }
}
