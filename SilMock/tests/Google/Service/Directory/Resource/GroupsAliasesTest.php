<?php

namespace Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Alias as GoogleDirectory_GroupsAlias;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory as GoogleMock_Directory;

class GroupsAliasesTest extends TestCase
{
    // GroupsTest and MembersTest need to share same DB, also
    // They are very dependent on order run.
    // groups.insert, groups.listGroups, members.insert, members.listMembers
    public string $dataFile = DATAFILE5;
    public const GROUP_EMAIL_ADDRESS = 'sample_group@groups.example.com';
    public const GROUP_ALIAS_ADDRESS = 'sample_group_alias@groups.example.com';

    public function testInsert()
    {
        $groupAlias = new GoogleDirectory_GroupsAlias();
        $groupAlias->setPrimaryEmail(self::GROUP_EMAIL_ADDRESS);
        $groupAlias->setAlias(self::GROUP_ALIAS_ADDRESS);

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $addedGroupAlias = $mockGoogleServiceDirectory->groups_aliases->insert(
                self::GROUP_EMAIL_ADDRESS,
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
        self::assertTrue(
            $addedGroupAlias->getAlias() === self::GROUP_ALIAS_ADDRESS
            && $addedGroupAlias->getPrimaryEmail() === self::GROUP_EMAIL_ADDRESS,
            'Was expecting the groups_alias.insert method to return a match to the value passed'
        );
    }

    public function testDelete()
    {
        // Set update a deletable email address
        $groupAlias = new GoogleDirectory_GroupsAlias();
        $groupAlias->setPrimaryEmail(self::GROUP_EMAIL_ADDRESS . 'delete');
        $groupAlias->setAlias(self::GROUP_ALIAS_ADDRESS);

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $mockGoogleServiceDirectory->groups_aliases->insert(self::GROUP_EMAIL_ADDRESS, $groupAlias);
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups_aliases.insert method to function for delete, but got: %s',
                    $exception->getMessage()
                )
            );
        }

        // Now try to delete it
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $mockGoogleServiceDirectory->groups->delete(self::GROUP_EMAIL_ADDRESS . 'delete');
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the groups.delete method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }

        try {
            $groupsAliasesObject = $mockGoogleServiceDirectory->groups_aliases->listGroupsAliases(
                self::GROUP_EMAIL_ADDRESS . 'delete'
            );
            $groupsAliases = $groupsAliasesObject->getAliases();
            self::assertEmpty(
                $groupsAliases,
                'Was expecting the groups aliases to be deleted, but found something'
            );
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting to confirm the group\'s alias was deleted, but got: %s',
                    $exception->getMessage()
                )
            );
        }
    }

    public function testListGroupsAliases()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $groups = [];
        try {
            $groups = $mockGoogleServiceDirectory->groups_aliases->listGroupsAliases(self::GROUP_EMAIL_ADDRESS);
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
            'Was expecting the groups_aliases.list method to have at least one group alias.'
        );
    }
}
