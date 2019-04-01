<?php
namespace SilMock\Google\Service;

use SilMock\Google\Service\Gmail\Resource\UsersSettings;
use SilMock\Google\Service\Gmail\Resource\UsersSettingsDelegates;
use SilMock\Google\Service\Gmail\Resource\UsersSettingsForwardingAddresses;

class Gmail
{
    public $users_settings_delegates;
    
    public function __construct($client, $dbFile = null)
    {
        $this->users_settings = new UsersSettings();
        $this->users_settings_delegates = new UsersSettingsDelegates();
        $this->users_settings_forwardingAddresses = new UsersSettingsForwardingAddresses();
    }
}
