<?php

use SilMock\DataStore\Sqlite\SqliteUtils;

class SqliteUtilsTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = '../DataStore/Sqlite/Test_Google_Service_Data.db';

    public function testRecordData()
    {
        file_put_contents($this->dataFile, '');
        $newSql = new SqliteUtils($this->dataFile);
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->recordData('directory', 'user',
                       'test data');
        $msg = " *** Expected to add data successfully.";
        $this->assertTrue($results, $msg);
    }

    public function loadData()
    {
        file_put_contents($this->dataFile, '');
        $newSql = new SqliteUtils($this->dataFile);
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->recordData('directory', 'user',
            '{"primaryEmail":"user_test1@sil.org","id":1,"password":"testPass1"}');
        $results = $newSql->recordData('directory', 'users_alias',
            '{"primaryEmail":"user_test1@sil.org","alias":"users_alias2@sil.org"}');
        $results = $newSql->recordData('app_engine', 'webapp',
            'webapp3 test data');
        $results = $newSql->recordData('directory', 'user',
            '{"primaryEmail":"user_test4@sil.org","id":4,"password":"testPass4"}');
        $results = $newSql->recordData('directory', 'users_alias',
            '{"primaryEmail":"user_test1@sil.org","alias":"users_alias5@sil.org"}');

        return $newSql;
    }

    public function testGetData_All()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getData('', '');
        $expected = array(
            array('id' => '1',
                  'type' => 'directory',
                  'class' => 'user',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"id":1,"password":"testPass1"}',
                 ),
            array('id' => '2',
                  'type' => 'directory',
                  'class' => 'users_alias',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"alias":"users_alias2@sil.org"}',
            ),
            array('id' => '3',
                  'type' => 'app_engine',
                  'class' => 'webapp',
                  'data' => 'webapp3 test data',
            ),
            array('id' => '4',
                  'type' => 'directory',
                  'class' => 'user',
                  'data' => '{"primaryEmail":"user_test4@sil.org",' .
                            '"id":4,"password":"testPass4"}',
            ),
            array('id' => '5',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                    '"alias":"users_alias5@sil.org"}',
            ),
        );
        $msg = " *** Mismatched data results for all data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetData_Directory()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getData('directory', '');
        $expected = array(
            array('id' => '1',
                'type' => 'directory',
                'class' => 'user',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                          '"id":1,"password":"testPass1"}',
            ),
            array('id' => '2',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                          '"alias":"users_alias2@sil.org"}',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => '{"primaryEmail":"user_test4@sil.org",' .
                          '"id":4,"password":"testPass4"}',
            ),
            array('id' => '5',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                          '"alias":"users_alias5@sil.org"}',
            ),
        );
        $msg = " *** Mismatched data results for directory data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetData_DirectoryUser()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getData('directory', 'user');
        $expected = array(
            array('id' => '1',
                  'type' => 'directory',
                  'class' => 'user',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"id":1,"password":"testPass1"}',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => '{"primaryEmail":"user_test4@sil.org",' .
                          '"id":4,"password":"testPass4"}',
            ),
        );
        $msg = " *** Mismatched data results for user data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetData_NoMatches()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getData('directory', 'no_there');
        $expected = array();
        $msg = " *** Mismatched data results for missing data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetData_EmptyFile()
    {
        file_put_contents($this->dataFile, '');
        $newSql = new SqliteUtils($this->dataFile);
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->getData('', '');
        $expected = array();
        $msg = " *** Expected no data but got something.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetRecordByDataKey_DirectoryUserId()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getRecordByDataKey('directory', 'user', 'id', 4);
        $expected =  array('id' => '4',
                 'type' => 'directory',
                 'class' => 'user',
                 'data' => '{"primaryEmail":"user_test4@sil.org",' .
                           '"id":4,"password":"testPass4"}',
                    );
        $msg = " *** Mismatched data results for user data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetRecordByDataKey_DirectoryUserPrimaryEmail()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getRecordByDataKey('directory', 'user',
                                           'primaryEmail', 'user_test1@sil.org');
        $expected =  array('id' => '1',
            'type' => 'directory',
            'class' => 'user',
            'data' => '{"primaryEmail":"user_test1@sil.org",' .
                '"id":1,"password":"testPass1"}',
        );
        $msg = " *** Mismatched data results for user data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testGetAllRecordsByDataKey_DirectoryUsersAliasPrimaryEmail()
    {
        $newSql =  $this->loadData();
        $results = $newSql->getAllRecordsByDataKey('directory', 'users_alias',
                                        'primaryEmail', 'user_test1@sil.org');
        $expected =  array(
            array('id' => '2',
                  'type' => 'directory',
                  'class' => 'users_alias',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"alias":"users_alias2@sil.org"}',
            ),
            array('id' => '5',
                  'type' => 'directory',
                  'class' => 'users_alias',
                  'data' => '{"primaryEmail":"user_test1@sil.org",' .
                            '"alias":"users_alias5@sil.org"}',
            ),
        );
        $msg = " *** Mismatched data results for user data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testDeleteAllData()
    {
        $newSql =  $this->loadData();
        $newSql->deleteAllData();
        $results = $newSql->getData('', '');
        $expected = array();
        $msg = " *** Mismatched data results for missing data.";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testDeleteRecordById()
    {
        $newSql =  $this->loadData();
        $newSql->deleteRecordById(2);
        $results = $newSql->getData('', '');

        $expected = array(
            array('id' => '1',
                'type' => 'directory',
                'class' => 'user',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                          '"id":1,"password":"testPass1"}',
            ),
            array('id' => '3',
                'type' => 'app_engine',
                'class' => 'webapp',
                'data' => 'webapp3 test data',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => '{"primaryEmail":"user_test4@sil.org",' .
                          '"id":4,"password":"testPass4"}',
            ),
            array('id' => '5',
                'type' => 'directory',
                'class' => 'users_alias',
                'data' => '{"primaryEmail":"user_test1@sil.org",' .
                          '"alias":"users_alias5@sil.org"}',
            ),
        );
        $msg = " *** Mismatched data results for remaining data.";
        $this->assertEquals($expected, $results, $msg);
    }

} 