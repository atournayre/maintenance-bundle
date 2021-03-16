<?php

namespace Atournayre\MaintenanceBundle\Exception;

use Exception;

class MaintenanceDisableException extends Exception
{
    protected $message = 'Oops, an error occurs during while disabling maintenance!';
}