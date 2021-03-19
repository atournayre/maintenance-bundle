<?php

namespace Atournayre\MaintenanceBundle\Listener;

use Atournayre\MaintenanceBundle\Service\MaintenanceChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    const TEMPLATE = '@AtournayreMaintenance/index.html.twig';
    const TEMPLATE_ERROR = '@AtournayreMaintenance/error.html.twig';

    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var MaintenanceChecker
     */
    private $maintenanceChecker;

    public function __construct(Environment $environment, MaintenanceChecker $maintenanceChecker)
    {
        $this->environment = $environment;
        $this->maintenanceChecker = $maintenanceChecker;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        try {
            $siteIsUndetMaintenance = $this->maintenanceChecker->maintenanceIsEnabled();
            if ($siteIsUndetMaintenance) {
                $template = $this->environment->render(self::TEMPLATE);
                $event->setResponse(new Response($template, 503));
            }
        } catch (\Exception $exception) {
            $template = $this->environment->render(self::TEMPLATE_ERROR, [
                'message' => $exception->getMessage(),
            ]);
            $event->setResponse(new Response($template, 503));
        }
    }
}