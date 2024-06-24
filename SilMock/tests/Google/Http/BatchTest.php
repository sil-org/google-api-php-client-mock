<?php

namespace Http;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SilMock\Google\Http\Batch;

class BatchTest extends TestCase
{
    public string $dataFile = DATAFILE5;
    public const GROUP_EMAIL_ADDRESS = 'sample_group@groups.example.com';

    public function testAddAndExecute()
    {
        $batch = new Batch();
        $batch->add('RESULT', 'key');
        $results = $batch->execute();
        $expected = [
            'key' => 'RESULT',
        ];
        Assert::assertEquals($expected, $results);
    }
}
