<?php

namespace SilMock\Google\Service\Gmail\Resource;

use Google_Service_Gmail_ListForwardingAddressesResponse;
use Webmozart\Assert\Assert;

class UsersSettingsForwardingAddresses
{
    /** @var string - The path (with file name) to the SQLite database. */
    private $dbFile;
    
    /** @var string - The 'type' field to use in the database. */
    private $dataType = 'gmail';
    
    /** @var string - The 'class' field to use in the database */
    private $dataClass = 'users_settings_forwardingAddresses';
    
    public function __construct($dbFile = null)
    {
        $this->dbFile = $dbFile;
    }
    
    public function listUsersSettingsForwardingAddresses($userId, $optParams = array())
    {
        return new Google_Service_Gmail_ListForwardingAddressesResponse(array(
            'forwardingAddresses' => array(),
        ));
    }
}
