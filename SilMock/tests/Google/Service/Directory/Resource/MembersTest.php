<?php

namespace Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Member as GoogleDirectory_Member;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory as GoogleMock_Directory;

class MembersTest extends TestCase
{
    // GroupsTest and MembersTest need to share same DB, also
    // They are very dependent on order run.
    // groups.insert, groups.listGroups, members.insert, members.listMembers
    public string $dataFile = DATAFILE5;
    public const MEMBER_EMAIL_ADDRESS = 'member@example.com';

    public function testInsert()
    {
        $emailAddress = self::MEMBER_EMAIL_ADDRESS;
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;

        $member = new GoogleDirectory_Member();
        $member->setEmail($emailAddress);
        $member->setRole('MEMBER');

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $addedMember = $mockGoogleServiceDirectory->members->insert($groupEmailAddress, $member);
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the members.insert method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        self::assertTrue($addedMember instanceof GoogleDirectory_Member);
    }

    public function testGet()
    {
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $emailAddress = self::MEMBER_EMAIL_ADDRESS;
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $member = $mockGoogleServiceDirectory->members->get($groupEmailAddress, $emailAddress);
        } catch (Exception $exception) {
            self::fail(
                sprintf(
                    'Was expecting the members.get method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        self::assertTrue($member instanceof GoogleDirectory_Member);
    }

    public function testHasMember()
    {
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $result = $mockGoogleServiceDirectory->members->hasMember(
                $groupEmailAddress,
                self::MEMBER_EMAIL_ADDRESS
            );
            $hasMember = $result['isMember'] ?? false;
        } catch (Exception $exception) {
            self::fail(
                $exception->getMessage()
            );
        }
        self::assertTrue($hasMember);
    }

    public function testListMembersAll()
    {
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $members = [];
        try {
            $members = $mockGoogleServiceDirectory->members->listMembers($groupEmailAddress);
        } catch (Exception $exception) {
            $this->failure($exception, 'listMembersAll');
        }
        self::assertNotEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have at least one member entry.'
        );
    }

    public function testListMembersOnlyMember()
    {
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $members = [];
        try {
            $members = $mockGoogleServiceDirectory->members->listMembers(
                $groupEmailAddress,
                [
                    'roles' => 'MEMBER'
                ]
            );
        } catch (Exception $exception) {
            $this->failure($exception, 'listMembersOnlyMember');
        }
        self::assertNotEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have at least one member type entry.'
        );
    }

    public function testListMembersOnlyOwner()
    {
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $members = [];
        try {
            $members = $mockGoogleServiceDirectory->members->listMembers(
                $groupEmailAddress,
                [
                    'roles' => 'OWNER'
                ]
            );
        } catch (Exception $exception) {
            $this->failure($exception, 'listMembersOnlyOwner');
        }
        self::assertEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have no owner types.'
        );
    }

    public function testDelete()
    {
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $groupEmailAddress = GroupsTest::GROUP_EMAIL_ADDRESS;
        $emailAddress = self::MEMBER_EMAIL_ADDRESS;
        try {
            $result = $mockGoogleServiceDirectory->members->hasMember(
                $groupEmailAddress,
                $emailAddress
            );
            $hasMember = $result['isMember'] ?? false;
        } catch (Exception $exception) {
            self::fail(
                $exception->getMessage()
            );
        }

        if (! $hasMember) {
            $member = new GoogleDirectory_Member();
            $member->setEmail($emailAddress);
            $member->setRole('MEMBER');
            $mockGoogleServiceDirectory->members->insert($groupEmailAddress, $member);
        }
        $mockGoogleServiceDirectory->members->delete($groupEmailAddress, $emailAddress);

        try {
            $result = $mockGoogleServiceDirectory->members->hasMember(
                $groupEmailAddress,
                self::MEMBER_EMAIL_ADDRESS
            );
            $hasMember = $result['isMember'] ?? false;
        } catch (Exception $exception) {
            self::fail(
                $exception->getMessage()
            );
        }
        self::assertFalse(
            $hasMember,
            sprintf(
                'Failed to delete %s from group %s',
                $emailAddress,
                $groupEmailAddress
            )
        );
    }

    protected function failure(Exception $exception, string $function): void
    {
        self::fail(
            sprintf(
                'Was expecting the %s method to function, but got: %s',
                $function,
                $exception->getMessage()
            )
        );
    }
}
