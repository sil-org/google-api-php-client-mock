<?php

namespace SilMock\Google\Service\Directory\Resource;

use SilMock\Google\Service\DbClass;

class TwoStepVerification extends dbClass
{
    public function __construct(string $dbFile = '')
    {
        parent::__construct($dbFile, 'directory', 'twoStepVerification');
    }

    /**
     * Turn off 2SV for a given email account.
     *
     * NOTE: This doesn't need to work. It just needs to exist.
     *
     * @param $userKey
     * @param $optParams
     * @return void
     */
    public function turnOff($userKey, $optParams = [])
    {
        // Confirm verification codes exist.
        $sqliteUtils = $this->getSqliteUtils();
        $twoStepVerificationRecord = $sqliteUtils->getAllRecordsByDataKey(
            $this->dataType,
            $this->dataClass,
            'twoStepVerification',
            $userKey
        );
        $recordId = $twoStepVerificationRecord['id'];
        $twoStepVerificationRecord['onOrOff'] = 'off';
        $sqliteUtils->updateRecordById($recordId, json_encode($twoStepVerificationRecord));
    }
}