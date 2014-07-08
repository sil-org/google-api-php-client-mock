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


    public function testUsersGet()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $userData = '{"changePasswordAtNextLogin":false,"hashFunction":"SHA-1",' .
                    '"id":999991,"password":"testP4ss",' .
                    '"primaryEmail":"user_test4@sil.org",' .
                    '"suspended":false,"suspensionReason":null}';

        $fixtures = array(
            array('directory', 'user', 'user1 test data'),
            array('directory', 'useralias', 'useralias2 test data'),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user', $userData),
            array('directory', 'user', 'user5 test data'),
        );

        $fixturesClass->addFixtures($fixtures);
        $newDir = new Directory(null);

        $results = json_encode($newDir->users->get('user_test4@sil.org'));
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

        $fixtures = array(
            array('directory', 'user', '{"primaryEmail":"user1@sil.org"}'),
            array('directory', 'useralias', 'useralias2 test data'),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user','{"primaryEmail":"' . $primaryEmail . '"}'),
            array('directory', 'user', 'user5 test data'),
        );

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


    public function testUsersDelete()
    {
        $fixturesClass = new GoogleFixtures();
        $fixturesClass->removeAllFixtures();

        $primaryEmail = "user_test4@sil.org";

        $fixtures = array(
            array('directory', 'user', '{"primaryEmail":"user1@sil.org"}'),
            array('directory', 'useralias', 'useralias2 test data'),
            array('app_engine', 'webapp', 'webapp3 test data'),
            array('directory', 'user','{"primaryEmail":"' . $primaryEmail . '"}'),
            array('directory', 'user', 'user5 test data'),
        );

        $fixturesClass->addFixtures($fixtures);

        $newDir = new Directory(null);
        $newDir->users->delete($primaryEmail);

        $sqliteClass = new SqliteUtils();
        $results = $sqliteClass->getData('', '');

        $expected = array(
            array('id' => 1, 'type' => 'directory', 'class' => 'user',
                  'data' => '{"primaryEmail":"user1@sil.org"}'),
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