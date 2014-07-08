<?php
namespace SilMock\DataStore\Sqlite;

class SqliteUtils {

    /**
     * The PDO connection to the database (or null if unititialized).
     * @var null|PDO
     */
	private $_db = null;

    /**
     * The SQLite database file.
     * @var string
     */
    private $_dbFile = '../DataStore/Sqlite/Google_Services_Data.db';

    private $_dbTable = 'google_service';

    /**
     * A utility function to get the GSD Mock data out of the data
     *     file using json_decode for a particular GSD class
     *
     * @param string $className, the name of a GSD mock class
     * @param string $dataFile, a path and file name
     * @return null if exception or if no GSD data for that class name,
     *        otherwise the json_decode version of the GSD data.
     */
    public function getData($dataType, $dataClass)
    {
        if (! file_exists( $this->_dbFile)) {
            return null;
        }

        $whereClause = '';
        $whereArray = array();

        if (is_string($dataType) && $dataType ) {
            $whereClause = " WHERE type = :type";
            $whereArray[':type'] = $dataType;

            if (is_string($dataClass) && $dataClass) {
                $whereClause .= " AND class = :class";
                $whereArray[':class'] = $dataClass;
            }
        }

        if ( ! $whereClause) {
            return $this-> runSql( "SELECT * FROM " . $this->_dbTable, array(),
                                  false, true);
        }

        return $this->runSql(
            "SELECT * FROM " . $this->_dbTable  . $whereClause,
            $whereArray, false, true);
    }


    /**
     * Add a record of data
     *
     * @param string $dataType The type of data e.g. "directory".
     * @param string $dataClass The class of data e.g. "user".
     * @param string $data The data itself )in json format).
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
        $this->runSql('INSERT INTO ' . $this->_dbTable . ' (' .
            'type, class, data' .
            ') VALUES (' .
            ':type, :class, :data' .
            ')',
            array(
                ':type' => $dataType,
                ':class' => $dataClass,
                ':data' => $data,
            ),
            true);

        return true;
    }

    /**
     *  If the database file does not exist, creates it with an empty string
     *  with 0644 permissions.
     */
    public function createDbIfNotExists()
    {
        if ( ! file_exists($this->_dbFile)){
            file_put_contents($this->_dbFile, '');
            chmod($this->_dbFile, 0644);
        }
    }

    /**
     *  Database has one table with an id (int PK) column and three TEXT columns ...
     *    type (e.g. "directory"),
     *    class_name (e.g. "user"),
     *    data (json dump)
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
     * to \PDO::FETCH_ASSOC.
     * @return array|null The array of returned results (if requested) as an
     * associative array, otherwise null.
     * @throws \Exception
     */
    protected function runSql(
                                $sql,
                                $data = array(),
                                $confirmAffectedRows = false,
                                $returnData = false,
                                $pdoFetchType = \PDO::FETCH_ASSOC
    ) {
        // Make sure we're connected to the database.
        $this->setupDbConnIfNeeded();

        // Update the record in the database.
        $stmt = $this->_db->prepare($sql);

        // Execute the prepared update statement with the desired data.
        $stmtSuccess = $stmt->execute($data);

        // If the statement was NOT successful...
        if ($stmtSuccess === FALSE) {

            // Indicate failure.
            throw new \Exception('SQL statement failed: ' . $sql);
        }

        // If told to confirm that rows were affected
        // AND
        // if the statement didn't affect any rows...
        elseif ($confirmAffectedRows && ($stmt->rowCount() < 1)) {

            // Indicate failure.
            throw new \Exception('SQL statement affected no rows: ' . $sql);
        }

        // If told to return data, do so.
        if ($returnData) {
            return $stmt->fetchAll($pdoFetchType);
        }
        else {
            return null;
        }
    }


    protected function setupDbConnIfNeeded() {

        // If we have not yet setup the database connection...
        if (is_null($this->_db)) {

            // Make sure the database itself exists.
            $this->createDbIfNotExists();

            // Connect to the SQLite database file.
            $this->_db = new \PDO('sqlite:' . $this->_dbFile);

            // Set errormode to exceptions.
            $this->_db->setAttribute(\PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION);
        }
    }

} 