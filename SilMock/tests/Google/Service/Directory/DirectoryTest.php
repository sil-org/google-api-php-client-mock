<?php

//require_once(dirname(__FILE__).'/../../../../Google/Service/Directory.php');

use SilMock\Google\Service\Directory;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
    public $dataFile = '../DataStore/Sqlite/Google_Services_Data.db';

    public function testBlank()
    {
        file_put_contents($this->dataFile, '');
        $newDir = new Directory(null);

        $msg = " *** How did that happen?";
        $this->assertTrue(true, $msg);
    }

} 