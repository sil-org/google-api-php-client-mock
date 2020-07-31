<?php
namespace SilMock\DataStore\Sqlite;

use PDO;

class SqliteUtils
{

    /**
     * The PDO connection to the database (or null if unititialized).
     * @var PDO|null
     */
    private $_db = null;

    /**
     * The SQLite database file (path with file name).
     * @var string
     */
    private $_dbFile;

    private $_dbTable = 'google_service';

    /**
     *
     * If needed, this creates the sqlite database and/or its structure
     * @param string $dbFile path and filename of the database for Mock Google
     */
    public function __construct($dbFile = null)
    {
        // default database path
        $this->_dbFile = __DIR__ . '/Google_Service_Data.db';

        // if database path given, use it instead
        if ($dbFile) {
            $this->_dbFile = $dbFile;
        }

        $this->createDbStructureAsNecessary();
    }

    /**
     * A utility function to get the Google Mock data out of the data
     *     file using json_decode for a particular Google class
     *
     * @param string $dataType, the name of a Google mock service (e.g. 'directory')
     * @param string $dataClass, the name of a Google mock class (e.g. 'users_alias')
     * @return null if exception or if no data for that class name,
     *        otherwise the json_decode version of the Google mock data.
     *        If $dataType and $dataClass are not strings, returns everything
     *        from the data table.
     */
    public function getData($dataType, $dataClass)
    {
        if (! file_exists($this->_dbFile)) {
            return null;
        }

        $whereClause = '';
        $whereArray = array();

        if (is_string($dataType) && $dataType) {
            $whereClause = " WHERE type = :type";
            $whereArray[':type'] = $dataType;

            if (is_string($dataClass) && $dataClass) {
                $whereClause .= " AND class = :class";
                $whereArray[':class'] = $dataClass;
            }
        }

        if (! $whereClause) {
            return $this->runSql(
                "SELECT * FROM " . $this->_dbTable,
                array(),
                false,
                true
            );
        }

        return $this->runSql(
            "SELECT * FROM " . $this->_dbTable  . $whereClause,
            $whereArray,
            false,
            true
        );
    }

    /**
     * Finds and returns the first record in the database that matches the input values.
     *
     * @param $dataType string (e.g. "directory")
     * @param $dataClass string (e.g. "user")
     * @param $dataKey string|int  (e.g. "primaryEmail" or "id")
     * @param $dataValue string
     * @return array|null -- array for the matching database entry, null otherwise
     */
    public function getRecordByDataKey($dataType, $dataClass, $dataKey, $dataValue)
    {
        $allOfClass = $this->getData($dataType, $dataClass);

        foreach ($allOfClass as $nextEntry) {
            $nextData = json_decode($nextEntry['data'], true);
            if (isset($nextData[$dataKey]) &&
                $nextData[$dataKey] === $dataValue) {
                return $nextEntry;
            }
        }

        return null;
    }

    /**
     * Finds and returns all records in the database that matches the input values.
     *
     * @param $dataType string (e.g. "directory")
     * @param $dataClass string (e.g. "user")
     * @param $dataKey string|int  (e.g. "primaryEmail" or "id")
     * @param $dataValue string
     * @return array -- an array for the matching database entry
     */
    public function getAllRecordsByDataKey($dataType, $dataClass, $dataKey, $dataValue)
    {
        $allOfClass = $this->getData($dataType, $dataClass);

        $foundEntries = array();

        foreach ($allOfClass as $nextEntry) {
            $nextData = json_decode($nextEntry['data'], true);
            if (isset($nextData[$dataKey]) &&
                $nextData[$dataKey] === $dataValue) {
                $foundEntries[] = $nextEntry;
            }
        }

        return $foundEntries;
    }


    /**
     * Deletes the database record whose "id" field matches the input value
     * @param $recordId int
     */
    public function deleteRecordById($recordId)
    {
        $this->runSql(
            "DELETE FROM " .  $this->_dbTable . " WHERE id = :id",
            [':id' => $recordId],
            true
        );
    }

    /**
     * A utility function to delete the Google Mock data based on a
     * particular data type and data class for a specific email.
     *
     * @param string $dataType, the name of a Google mock service (e.g. 'directory')
     * @param string $dataClass, the name of a Google mock class (e.g. 'users_alias')
     *        If $dataType and $dataClass are not strings, nothing is deleted.
     * @param string $emailAddress -- the primary email address.
     * @return null -- If $dataKey user doesn't exist, just returns.
     */
    public function deleteDataByEmail(string $dataType, string $dataClass, string $emailAddress)
    {
        if (! file_exists($this->_dbFile)) {
            return null;
        }
        if (empty($dataType)) {
            return null;
        }
        if (empty($dataClass)) {
            return null;
        }
        if (empty($emailAddress)) {
            return null;
        }

        $matchingRecords = $this->getAllRecordsByDataKey($dataType, $dataClass, 'primaryEmail', $emailAddress);
        foreach ($matchingRecords as $matchingRecord) {
            $id = $matchingRecord['id'];
            $this->deleteRecordById($id);
        }
        return null;
    }

    /**
     * Updates the "data" field of the database record whose id field matches
     *     the input value
     * @param $recordId int
     * @param $newData string
     * @return null
     */
    public function updateRecordById($recordId, $newData)
    {
        $this->runSql(
            "UPDATE " .  $this->_dbTable . " SET data = :data WHERE id = :id",
            [':id' => $recordId, ':data' => $newData],
            true
        );
    }

    /**
     * Deletes all records from the database table
     * @return null
     */
    public function deleteAllData()
    {
        return $this->runSql(
            "DELETE FROM " . $this->_dbTable . " WHERE id > -1"
        );
    }

    /**
     * Adds a record of data
     *
     * @param string $dataType The type of data e.g. "directory".
     * @param string $dataClass The class of data e.g. "user".
     * @param string $data The data itself )in json format).
     * @returns true if no errors/exceptions
     * @throws \Exception
     */
    public function recordData($dataType, $dataClass, $data)
    {
        if (!is_string($dataType) || ($dataType == '')) {
            throw new \Exception("No data type given when trying to record " .
                "data.");
        }
        if (!is_string($dataClass) || ($dataClass == '')) {
            throw new \Exception("No data class given when trying to record " .
                "data (data type: " . $dataType . ").");
        }

        // Add the record.
        $this->runSql(
            'INSERT INTO ' . $this->_dbTable . ' (type, class, data)' .
            ' VALUES (:type, :class, :data)',
            [':type' => $dataType, ':class' => $dataClass, ':data' => $data],
            true
        );

        return true;
    }

    /**
     *  If the database file does not exist, creates it with an empty string
     *  with 0644 permissions.
     * @returns null
     */
    public function createDbIfNotExists()
    {
        if (! file_exists($this->_dbFile)) {
            file_put_contents($this->_dbFile, '');
            chmod($this->_dbFile, 0644);
        }
    }

    /**
     *  Database has one table with an id (int PK) column and three TEXT columns ...
     *    type (e.g. "directory"),
     *    class_name (e.g. "user"),
     *    data (json dump)
     * @returns null
     */
    public function createDbStructureAsNecessary()
    {
        // Make sure the database file exists.
        $this->createDbIfNotExists();

        $this->runSql(
            "CREATE TABLE IF NOT EXISTS " . $this->_dbTable . " (" .
            "id INTEGER PRIMARY KEY, " .
            "type TEXT, " .        // e.g. "directory"
            "class TEXT, " .    // e.g. "user"
            "data TEXT" .          // json
            ");"
        );
    }

    /**
     * Run the given SQL statement as a PDO prepared statement, using the given
     * array of data.
     *
     * @param string $sql The SQL statement. Example: "SELECT * FROM table WHERE
     * id = :id"
     * @param array $data (Optional:) An associative array where the keys are
     * the placeholders in the SQL statement. Example: array(':id' => 1).
     * Defaults to an empty array (for when there are no placeholders in the
     * SQL statement).
     * @param bool $confirmAffectedRows (Optional:) Whether to throw an
     * exception if PDOStatement::rowCount() indicates no rows were
     * affected. Defaults to false.
     * @param bool $returnData (Optional:) Whether to retrieve (and return) the
     * resulting data by a call to PDOStatement::fetchAll($pdoFetchType).
     * Defaults to false.
     * @param int $pdoFetchType (Optional:) The PDO FETCH_* constant defining
     * the desired return array configuration. See
     * http://www.php.net/manual/en/pdo.constants.php for options. Defaults
     * to PDO::FETCH_ASSOC.
     * @return array|null The array of returned results (if requested) as an
     * associative array, otherwise null.
     * @throws \Exception
     */
    protected function runSql(
        $sql,
        $data = array(),
        $confirmAffectedRows = false,
        $returnData = false,
        $pdoFetchType = PDO::FETCH_ASSOC
    ) {
        // Make sure we're connected to the database.
        $this->setupDbConnIfNeeded();

        // Update the record in the database.
        $stmt = $this->_db->prepare($sql);

        // Execute the prepared update statement with the desired data.
        $stmtSuccess = $stmt->execute($data);

        // If the statement was NOT successful...
        if ($stmtSuccess === false) {
            // Indicate failure.
            throw new \Exception('SQL statement failed: ' . $sql);

            // If told to confirm that rows were affected
            // AND
            // if the statement didn't affect any rows...
        } elseif ($confirmAffectedRows && ($stmt->rowCount() < 1)) {
            // Indicate failure.
            throw new \Exception('SQL statement affected no rows: ' . $sql);
        }

        // If told to return data, do so.
        if ($returnData) {
            return $stmt->fetchAll($pdoFetchType);
        } else {
            return null;
        }
    }


    protected function setupDbConnIfNeeded()
    {
        // If we have not yet setup the database connection...
        if (is_null($this->_db)) {
            // Make sure the database itself exists.
            $this->createDbIfNotExists();

            // Connect to the SQLite database file.
            $this->_db = new PDO('sqlite:' . $this->_dbFile);

            // Set errormode to exceptions.
            $this->_db->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        }
    }
}
