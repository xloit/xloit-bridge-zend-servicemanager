<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Zend\ServiceManager;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * An {@link AbstractServiceInitializer} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\ServiceManager
 */
abstract class AbstractServiceInitializer implements InitializerInterface
{
    /**
     *
     *
     * @return array
     */
    protected $skipped = [];

    /**
     *
     *
     * @return ContainerInterface
     */
    protected $peerContainer;

    /**
     *
     *
     * @return string
     */
    private $instanceName;

    /**
     * Initialize the given instance.
     *
     * @param  ContainerInterface $container
     * @param  mixed              $instance
     *
     * @return void
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (!is_object($instance)) {
            return;
        }

        $method            = $this->getMethods();
        $awareInterface    = $this->getAwareInstanceInterface();
        $instanceInterface = $this->getInstanceInterface();
        $hashInstance      = get_class($instance) . '@' . spl_object_hash($instance);

        // TODO: Benchmark, please
        if ($instance instanceof $awareInterface && !array_key_exists($hashInstance, $this->skipped)) {
            $instanceValue = null;

            // Some vendor will try to throw an exception
            try {
                $instanceValue = $instance->{$method['getter']}();
            } catch (\Exception $exception) {
            }

            // Maybe it was initialized by factory
            if ($instanceValue && is_string($instanceInterface) && $instanceValue instanceof $instanceInterface) {
                $this->skipped[$hashInstance] = true;

                return;
            }

            if ($this->peerContainer === null) {
                $this->peerContainer = AbstractFactory::getPeerContainer($container);
            }

            $instance->{$method['setter']}($this->getServiceInstance($container));
        }
    }

    /**
     *
     *
     * @return array
     */
    abstract protected function getMethods();

    /**
     *
     *
     * @return string
     */
    abstract protected function getAwareInstanceInterface();

    /**
     *
     *
     * @return string
     */
    abstract protected function getInstanceInterface();

    /**
     *
     *
     * @return array
     */
    abstract protected function getServiceNames();

    /**
     *
     *
     * @param ContainerInterface $container
     *
     * @return mixed
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getServiceInstance(ContainerInterface $container)
    {
        $peerContainer = $this->peerContainer;
        $usePeer       = false;

        if (!$this->instanceName) {
            $serviceNames = (array) $this->getServiceNames();
            $serviceName  = null;

            while (count($serviceNames) > 0) {
                $serviceName = array_shift($serviceNames);

                if ($container->has($serviceName)) {
                    break;
                } elseif (
                    $peerContainer
                    && $peerContainer instanceof ServiceLocatorInterface
                    && $peerContainer->has($serviceName)
                ) {
                    $usePeer = true;

                    break;
                }
            }

            $this->instanceName = $serviceName;
        } else if (
            $peerContainer
            && $peerContainer instanceof ServiceLocatorInterface
            && $peerContainer->has($this->instanceName)
        ) {
            $usePeer = true;
        }

        return $usePeer ? $peerContainer->get($this->instanceName)
            : $container->get($this->instanceName);
    }
}
