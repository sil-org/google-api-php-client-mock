<?php
namespace SilMock\Google\Service;

use SilMock\Google\Service\Directory\UsersResource;
use SilMock\Google\Service\Directory\UsersAliasesResource;

class Directory {

    public $users;
    public $users_aliases;

    /**
     *
     * @param  $client (normally it would be a Google_Client)
     */
    public function __construct($client, $dbFile=null)
    {
        $this->users = new UsersResource($dbFile);
        $this->users_aliases = new UsersAliasesResource($dbFile);
    }

} 