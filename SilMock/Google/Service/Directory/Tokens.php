<?php

namespace SilMock\Google\Service\Directory;

use Google_Service_Directory_Token;
use Google_Service_Directory_Tokens;
use Google_Service_Exception;
use SilMock\Google\Service\DbClass;

class Tokens extends DbClass
{
    public function __construct($dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'tokens');
    }
    
    public function listTokens($userKey, $optParams = []): Google_Service_Directory_Tokens
    {
        return new Google_Service_Directory_Tokens([ 'items' => [] ]);
    }

    /**
     * @throws Google_Service_Exception
     */
    public function delete($userKey, $clientId)
    {
        $this->assertIsValidUserKey($userKey);

        foreach ($this->listTokensFor($userKey) as $recordId => $token) {
            /** @var Google_Service_Directory_Token $token */
            if ($token->clientId === $clientId) {
                $this->removeToken($recordId);
                return;
            }
        }
    }

    protected function assertIsValidUserKey(string $userId)
    {
        if (! $this->isValidEmailAddress($userId)) {
            throw new Google_Service_Exception('Invalid userId: ' . $userId, 400);
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
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }

    protected function listTokensFor(string $userId): array
    {
        return [];
    }

    protected function removeToken($recordId)
    {
        $this->deleteRecordById($recordId);
    }
}
