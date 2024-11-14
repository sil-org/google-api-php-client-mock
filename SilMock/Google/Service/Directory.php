<?php

namespace SilMock\Google\Service;

use Google\Client;
use SilMock\Google\Http\Batch;
use SilMock\Google\Service\Directory\Asps;
use SilMock\Google\Service\Directory\Resource\Groups;
use SilMock\Google\Service\Directory\Resource\GroupsAliases;
use SilMock\Google\Service\Directory\Resource\Members;
use SilMock\Google\Service\Directory\Resource\TwoStepVerification;
use SilMock\Google\Service\Directory\Tokens;
use SilMock\Google\Service\Directory\UsersAliasesResource;
use SilMock\Google\Service\Directory\UsersResource;
use SilMock\Google\Service\Directory\VerificationCodesResource;
use Webmozart\Assert\Assert;

class Directory
{
    public $asps;
    public Members $members;
    public Groups $groups;
    public GroupsAliases $groups_aliases;
    public $tokens;
    public $users;
    public $users_aliases;
    public $verificationCodes;
    public $twoStepVerification;
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
        $this->asps = new Asps($dbFile);
        $this->members = new Members($dbFile);
        $this->groups = new Groups($dbFile);
        $this->groups_aliases = new GroupsAliases($dbFile);
        $this->tokens = new Tokens($dbFile);
        $this->users = new UsersResource($dbFile);
        $this->users_aliases = new UsersAliasesResource($dbFile);
        $this->verificationCodes = new VerificationCodesResource($dbFile);
        $this->twoStepVerification = new TwoStepVerification($dbFile);
        $this->client = new Client();
        Assert::notEmpty($client, 'Expecting a client to be passed!');
    }

    public function getClient()
    {
        return $this->client;
    }

    public function createBatch()
    {
        return new Batch();
    }
}
