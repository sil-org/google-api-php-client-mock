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

    public function testInsert()
    {
        $emailAddress = 'test@example.com';
        $groupEmailAddress = 'sample_group@groups.example.com';

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
        $groupEmailAddress = 'sample_group@groups.example.com';
        $emailAddress = 'test@example.com';
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
        $groupEmailAddress = 'sample_group@groups.example.com';
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $result = $mockGoogleServiceDirectory->members->hasMember(
                $groupEmailAddress,
                'test@example.com'
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
        $groupEmailAddress = 'sample_group@groups.example.com';
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $members = [];
        try {
            $members = $mockGoogleServiceDirectory->members->listMembers($groupEmailAddress);
        } catch (Exception $exception) {
            $this->failure($exception);
        }
        self::assertNotEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have at least one member entry.'
        );
    }

    public function testListMembersOnlyMember()
    {
        $groupEmailAddress = 'sample_group@groups.example.com';
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
            $this->failure($exception);
        }
        self::assertNotEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have at least one member type entry.'
        );
    }

    public function testListMembersOnlyOwner()
    {
        $groupEmailAddress = 'sample_group@groups.example.com';
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
            $this->failure($exception);
        }
        self::assertEmpty(
            $members->getMembers(),
            'Was expecting the members.list method to have no owner types.'
        );
    }

    protected function failure(Exception $exception): void
    {
        self::fail(
            sprintf(
                'Was expecting the members.insert method to function, but got: %s',
                $exception->getMessage()
            )
        );
    }
}
