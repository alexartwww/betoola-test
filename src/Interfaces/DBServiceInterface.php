<?php

namespace Alexartwww\Betoola\Interfaces;


interface DBServiceInterface
{
    public function prepare(string $query, array $options = []): \PDOStatement|false;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;
}
