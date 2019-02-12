<?php

namespace SilMock\Google\Service\Gmail\Resource;

use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\Directory\ObjectUtils;
use Webmozart\Assert\Assert;

class UsersSettingsDelegates
{
    /** @var string - The path (with file name) to the SQLite database. */
    private $dbFile;
    
    /** @var string - The 'type' field to use in the database. */
    private $dataType = 'gmail';
    
    /** @var string - The 'class' field to use in the database */
    private $dataClass = 'users_settings_delegate';
    
    public function __construct($dbFile = null)
    {
        $this->dbFile = $dbFile;
    }
    
    /**
     * Add a delegate to the specified user account.
     *
     * @param string $userId - The email or immutable Id of the user
     * @param Google_Service_Gmail_Delegate $postBody - The object with the data
     *     for that delegate.
     * @return Google_Service_Gmail_Delegate - A real
     *     Google_Service_Gmail_Delegate instance.
     * @throws \Google_Service_Exception - If something went wrong.
     */
    public function create($userId, \Google_Service_Gmail_Delegate $postBody, $optParams = array())
    {
        // If the userId is not an email address, it must be an id.
        $key = 'primaryEmail';
        if (! $this->isValidEmailAddress($userId)) {
            $key = 'id';
            $userId = intval($userId);
        }
        
        if ($postBody->$key === null) {
            $postBody->$key = $userId;
        }
        
        return $this->insertAssumingUserExists($postBody);
    }
    
    /**
     * Determine whether the given string is a valid email address.
     *
     * @param string $email The email address to check.
     * @return bool Whether the string is a valid email address.
     */
    protected function isValidEmailAddress($email)
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }
    
    /**
     * Add a delegate to a user, assuming the user is already in the database.
     *
     * @param Google_Service_Gmail_Delegate $postBody - The object with the data
     *     for that delegate.
     * @return Google_Service_Gmail_Delegate - A real
     *     Google_Service_Gmail_Delegate instance.
     */
    public function insertAssumingUserExists($postBody)
    {
        $entryData = json_encode(get_object_vars($postBody));
        $sqliteUtils = new SqliteUtils($this->dbFile);
        $sqliteUtils->recordData(
            $this->dataType,
            $this->dataClass,
            $entryData
        );
        $allDelegates = $sqliteUtils->getData($this->dataType, $this->dataClass);
        
        if (! $allDelegates) {
            return null;
        }
        
        $newDelegate = new \Google_Service_Gmail_Delegate();
        ObjectUtils::initialize($newDelegate, $postBody);
        $newDelegate->verificationStatus = 'accepted';
        
        return $newDelegate;
    }
}
