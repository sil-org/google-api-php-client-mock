<?php

namespace SilMock\Google\Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Group as GoogleDirectory_Group;
use Google\Service\Directory\Groups as GoogleDirectory_Groups;
use Google\Service\Directory\Alias as GoogleDirectory_GroupAlias;
use SilMock\Google\Service\DbClass;
use SilMock\Google\Service\Directory\ObjectUtils;

class Groups extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'groups');
    }

    public function delete(string $groupKey)
    {
        $groupRecords = $this->getRecords();
        foreach ($groupRecords as $groupRecord) {
            $groupRecordData = json_decode($groupRecord['data'], true);
            $keysToCheck = $groupRecordData['aliases'];
            $keysToCheck[] = $groupRecordData['email'];
            if (in_array($groupKey, $keysToCheck)) {
                $this->deleteRecordById($groupRecord['id']);
                $groupAliasesObject = new GroupsAliases($this->dbFile);
                $groupAliasesObject->deletedByGroup($groupRecordData['email']);
            }
        }
    }

    public function get(string $groupKey): ?GoogleDirectory_Group
    {
        $matchingGroupKey = null;
        $groupRecords = $this->getRecords();
        foreach ($groupRecords as $groupRecord) {
            $groupRecordData = json_decode($groupRecord['data'], true);
            $keysToCheck = $groupRecordData['aliases'];
            $keysToCheck[] = $groupRecordData['email'];
            if (in_array($groupKey, $keysToCheck)) {
                $matchingGroupKey = $groupRecordData['email'];
            }
        }
        if ($matchingGroupKey === null) {
            return null;
        }
        $mockGroupsObject = new Groups($this->dbFile);
        $groupsObject = $mockGroupsObject->listGroups();
        $groups = $groupsObject->getGroups();
        $matchedGroup = null;
        foreach ($groups as $group) {
            if (mb_strtolower($group->getEmail()) === mb_strtolower($matchingGroupKey)) {
                $matchedGroup = $group;
                break;
            }
        }
        if ($matchedGroup !== null) {
            $mockGroupsAliasesObject = new GroupsAliases($this->dbFile);
            $aliases = $mockGroupsAliasesObject->listGroupsAliases($matchedGroup->getEmail());
            $matchedGroup->setAliases($aliases->getAliases());
        }
        return $matchedGroup;
    }

    /**
     * @throws Exception
     */
    public function insert(GoogleDirectory_Group $postBody, $optParams = [])
    {
        if ($this->isNewGroup($postBody->getEmail())) {
            $id = str_replace(array(' ', '.'), '', microtime());
            $postBody['id'] = $postBody['id'] ?? $id;
            $dataAsJson = json_encode(get_object_vars($postBody));
            $this->addRecord($dataAsJson);
        } else {
            throw new Exception(
                "Cannot group.insert an existing group: " . $postBody->getEmail()
            );
        }

        // This should leave aliases as is.
        return $this->get($postBody->getEmail());
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

    public function update(string $groupKey, GoogleDirectory_Group $postBody, $optParams = []): GoogleDirectory_Group
    {
        if ($this->isNewGroup($postBody->getEmail())) {
            throw new Exception("Group '{$groupKey}' does not exist.");
        }
        $group = $this->get($groupKey);

        // remember aliases, because they don't change.
        $aliases = $group->getAliases();

        // update by deleting and reinserting, deletion causes a loss of aliases
        $this->delete($groupKey);
        ObjectUtils::initialize($group, $postBody);
        $this->insert($group);

        // re-add the aliases
        $mockGroupAliasesObject = new GroupsAliases($this->dbFile);
        foreach ($aliases as $alias) {
            $aliasObject = new GoogleDirectory_GroupAlias();
            $aliasObject->setAlias($alias);
            $aliasObject->setPrimaryEmail($group->getEmail());
            $mockGroupAliasesObject->insert($groupKey, $aliasObject);
        }

        return $this->get($groupKey);
    }
}
