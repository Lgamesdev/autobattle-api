<?php

namespace App\Exception;

use JetBrains\PhpStorm\Pure;

class FightException extends \Exception implements AutobattleExceptionInterface
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}