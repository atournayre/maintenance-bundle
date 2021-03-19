<?php

namespace Atournayre\MaintenanceBundle\Service;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MaintenanceChecker
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(ParameterBagInterface $parameterBag, RequestStack $requestStack)
    {
        $this->parameterBag = $parameterBag;
        $this->requestStack = $requestStack;
    }

    public function maintenanceIsEnabled(): bool
    {
        if (!$this->parameterBag->has('atournayre_maintenance.is_enabled')) {
            throw new ParameterNotFoundException('atournayre_maintenance.is_enabled');
        }

        if (!$this->parameterBag->has('atournayre_maintenance.start_date_time')) {
            throw new ParameterNotFoundException('atournayre_maintenance.start_date_time');
        }

        if (!$this->parameterBag->has('atournayre_maintenance.authorized_ips')) {
            throw new ParameterNotFoundException('atournayre_maintenance.authorized_ips');
        }

        return $this->parameterBag->get('atournayre_maintenance.is_enabled')
            && $this->maintenanceStartTimeHasPassed()
            && $this->clientIpIsNotAllowed();
    }

    private function maintenanceStartTimeHasPassed()
    {
        $maintenanceStartDateTime = new \DateTime($this->parameterBag->get('atournayre_maintenance.start_date_time'));
        return (new \DateTime()) > $maintenanceStartDateTime;
    }

    private function clientIpIsNotAllowed()
    {
        $currentIP = $this->requestStack->getMasterRequest()->getClientIp();
        $authorizedIps = explode(',',$this->parameterBag->get('atournayre_maintenance.authorized_ips'));
        return !in_array($currentIP, $authorizedIps);
    }
}
