<?php
namespace SilMock\Google\Service\Directory;


class UsersAliases_Resource {


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
        $params = array('userKey' => $userKey, 'alias' => $alias);
        $params = array_merge($params, $optParams);
        //TODO: finish this

//        return $this->call('delete', array($params));
    }

    /**
     * Add a alias for the user (aliases.insert)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param Google_Alias $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_Alias
     */
    public function insert($userKey, Google_Service_Directory_Alias $postBody, $optParams = array())
    {
        $params = array('userKey' => $userKey, 'postBody' => $postBody);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('insert', array($params), "Google_Service_Directory_Alias");
    }

    /**
     * List all aliases for a user (aliases.list)
     *
     * @param string $userKey
     * Email or immutable Id of the user
     * @param array $optParams Optional parameters.
     * @return Google_Service_Directory_Aliases
     */
    public function listUsersAliases($userKey, $optParams = array())
    {
        $params = array('userKey' => $userKey);
        $params = array_merge($params, $optParams);
        //TODO: finish this
//        return $this->call('list', array($params), "Google_Service_Directory_Aliases");
    }
} 