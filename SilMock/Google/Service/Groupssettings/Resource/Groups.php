<?php

namespace SilMock\Google\Service\Groupssettings\Resource;

use Exception;
use Google\Service\Groupssettings\Groups as GoogleGroupsSettings_Groups;
use SilMock\Google\Service\DbClass;
use SilMock\Google\Service\Directory\ObjectUtils;

class Groups extends DbClass
{
    public function __construct(?string $dbFile = null)
    {
        parent::__construct($dbFile, 'groupssettings', 'groups');
    }

    /**
     * This function is not part of the API, and is provided to delete
     * group settings, when a group is deleted in the mock objects.
     *
     * @param string $groupKey
     * @return void
     */
    public function delete(string $groupKey)
    {
        $groupRecords = $this->getRecords();
        foreach ($groupRecords as $groupRecord) {
            $groupRecordData = json_decode($groupRecord['data'], true);
            if (strcasecmp($groupKey, $groupRecordData['email']) === 0) {
                $this->deleteRecordById($groupRecord['id']);
            }
        }
    }

    protected function doesGroupExist(string $groupKey): bool
    {
        echo "PASSED GROUPKEY: " . $groupKey . "\n";
        $groupsSettings = $this->get($groupKey);
        echo "RESULTS: " . json_encode($groupsSettings, JSON_PRETTY_PRINT) . "\n";
        return ($groupsSettings !== null);
    }

    public function get(string $groupKey): ?GoogleGroupsSettings_Groups
    {
        $matchingData = null;
        $groupRecords = $this->getRecords();
        foreach ($groupRecords as $groupRecord) {
            $groupRecordData = json_decode($groupRecord['data'], true);
            echo "CHECKING " . $groupKey . " FOR: " . $groupRecordData['email'] . "\n";
            if (strcasecmp($groupKey, $groupRecordData['email']) === 0) {
                $matchingData = $groupRecordData;
            }
        }
        if ($matchingData === null) {
            return null;
        }
        $groupsSettings = new GoogleGroupsSettings_Groups();
        ObjectUtils::initialize($groupsSettings, $matchingData);
        return $groupsSettings;
    }

    /**
     * This function is not part of the API, and is provided to create
     * group settings, when a group is added in the mock objects.
     *
     * @throws Exception
     */
    public function insert(GoogleGroupsSettings_Groups $postBody, $optParams = [])
    {
        if (! $this->doesGroupExist($postBody->getEmail())) {
            $id = str_replace(array(' ', '.'), '', microtime());
            $postBody['id'] = $postBody['id'] ?? $id;
            $dataAsJson = json_encode(get_object_vars($postBody));
            $this->addRecord($dataAsJson);
        }
        return $this->get($postBody->getEmail());
    }

    public function update(string $groupKey, GoogleGroupsSettings_Groups $postBody, $optParams = []): GoogleGroupsSettings_Groups
    {
        if (! $this->doesGroupExist($postBody->getEmail())) {
            throw new Exception("Group '{$groupKey}' does not exist.");
        }
        $groupsSettings = $this->get($groupKey);

        // update by deleting and reinserting
        $this->delete($groupKey);
        ObjectUtils::initialize($groupsSettings, $postBody);
        $this->insert($groupsSettings);

        return $this->get($groupKey);
    }
}
