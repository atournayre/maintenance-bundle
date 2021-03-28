<?php

namespace Atournayre\MaintenanceBundle\Service;

use Atournayre\Component\DotEnvEditor\DotEnvEditor;
use Atournayre\Component\DotEnvEditor\Exception\DotEnvEditorAddVariableTypeException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceInvalidIpException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceIpAlreadyDefinedException;
use DateTime;
use Exception;

class MaintenanceService
{
    private static $specialIps = [
        'localhost',
    ];

    /**
     * @param string   $envPath
     * @param DateTime $startDateTime
     * @throws DotEnvEditorAddVariableTypeException
     */
    public function start(string $envPath, DateTime $startDateTime): void
    {
        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $dotEnvEditor->add('MAINTENANCE_IS_ENABLED', 'true');
        $dotEnvEditor->add('MAINTENANCE_START_DATETIME', $startDateTime->format('Y-m-d H:i:s'));
        $dotEnvEditor->save();
    }

    /**
     * @param string $envPath
     * @throws DotEnvEditorAddVariableTypeException
     */
    public function enable(string $envPath): void
    {
        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $dotEnvEditor->add('MAINTENANCE_IS_ENABLED', 'true');
        $dotEnvEditor->add('MAINTENANCE_START_DATETIME', (new DateTime())->format('Y-m-d H:i:s'));
        $dotEnvEditor->save();
    }

    /**
     * @param string $envPath
     * @throws DotEnvEditorAddVariableTypeException
     */
    public function disable(string $envPath): void
    {
        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $dotEnvEditor->add('MAINTENANCE_IS_ENABLED', 'false');
        $dotEnvEditor->save();
    }

    /**
     * @param string $envPath
     * @param string $ip
     * @throws MaintenanceInvalidIpException
     * @throws MaintenanceIpAlreadyDefinedException
     * @throws DotEnvEditorAddVariableTypeException
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

        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $dotEnvEditor->add('MAINTENANCE_AUTHORIZED_IPS', implode(',', $authorizedIps));
        $dotEnvEditor->save();
    }

    /**
     * @param string $envPath
     * @return array
     * @throws Exception
     */
    public function cleanIps(string $envPath): array
    {
        $cleanedIps = $this->listAuthorizedIps($envPath);

        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $dotEnvEditor->reset('MAINTENANCE_AUTHORIZED_IPS');
        $dotEnvEditor->save();
        return $cleanedIps;
    }

    /**
     * @param string $envPath
     * @return array
     * @throws Exception
     */
    public function listAuthorizedIps(string $envPath): array
    {
        $dotEnvEditor = new DotEnvEditor($envPath);
        $dotEnvEditor->load();
        $authorizedIps = $dotEnvEditor->get('MAINTENANCE_AUTHORIZED_IPS');
        return empty($authorizedIps)
            ? []
            : explode(',', $authorizedIps);
    }
}
