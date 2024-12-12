<?php

namespace Service\Directory\Resource;

use PHPUnit\Framework\TestCase;
use SilMock\Google\Service\Directory\Resource\Domains;

class DomainsTest extends TestCase
{
    public function testListDomains()
    {
        $domainsApi = new Domains();
        $domainNameListObject = $domainsApi->listDomains('any_customer');
        $arrayOfDomainsObjects = $domainNameListObject->getDomains();
        $domainNameList = [];
        foreach ($arrayOfDomainsObjects as $domainObject) {
            $domainNameList[$domainObject->getDomainName()] = $domainObject->getDomainName();
        }
        $expected = [
            'groups.example.org' => 'groups.example.org',
            'example.org' => 'example.org',
        ];
        self::assertEquals($expected, $domainNameList, 'Domain name list does not match expected values');
    }
}
