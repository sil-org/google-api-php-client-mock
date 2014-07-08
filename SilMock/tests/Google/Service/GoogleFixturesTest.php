<?php

//require_once(dirname(__FILE__).'/../../../../Google/Service/Directory.php');

use SilMock\Google\Service\GoogleFixtures;
use SilMock\DataStore\Sqlite\SqliteUtils;

class GoogleFixturesTest extends PHPUnit_Framework_TestCase
{

    public function testAddFixtures()
    {
        $sqliteClass = new SqliteUtils();
        $sqliteClass->deleteAllData();

        $fixturesClass = new GoogleFixtures();

        $fixtures = array(
           array('directory', 'user', 'user1 test data'),
           array('directory', 'useralias', 'useralias2 test data'),
           array('app_engine', 'webapp', 'webapp3 test data'),
           array('directory', 'user', 'user4 test data'),
        );

        $fixturesClass->addFixtures($fixtures);

        $expected = array(
            array('id' => 1,
                  'type' => 'directory',
                  'class' => 'user',
                  'data' => 'user1 test data',
                 ),
            array('id' => 2,
                'type' => 'directory',
                'class' => 'useralias',
                'data' => 'useralias2 test data',
            ),
            array('id' => 3,
                'type' => 'app_engine',
                'class' => 'webapp',
                'data' => 'webapp3 test data',
            ),
            array('id' => 4,
                'type' => 'directory',
                'class' => 'user',
                'data' => 'user4 test data',
            ),
        );
        $results = $sqliteClass->getData('', '');

        $msg = " *** Mismatching fixtures arrays";
        $this->assertEquals($expected, $results, $msg);
    }

    public function testRemoveAllFixtures()
    {
        $fixturesClass = new GoogleFixtures();

        $fixtures = array(
            array('directory', 'user', 'user1 test data'),
        );

        $fixturesClass->addFixtures($fixtures);
        $fixturesClass->removeAllFixtures();

        $sqliteClass = new SqliteUtils();
        $results = $sqliteClass->getData('','');
        $expected = array();

        $msg = " *** Mismatching fixtures arrays";
        $this->assertEquals($expected, $results, $msg);
    }

} 