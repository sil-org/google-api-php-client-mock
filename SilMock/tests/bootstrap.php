<?php
//include_once('AutoLoader.php');
// Register the directory to your include files
//AutoLoader::registerDirectory(__DIR__ . '/../../SilMock');

define('DATAFILE1', __DIR__.'/../DataStore/Sqlite/Test1_Google_Service_Data.db');
define('DATAFILE2', __DIR__.'/../DataStore/Sqlite/Test2_Google_Service_Data.db');
define('DATAFILE3', __DIR__.'/../DataStore/Sqlite/Test3_Google_Service_Data.db');

if(!file_exists(DATAFILE1)){
    touch(DATAFILE1);
}

if(!file_exists(DATAFILE2)){
    touch(DATAFILE2);
}

if(!file_exists(DATAFILE3)){
    touch(DATAFILE3);
}