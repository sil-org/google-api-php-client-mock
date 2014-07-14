<?php
namespace SilMock\Google\Service\Directory;

use SilMock\DataStore\Sqlite\SqliteUtils;

class UsersAliasesResource {

    private $_dbFile;  // path for the Sqlite database
    private $_dataType = 'directory';
    private $_dataClass = 'users_alias';


    public function __construct($dbFile=null)
    {
        $this->_dbFile = $dbFile;
    }

    /**
     * Remove a alias for the user (aliases.delete)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param string $alias
     * The alias to be removed
     * @param array $optParams Optional parameters.
     * @throws \Exception with code 201407101645
     */
    public function delete($userKey, $alias, $optParams = array())
    {
        // TODO: Consider doing something with $params
        $params = array('userKey' => $userKey, 'alias' => $alias);
        $params = array_merge($params, $optParams);

        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        // ensure that user exists in db
        $dir = new \SilMock\Google\Service\Directory('anything', $this->_dbFile);
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407101645);
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $aliases =  $sqliteUtils->getAllRecordsByDataKey($this->_dataType,
            $this->_dataClass, $key, $userKey);

        if ( ! $aliases) {
            return null;
        }

        foreach ($aliases as $nextAlias) {
            $aliasData = json_decode($nextAlias['data'], true);
            if ($aliasData['alias'] === $alias) {
                $sqliteUtils->deleteRecordById(intval($nextAlias['id']));
                return true;
            }
        }

        return null;
    }

    /**
     * Add a alias for the user (aliases.insert)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param Alias $postBody
     * @param array $optParams Optional parameters.
     * @return Alias instance
     * @throws \Exception with code 201407110830
     */
    public function insert($userKey, $postBody, $optParams = array())
    {
        // TODO: Consider doing something with $params
        $params = array('userKey' => $userKey, 'postBody' => $postBody);
        $params = array_merge($params, $optParams);

        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        // ensure that user exists in db
        $dir = new \SilMock\Google\Service\Directory('anything', $this->_dbFile);
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407110830);
        }

        if ($postBody->$key === null) {
            $postBody->$key = $userKey;
        }

        return $this->insertAssumingUserExists($postBody);
    }

    public function insertAssumingUserExists($postBody)
    {
        $entryData = json_encode($postBody);
        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->recordData($this->_dataType, $this->_dataClass,
            $entryData, true);
        $allAliases = $sqliteUtils->getData($this->_dataType, $this->_dataClass);

        if ( ! $allAliases) {
            return null;
        }
        $newEntry = end(array_values($allAliases));

        $newAlias = new Alias();
        $newAlias->initialize(json_decode($newEntry['data'], true));

        return $newAlias;
    }

    /**
     * List all aliases for a user (aliases.list)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_Aliases
     * @throws \Exception with code 201407101420
     */
    public function listUsersAliases($userKey, $optParams = array())
    {
        // TODO: Consider doing something with $params
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);

        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }
        // ensure that user exists in db
        $dir = new \SilMock\Google\Service\Directory('anything', $this->_dbFile);
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407101420);
        }

        $foundAliases =  $this->fetchAliasesByUser($key, $userKey);

        return $foundAliases;
    }

    public function fetchAliasesByUser($keyType, $userKey) {
        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $aliases =  $sqliteUtils->getAllRecordsByDataKey($this->_dataType,
            $this->_dataClass, $keyType, $userKey);

        if ( ! $aliases) {
            return null;
        }

        $foundAliases = new Aliases();

        foreach ($aliases as $nextAlias) {
            $newAlias = new Alias();
            $newAlias->initialize(json_decode($nextAlias['data'], true));
            $foundAliases->aliases[] = $newAlias;
        }
        $foundAliases->refreshAliases();

        return $foundAliases;
    }
}
