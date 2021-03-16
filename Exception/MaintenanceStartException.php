<?php

namespace Atournayre\MaintenanceBundle\Exception;

use Exception;

class MaintenanceStartException extends Exception
{
    protected $message = 'Oops, an error occurs during while scheduling maintenance! Maybe date format is incorrect, please check.';
}