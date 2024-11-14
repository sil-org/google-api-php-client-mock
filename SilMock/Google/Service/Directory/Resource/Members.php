<?php

namespace SilMock\Google\Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Member as GoogleDirectory_Member;
use Google\Service\Directory\Members as GoogleDirectory_Members;
use SilMock\Google\Service\DbClass;
use SilMock\Google\Service\Directory\ObjectUtils;

class Members extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'members');
    }

    public function delete(string $groupKey, string $memberKey)
    {
        $directoryMemberRecords = $this->getRecords();
        foreach ($directoryMemberRecords as $record) {
            $decodedRecordData = json_decode($record['data'], true);
            if (
                $decodedRecordData['groupKey'] === $groupKey
                && $decodedRecordData['member']['email'] === $memberKey
            ) {
                $recordId = $record['id'];
                $this->deleteRecordById($recordId);
            }
        }
    }

    public function get(string $groupKey, string $memberKey): GoogleDirectory_Member
    {
        $members = $this->listMembers($groupKey);
        $memberList = $members->getMembers();
        foreach ($memberList as $member) {
            $memberEmailAddress = mb_strtolower($member->getEmail());
            if ($memberEmailAddress === mb_strtolower($memberKey)) {
                return $member;
            }
        }
        throw new Exception('Member not found');
    }

    public function hasMember(string $groupKey, string $memberKey): array
    {
        $members = $this->listMembers($groupKey);
        $memberList = $members->getMembers();
        $memberEmailAddresses = [];
        foreach ($memberList as $member) {
            $memberEmailAddresses[] = mb_strtolower($member->getEmail());
        }
        $isMember = in_array(mb_strtolower($memberKey), $memberEmailAddresses);
        return [
            'isMember' => $isMember,
        ];
    }

    /**
     * @throws Exception
     */
    public function insert(string $groupKey, GoogleDirectory_Member $postBody, $optParams = [])
    {
        $this->validateGroupExists($groupKey);
        if ($this->isNewMember($groupKey, $postBody)) {
            $dataAsJson = json_encode(
                [
                    'groupKey' => $groupKey,
                    'member' => get_object_vars($postBody),
                ]
            );
            $this->addRecord($dataAsJson);
        }

        $newMember = new GoogleDirectory_Member();
        ObjectUtils::initialize($newMember, $postBody);

        return $newMember;
    }

    public function listMembers($groupKey, $optParams = [])
    {
        $this->validateGroupExists($groupKey);
        $pageSize = $optParams['pageSize'] ?? 10;
        $pageToken = $optParams['pageToken'] ?? 0;
        $roles = $optParams['roles'] ?? null;
        $expectedRoles = $this->extractRoles($roles);
        $members = new GoogleDirectory_Members();
        $directoryMemberRecords = $this->getRecords();
        $memberCounter = 0;
        foreach ($directoryMemberRecords as $memberRecord) {
            $memberData = json_decode($memberRecord['data'], true);
            if (
                $memberData['groupKey'] === $groupKey            // Matches the expected group
                && $memberCounter >= ($pageToken * $pageSize)    // Matches the subsection of all the members
                && (empty($expectedRoles) || in_array($memberData['member']['role'], $expectedRoles)) // Matches role
            ) {
                $memberCounter = $memberCounter + 1;
                $this->addToMembers($memberData, $members);
            }
            $currentMembers = $members->getMembers();
            $currentResultSize = count($currentMembers);
            if ($currentResultSize === $pageSize) {
                break;
            }
        }
        $currentMembers = $members->getMembers();
        $currentResultSize = count($currentMembers);
        if (0 < $currentResultSize && $currentResultSize <= $pageSize) {
            $members->setNextPageToken(sprintf("%d", $pageToken + 1));
        }
        return $members;
    }

    protected function extractRoles(?string $roles): array
    {
        if (! empty($roles)) {
            $allExpectedRoles = explode(',', $roles);
            $expectedRoles = array_map(
                function ($role) {
                    return mb_strtoupper(trim($role));
                },
                $allExpectedRoles
            );
        } else {
            $expectedRoles = [];
        }
        return $expectedRoles;
    }

    protected function addToMembers(array $memberData, GoogleDirectory_Members $members): void
    {
        $currentMembers = $members->getMembers();
        $currentMember = new GoogleDirectory_Member();
        ObjectUtils::initialize($currentMember, $memberData['member']);
        $currentMembers[] = $currentMember;
        $members->setMembers($currentMembers);
    }

    protected function validateGroupExists(string $groupKey): void
    {
        $mockGroupsObject = new Groups($this->dbFile);
        $groupsObject = $mockGroupsObject->listGroups();
        $groups = $groupsObject->getGroups();
        $groupEmailAddresses = [];
        foreach ($groups as $group) {
            $groupEmailAddresses[] = $group->getEmail();
        }
        $uppercaseGroupEmailAddresses = array_map('mb_strtoupper', $groupEmailAddresses);
        $uppercaseGroupEmailAddress = mb_strtoupper($groupKey);
        if (! in_array($uppercaseGroupEmailAddress, $uppercaseGroupEmailAddresses)) {
            throw new Exception(
                sprintf(
                    'Group %s does not exist',
                    $groupKey
                )
            );
        }
    }

    protected function isNewMember(string $groupKey, GoogleDirectory_Member $postBody): bool
    {
        $memberRecords = $this->getRecords();
        foreach ($memberRecords as $memberRecord) {
            $memberJsonData = $memberRecord['data'];
            $memberData = json_decode($memberJsonData, true);
            $member = new GoogleDirectory_Member();
            ObjectUtils::initialize($member, $memberData['member']);
            if (
                $member->getEmail() === $postBody->getEmail() &&
                $memberData['groupKey'] === $groupKey
            ) {
                return false;
            }
        }
        return true;
    }
}
