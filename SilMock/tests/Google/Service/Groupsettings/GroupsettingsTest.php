<?php

namespace Service\Groupsettings;

use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Groupssettings\Resource\Groups as MockGroupsSettings_ResourceGroups;

class GroupsettingsTest extends TestCase
{
    // GroupsTest and this one need to share same DB, also
    // They are very dependent on order run.
    public string $dataFile = DATAFILE5;
    public const GROUP_EMAIL_ADDRESS = 'sample_group@example.comupdate';

    public function testGet()
    {
        $mockGroupsSettings = new MockGroupsSettings_ResourceGroups($this->dataFile);
        $groupsSettings = $mockGroupsSettings->get(static::GROUP_EMAIL_ADDRESS);
        static::assertNotNull($groupsSettings, 'Expecting group settings to exist');
        static::assertSame(
            $groupsSettings->getEmail(),
            static::GROUP_EMAIL_ADDRESS,
            sprintf(
                'Was expecting the groupsSettings.get method to return a match for the group: %s',
                json_encode($groupsSettings, JSON_PRETTY_PRINT)
            )
        );
        // getting group settings by alias is not implemented
    }

    public function testUpdate()
    {
        $mockGroupsSettings = new MockGroupsSettings_ResourceGroups($this->dataFile);
        $groupsSettings = $mockGroupsSettings->get(static::GROUP_EMAIL_ADDRESS);
        static::assertEquals('false', $groupsSettings->getIsArchived(), 'Expecting default to be not archived');
        $groupsSettings->setIsArchived('true');
        $mockGroupsSettings->update(static::GROUP_EMAIL_ADDRESS, $groupsSettings);
        $groupsSettings = $mockGroupsSettings->get(static::GROUP_EMAIL_ADDRESS);
        static::assertEquals('true', $groupsSettings->getIsArchived(), 'Expecting isArchived to be true');
    }
}
