<?php

namespace Atournayre\MaintenanceBundle\Exception;

use Exception;

class MaintenanceEnableException extends Exception
{
    protected $message = 'Oops, an error occurs during while enabling maintenance!';
}