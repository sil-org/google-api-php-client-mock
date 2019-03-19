<?php
namespace SilMock\Google\Service;

use SilMock\Google\Service\Gmail\Resource\UsersSettingsDelegates;

class Gmail
{
    public $users_settings_delegates;
    
    public function __construct($client, $dbFile = null)
    {
        $this->users_settings_delegates = new UsersSettingsDelegates();
    }
}
