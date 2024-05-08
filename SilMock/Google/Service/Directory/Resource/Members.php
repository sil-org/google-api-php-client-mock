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

    /**
     * @throws Exception
     */
    public function insert(string $groupKey, GoogleDirectory_Member $postBody, $optParams = [])
    {
        $dataAsJson = json_encode([
            'groupKey' => $groupKey,
            'member' => get_object_vars($postBody),
        ]);
        $this->addRecord($dataAsJson);

        $newMember = new GoogleDirectory_Member();
        ObjectUtils::initialize($newMember, $postBody);

        return $newMember;
    }

    public function listMembers($groupKey, $optParams = [])
    {
        $pageSize = $optParams['pageSize'] ?? 10;
        $pageToken = $optParams['pageToken'] ?? 0;
        $members = new GoogleDirectory_Members();
        $directoryMemberRecords = $this->getRecords();
        $memberCounter = 0;
        foreach ($directoryMemberRecords as $memberRecord) {
            $memberData = json_decode($memberRecord['data'], true);
            if ($memberData['groupKey'] === $groupKey) {
                $memberCounter = $memberCounter + 1;
                if ($memberCounter >= ($pageToken * $pageSize)) {
                    $currentMembers = $members->getMembers();
                    $currentMember = new GoogleDirectory_Member();
                    ObjectUtils::initialize($currentMember, $memberData['member']);
                    $currentMembers[] = $currentMember;
                    $members->setMembers($currentMembers);
                }
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
}
