<?php
/*
 * This file is part of the CSBill package.
 *
 * (c) Pierre du Plessis <info@customscripts.co.za>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSBill\InstallBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CSBill\InstallBundle\Installer\Installer;

/**
 * Listener class to intercept requests
 * and redirect to the installer if necessary
 */
class RequestListener
{
    /**
     * @var ContainerInterface $container
     */
    public $container;

    /**
     * Core paths for assets
     *
     * @var array $core_paths
     */
    protected $core_paths = array('css', 'images', 'js');

    /**
     * Core routes
     *
     * @var array $core_routes
     */
    protected $core_routes = array(	Installer::INSTALLER_ROUTE,
                                    Installer::INSTALLER_SUCCESS_ROUTE,
                                    Installer::INSTALLER_RESTART_ROUTE,
                                    '_installer_step',
                                    '_profiler',
                                    '_wdt'
                                  );

    /**
     * @DI\Observe("kernel.request", priority = 10)
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $event->getRequest()->get('_route');

        $map = array_map(function($route) use ($event) {
                return strpos($event->getRequest()->getPathInfo(), $route);
            }, $this->core_paths);

        if (!in_array($route, $this->core_routes) && !in_array(true, $map) && $event->getRequestType() === HttpKernel::MASTER_REQUEST) {

            $installer = $this->container->get('csbill.installer');

            if (!$installer->isInstalled()) {
                $response = $installer->getRedirectResponse();

                return $event->setResponse($response);
            }

            return null;
        }
    }

    /**
     * Sets an instance of the service container
     *
     * @param  ContainerInterface $container
     * @return void
     */

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
