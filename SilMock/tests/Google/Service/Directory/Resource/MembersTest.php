<?php

namespace Service\Directory\Resource;

use Exception;
use Google\Service\Directory\Member as GoogleDirectory_Member;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory as GoogleMock_Directory;

class MembersTest extends TestCase
{
    public $dataFile = DATAFILE2;

    public function testInsert()
    {
        $emailAddress = 'test@example.com';
        $groupEmailAddress = 'sample_group@groups.example.com';

        $member = new GoogleDirectory_Member();
        $member->setEmail($emailAddress);

        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        try {
            $addedMember = $mockGoogleServiceDirectory->members->insert($groupEmailAddress, $member);
        } catch (Exception $exception) {
            $this->assertFalse(
                true,
                sprintf(
                    'Was expecting the members.insert method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        $this->assertTrue($addedMember instanceof GoogleDirectory_Member);
    }

    public function testListMembers()
    {
        $groupEmailAddress = 'sample_group@groups.example.com';
        $mockGoogleServiceDirectory = new GoogleMock_Directory('anyclient', $this->dataFile);
        $members = [];
        try {
            $members = $mockGoogleServiceDirectory->members->listMembers($groupEmailAddress);
        } catch (Exception $exception) {
            $this->assertFalse(
                true,
                sprintf(
                    'Was expecting the members.list method to function, but got: %s',
                    $exception->getMessage()
                )
            );
        }
        $this->assertNotEmpty(
            $members,
            'Was expecting the members.list method to have at least one member.'
        );
    }
}
