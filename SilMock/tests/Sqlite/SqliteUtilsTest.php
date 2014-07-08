<?php

use SilMock\DataStore\Sqlite\SqliteUtils;

class SqliteUtilsTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = '../DataStore/Sqlite/Google_Services_Data.db';

    public function testRecordData()
    {
        file_put_contents($this->dataFile, '');
        $newSql = new SqliteUtils();
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->recordData('directory', 'user',
                       'test data');
        $msg = " *** Expected to add data successfully.";
        $this->assertTrue($results, $msg);
    }

    public function loadData()
    {
        file_put_contents($this->dataFile, '');
        $newSql = new SqliteUtils();
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->recordData('directory', 'user',
            'user1 test data');
        $results = $newSql->recordData('directory', 'useralias',
            'useralias2 test data');
        $results = $newSql->recordData('app_engine', 'webapp',
            'webapp3 test data');
        $results = $newSql->recordData('directory', 'user',
            'user4 test data');

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
                  'data' => 'user1 test data',
                 ),
            array('id' => '2',
                  'type' => 'directory',
                  'class' => 'useralias',
                  'data' => 'useralias2 test data',
            ),
            array('id' => '3',
                  'type' => 'app_engine',
                  'class' => 'webapp',
                  'data' => 'webapp3 test data',
            ),
            array('id' => '4',
                  'type' => 'directory',
                  'class' => 'user',
                  'data' => 'user4 test data',
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
                'data' => 'user1 test data',
            ),
            array('id' => '2',
                'type' => 'directory',
                'class' => 'useralias',
                'data' => 'useralias2 test data',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => 'user4 test data',
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
                'data' => 'user1 test data',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => 'user4 test data',
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
        $newSql = new SqliteUtils();
        $newSql->createDbStructureAsNecessary();

        $results = $newSql->getData('', '');
        $expected = array();
        $msg = " *** Expected no data but got something.";
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
                'data' => 'user1 test data',
            ),
            array('id' => '3',
                'type' => 'app_engine',
                'class' => 'webapp',
                'data' => 'webapp3 test data',
            ),
            array('id' => '4',
                'type' => 'directory',
                'class' => 'user',
                'data' => 'user4 test data',
            ),
        );
        $msg = " *** Mismatched data results for remaining data.";
        $this->assertEquals($expected, $results, $msg);
    }

} 