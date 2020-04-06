<?php

use PHPUnit\Framework\Assert;
use SilMock\Google\Service\Gmail\Resource\UsersSettingsDelegates;
use SilMock\Google\Service\GoogleFixtures;

class UsersSettingsDelegatesTest extends PHPUnit\Framework\TestCase
{
    public $dataFile = DATAFILE4;
    
    protected function setUp(): void
    {
        $this->emptyFixturesDataFile();
    }
    
    private function emptyFixturesDataFile()
    {
        $fixturesClass = new GoogleFixtures($this->dataFile);
        $fixturesClass->removeAllFixtures();
    }
    
    protected function tearDown(): void
    {
        $this->emptyFixturesDataFile();
    }
    
    public function testListUsersSettingsDelegates()
    {
        // Arrange:
        $accountEmail = 'john_smith@example.org';
        $delegateEmail = 'mike_manager@example.org';
        
        // Act
        $before = $this->getDelegatesForAccount($accountEmail);
        $this->delegateAccessToAccountBy($accountEmail, $delegateEmail);
        $after = $this->getDelegatesForAccount($accountEmail);
        
        // Assert:
        $delegatesBefore = $before->getDelegates();
        Assert::assertEmpty($delegatesBefore);
        
        $delegatesAfter = $after->getDelegates();
        Assert::assertNotEmpty($delegatesAfter);
        
        $foundExpectedDelegate = false;
        foreach ($delegatesAfter as $delegate) {
            if ($delegate->delegateEmail === $delegateEmail) {
                $foundExpectedDelegate = true;
                break;
            }
        }
        Assert::assertTrue($foundExpectedDelegate, sprintf(
            'Did not find %s in %s',
            $delegateEmail,
            json_encode($delegatesAfter)
        ));
    }
    
    private function delegateAccessToAccountBy(
        string $accountEmail,
        string $delegateEmail
    ) {
        $gmailDelegate = new Google_Service_Gmail_Delegate();
        $gmailDelegate->setDelegateEmail($delegateEmail);
        $userSettingsDelegates = new UsersSettingsDelegates($this->dataFile);
        $userSettingsDelegates->create(
            $accountEmail,
            $gmailDelegate
        );
    }
    
    private function getDelegatesForAccount(string $emailAddress)
    {
        $userSettingsDelegates = new UsersSettingsDelegates($this->dataFile);
        return $userSettingsDelegates->listUsersSettingsDelegates($emailAddress);
    }
} 
