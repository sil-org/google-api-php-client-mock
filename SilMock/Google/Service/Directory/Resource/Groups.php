<?php

namespace SilMock\Google\Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Group as GoogleDirectory_Group;
use Google\Service\Directory\Groups as GoogleDirectory_Groups;
use SilMock\Google\Service\DbClass;
use SilMock\Google\Service\Directory\ObjectUtils;

class Groups extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'groups');
    }

    public function get(string $groupKey): ?GoogleDirectory_Group
    {
        $mockGroupsObject = new Groups($this->dbFile);
        $groupsObject = $mockGroupsObject->listGroups();
        $groups = $groupsObject->getGroups();
        foreach ($groups as $group) {
            if (mb_strtolower($group->getEmail()) === mb_strtolower($groupKey)) {
                return $group;
            }
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function insert(GoogleDirectory_Group $postBody, $optParams = [])
    {
        if ($this->isNewGroup($postBody->getEmail())) {
            $dataAsJson = json_encode(get_object_vars($postBody));
            $this->addRecord($dataAsJson);
        }

        $newGroup = new GoogleDirectory_Group();
        ObjectUtils::initialize($newGroup, $postBody);

        return $newGroup;
    }

    /**
     * @param $optParams -- Initial implementation ignores this.
     * @return GoogleDirectory_Groups
     */
    public function listGroups($optParams = []): GoogleDirectory_Groups
    {
        $pageSize = $optParams['maxResults'] ?? 100;
        $pageToken = $optParams['pageToken'] ?? 0;
        $groups = new GoogleDirectory_Groups();
        $directoryGroupRecords = $this->getRecords();
        $groupCounter = 0;
        foreach ($directoryGroupRecords as $groupRecord) {
            $groupData = json_decode($groupRecord['data'], true);
            $groupCounter = $groupCounter + 1;
            if ($groupCounter >= ($pageToken * $pageSize)) {
                $currentGroups = $groups->getGroups();
                $currentGroup = new GoogleDirectory_Group();
                ObjectUtils::initialize($currentGroup, $groupData);
                $currentGroups[] = $currentGroup;
                $groups->setGroups($currentGroups);
            }
            $currentGroups = $groups->getGroups();
            $currentResultSize = count($currentGroups);
            if ($currentResultSize === $pageSize) {
                break;
            }
        }
        $currentGroups = $groups->getGroups();
        $currentResultSize = count($currentGroups);
        if (0 < $currentResultSize && $currentResultSize <= $pageSize) {
            $groups->setNextPageToken(sprintf("%d", $pageToken + 1));
        }
        return $groups;
    }

    protected function isNewGroup(string $groupKey): bool
    {
        $mockGroupsObject = new Groups($this->dbFile);
        $groupsObject = $mockGroupsObject->listGroups();
        $groups = $groupsObject->getGroups();
        $groupEmailAddresses = [];
        foreach ($groups as $group) {
            $groupEmailAddresses[] = mb_strtoupper($group->getEmail());
        }
        $uppercaseGroupEmailAddress = mb_strtoupper($groupKey);
        return ! in_array($uppercaseGroupEmailAddress, $groupEmailAddresses);
    }
}
