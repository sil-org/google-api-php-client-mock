<?php

namespace SilMock\tests\Google\Service;

use Google\Client;
use Google\Service\Directory\Alias as Google_Service_Directory_Alias;
use Google\Service\Directory\User as  Google_Service_Directory_User;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Http\Batch;
use SilMock\Google\Service\Directory;
use SilMock\Google\Service\Directory\ObjectUtils;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\GoogleFixtures;

class DirectoryTest extends TestCase
{
    use SampleUser;

    public string $dataFile = DATAFILE2;
    public string $userId = '999991';

    public function getProperties($object, $propKeys = null): array
    {
        $outArray = [];

        if ($object !== null) {
            if ($propKeys === null) {
                $propKeys = [
                    "changePasswordAtNextLogin",
                    "hashFunction",
                    "id",
                    "password",
                    "primaryEmail",
                    "suspended",
                    "isEnforcedIn2Sv",
                    "isEnrolledIn2Sv",
                    "aliases",
                ];
            }


            foreach ($propKeys as $key) {
                $outArray[$key] = $object->$key;
            }
        }
        return $outArray;
    }

    public function testCreateBatch()
    {
        $batch = new Batch();
        static::assertInstanceOf(Batch::class, $batch);
    }

    public function testDirectory()
    {
        $expectedKeys = [
            'asps',
            'users',
            'users_aliases',
            'verificationCodes',
            'tokens',
        ];
        $errorMessage = " *** Directory was not initialized properly";

        $directory = new Directory('anyclient', $this->dataFile);

        $directoryAsJson = json_encode($directory);
        $directoryInfo = json_decode($directoryAsJson, true);
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $directoryInfo, $errorMessage);
            $this->assertEmpty($directoryInfo[$expectedKey], $errorMessage);
        }
    }

    public function testGetClient()
    {
        $dir = new Directory('anyclient', $this->dataFile);
        $client = $dir->getClient();
        Assert::assertInstanceOf(Client::class, $client);
    }

    public function testUsersInsert()
    {
        $newUser = $this->setupSampleUser($this->dataFile);
        $results = $this->getProperties($newUser);

        $expected = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test1@sil.org",
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];
        $msg = " *** Bad returned user";
        $this->assertEquals($expected, $results, $msg);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $sqliteData = $sqliteClass->getData('', '');
        $sqliteDataValues = array_values($sqliteData);
        $lastDataEntry = end($sqliteDataValues);
        $dataObj = json_decode($lastDataEntry['data']);
        $results = $this->getProperties($dataObj);

        // $expected is the same as above
        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersInsertWithAliases()
    {
        $newUser = $this->setupSampleUser($this->dataFile, true);

        $results =  $this->getProperties($newUser);
        $expected = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test1@sil.org",
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => ["user_alias1@sil.org", "user_alias2@sil.org"],
        ];
        $msg = " *** Bad returned user";
        $this->assertEquals($expected, $results, $msg);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $sqliteData = $sqliteClass->getData('', '');
        $sqliteDataValues = array_values($sqliteData);
        $lastDataEntry = end($sqliteDataValues);
        $lastAlias = json_decode($lastDataEntry['data'], true);

        $results = $lastAlias;

        $expected = [
            "alias" => "user_alias2@sil.org",
            "kind" => "personal",
            "primaryEmail" => "user_test1@sil.org",
            'etag' => null,
            'id' => null,
        ];

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function getFixtures(): array
    {
        $user4Data = '{"changePasswordAtNextLogin":false,' .
            '"hashFunction":"SHA-512",' .
            '"id":"' . $this->userId .'","password":"testP4ss",' .
            '"primaryEmail":"user_test4@sil.org",' .
            '"isEnforcedIn2Sv":false,' .
            '"isEnrolledIn2Sv":true,' .
            '"suspended":false,"suspensionReason":null}';

        $alias2 = new Google_Service_Directory_Alias();
        $alias2->setAlias("users_alias2@sil.org");
        $alias2->setPrimaryEmail("user_test1@sil.org");

        $alias6 = new Google_Service_Directory_Alias();
        $alias6->setAlias("users_alias6@sil.org");
        $alias6->setId("1");

        return [
            [
                'directory', 'user', '{"primaryEmail":"user_test1@sil.org",' .
                '"id":"999990"}'
            ],
            ['directory', 'users_alias', json_encode($alias2)],
            ['app_engine', 'webapp', 'webapp3 test data'],
            ['directory', 'user', $user4Data],
            ['directory', 'user', 'user5 test data'],
            ['directory', 'users_alias', json_encode($alias6)],
        ];
    }

    public function getAliasFixture($alias, $email, ?string $id): Google_Service_Directory_Alias
    {
        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->setAlias($alias);
        if ($email) {
            $newAlias->setPrimaryEmail($email);
        }

        if (! empty($id)) {
            $newAlias->setId($id);
        }

        return $newAlias;
    }

    public function testUsersGet()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = 'user_test4@sil.org';

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);

        $newUser = $newDir->users->get($primaryEmail);
        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersGetById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->get($this->userId);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersGetAliases()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $email = "user_test4@sil.org";

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $email,
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => ["users_alias1A@sil.org", "users_alias1B@sil.org"],
        ];


        $aliasA = $this->getAliasFixture("users_alias1A@sil.org", $email, null);
        $aliasB = $this->getAliasFixture("users_alias1B@sil.org", $email, null);

        $newFixtures = [
            ['directory', 'users_alias', json_encode($aliasA)],
            ['directory', 'users_alias', json_encode($aliasB)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->get($email);
        $results = $this->getProperties($newUser);

        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersGetByAlias()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $email = "user_test4@sil.org";

        $aliasA = $this->getAliasFixture("users_alias1A@sil.org", $email, null);
        $aliasB = $this->getAliasFixture("users_alias1B@sil.org", $email, null);

        $newFixtures = [
            ['directory', 'users_alias', json_encode($aliasA)],
            ['directory', 'users_alias', json_encode($aliasB)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->get('users_alias1A@sil.org');

        $this->assertNotNull(
            $newUser,
            'Failed to get user by an alias'
        );
        $this->assertEquals(
            $email,
            $newUser['primaryEmail'],
            'Failed to get correct user by an alias'
        );
    }

    public function testUsersUpdate()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($primaryEmail, $newUser);

        $newUser = $newDir->users->get($primaryEmail);
        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersUpdateById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($this->userId, $newUser);
        $newUser = $newDir->users->get($this->userId);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdateByIdChangeEmail()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $oldEmailAddress = 'user_test4@sil.org';
        $newEmailAddress = 'user_test4a@sil.org';

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $newEmailAddress,
            "suspended" => false,
            "isEnforcedIn2Sv" => false,
            "isEnrolledIn2Sv" => true,
            "aliases" => [],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($this->userId, $newUser);
        $newUser = $newDir->users->get($this->userId);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $expected['aliases'][] = $oldEmailAddress;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);

        // Attempt to get the user by the alias, after updating the primary email address
        $newUser = $newDir->users->get($oldEmailAddress);
        $results = $this->getProperties($newUser);
        // $msg is as above
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdateWithAlias()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "isEnrolledIn2Sv" => true,
            "isEnforcedIn2Sv" => false,
            "aliases" => ['user_alias4B@sil.org'],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($primaryEmail, $newUser);
        $newUser = $newDir->users->get($primaryEmail);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdateWithDifferentAliases()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $aliasFixture = $this->getAliasFixture(
            "users_alias4B@sil.org",
            $primaryEmail,
            null
        );
        $newFixtures = [
            ['directory', 'users_alias', json_encode($aliasFixture)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        // Different aliases
        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $this->userId,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "isEnrolledIn2Sv" => true,
            "isEnforcedIn2Sv" => false,
            "aliases" => ['user_alias4C@sil.org', 'user_alias4D@sil.org'],
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($primaryEmail, $newUser);
        $newUser = $newDir->users->get($primaryEmail);

        $results = $this->getProperties($newUser);
        $expected = $userData;

        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdateNotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userId = 999999;

        $userData = [
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-512",
            "id" => $userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
        ];

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);

        $this->expectExceptionCode(201407101130);
        $newDir->users->update($userId, $newUser);
    }

    public function testUsersDelete()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->delete($primaryEmail);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $results = $sqliteClass->getData('', '');

        $expected = [
            [
                'id' => 1, 'type' => 'directory', 'class' => 'user',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                    '"id":"999990"}'
            ],
            [
                'id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias2@sil.org","etag":null,' .
                    '"id":null,"kind":null,' .
                    '"primaryEmail":"user_test1@sil.org"}'
            ],
            [
                'id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                'data' => 'webapp3 test data'
            ],
            [
                'id' => 5, 'type' => 'directory', 'class' => 'user',
                'data' => 'user5 test data'
            ],
            [
                'id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                    '"id":"1","kind":null,"primaryEmail":null}'
            ],
        ];

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersDeleteById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->delete($this->userId);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $results = $sqliteClass->getData('', '');

        $expected = [
            [
                'id' => 1, 'type' => 'directory', 'class' => 'user',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                    '"id":"999990"}'
            ],
            [
                'id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias2@sil.org","etag":null,' .
                    '"id":null,"kind":null,' .
                    '"primaryEmail":"user_test1@sil.org"}'
            ],
            [
                'id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                'data' => 'webapp3 test data'
            ],
            [
                'id' => 5, 'type' => 'directory', 'class' => 'user',
                'data' => 'user5 test data'
            ],
            [
                'id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                    '"id":"1","kind":null,"primaryEmail":null}'
            ],
        ];

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersAliasesInsert()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->alias = "users_alias1@sil.org";
        $newAlias->kind = "personal";

        $newDir = new Directory('anyclient', $this->dataFile);
        $newAlias = $newDir->users_aliases->insert("user_test1@sil.org", $newAlias);

        $results = json_encode($newAlias);
        $expected = '{"alias":"users_alias1@sil.org","etag":null,"id":null,' .
            '"kind":"personal","primaryEmail":"user_test1@sil.org"}'
        ;
        $msg = " *** Bad returned alias";
        $this->assertEquals($expected, $results, $msg);


        $sqliteClass = new SqliteUtils($this->dataFile);
        $sqliteData = $sqliteClass->getData('', '');
        $sqliteDataValues = array_values($sqliteData);
        $lastDataEntry = end($sqliteDataValues);
        $results = $lastDataEntry['data'];

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersAliasesInsertUserNotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->alias = "users_alias1@sil.org";
        $newAlias->kind = "personal";

        $newDir = new Directory('anyclient', $this->dataFile);

        $this->expectExceptionCode(201407110830);
        $newDir->users_aliases->insert("no_user@sil.org", $newAlias);
    }

    public function testUsersAliasesListUsersAliasesEmail()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $aliasFixture = $this->getAliasFixture(
            "users_alias7@sil.org",
            "user_test1@sil.org",
            "1"
        );

        $newFixtures = [
            ['directory', 'users_alias', json_encode($aliasFixture)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("user_test1@sil.org");

        $results = [];
        foreach ($aliases['aliases'] as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = [
            '{"alias":"users_alias2@sil.org","etag":null,"id":null,' .
            '"kind":null,"primaryEmail":"user_test1@sil.org"}',
            '{"alias":"users_alias7@sil.org","etag":null,"id":"1",' .
            '"kind":null,"primaryEmail":"user_test1@sil.org"}'
        ];

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersAliasesListUsersAliasesId()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test7@sil.org";

        $aliasB = $this->getAliasFixture("users_alias7b@sil.org", $email, "7");
        $aliasC = $this->getAliasFixture("users_alias7c@sil.org", null, "7");

        $newFixtures = [
            [
                'directory', 'user',
                '{"id":"7","primaryEmail":"' . $email . '",' .
                '"aliases":[]}'
            ],
            ['directory', 'users_alias', json_encode($aliasB)],
            ['directory', 'users_alias', json_encode($aliasC)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("7");

        $results = [];
        foreach ($aliases['aliases'] as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = [
            '{"alias":"users_alias7b@sil.org","etag":null,"id":"7","kind":null,' .
            '"primaryEmail":"user_test7@sil.org"}',
            '{"alias":"users_alias7c@sil.org","etag":null,"id":"7","kind":null,' .
            '"primaryEmail":null}',
        ];

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersAliasesListUsersAliasesStructure()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test1@sil.org";

        $alias = $this->getAliasFixture("users_alias7@sil.org", $email, "1");
        $newFixtures = [
            ['directory', 'users_alias', json_encode($alias)],
        ];

        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("user_test1@sil.org");

        $results = isset($aliases['aliases']);
        $this->assertTrue($results, ' *** The aliases property is not accessible');

        $results = is_array($aliases['aliases']);
        $this->assertTrue($results, ' *** The aliases property is not an array');

        $user_aliases = [];

        foreach ($aliases['aliases'] as $alias) {
            $user_aliases[] = $alias['alias'];
        }

        $results = $user_aliases;
        $expected = ["users_alias2@sil.org", "users_alias7@sil.org"];
        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersAliasesListUsersAliasesUserNotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $alias = $this->getAliasFixture(
            "users_alias7@sil.org",
            "user_test1@sil.org",
            "1"
        );
        $newFixtures = [
            ['directory', 'users_alias', json_encode($alias)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);

        $this->expectExceptionCode(201407101420);
        $newDir->users_aliases->listUsersAliases("no_user@sil.org");
    }

    public function testUsersAliasesDelete()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test1@sil.org";

        $alias = $this->getAliasFixture("users_alias7@sil.org", $email, "1");

        $newFixtures = [
            ['directory', 'users_alias', json_encode($alias)],
        ];
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $results = $newDir->users_aliases->delete(
            "user_test1@sil.org",
            "users_alias2@sil.org"
        );

        $this->assertTrue($results, " *** Didn't appear to delete the alias.");

        $sqliteUtils = new SqliteUtils($this->dataFile);
        $results = $sqliteUtils->getData('directory', 'users_alias');

        $expected = [
            [
                'id' => '6',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                    '"id":"1","kind":null,"primaryEmail":null}',
            ],
            [
                'id' => '7',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"alias":"users_alias7@sil.org","etag":null,' .
                    '"id":"1","kind":null,"primaryEmail":"' . $email . '"}'
            ],
        ];
        $msg = " *** Mismatching users_aliases in db";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUserArrayAccess()
    {
        $user = new Google_Service_Directory_User();
        $user->suspended = false;

        $this->assertFalse($user->suspended, ' *** class access failed');
        $this->assertFalse($user['suspended'], ' *** array access failed');
    }

    public function testUserClassAccess()
    {
        $user = new Google_Service_Directory_User();
        $user['suspended'] = false;

        $this->assertFalse($user->suspended, ' *** class access failed');
        $this->assertFalse($user['suspended'], ' *** array access failed');
    }

    public function testAliasArrayAccess()
    {
        $alias = new Google_Service_Directory_Alias();
        $email = 'user_test@sil.org';
        $alias->primaryEmail = $email;

        $this->assertEquals($email, $alias->primaryEmail, ' *** class access failed');
        $this->assertEquals($email, $alias['primaryEmail'], ' *** array access failed');
    }

    public function testAliasClassAccess()
    {
        $alias = new Google_Service_Directory_Alias();
        $email = 'user_test@sil.org';
        $alias['primaryEmail'] = $email;

        $this->assertEquals($email, $alias->primaryEmail, ' *** class access failed');
        $this->assertEquals($email, $alias['primaryEmail'], ' *** array access failed');
    }
}
