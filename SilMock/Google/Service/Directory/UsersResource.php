<?php
namespace SilMock\Google\Service\Directory;

use SilMock\DataStore\Sqlite\SqliteUtils;
class UsersResource {

    private $_dbFile;  // path for the Sqlite database
    private $_dataType = 'directory';
    private $_dataClass = 'user';

    public function __construct($dbFile=null)
    {
        $this->_dbFile = $dbFile;
    }
    /**
     * Delete user (users.delete)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param array $optParams Optional parameters.
     */
    public function delete($userKey, $optParams = array())
    {
        //TODO: consider doing something with the $params
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);

        $userEntry = $this->getDbUser($userKey);

        if ($userEntry === null) {
            return null;
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->deleteRecordById($userEntry['id']);
        return true;
    }

    /**
     * retrieve user (users.get)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_User
     */
    public function get($userKey, $optParams = array())
    {
        //TODO: consider doing something with the $params
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);

        $newUser = null;
        $userEntry = $this->getDbUser($userKey);

        if ($userEntry === null) {
            return null;
        }

        $newUser = new User();
        $newUser->initialize(json_decode($userEntry['data'], true));

        // populate aliases
        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        $usersAliases = new UsersAliasesResource($this->_dbFile);
        $aliases =  $usersAliases->fetchAliasesByUser($key, $userKey);
        if ( $aliases) {
            $foundAliases = array();

            foreach ($aliases['aliases'] as $nextAlias) {
                $foundAliases[] = $nextAlias['alias'];
            }

            $newUser->aliases = $foundAliases;
        }

        return $newUser;
    }

    /**
     * create user. (users.insert)
     *
     * @param Google_User $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_User
     * @throws \Exception with code 201407101120
     */
    public function insert($postBody, $optParams = array())
    {
        //TODO: consider doing something with the $params
        $params = array('postBody' => $postBody);
        $params = array_merge($params, $optParams);

        $currentUser = $this->get($postBody->primaryEmail);

        if ($currentUser) {
            throw new \Exception("Account already exists: " .
                $postBody['primaryEmail'],
                201407101120);
        }

        $newUser = new User();
        $newUser->initialize($postBody);
        $userData = $newUser->encode2json();

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->recordData($this->_dataType, $this->_dataClass, $userData);

        if ($postBody->aliases) {
            $usersAliases = new UsersAliasesResource($this->_dbFile);

            foreach($postBody->aliases as $alias) {
                $newAlias = new Alias();
                $newAlias->alias = $alias;
                $newAlias->kind = "personal";
                $newAlias->primaryEmail = $postBody->primaryEmail;

                $insertedAlias = $usersAliases->insertAssumingUserExists($newAlias);
            }
        }
        return $this->get($postBody->primaryEmail);
    }

    /**
     * update user (users.update)
     *
     * @param string $userKey
     * Email or immutable Id of the user. If Id, it should match with id of user object
     * @param Google_User $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_User
     * @throws \Exception with code 201407101130
     */
    public function update($userKey, $postBody, $optParams = array())
    {
        //TODO: consider doing something with the $params
        $params = array('userKey' => $userKey, 'postBody' => $postBody);
        $params = array_merge($params, $optParams);

        $userEntry = $this->getDbUser($userKey);
        if ($userEntry === null) {
            throw new \Exception("Account doesn't exist: " . $userKey,
                201407101130);
        }

        /*
         * only keep the non-null properties of the $postBody user
         */

        $dbUserProps = json_decode($userEntry['data'], true);

        $newUser = $postBody;
        $newUserProps = json_decode($newUser->encode2json(), true);

        foreach ($newUserProps as $key => $value) {
            if ($value !== null || $key === "suspensionReason") {
                $dbUserProps[$key] = $value;
            }
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->updateRecordById($userEntry['id'], json_encode($dbUserProps));

        if (isset($postBody->aliases) && $postBody->aliases) {
            $usersAliases = new UsersAliasesResource($this->_dbFile);

            foreach($postBody->aliases as $alias) {
                $newAlias = new Alias();
                $newAlias->alias = $alias;
                $newAlias->kind = "personal";
                $newAlias->primaryEmail = $postBody->primaryEmail;

                $insertedAlias = $usersAliases->insertAssumingUserExists($newAlias);
            }
        }

        return $this->get($userKey);

    }


    private function getDbUser($userKey)
    {

        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        return $sqliteUtils->getRecordByDataKey($this->_dataType,
                               $this->_dataClass, $key, $userKey);
    }

} 