<?php
namespace SilMock\Google\Service\Directory;

use SilMock\DataStore\Sqlite\SqliteUtils;
class UsersResource {

    private $_dbFile;  // path (with file name) for the Sqlite database
    private $_dataType = 'directory'; // string to put in the 'type' field in the database
    private $_dataClass = 'user'; // string to put in the 'class' field in the database

    public function __construct($dbFile=null)
    {
        $this->_dbFile = $dbFile;
    }

    /**
     * Deletes a user (users.delete)
     *
     * @param string $userKey - The Email or immutable Id of the user
     * @return null|true depending on if the user was found.
     */
    public function delete($userKey)
    {
        $userEntry = $this->getDbUser($userKey);

        if ($userEntry === null) {
            return null;
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->deleteRecordById($userEntry['id']);
        return true;
    }

    /**
     * Retrieves a user (users.get) and sets its aliases property
     *     based on its aliases found in the database.
     *
     * @param string $userKey - The Email or immutable Id of the user
     * @return null|a real Google_Service_Directory_User instance
     */
    public function get($userKey)
    {
        $newUser = null;
        $userEntry = $this->getDbUser($userKey);

        if ($userEntry === null) {
            return null;
        }

        $newUser = new \Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, json_decode($userEntry['data'], true));

        // if the $userKey is not an email address, then it's an id
        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        // find its aliases in the database and populate its aliases property

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
     * Creates a user (users.insert) and sets its aliases property if any
     *     are given.
     *
     * @param Google_User $postBody
     * @return null|a real Google_Service_Directory_User instance
     * @throws \Exception with code 201407101120, if the user already exists
     */
    public function insert($postBody)
    {
        $defaults = array(
            'id' => intval(str_replace(array(' ','.'),'',microtime())),
            'suspended' => false,
            'changePasswordAtNextLogin' => false,
            'isAdmin' => false,
            'isDelegatedAdmin' => false,
            'lastLoginTime' => time(),
            'creationTime' => time(),
        );

        // array_merge will not work, since $postBody is an object which only
        // implements ArrayAccess
        foreach ($defaults as $key=>$value) {
            if (!isset($postBody[$key])) {
                $postBody[$key] = $value;
            }
        }

        $currentUser = $this->get($postBody->primaryEmail);

        if ($currentUser) {
            throw new \Exception("Account already exists: " .
                $postBody['primaryEmail'],
                201407101120);
        }

        $newUser = new \Google_Service_Directory_User();
        ObjectUtils::initialize($newUser, $postBody);
        $userData = json_encode($newUser);

        // record the user in the database
        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->recordData($this->_dataType, $this->_dataClass, $userData);

        // record the user's aliases in the database
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

        // Get (and return) the new user that was just created back out of the database
        return $this->get($postBody->primaryEmail);
    }

    /**
     * Updates a user (users.update) in the database as well as its aliases
     *
     * @param string $userKey - The Email or immutable Id of the user.
     * @param Google_User $postBody
     * @return  null|a real Google_Service_Directory_User instance
     * @throws \Exception with code 201407101130 if a matching user is not found
     */
    public function update($userKey, $postBody)
    {

        $userEntry = $this->getDbUser($userKey);
        if ($userEntry === null) {
            throw new \Exception("Account doesn't exist: " . $userKey,
                201407101130);
        }

        /*
         * only keep the non-null properties of the $postBody user,
         * except for suspensionReason.
         */

        $dbUserProps = json_decode($userEntry['data'], true);
        $newUserProps = get_object_vars($postBody);

        foreach ($newUserProps as $key => $value) {
            if ($value !== null || $key === "suspensionReason") {
                $dbUserProps[$key] = $value;
            }
        }

        // Delete the user's old aliases before adding the new ones
        $usersAliases = new UsersAliasesResource($this->_dbFile);
        $aliasesObject = $usersAliases->listUsersAliases($userKey);

        if ($aliasesObject && isset($aliasesObject['aliases'])) {
            foreach ($aliasesObject['aliases'] as $nextAliasObject) {
                $usersAliases->delete($userKey, $nextAliasObject['alias']);
            }
        }

        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $sqliteUtils->updateRecordById($userEntry['id'], json_encode($dbUserProps));

        // Save the user's aliases
        if (isset($postBody->aliases) && $postBody->aliases) {

            foreach($postBody->aliases as $alias) {
                $newAlias = new \Google_Service_Directory_Alias();
                $newAlias->alias = $alias;
                $newAlias->kind = "personal";
                $newAlias->primaryEmail = $postBody->primaryEmail;

                $insertedAlias = $usersAliases->insertAssumingUserExists($newAlias);
            }
        }

        return $this->get($userKey);

    }

    /**
     * Retrieves a user record from the database (users.delete)
     *
     * @param string $userKey - The Email or immutable Id of the user
     * @return null|nested array for the matching database entry
     */
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

    /**
     * This mocks the Google_Service_Directory_Users_Resource's listUser
     * functionality.
     *
     * @param array $parameters -- This will have three keys.
     *     domain: The domain to limit the search to. It's ignored.
     *     maxResults: Used to limit the number of results.
     *                 It defaults to 100.
     *     query: A string of the form "foo:baz[*]".
     *            Where foo is a field to search on.
     *            And baz is what to partially match on.
     *            The '*' syntax is ignored.
     *
     * @return \Google_Service_Directory_Users
     */
    public function listUsers($parameters = [])
    {
        $results = new \Google_Service_Directory_Users();
        if (!key_exists('domain',$parameters)) {
            $parameters['domain'] = 'ZZZZZZZ';
        }
        if (!key_exists('maxResults',$parameters)) {
            $parameters['maxResults'] = 100;
        }
        if (!key_exists('query',$parameters)) {
            $parameters['query'] = '';
        }
        $sqliteUtils = new SqliteUtils($this->_dbFile);
        $allData = $sqliteUtils->getData($this->_dataType,$this->_dataClass);
        foreach ($allData as $userRecord) {
            $userEntry = json_decode($userRecord['data'],true);
            if ($this->doesUserMatch($userEntry,$parameters['query'])) {
                $allResultsUsers = $results->getUsers();
                $allResultsUsers[] = $userEntry;
                $results->setUsers($allResultsUsers);
            }
            if (count($results->getUsers())>= $parameters['maxResults']) {
                break;
            }
        }
        return $results;
    }

    public function doesUserMatch($entry,$query ='')
    {
        if ($query==='') {
            return true;
        }
        $query = str_replace('*','',$query);
        ($field,$value) = explode(':',$query);
        if (mb_strpos($entry[$field],$value)!==false) {
            return true;
        } else {
            return false;
        }
    }
} 