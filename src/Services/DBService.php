<?php

namespace Alexartwww\Betoola\Services;

use Alexartwww\Betoola\Interfaces\DBServiceInterface;

class DBService extends \PDO implements DBServiceInterface
{
    public function __construct(string $host, string $db, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        $dsn = 'mysql:dbname='.$db.';host='.$host;
        parent::__construct($dsn, $username, $password, $options);
    }
}
