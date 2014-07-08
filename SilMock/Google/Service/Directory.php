<?php
namespace SilMock\Google\Service;

class Directory {

    public $users;
    public $users_aliases;

    /**
     *
     * @param  $client (normally it would be a Google_Client)
     */
    public function __construct($client)
    {
        $this->users = new Directory\Users_Resource();
        $this->users_aliases = new Directory\UsersAliases_Resource();
    }

} 