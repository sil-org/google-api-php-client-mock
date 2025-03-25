<?php

namespace SilMock\Google\Service\Gmail\Resource;

use Google_Service_Exception;
use Google_Service_Gmail_ListForwardingAddressesResponse;
use SilMock\Google\Service\DbClass;

class UsersSettingsForwardingAddresses extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'gmail', 'users_settings_forwardingAddresses');
    }

    public function listUsersSettingsForwardingAddresses($userId, $optParams = array())
    {
        return new Google_Service_Gmail_ListForwardingAddressesResponse(array(
            'forwardingAddresses' => array(),
        ));
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
    protected function isValidEmailAddress(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @throws Google_Service_Exception
     */
    public function delete($userId, $forwardedAddress, $optParams = array())
    {
        $this->assertIsValidUserId($userId);
        $this->assertIsValidDelegateEmail($forwardedAddress);

        foreach ($this->listForwardingAddressesFor($userId) as $recordId => $forwardingAddress) {
            if ($forwardingAddress->getForwardingEmail() === $forwardedAddress) {
                $this->removeForwardingAddress($recordId);
                return;
            }
        }
    }

    protected function listForwardingAddressesFor(string $userId): array
    {
        return [];
    }

    protected function removeForwardingAddress($recordId)
    {
        $this->deleteRecordById($recordId);
    }
}
