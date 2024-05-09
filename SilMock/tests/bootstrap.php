<?php

//include_once('AutoLoader.php');
// Register the directory to your include files
//AutoLoader::registerDirectory(__DIR__ . '/../../SilMock');

const DATAFILE1 = __DIR__ . '/../DataStore/Sqlite/Test1_Google_Service_Data.db';
const DATAFILE2 = __DIR__ . '/../DataStore/Sqlite/Test2_Google_Service_Data.db';
const DATAFILE3 = __DIR__ . '/../DataStore/Sqlite/Test3_Google_Service_Data.db';
const DATAFILE4 = __DIR__ . '/../DataStore/Sqlite/Test4_Google_Service_Data.db';
const DATAFILE5 = __DIR__ . '/../DataStore/Sqlite/Test5_Google_Service_Data.db';

if (!file_exists(DATAFILE1)) {
    touch(DATAFILE1);
}

if (!file_exists(DATAFILE2)) {
    touch(DATAFILE2);
}

if (!file_exists(DATAFILE3)) {
    touch(DATAFILE3);
}

if (!file_exists(DATAFILE4)) {
    touch(DATAFILE4);
}

if (!file_exists(DATAFILE5)) {
    touch(DATAFILE5);
}
