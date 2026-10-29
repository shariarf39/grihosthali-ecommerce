<?php

namespace App\Services\Couriers;

use RuntimeException;

class CourierException extends RuntimeException
{
    public function __construct(string $message, public array $response = [])
    {
        parent::__construct($message);
    }
}
