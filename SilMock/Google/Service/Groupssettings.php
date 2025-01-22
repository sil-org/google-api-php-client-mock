<?php

namespace SilMock\Google\Service;

use Google\Client;
use SilMock\Google\Service\Groupssettings\Resource\Groups;
use Webmozart\Assert\Assert;

class Groupssettings
{
    public Groups $groups;
    public Client $client;

    /**
     * Sets the users and users_aliases properties to be instances of
     *    the corresponding mock classes.
     *
     * @param mixed $client -- Ignored (normally it would be a Google_Client)
     * @param string|null $dbFile -- (optional) The path and file name of the database file
     */
    public function __construct($client, ?string $dbFile = null)
    {
        $this->groups = new Groups($dbFile);
        $this->client = new Client();
        Assert::notEmpty($client, 'Expecting a client to be passed!');
    }

    public function getClient()
    {
        return $this->client;
    }
}
