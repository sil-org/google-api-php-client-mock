<?php

namespace SilMock\Google\Service;

use Exception;
use SilMock\DataStore\Sqlite\SqliteUtils;
use Sil\Psr3Adapters\Psr3EchoLogger;

class DbClass
{
    /** @var string|null - The path (with file name) to the SQLite database. */
    protected ?string $dbFile;

    /** @var string - The 'type' field to use in the database. */
    protected string $dataType;

    /** @var string - The 'class' field to use in the database */
    protected string $dataClass;

    protected Psr3EchoLogger $logger;

    public function __construct(?string $dbFile, string $dataType, string $dataClass)
    {
        $this->dbFile = $dbFile;
        $this->dataType = $dataType;
        $this->dataClass = $dataClass;
        $this->logger = new Psr3EchoLogger();
        if (empty($dbFile)) {
            $exception = new Exception();
            $exceptions = explode("\n", $exception->getTraceAsString());
            $previousLocationMessage = $exceptions[1];
            $this->logger->warning(
                sprintf(
                    "Empty dbFile provided:\n%s",
                    $previousLocationMessage
                )
            );
        }
    }

    protected function getSqliteUtils(): SqliteUtils
    {
        return new SqliteUtils($this->dbFile);
    }

    protected function getRecords(): ?array
    {
        $sqliteUtils = $this->getSqliteUtils();
        return $sqliteUtils->getData($this->dataType, $this->dataClass);
    }

    protected function deleteRecordById(int $recordId)
    {
        $sqliteUtils = $this->getSqliteUtils();
        $sqliteUtils->deleteRecordById($recordId);
    }

    protected function addRecord(string $data)
    {
        $sqliteUtils = $this->getSqliteUtils();
        $sqliteUtils->recordData(
            $this->dataType,
            $this->dataClass,
            $data
        );
    }
}
