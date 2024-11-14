<?php

namespace SilMock\Google\Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Alias as GoogleDirectory_GroupsAlias;
use Google\Service\Directory\Aliases as GoogleDirectory_GroupsAliases;
use SilMock\Google\Service\DbClass;
use SilMock\Google\Service\Directory\ObjectUtils;

class GroupsAliases extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'groupsaliases');
    }

    public function delete(string $groupKey, string $alias, array $optParams = [])
    {
        $groupAliasRecords = $this->getRecords();
        foreach ($groupAliasRecords as $groupAliasRecord) {
            $groupAliasRecordData = json_decode($groupAliasRecord['data'], true);
            if (
                $groupAliasRecordData['primaryEmail'] === $groupKey
                && $groupAliasRecordData['alias'] === $alias
            ) {
                $this->deleteRecordById($groupAliasRecordData['id']);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function insert(
        string $groupKey,
        GoogleDirectory_GroupsAlias $postBody,
        array $optParams = []
    ): GoogleDirectory_GroupsAlias {
        if ($this->isNewGroupAlias($groupKey, $postBody->getAlias())) {
            $id = str_replace(array(' ', '.'), '', microtime());
            $postBody['id'] = $postBody['id'] ?? $id;
            $dataAsJson = json_encode(get_object_vars($postBody));
            $this->addRecord($dataAsJson);
        }

        $newGroupAlias = new GoogleDirectory_GroupsAlias();
        ObjectUtils::initialize($newGroupAlias, $postBody);

        return $newGroupAlias;
    }

    /**
     * @param $optParams -- Initial implementation ignores this.
     * @return GoogleDirectory_GroupsAliases
     */
    public function listGroupsAliases(string $groupKey, array $optParams = []): GoogleDirectory_GroupsAliases
    {
        $groupAliases = [];
        $groupAliasRecords = $this->getRecords();
        $groupAliasCounter = 0;
        foreach ($groupAliasRecords as $groupAliasRecord) {
            $groupRecordData = json_decode($groupAliasRecord['data'], true);
            if ($groupRecordData['primaryEmail'] === $groupKey) {
                $currentGroup = new GoogleDirectory_GroupsAlias();
                ObjectUtils::initialize($currentGroup, $groupRecordData);
                $groupAliases[] = $currentGroup;
                $groupAliasCounter = $groupAliasCounter + 1;
            }
        }
        $groupsAliasesObject = new GoogleDirectory_GroupsAliases();
        $groupsAliasesObject->setEtag('');
        $groupsAliasesObject->setKind('groupAliases');
        $groupsAliasesObject->setAliases($groupAliases);
        return $groupsAliasesObject;
    }

    protected function isNewGroupAlias(string $groupKey, string $alias): bool
    {
        $mockGroupsObject = new GroupsAliases($this->dbFile);
        $groupsAliasesObject = $mockGroupsObject->listGroupsAliases($groupKey);
        /** @var GoogleDirectory_GroupsAlias[] $aliasObjects */
        $aliasObjects = $groupsAliasesObject->getAliases();
        $lowercaseAliasEmailAddress = mb_strtolower($alias);
        foreach ($aliasObjects as $aliasObject) {
            if (mb_strtolower($aliasObject->getAlias()) === $lowercaseAliasEmailAddress) {
                return false;
            }
        }
        return true;
    }
}
