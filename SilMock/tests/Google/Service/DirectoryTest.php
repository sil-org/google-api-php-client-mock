<?php

//require_once(dirname(__FILE__).'/../../../../Google/Service/Directory.php');

use SilMock\Google\Service\Directory;
use SilMock\Google\Service\Directory\User;
use SilMock\Google\Service\Directory\Alias;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\GoogleFixtures;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = '../DataStore/Sqlite/Google_Services_Data.db';

    public function testDirectory()
    {
        $dir = new Directory('whatever');
        $results = json_encode($dir);
        $expected = '{"users":{},"users_aliases":{}}';
        $msg = " *** Directory was not initialized properly";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersInsert()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $newUser = new User();
        $newUser->changePasswordAtNextLogin = false; // bool
        $newUser->hashFunction = "SHA-1"; // string
        $newUser->id = 999991; // int???
        $newUser->password = 'testP4ss'; // string
        $newUser->primaryEmail = 'user_test1@sil.org'; // string email
        $newUser->suspended = false; // bool
      //  $newUser->$suspensionReason = ''; // string

        $newDir = new Directory(null);
        $newUser = $newDir->users->insert($newUser);

        $results = json_encode($newUser);
        $expected = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
            '"id":999991,"password":"testP4ss",' .
            '"primaryEmail":"user_test1@sil.org",' .
            '"suspended":false,"suspensionReason":null}';
        $msg = " *** Bad returned user";
        $this->assertEquals($expected, $results, $msg);


        $sqliteClass = new SqliteUtils();
        $lastDataEntry = end(array_values($sqliteClass->getData('', '')));
        $results = $lastDataEntry['data'];

        $expected = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
                    '"id":999991,"password":"testP4ss",' .
                    '"primaryEmail":"user_test1@sil.org",' .
                    '"suspended":false,"suspensionReason":null}';

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function get_fixtures() {
        $user4Data = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
            '"id":999991,"password":"testP4ss",' .
            '"primaryEmail":"user_test4@sil.org",' .
            '"suspended":false,"suspensionReason":null}';


        $fixtures = array(
            array('directory', 'user', '{"primaryEmail":"user_test1@sil.org",' .
                                       '"id":999990}'),
            array('directory', 'users_alias', '{"primaryEmail":"user_test1@sil.org",' .
                                              '"alias":"users_alias2@sil.org"}'),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user', $user4Data),
            array('directory', 'user', 'user5 test data'),
            array('directory', 'users_alias', '{"id":1,' .
                                              '"alias":"users_alias6@sil.org"}'),
        );

        return $fixtures;
    }

    public function testUsersGet()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $primaryEmail = 'user_test4@sil.org';

        $userData = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
                    '"id":999991,"password":"testP4ss",' .
                    '"primaryEmail":"' . $primaryEmail . '",' .
                    '"suspended":false,"suspensionReason":null}';

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory(null);

        $results = json_encode($newDir->users->get($primaryEmail));
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersGet_ById()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $userId = '999991';

        $userData = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
            '"id":' . $userId . ',"password":"testP4ss",' .
            '"primaryEmail":"user_test4@sil.org",' .
            '"suspended":false,"suspensionReason":null}';

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory(null);

        $results = json_encode($newDir->users->get($userId));
        $expected = $userData;
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersUpdate()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => 999991,
            "password" => "testP4ss",
            "primaryEmail" => $primaryEmail,
            "suspended" => false,
            "suspensionReason" => null
        );

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new User();
        $newUser->initialize($userData);

        $newDir = new Directory(null);
        $newDir->users->update($primaryEmail, $newUser);

        $results = json_encode($newDir->users->get($primaryEmail));
        $expected = json_encode($userData);
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersUpdate_ById()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $userId = 999991;

        $userData = array(
            "changePasswordAtNextLogin" => false,
            "hashFunction" => "SHA-1",
            "id" => $userId,
            "password" => "testP4ss",
            "primaryEmail" => "user_test4@sil.org",
            "suspended" => false,
            "suspensionReason" => null
        );

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newUser = new User();
        $newUser->initialize($userData);

        $newDir = new Directory(null);
        $newDir->users->update($userId, $newUser);

        $results = json_encode($newDir->users->get($userId));
        $expected = json_encode($userData);
        $msg = " *** Bad user data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersDelete()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory(null);
        $newDir->users->delete($primaryEmail);

        $sqliteClass = new SqliteUtils();
        $results = $sqliteClass->getData('', '');

        $expected = array(
            array('id' => 1, 'type' => 'directory', 'class' => 'user',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                              '"id":999990}'),
            array('id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"alias":"users_alias2@sil.org"}'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                  'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                  'data' => 'user5 test data'),
            array('id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                'data' => '{"id":1,"alias":"users_alias6@sil.org"}'),
        );

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersDelete_ById()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $userId = 999991;

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory(null);
        $newDir->users->delete($userId);

        $sqliteClass = new SqliteUtils();
        $results = $sqliteClass->getData('', '');

        $expected = array(
            array('id' => 1, 'type' => 'directory', 'class' => 'user',
                  'data' => '{"primaryEmail":"user_test1@sil.org","id":999990}'),
            array('id' => 2, 'type' => 'directory', 'class' => 'users_alias',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                           '"alias":"users_alias2@sil.org"}'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                  'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                  'data' => 'user5 test data'),
            array('id' => 6, 'type' => 'directory', 'class' => 'users_alias',
                  'data' => '{"id":1,"alias":"users_alias6@sil.org"}'),
        );

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }


    public function testUsersAliasesInsert()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newAlias = new Alias();
        $newAlias->alias = "users_alias1@sil.org";
        $newAlias->kind = "personal";

        $newDir = new Directory(null);
        $newAlias = $newDir->users_aliases->insert("user_test1@sil.org", $newAlias);

        $results = json_encode($newAlias);
        $expected = '{"alias":"users_alias1@sil.org","etag":null,"id":null,' .
                    '"kind":"personal","primaryEmail":"user_test1@sil.org"}'
        ;
        $msg = " *** Bad returned alias";
        $this->assertEquals($expected, $results, $msg);


        $sqliteClass = new SqliteUtils();
        $lastDataEntry = end(array_values($sqliteClass->getData('', '')));
        $results = $lastDataEntry['data'];

        $msg = " *** Bad data from sqlite database";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testUsersAliasesListUsersAliases_Email()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newFixtures = array(
            array('directory', 'users_alias', '{"id":1,"primaryEmail":' .
            '"user_test1@sil.org","alias":"users_alias7@sil.org"}'),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory(null);
        $aliases = $newDir->users_aliases->listUsersAliases("user_test1@sil.org");

        $results = array();
        foreach ($aliases->_values as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = array(
            '{"alias":"users_alias2@sil.org","etag":null,"id":null,"kind":null,' .
                '"primaryEmail":"user_test1@sil.org"}',
            '{"alias":"users_alias7@sil.org","etag":null,"id":1,"kind":null,' .
                '"primaryEmail":"user_test1@sil.org"}',
        );

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);

    }

    public function testUsersAliasesListUsersAliases_ID()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $fixtures = $this->get_fixtures();
        $fixturesClass->addFixtures($fixtures);

        $newFixtures = array(
            array('directory', 'users_alias', '{"id":1,"primaryEmail":' .
            '"user_test1@sil.org","alias":"users_alias7@sil.org"}'),
        );
        $fixturesClass->addFixtures($newFixtures);

        $newDir = new Directory(null);
        $aliases = $newDir->users_aliases->listUsersAliases("1");

        $results = array();
        foreach ($aliases->_values as $nextAlias) {
            $results[] = json_encode($nextAlias);
        }

        $expected = array(
            '{"id":5,"alias":"users_alias2@sil.org"}',
            '{"primaryEmail":"user_test1@sil.org","id":1,"alias":"users_alias7@sil.org"}',
        );

        $expected = array(
            '{"alias":"users_alias6@sil.org","etag":null,"id":1,"kind":null,'.
                '"primaryEmail":null}',
            '{"alias":"users_alias7@sil.org","etag":null,"id":1,"kind":null,' .
                 '"primaryEmail":"user_test1@sil.org"}',
        );

        $msg = " *** Bad returned Aliases";
        $this->assertEquals($expected, $results, $msg);

    }
} 