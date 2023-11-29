<?php

declare(strict_types=1);

namespace App\Helper;

use Propel\Runtime\Connection\ConnectionInterface;

class PropelHelper
{
    public const MAX_STATEMENT_COUNT = 200;

    public static function startTransaction(ConnectionInterface $connection): void
    {
        if (!$connection->inTransaction()) {
            $connection->beginTransaction();
        }
    }

    public static function commitTransaction(ConnectionInterface $connection): void
    {
        if ($connection->inTransaction()) {
            $connection->commit();
        }
    }

    public static function rollBack(ConnectionInterface $connection): void
    {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
    }
}
