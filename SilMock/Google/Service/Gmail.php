<?php

namespace SilMock\Google\Service;

use SilMock\Google\Service\Gmail\Resource\UsersSettings;
use SilMock\Google\Service\Gmail\Resource\UsersSettingsDelegates;
use SilMock\Google\Service\Gmail\Resource\UsersSettingsForwardingAddresses;

class Gmail
{
    public $users_settings;
    public $users_settings_delegates;
    public $users_settings_forwardingAddresses;
    
    public function __construct($client, $dbFile = null)
    {
        $this->users_settings = new UsersSettings($dbFile);
        $this->users_settings_delegates = new UsersSettingsDelegates($dbFile);
        $this->users_settings_forwardingAddresses = new UsersSettingsForwardingAddresses($dbFile);
    }
}
