<?php
namespace SilMock\Google\Service\Directory;

use SilMock\DataStore\Sqlite\SqliteUtils;

class UsersAliasesResource {

    private $_dataType = 'directory';
    private $_dataClass = 'users_alias';

    /**
     * Remove a alias for the user (aliases.delete)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param string $alias
     * The alias to be removed
     * @param array $optParams Optional parameters.
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
        $dir = new \SilMock\Google\Service\Directory('anything');
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407101645);
        }

        $sqliteUtils = new SqliteUtils();
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
        $dir = new \SilMock\Google\Service\Directory('anything');
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407110830);
        }

        if ($postBody->$key === null) {
            $postBody->$key = $userKey;
        }

        $entryData = json_encode($postBody);
        $sqliteUtils = new SqliteUtils();
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
        $dir = new \SilMock\Google\Service\Directory('anything');
        $matchingUsers = $dir->users->get($userKey);

        if ($matchingUsers === null) {
            throw new \Exception("Account doesn't exist: " . $userKey, 201407101420);
        }

        $sqliteUtils = new SqliteUtils();
        $aliases =  $sqliteUtils->getAllRecordsByDataKey($this->_dataType,
            $this->_dataClass, $key, $userKey);

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