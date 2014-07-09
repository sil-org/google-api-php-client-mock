<?php
namespace SilMock\Google\Service;

use SilMock\Google\Service\Directory\Users_Resource;
use SilMock\Google\Service\Directory\UsersAliases_Resource;

class Directory {

    public $users;
    public $users_aliases;

    /**
     *
     * @param  $client (normally it would be a Google_Client)
     */
    public function __construct($client)
    {
        $this->users = new Users_Resource();
        $this->users_aliases = new UsersAliases_Resource();
    }

} 