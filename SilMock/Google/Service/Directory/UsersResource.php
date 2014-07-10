<?php
namespace SilMock\Google\Service\Directory;

use SilMock\DataStore\Sqlite\SqliteUtils;
class UsersResource {

    private $_dataType = 'directory';
    private $_dataClass = 'user';

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

        $sqliteUtils = new SqliteUtils();
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

        $userData = json_encode($postBody);

        $currentUser = $this->get($postBody->primaryEmail);

        if ($currentUser) {
            throw new \Exception("Account already exists: " .
                $postBody['primaryEmail'],
                201407101120);
        }

        $sqliteUtils = new SqliteUtils();
        $sqliteUtils->recordData($this->_dataType, $this->_dataClass, $userData);

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
        $newUserProps = json_decode(json_encode($postBody), true);

        foreach ($newUserProps as $key => $value) {
            if ($value !== null || $key === "suspensionReason") {
                $dbUserProps[$key] = $value;
            }
        }

        $sqliteUtils = new SqliteUtils();
        $sqliteUtils->updateRecordById($userEntry['id'], json_encode($dbUserProps));
        return $this->get($userKey);

    }


    private function getDbUser($userKey)
    {

        $key = 'primaryEmail';
        if ( ! filter_var($userKey, FILTER_VALIDATE_EMAIL)) {
            $key = 'id';
            $userKey = intval($userKey);
        }

        $sqliteUtils = new SqliteUtils();
        return $sqliteUtils->getRecordByDataKey($this->_dataType,
                               $this->_dataClass, $key, $userKey);
    }

} 