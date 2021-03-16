<?php

namespace Atournayre\MaintenanceBundle\Service;

use Atournayre\MaintenanceBundle\Exception\MaintenanceDisableException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceEnableException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceInvalidIpException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceIpAlreadyDefinedException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceStartException;
use DateTime;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MaintenanceService
{
    private static $specialIps = [
        'localhost',
    ];

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var EnvFileService
     */
    private $envFileService;

    public function __construct(ParameterBagInterface $parameterBag, EnvFileService $envFileService)
    {
        $this->parameterBag = $parameterBag;
        $this->envFileService = $envFileService;
    }

    public function start(string $envPath, DateTime $startDateTime): void
    {
        try {
            $this->envFileService->load($envPath);
            $this->envFileService->add('MAINTENANCE_IS_ENABLED', 'true');
            $this->envFileService->add('MAINTENANCE_START_DATETIME', $startDateTime->format('Y-m-d H:i:s'));
            $this->envFileService->save();
        } catch (Exception $exception) {
            throw new MaintenanceStartException();
        }
    }

    /**
     * @param string $envPath
     * @throws MaintenanceEnableException
     */
    public function enable(string $envPath): void
    {
        try {
            $this->envFileService->load($envPath);
            $this->envFileService->add('MAINTENANCE_IS_ENABLED', 'true');
            $this->envFileService->add('MAINTENANCE_START_DATETIME', (new DateTime())->format('Y-m-d H:i:s'));
            $this->envFileService->save();
        } catch (Exception $exception) {
            throw new MaintenanceEnableException();
        }
    }

    /**
     * @param string $envPath
     * @throws MaintenanceDisableException
     */
    public function disable(string $envPath): void
    {
        try {
            $this->envFileService->load($envPath);
            $this->envFileService->add('MAINTENANCE_IS_ENABLED', 'false');
            $this->envFileService->save();
        } catch (Exception $exception) {
            throw new MaintenanceDisableException();
        }
    }

    /**
     * @param string $envPath
     * @param string $ip
     * @throws MaintenanceInvalidIpException
     * @throws MaintenanceIpAlreadyDefinedException
     */
    public function addIp(string $envPath, string $ip): void
    {
        $ipIsInvalid = !in_array($ip, self::$specialIps)
            && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV4);

        if ($ipIsInvalid) {
            throw new MaintenanceInvalidIpException($ip);
        }

        $authorizedIps = $this->listAuthorizedIps($envPath);
        if (in_array($ip, array_values($authorizedIps))) {
            throw new MaintenanceIpAlreadyDefinedException($ip);
        }
        array_push($authorizedIps, $ip);

        $this->envFileService->load($envPath);
        $this->envFileService->add('MAINTENANCE_AUTHORIZED_IPS', implode(',', $authorizedIps));
        $this->envFileService->save();
    }

    /**
     * @param string $envPath
     * @return array
     * @throws Exception
     */
    public function cleanIps(string $envPath): array
    {
        $cleanedIps = $this->listAuthorizedIps($envPath);

        $this->envFileService->load($envPath);
        $this->envFileService->reset('MAINTENANCE_AUTHORIZED_IPS');
        $this->envFileService->save();
        return $cleanedIps;
    }

    /**
     * @param string $envPath
     * @return array
     * @throws Exception
     */
    public function listAuthorizedIps(string $envPath): array
    {
        $this->envFileService->load($envPath);
        $authorizedIps = $this->envFileService->get('MAINTENANCE_AUTHORIZED_IPS');
        return empty($authorizedIps)
            ? []
            : explode(',', $authorizedIps);
    }
}
