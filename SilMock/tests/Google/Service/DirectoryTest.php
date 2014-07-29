<?php

use SilMock\Google\Service\Directory;
use SilMock\Google\Service\Directory\ObjectUtils;
use SilMock\Google\Service\Directory\User;
use SilMock\Google\Service\Directory\FakeGoogleUser;
use SilMock\Google\Service\Directory\Alias;
use SilMock\Google\Service\Directory\FakeGoogleAlias;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\GoogleFixtures;


class DirectoryTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = DATAFILE2;

    public function getProperties($object, $propKeys = null) {
        if ($propKeys === null) {
            $propKeys = array(
                "changePasswordAtNextLogin",
                "hashFunction",
                "id",
                "password",
                "primaryEmail",
                "suspended",
                "aliases",
            );
        }

        $outArray = array();

        foreach ($propKeys as $key) {
            $outArray[$key] = $object->$key;
        }

        return $outArray;
    }

    public function testDirectory()
    {
        $dir = new Directory('whatever', $this->dataFile);
        $results = json_encode($dir);
        $expected = '{"users":{},"users_aliases":{}}';
        $msg = " *** Directory was not initialized properly";
        $this->assertEquals($expected, $results, $msg);

        $ma = array('a'=>1, 'b'=>2, 'c'=>array());
    }

    public function testUsersInsert()
    {

        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
        $newUser = new Google_Service_Directory_User();
        $newUser->changePasswordAtNextLogin = false; // bool
        $newUser->hashFunction = "SHA-1"; // string
        $newUser->id = 999991; // int???
        $newUser->password = 'testP4ss'; // string
        $newUser->primaryEmail = 'user_test1@sil.org'; // string email
        $newUser->suspended = false; // bool
      //  $newUser->$suspensionReason = ''; // string
        $newUser->aliases = array();

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->insert($newUser);
        $results = $this->getProperties($newUser);

        $expected = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => "user_test1@sil.org",
            "suspended" => false,
            "aliases" => array(),
        );
        $msg = " *** Bad returned user";
        $this->assertEquals($expected, $results, $msg);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $sqliteData = $sqliteClass->getData('', '');
        $sqliteDataValues = array_values($sqliteData);
        $lastDataEntry = end($sqliteDataValues);
        $dataObj = json_decode($lastDataEntry['data']);
        $results = $this->getProperties($dataObj);

        $expected = array (
                            "changePasswordAtNextLogin" => false,
                            "hashFunction" => "SHA-1",
                            "id" => 999991,
                            "password" => "testP4ss",
                            "primaryEmail" => "user_test1@sil.org",
                            "suspended" => false,
                            "aliases" => array(),
                     );

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersInsert_WithAlias()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
        $newUser = new Google_Service_Directory_User();
        $newUser->changePasswordAtNextLogin = false; // bool
        $newUser->hashFunction = "SHA-1"; // string
        $newUser->id = 999991; // int???
        $newUser->password = 'testP4ss'; // string
        $newUser->primaryEmail = 'user_test1@sil.org'; // string email
        $newUser->suspended = false; // bool
        //  $newUser->$suspensionReason = ''; // string

        $newAliases = new Google_Service_Directory_Aliases();
        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->alias = 'user_alias1@sil.org';
        $newAlias->setKind("personal");
        $newAlias->primaryEmail = $newUser->primaryEmail;
        $newAliases->setAliases(array($newAlias));

        $newUser->aliases = $newAliases; // bool

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->insert($newUser);

        $results =  $this->getProperties($newUser);
        $expected = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => "user_test1@sil.org",
            "suspended" => false,
            "aliases" => array(
                'etag' => null,
                'kind' => null,
                'aliases' => array(
                    array(
                        'alias' => "user_alias1@sil.org",
                        'etag' => null,
                        'id' => null,
                        'kind' => 'personal',
                        'primaryEmail' => 'user_test1@sil.org')),
            ),
        );
        $msg = " *** Bad returned user";
        $this->assertEquals($expected, $results, $msg);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $lastDataEntry = end(array_values($sqliteClass->getData('', '')));
        $lastAliases = json_decode($lastDataEntry['data'], true);

        $results = $lastAliases['aliases']['aliases'];

        $expected = array(
            array(
                "alias" => "user_alias1@sil.org",
                "kind" => "personal",
                "primaryEmail" => "user_test1@sil.org",
                'etag' => null,
                'id' => null,
            ),
        );

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function getFixtures() {
        $user4Data = '{"changePasswordAtNextLogin":false,' .
            '"hashFunction":"SHA-1",' .
            '"id":999991,"password":"testP4ss",' .
            '"primaryEmail":"user_test4@sil.org",' .
            '"suspended":false,"suspensionReason":null}';

        $alias2 = new Google_Service_Directory_Alias();
        $alias2->setAlias("users_alias2@sil.org");
        $alias2->setPrimaryEmail("user_test1@sil.org");

        $alias6 = new Google_Service_Directory_Alias();
        $alias6->setAlias("users_alias6@sil.org");
        $alias6->setId(1);

        $fixtures = array(
            array('directory', 'user', '{"primaryEmail":"user_test1@sil.org",' .
                                       '"id":999990}'),
            array('directory', 'users_alias', json_encode($alias2)),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user', $user4Data),
            array('directory', 'user', 'user5 test data'),
            array('directory', 'users_alias', json_encode($alias6)),
        );

        return $fixtures;
    }

    public function getAliasFixture($alias, $email, $id)
    {
        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->setAlias($alias);
        if ($email) {
            $newAlias->setPrimaryEmail($email);
        }

        if ($id) {
            $newAlias->setId($id);
        }

        return $newAlias;
    }

    public function testUsersGet()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = 'user_test4@sil.org';

        $userData = array(
                        "changePasswordAtNextLogin" => false,
                        "hashFunction" => "SHA-1",
                        "id" => 999991,
                        "password" => "testP4ss",
                        "primaryEmail" => $primaryEmail,
                        "suspended" => false,
                        "aliases" =>null,
                    );

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);

        $newUser = $newDir->users->get($primaryEmail);
        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersGet_ById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userId = '999991';

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => $userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
            "aliases" =>null,
        );

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->get($userId);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersGet_Aliases()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $userId = '999991';
        $email = "user_test4@sil.org";

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => intval($userId),
            "password" => "testP4ss",
            "primaryEmail" => $email,
            "suspended" => false,
            "aliases" => array("users_alias1A@sil.org", "users_alias1B@sil.org"),
        );


        $aliasA = $this->getAliasFixture("users_alias1A@sil.org", $email, null);
        $aliasB = $this->getAliasFixture("users_alias1B@sil.org", $email, null);

        $newFixtures = array(
            array('directory', 'users_alias', json_encode($aliasA)),
            array('directory', 'users_alias', json_encode($aliasB)),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newUser = $newDir->users->get($email);
        $results = $this->getProperties($newUser);

        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdate()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "aliases" => array(),
        );

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


    public function testUsersUpdate_ById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userId = 999991;

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => $userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
            "aliases" => array(),
        );

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($userId, $newUser);
        $newUser = $newDir->users->get($userId);

        $results = $this->getProperties($newUser);
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdate_WithAlias()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "aliases" => array('user_alias4B@sil.org'),
        );

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

    public function testUsersUpdate_WithDifferentAliases()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $aliasFixture = $this->getAliasFixture("users_alias4B@sil.org",
            $primaryEmail, null);
        $newFixtures = array(
            array('directory', 'users_alias', json_encode($aliasFixture)),
        );
        $fixturesClass->addFixtures($newFixtures);

        // Different aliases
        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "aliases" => array('user_alias4C@sil.org', 'user_alias4D@sil.org'),
        );

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

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 201407101130
     */
    public function testUsersUpdate_NotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userId = 999999;

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => $userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
        );

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $userData);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->update($userId, $newUser);
        // the assert is in the doc comment
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

        $expected = array(
            array('id' => 1, 'type' => 'directory', 'class' => 'user',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                              '"id":999990}'),
            array('id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                  'data' => '{"alias":"users_alias2@sil.org","etag":null,' .
                            '"id":null,"kind":null,' .
                            '"primaryEmail":"user_test1@sil.org"}'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                  'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                  'data' => 'user5 test data'),
            array('id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                  'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                  '"id":1,"kind":null,"primaryEmail":null}'),
        );

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersDelete_ById()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $userId = 999991;

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $newDir->users->delete($userId);

        $sqliteClass = new SqliteUtils($this->dataFile);
        $results = $sqliteClass->getData('', '');

        $expected = array(
            array('id' => 1, 'type' => 'directory', 'class' => 'user',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                    '"id":999990}'),
            array('id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias2@sil.org","etag":null,' .
                    '"id":null,"kind":null,' .
                    '"primaryEmail":"user_test1@sil.org"}'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                'data' => 'user5 test data'),
            array('id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                    '"id":1,"kind":null,"primaryEmail":null}'),
        );

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
        $lastDataEntry = end(array_values($sqliteClass->getData('', '')));
        $results = $lastDataEntry['data'];

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 201407110830
     */
    public function testUsersAliasesInsert_UserNotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $newAlias = new Google_Service_Directory_Alias();
        $newAlias->alias = "users_alias1@sil.org";
        $newAlias->kind = "personal";

        $newDir = new Directory('anyclient', $this->dataFile);
        $newAlias = $newDir->users_aliases->insert("no_user@sil.org", $newAlias);
        // the assert is in the doc comments with @expectedException
    }

    public function testUsersAliasesListUsersAliases_Email()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);

        $aliasFixture = $this->getAliasFixture("users_alias7@sil.org",
                                                  "user_test1@sil.org", 1);

        $newFixtures = array(
            array('directory', 'users_alias', json_encode($aliasFixture)),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("user_test1@sil.org");

        $results = array();
        foreach ($aliases['aliases'] as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = array(
            '{"alias":"users_alias2@sil.org","etag":null,"id":null,' .
              '"kind":null,"primaryEmail":"user_test1@sil.org"}',
            '{"alias":"users_alias7@sil.org","etag":null,"id":1,' .
              '"kind":null,"primaryEmail":"user_test1@sil.org"}'
        );

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);

    }

    public function testUsersAliasesListUsersAliases_ID()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test7@sil.org";

        $aliasB = $this->getAliasFixture("users_alias7b@sil.org", $email, 7);
        $aliasC = $this->getAliasFixture("users_alias7c@sil.org", null, 7);

        $newFixtures = array(
            array('directory', 'user',
                    '{"id":7,"primaryEmail":"' . $email . '",' .
                     '"aliases":[]}'),
            array('directory', 'users_alias', json_encode($aliasB)),
            array('directory', 'users_alias', json_encode($aliasC)),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("7");

        $results = array();
        foreach ($aliases['aliases'] as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = array(
            '{"alias":"users_alias7b@sil.org","etag":null,"id":7,"kind":null,' .
               '"primaryEmail":"user_test7@sil.org"}',
            '{"alias":"users_alias7c@sil.org","etag":null,"id":7,"kind":null,' .
               '"primaryEmail":null}',
        );

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);

    }

    public function testUsersAliasesListUsersAliases_Structure()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test1@sil.org";

        $alias = $this->getAliasFixture("users_alias7@sil.org", $email, 1);
        $newFixtures = array(
            array('directory', 'users_alias', json_encode($alias)),
        );

        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("user_test1@sil.org");

        $results = isset($aliases['aliases']);
        $this->assertTrue($results, ' *** The aliases property is not accessible');

        $results = is_array($aliases['aliases']);
        $this->assertTrue($results, ' *** The aliases property is not an array');

        $user_aliases = array();

        foreach($aliases['aliases'] as $alias) {
            $user_aliases[] = $alias['alias'];
        }

        $results = $user_aliases;
        $expected = array("users_alias2@sil.org", "users_alias7@sil.org");
        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 201407101420
     */
    public function testUsersAliasesListUsersAliases_UserNotThere()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $alias = $this->getAliasFixture("users_alias7@sil.org",
                                          "user_test1@sil.org", 1);
        $newFixtures = array(
            array('directory', 'users_alias', json_encode($alias)),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $aliases = $newDir->users_aliases->listUsersAliases("no_user@sil.org");
    }

    public function testUsersAliasesDelete()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->getFixtures();
        $fixturesClass->addFixtures($fixtures);
        $email = "user_test1@sil.org";

        $alias = $this->getAliasFixture("users_alias7@sil.org", $email, 1);

        $newFixtures = array(
            array('directory', 'users_alias', json_encode($alias)),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory('anyclient', $this->dataFile);
        $results = $newDir->users_aliases->delete("user_test1@sil.org",
                                                  "users_alias2@sil.org");

        $this->assertTrue($results, " *** Didn't appear to delete the alias.");

        $sqliteUtils = new SqliteUtils($this->dataFile);
        $results = $sqliteUtils->getData('directory', 'users_alias');

        $expected = array(
            array('id' => '6',
                  'type' => 'directory',
                  'class' => 'users_alias',
                  'data' => '{"alias":"users_alias6@sil.org","etag":null,' .
                            '"id":1,"kind":null,"primaryEmail":null}',
            ),
            array('id' => '7',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"alias":"users_alias7@sil.org","etag":null,' .
                          '"id":1,"kind":null,"primaryEmail":"' . $email . '"}'),
        );
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