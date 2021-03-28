<?php

namespace Atournayre\MaintenanceBundle\Tests;

use Atournayre\Component\DotEnvEditor\DotEnvEditor;
use Atournayre\MaintenanceBundle\Exception\MaintenanceInvalidIpException;
use Atournayre\MaintenanceBundle\Exception\MaintenanceIpAlreadyDefinedException;
use Atournayre\MaintenanceBundle\Service\MaintenanceService;
use PHPUnit\Framework\TestCase;

class MaintenanceServiceTest extends TestCase
{
    const PATH_TO_DOTENV_DOTTEST_DOTPHP = __DIR__.'/datas/.env.test.php';
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;
    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    protected function setUp(): void
    {
        copy(__DIR__.'/datas/.env.php', self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
        $this->maintenanceService = new MaintenanceService();
        $this->dotEnvEditor = new DotEnvEditor(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
    }

    protected function tearDown(): void
    {
        unlink(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
    }

    public function testStartDateTime()
    {
        $startDateTime = new \DateTime();
        $this->maintenanceService->start(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, $startDateTime);
        $this->dotEnvEditor->load();
        $this->assertEquals(
            $startDateTime->format('Y-m-d H:i:s'),
            (new \DateTime($this->dotEnvEditor->get('MAINTENANCE_START_DATETIME')))->format('Y-m-d H:i:s')
        );
    }

    public function testEnable()
    {
        $this->maintenanceService->enable(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
        $this->dotEnvEditor->load();
        $this->assertTrue($this->dotEnvEditor->get('MAINTENANCE_IS_ENABLED'));
    }

    public function testDisable()
    {
        $this->maintenanceService->disable(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
        $this->dotEnvEditor->load();
        $this->assertFalse($this->dotEnvEditor->get('MAINTENANCE_IS_ENABLED'));
    }

    public function testAddIncorrectIpV4ThrowException()
    {
        $this->expectException(MaintenanceInvalidIpException::class);
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '192.168..');
    }

    public function testAddIncorrectIpV6ThrowException()
    {
        $this->expectException(MaintenanceInvalidIpException::class);
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '56FE::2159:5BBC::6594');
    }

    public function testAddIpV4()
    {
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '127.0.0.1');
        $this->dotEnvEditor->load();
        $this->assertEquals('127.0.0.1', $this->dotEnvEditor->get('MAINTENANCE_AUTHORIZED_IPS'));
    }

    public function testAddIpV4AlreadyAddedThrowException()
    {
        $this->expectException(MaintenanceIpAlreadyDefinedException::class);
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '127.0.0.1');
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '127.0.0.1');
    }

    public function testAddIpSpecialLocalhost()
    {
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, 'localhost');
        $this->dotEnvEditor->load();
        $this->assertEquals('localhost', $this->dotEnvEditor->get('MAINTENANCE_AUTHORIZED_IPS'));
    }

    public function testCleanIpsGetPreviousIps()
    {
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, 'localhost');
        $cleanedIps = $this->maintenanceService->cleanIps(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
        $this->assertEquals(['localhost'], $cleanedIps);

    }

    public function testCleanIpsGetNewIps()
    {
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, 'localhost');
        $this->maintenanceService->cleanIps(self::PATH_TO_DOTENV_DOTTEST_DOTPHP);
        $this->maintenanceService->addIp(self::PATH_TO_DOTENV_DOTTEST_DOTPHP, '127.0.0.1');

        $this->dotEnvEditor->load();
        $this->assertEquals('127.0.0.1', $this->dotEnvEditor->get('MAINTENANCE_AUTHORIZED_IPS'));

    }
}

