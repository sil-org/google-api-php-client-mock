<?php

namespace Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Group as GoogleDirectory_Group;
use Google\Service\Directory\Alias as GoogleDirectory_GroupAlias;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory as GoogleMock_Directory;

class GroupsTest extends TestCase
{
    // GroupsTest and MembersTest need to share same DB, also
    // They are very dependent on order run.
    // groups.insert, groups.listGroups, members.insert, members.listMembers
    public string $dataFile = DATAFILE5;
    public const GROUP_EMAIL_ADDRESS = 'sample_group@example.com';
    public const GROUP_ALIAS_ADDRESS = 'ma_org_sample_group@groups.example.com';

    public function testInitialSetup()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS);
        if ($group !== null) {
            $mockGoogleServiceDirectory->groups->delete(self::GROUP_EMAIL_ADDRESS);
        }
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS);
        self::assertNull($group, "Failed to clean up previous insert tests.");
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS . 'update');
        if ($group !== null) {
            $mockGoogleServiceDirectory->groups->delete(self::GROUP_EMAIL_ADDRESS . 'update');
        }
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS . 'update');
        self::assertNull($group, "Failed to clean up previous update tests.");
    }

    public function testInsert()
    {
        $group = new GoogleDirectory_Group();
        $group->setEmail(self::GROUP_EMAIL_ADDRESS);
        $group->setAliases([self::GROUP_ALIAS_ADDRESS]); // read-only, should not save anything
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
        self::assertEmpty($addedGroup->getAliases(), "Expecting no group aliases inserted by group.insert");
    }

    public function testUpdate()
    {
        $group = new GoogleDirectory_Group();
        $group->setEmail(self::GROUP_EMAIL_ADDRESS . 'update');
        $group->setAliases([self::GROUP_ALIAS_ADDRESS . 'update']); // this shouldn't change aliases.
        $group->setName('Sample Group Update');
        $group->setDescription('A Sample Group used for testing update');

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
        self::assertEmpty($addedGroup->getAliases(), "Expecting no group aliases inserted by group.insert");

        // Google group does not update aliases, but for coding simplicity the mock object does.
        $group->setAliases([self::GROUP_ALIAS_ADDRESS . 'update-change']); // this shouldn't change aliases.
        $updatedGroup = $mockGoogleServiceDirectory->groups->update($group->getEmail(), $group);
        self::assertTrue($updatedGroup instanceof GoogleDirectory_Group);
        self::assertEmpty($updatedGroup->getAliases()); // this should confirm aliases are unset
    }

    protected function deleteTestSetup()
    {
        // Set update a deletable email address
        $group = new GoogleDirectory_Group();
        $group->setEmail(self::GROUP_EMAIL_ADDRESS . 'delete');
        $group->setAliases([self::GROUP_ALIAS_ADDRESS . 'delete']); // read-only property, should have no effect
        $group->setName('Sample Deletable Group');
        $group->setDescription('A Sample Deletable Group used for testing');

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
        self::assertEmpty($addedGroup->getAliases(), "Expecting no group aliases");

        // Set update a deletable email address
        $groupAlias = new GoogleDirectory_GroupAlias();
        $groupAlias->setPrimaryEmail(self::GROUP_EMAIL_ADDRESS . 'delete');
        $groupAlias->setAlias(self::GROUP_ALIAS_ADDRESS . 'delete');

        try {
            $addedGroupAliases = $mockGoogleServiceDirectory->groups_aliases->insert(
                self::GROUP_EMAIL_ADDRESS . 'delete',
                $groupAlias
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups_aliases.insert method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        self::assertTrue($addedGroupAliases instanceof GoogleDirectory_GroupAlias);
    }

    public function testDeleteByName()
    {
        $this->deleteTestSetup();

        // Now try to delete it
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $mockGoogleServiceDirectory->groups->delete(self::GROUP_EMAIL_ADDRESS . 'delete');
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups.delete method to function for name, but got: %s',
                    $exception->getMessage()
                )
            );
        }

        try {
            $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS . 'delete');
            self::assertNull(
                $group,
                'Was expecting the group to be deleted by name, but found something'
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting to confirm the group was deleted by name, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        try {
            $groupAliases = $mockGoogleServiceDirectory->groups_aliases->listGroupsAliases(
                self::GROUP_EMAIL_ADDRESS . 'delete'
            );
            self::assertEmpty(
                $groupAliases->getAliases(),
                'Was expecting the group aliases to be deleted by name, but found something'
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting to confirm the group aliases were also deleted by name, but got: %s',
                    $exception->getMessage()
                )
            );
        }
    }

    public function testDeleteByAlias()
    {
        $this->deleteTestSetup();

        // Now try to delete it
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $mockGoogleServiceDirectory->groups->delete(self::GROUP_ALIAS_ADDRESS . 'delete');
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups.delete method to function for aliases, but got: %s',
                    $exception->getMessage()
                )
            );
        }

        try {
            $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS . 'delete');
            self::assertNull(
                $group,
                'Was expecting the group to be deleted by alias, but found something'
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting to confirm the group was deleted by alias, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        try {
            $groupAliases = $mockGoogleServiceDirectory->groups_aliases->listGroupsAliases(self::GROUP_EMAIL_ADDRESS . 'delete');
            self::assertEmpty(
                $groupAliases->getAliases(),
                'Was expecting the group aliases to be deleted by name, but found something'
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting to confirm the group aliases were also deleted by name, but got: %s',
                    $exception->getMessage()
                )
            );
        }
    }

    public function testGetByName()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_EMAIL_ADDRESS);
        self::assertInstanceOf(GoogleDirectory_Group::class, $group);
        self::assertEquals(self::GROUP_EMAIL_ADDRESS, $group->getEmail());
    }

    public function testGetByAlias()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $group = $mockGoogleServiceDirectory->groups->get(self::GROUP_ALIAS_ADDRESS);
        self::assertInstanceOf(GoogleDirectory_Group::class, $group);
        self::assertEquals(self::GROUP_EMAIL_ADDRESS, $group->getEmail());
    }

    public function testListGroups()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $groups = [];
        try {
            $groups = $mockGoogleServiceDirectory->groups->listGroups();
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
