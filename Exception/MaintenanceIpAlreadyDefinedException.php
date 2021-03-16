<?php

namespace Atournayre\MaintenanceBundle\Exception;

use Exception;
use Throwable;

class MaintenanceIpAlreadyDefinedException extends Exception
{
    public function __construct(?string $ip, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = sprintf('%s is already authorized.', $ip);
    }
}
