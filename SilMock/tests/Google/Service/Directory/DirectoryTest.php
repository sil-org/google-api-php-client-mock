<?php

//require_once(dirname(__FILE__).'/../../../../Google/Service/Directory.php');

use SilMock\Google\Service\Directory;
use SilMock\Google\Service\Directory\User;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\GoogleFixtures;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = '../DataStore/Sqlite/Google_Services_Data.db';

    public function loadFixtures()
    {
        $fixtures = array(
            array('directory', 'user', '{')

        );
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
            array('directory', 'useralias', 'useralias2 test data'),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user', $user4Data),
            array('directory', 'user', 'user5 test data'),
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
            array('id' => 2, 'type' => 'directory', 'class' => 'useralias',
                  'data' => 'useralias2 test data'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                  'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                  'data' => 'user5 test data'),
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
            array('id' => 2, 'type' => 'directory', 'class' => 'useralias',
                'data' => 'useralias2 test data'),
            array('id' => 3, 'type' => 'app_engine', 'class' => 'webapp',
                'data' => 'webapp3 test data'),
            array('id' => 5, 'type' => 'directory', 'class' => 'user',
                'data' => 'user5 test data'),
        );

        $msg = " *** Bad database data returned";
        $this->assertEquals($expected, $results, $msg);
    }

} 