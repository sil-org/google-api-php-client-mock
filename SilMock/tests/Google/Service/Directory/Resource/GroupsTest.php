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
    public string $dataFile = DATAFILE5;
    public const GROUP_EMAIL_ADDRESS = 'sample_group@groups.example.com';

    public function testInsert()
    {
        $group = new GoogleDirectory_Group();
        $group->setEmail(self::GROUP_EMAIL_ADDRESS);
        $group->setAliases([]);
        $group->setName('Sample Group');
        $group->setDescription('A Sample Group used for testing');

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $addedGroup = $mockGoogleServiceDirectory->groups->insert($group);
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups.insert method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        self::assertTrue($addedGroup instanceof GoogleDirectory_Group);
    }

    public function testGet()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS);
        self::assertInstanceOf(GoogleDirectory_Group::class, $group);
    }

    public function testListGroups()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $groups = [];
        try {
            $groups = $mockGoogleServiceDirectory->groups->listGroups(self::GROUP_EMAIL_ADDRESS);
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups.list method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        self::assertNotEmpty(
            $groups,
            'Was expecting the groups.list method to have at least one group.'
        );
    }
}
