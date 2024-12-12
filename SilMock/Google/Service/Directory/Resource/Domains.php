<?php

namespace SilMock\Google\Service\Directory\Resource;

use Google\Service\Directory\Domains as GoogleDirectory_Domains;
use Google\Service\Directory\Domains2 as GoogleDirectory_Domains2;
use SilMock\Google\Service\DbClass;

class Domains extends DbClass
{
    // default to the same as Group and Members
    public function __construct(?string $dbFile = DATAFILE5)
    {
        parent::__construct($dbFile, 'directory', 'domains');
    }

    public function listDomains(string $customer, array $options = []): GoogleDirectory_Domains2
    {
        // no need to store anything at this time.
        $domainNamesString = getenv('DOMAIN_NAMES');
        $domainNames = explode(',', $domainNamesString);
        $domains = [];
        foreach ($domainNames as $domainName) {
            $domain = new GoogleDirectory_Domains();
            $domain->setDomainName($domainName);
            $domains[] = $domain;
        }
        $domains2 = new GoogleDirectory_Domains2();
        $domains2->setDomains($domains);
        return $domains2;
    }
}
