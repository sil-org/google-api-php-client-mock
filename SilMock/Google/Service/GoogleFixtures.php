<?php
namespace SilMock\Google\Service;

use SilMock\DataStore\Sqlite\SqliteUtils;

class GoogleFixtures {

    /**
     * @param array of arrays $fixtures ...
     *    Each array should have 3 string elements ...
     *    - the type of Google Service (e.g. "directory")
     *    - the class (e.g. "user")
     *    - the corresponding json formatted data
     */
    public function addFixtures($fixtures)
    {
        $newSqlite = new SqliteUtils();

        foreach ($fixtures as $nextFixture) {
            $type = $nextFixture[0];
            $class = $nextFixture[1];
            $data = $nextFixture[2];
            $newSqlite->recordData($type, $class, $data);
        }
    }

    /**
     *  Empties out the database table completely
     */
    public function removeAllFixtures()
    {
        $newSqlite = new SqliteUtils();
        $newSqlite->deleteAllData();
    }

}