<?php

namespace SilMock\Google\Service\Gmail\Resource;

use Google_Service_Exception;
use Google_Service_Gmail_Delegate;
use Google_Service_Gmail_ListDelegatesResponse;
use SilMock\DataStore\Sqlite\SqliteUtils;
use SilMock\Google\Service\DbClass;

class UsersSettingsDelegates extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'gmail', 'users_settings_delegate');
    }

    /**
     * Add a delegate to the specified user account.
     *
     * @param string $userId - The email or immutable Id of the user
     * @param Google_Service_Gmail_Delegate $postBody - The object with the data
     *     for that delegate.
     * @return Google_Service_Gmail_Delegate - A real
     *     Google_Service_Gmail_Delegate instance.
     * @throws Google_Service_Exception - If something went wrong.
     */
    public function create($userId, \Google_Service_Gmail_Delegate $postBody, $optParams = array())
    {
        $this->assertIsValidUserId($userId);
        
        if ($this->hasDelegate($userId, $postBody->delegateEmail)) {
            throw new Google_Service_Exception('Already has delegate', 409);
        }
        
        return $this->addDelegate($userId, $postBody->delegateEmail);
    }
    
    protected function hasDelegate(string $userId, string $delegateEmail): bool
    {
        foreach ($this->listDelegatesFor($userId) as $delegate) {
            if ($delegate->delegateEmail === $delegateEmail) {
                return true;
            }
        }
        return false;
    }
    
    protected function listDelegatesFor(string $userId): array
    {
        $matchingRecords = [];
        foreach ($this->getDelegateRecords() as $delegateRecord) {
            $delegate = json_decode($delegateRecord['data']);
            if ($delegate !== null && $delegate->primaryEmail === $userId) {
                $matchingRecord = new \Google_Service_Gmail_Delegate();
                $matchingRecord->setDelegateEmail($delegate->delegateEmail);
                $matchingRecord->setVerificationStatus($delegate->verificationStatus);
                $matchingRecords[$delegateRecord['id']] = $matchingRecord;
            }
        }
        return $matchingRecords;
    }
    
    /**
     * Get the delegate records. Example result:
     *
     *     [
     *         [
     *             'id' => '1',
     *             'type' => 'gmail',
     *             'class' => 'users_settings_delegate',
     *             'data' => '{"delegateEmail":"john_smith@example.org","verificationStatus":"accepted","primaryEmail":"other_person@example.org"}',
     *         ],
     *         // ...
     *     ]
     *
     * @return array[]
     */
    protected function getDelegateRecords(): array
    {
        return $this->getRecords();
    }
    
    protected function addDelegate(string $userId, string $delegateEmail)
    {
        $data = json_encode(
            [
                'primaryEmail' => $userId,
                'delegateEmail' => $delegateEmail,
                'verificationStatus' => 'accepted',
            ]
        );
        $this->addRecord($data);

        return $this->get($userId, $delegateEmail);
    }
    
    protected function getSqliteUtils(): SqliteUtils
    {
        return new SqliteUtils($this->dbFile);
    }
    
    protected function assertIsValidUserId(string $userId)
    {
        if (! $this->isValidEmailAddress($userId)) {
            throw new Google_Service_Exception('Invalid userId: ' . $userId, 400);
        }
    }
    
    protected function assertIsValidDelegateEmail($delegateEmail)
    {
        if (! $this->isValidEmailAddress($delegateEmail)) {
            throw new Google_Service_Exception('Invalid delegate: ' . $delegateEmail, 400);
        }
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
    
    public function get($userId, $delegateEmail, $optParams = array())
    {
        $this->assertIsValidUserId($userId);
        $this->assertIsValidDelegateEmail($delegateEmail);
        
        foreach ($this->listDelegatesFor($userId) as $delegate) {
            if ($delegate->delegateEmail === $delegateEmail) {
                $matchingRecord = new \Google_Service_Gmail_Delegate();
                $matchingRecord->setDelegateEmail($delegate->delegateEmail);
                $matchingRecord->setVerificationStatus($delegate->verificationStatus);
                return $matchingRecord;
            }
        }
        throw new Google_Service_Exception('Invalid delegate', 404);
    }
    
    public function delete($userId, $delegateEmail, $optParams = array())
    {
        $this->assertIsValidUserId($userId);
        $this->assertIsValidDelegateEmail($delegateEmail);
        
        foreach ($this->listDelegatesFor($userId) as $recordId => $delegate) {
            if ($delegate->delegateEmail === $delegateEmail) {
                $this->removeDelegate($recordId);
                return;
            }
        }
        throw new Google_Service_Exception('Invalid delegate', 404);
    }
    
    protected function removeDelegate(int $recordId)
    {
        $this->deleteRecordById($recordId);
    }
    
    public function listUsersSettingsDelegates($userId, $optParams = array())
    {
        $response = new Google_Service_Gmail_ListDelegatesResponse();
        $response->setDelegates($this->listDelegatesFor($userId));
        return $response;
    }
}
