<?php

namespace Google\Service\Directory;


class Users_Resource {

    /**
     * Delete user (users.delete)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param array $optParams Optional parameters.
     */
    public function delete($userKey, $optParams = array())
    {
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('delete', array($params));
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
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('get', array($params), "Google_Service_Directory_User");
    }
    /**
     * create user. (users.insert)
     *
     * @param Google_User $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_User
     */
    public function insert(Google_Service_Directory_User $postBody, $optParams = array())
    {
        $params = array('postBody' => $postBody);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('insert', array($params), "Google_Service_Directory_User");
    }

    /**
     * update user (users.update)
     *
     * @param string $userKey
     * Email or immutable Id of the user. If Id, it should match with id of user object
     * @param Google_User $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_User
     */
    public function update($userKey, Google_Service_Directory_User $postBody, $optParams = array())
    {
        $params = array('userKey' => $userKey, 'postBody' => $postBody);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('update', array($params), "Google_Service_Directory_User");
    }

} 