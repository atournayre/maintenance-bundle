<?php

namespace Atournayre\MaintenanceBundle\Command;

use Atournayre\MaintenanceBundle\Exception\MaintenanceIpAlreadyDefinedException;
use Atournayre\MaintenanceBundle\Service\MaintenanceService;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class MaintenanceCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'maintenance';
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(KernelInterface $kernel, MaintenanceService $maintenanceService, ParameterBagInterface $parameterBag)
    {
        parent::__construct(self::$defaultName);
        $this->kernel = $kernel;
        $this->maintenanceService = $maintenanceService;
        $this->parameterBag = $parameterBag;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Manage maintenance for your application')
            ->addArgument('envFile', null, InputArgument::REQUIRED, '.env.local.php')
            ->addOption('start', null, InputOption::VALUE_OPTIONAL, 'Schedule maintenance for specified date time (format : Y-m-d H:i:s)')
            ->addOption('enable', null, InputOption::VALUE_NONE, 'Enable maintenance')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable maintenance')
            ->addOption('add-ip', null, InputOption::VALUE_REQUIRED, 'Add IP address to existing ones')
            ->addOption('clean-ips', null, InputOption::VALUE_NONE, 'Remove all authorized IPs addresses')
            ->addOption('dump-ips', null, InputOption::VALUE_NONE, 'Dump all authorized IPs addresses')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Dump configuration')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $optionStart = $input->getOption('start');
        $optionAddIp = $input->getOption('add-ip');
        $optionDebug = $input->getOption('debug');

        $envPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.$input->getArgument('envFile');

        if (!is_null($optionStart)) {
            $this->start($io, $envPath, $optionStart);
        }

        if (true === $input->getOption('enable')) {
            $this->enable($io, $envPath);
        }

        if (true === $input->getOption('disable')) {
            $this->disable($io, $envPath);
        }

        if (true === $input->getOption('clean-ips')) {
            $this->cleanIps($io, $envPath);
        }

        if (!is_null($optionAddIp)) {
            $this->addIp($io, $envPath, $optionAddIp);
        }

        if (true === $input->getOption('dump-ips')) {
            $this->dumpIps($io, $envPath);
        }

        if (true === $optionDebug) {
            $this->debug($io, $envPath);
        }

        return 0;
    }

    private function start(SymfonyStyle $io, string $envPath, string $start)
    {
        try {
            $startDateTime = new DateTime($start);
            $this->maintenanceService->start($envPath, $startDateTime);
            $io->success(sprintf('Maintenance is scheduled for %s.', $startDateTime->format('F jS \\a\\t g:ia')));
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function enable(SymfonyStyle $io, string $envPath): void
    {
        try {
            $this->maintenanceService->enable($envPath);
            $io->success('Maintenance is enabled.');
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function disable(SymfonyStyle $io, string $envPath): void
    {
        try {
            $this->maintenanceService->disable($envPath);
            $io->success('Maintenance is disabled.');
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function addIp(SymfonyStyle $io, string $envPath, string $ip): void
    {
        try {
            $this->maintenanceService->addIp($envPath, $ip);
            $io->success(sprintf('%s has been added to authorized ips.', $ip));
        } catch (MaintenanceIpAlreadyDefinedException $exception) {
            $io->caution($exception->getMessage());
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function cleanIps(SymfonyStyle $io, string $envPath): void
    {
        try {
            $cleanedIps = $this->maintenanceService->cleanIps($envPath);
            if (count($cleanedIps) !== 0) {
                $io->writeln('Successfully remove following ips:');
                foreach ($cleanedIps as $authorizedIp) {
                    $io->writeln(sprintf('- %s', $authorizedIp));
                }
            }
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function dumpIps(SymfonyStyle $io, string $envPath): void
    {
        try {
            $authorizedIps = $this->maintenanceService->listAuthorizedIps($envPath);
            if (count($authorizedIps) === 0) {
                $io->comment('There are no authorized ips.');
            } else {
                $io->writeln('Authorized ips:');
                foreach ($authorizedIps as $authorizedIp) {
                    $io->writeln(sprintf('- %s', $authorizedIp));
                }
            }
        } catch (Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function debug(SymfonyStyle $io, string $envPath)
    {
        $io->title('Maintenance debug');
        if (!$this->parameterBag->has('atournayre_maintenance.is_enabled')) {
            $io->error('"atournayre_maintenance.is_enabled" is missing in services.yaml.');
        }
        if (!$this->parameterBag->has('atournayre_maintenance.start_date_time')) {
            $io->error('"atournayre_maintenance.start_date_time" is missing in services.yaml.');
        }
        if (!$this->parameterBag->has('atournayre_maintenance.authorized_ips')) {
            $io->error('"atournayre_maintenance.authorized_ips" is missing in services.yaml.');
        }

        $output = [];
        array_push($output, ['Application is', $this->parameterBag->get('atournayre_maintenance.is_enabled') ? '<fg=yellow>under maintenance</>' : '<fg=green>live</>']);
        array_push($output, new TableSeparator());

        $startDateTime = new DateTime($this->parameterBag->get('atournayre_maintenance.start_date_time'));
        array_push($output, ['Start (next/current)', $startDateTime->format('F jS \\a\\t g:ia')]);
        array_push($output, new TableSeparator());

        array_push($output, [
            'Authorized IPs',
            str_replace(',', PHP_EOL, $this->parameterBag->get('atournayre_maintenance.authorized_ips'))
        ]);

        $io->table([], $output);
    }
}
