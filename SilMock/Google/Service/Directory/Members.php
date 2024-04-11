<?php

namespace SilMock\Google\Service\Directory;

use Exception;
use Google\Service\Directory\Member;
use SilMock\Google\Service\DbClass;

class Members extends DbClass
{
    public function __construct($dbFile = null)
    {
        parent::__construct($dbFile, 'directory', 'members');
    }

    /**
     * @throws Exception
     */
    public function insert(string $groupKey, Member $postBody)
    {
        $dataAsJson = json_encode([
            'groupKey' => $groupKey,
            'member' => get_object_vars($postBody),
        ]);
        $sqliteUtils = $this->getSqliteUtils();
        $sqliteUtils->recordData(
            $this->dataType,
            $this->dataClass,
            $dataAsJson
        );

        $newMember = new Member();
        ObjectUtils::initialize($newMember, $postBody);

        return $newMember;
    }
}
