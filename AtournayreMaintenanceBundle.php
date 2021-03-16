<?php

namespace Atournayre\MaintenanceBundle;

use Atournayre\MaintenanceBundle\DependencyInjection\AtournayreMaintenanceExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AtournayreMaintenanceBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new AtournayreMaintenanceExtension();
        }

        return $this->extension;
    }
}
