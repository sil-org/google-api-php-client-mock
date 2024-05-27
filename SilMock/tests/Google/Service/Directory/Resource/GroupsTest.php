<?php

namespace Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Group as GoogleDirectory_Group;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory as GoogleMock_Directory;

class GroupsTest extends TestCase
{
    // GroupsTest and MembersTest need to share same DB, also
    // They are very dependent on order run.
    // groups.insert, groups.listGroups, members.insert, members.listMembers
    public $dataFile = DATAFILE5;

    public function testInsert()
    {
        $groupEmailAddress = 'sample_group@groups.example.com';

        $group = new GoogleDirectory_Group();
        $group->setEmail($groupEmailAddress);
        $group->setAliases([]);
        $group->setName('Sample Group');
        $group->setDescription('A Sample Group used for testing');

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $groups = $mockGoogleServiceDirectory->groups->listGroups($groupEmailAddress);
            $addedGroup = $mockGoogleServiceDirectory->groups->insert($group);
        } catch (Exception $exception) {
            $this->assertFalse(
                true,
                sprintf(
                    'Was expecting the groups.insert method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        $this->assertTrue($addedGroup instanceof GoogleDirectory_Group);
    }

    public function testListGroups()
    {
        $groupEmailAddress = 'sample_group@groups.example.com';
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $groups = [];
        try {
            $groups = $mockGoogleServiceDirectory->groups->listGroups($groupEmailAddress);
        } catch (Exception $exception) {
            $this->assertFalse(
                true,
                sprintf(
                    'Was expecting the groups.list method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        $this->assertNotEmpty(
            $groups,
            'Was expecting the groups.list method to have at least one group.'
        );
    }
}
