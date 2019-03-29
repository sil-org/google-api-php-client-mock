<?php

namespace SilMock\Google\Service\Gmail\Resource;

use Google_Service_Gmail_PopSettings;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\Directory\ObjectUtils;
use Webmozart\Assert\Assert;

class UsersSettings
{
    /** @var string - The path (with file name) to the SQLite database. */
    private $dbFile;
    
    /** @var string - The 'type' field to use in the database. */
    private $dataType = 'gmail';
    
    /** @var string - The 'class' field to use in the database */
    private $dataClass = 'users_settings';
    
    public function __construct($dbFile = null)
    {
        $this->dbFile = $dbFile;
    }
    
    public function updatePop($userId, Google_Service_Gmail_PopSettings $postBody, $optParams = array())
    {
        // No action necessary, since we do not yet mock any way to check the pop settings.
    }
}
