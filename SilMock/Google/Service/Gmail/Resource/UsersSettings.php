<?php

namespace SilMock\Google\Service\Gmail\Resource;

use Google_Service_Gmail_ImapSettings;
use Google_Service_Gmail_PopSettings;
use SilMock\Google\Service\DbClass;

class UsersSettings extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'gmail', 'users_settings');
    }
    
    public function updatePop($userId, Google_Service_Gmail_PopSettings $postBody, $optParams = array())
    {
        // No action necessary, since we do not yet mock any way to check the pop settings.
    }
    
    public function updateImap($userId, Google_Service_Gmail_ImapSettings $postBody, $optParams = array())
    {
        // No action necessary, since we do not yet mock any way to check the IMAP settings.
    }
}
