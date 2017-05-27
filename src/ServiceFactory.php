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
use ReflectionClass;
use Xloit\Std\ArrayUtils;
use Zend\ServiceManager\Factory\FactoryInterface as ZendFactoryInterface;

/**
 * A {@link ServiceFactory} abstract class.
 *
 * @package Xloit\Bridge\Zend\ServiceManager
 */
class ServiceFactory extends AbstractServiceFactory
{
    /**
     * Constructor to prevent {@link ServiceFactory} from being loaded more than once.
     *
     * @param string $namespace
     * @param string $pattern
     */
    public function __construct(
        $namespace = 'xloit',
        $pattern = "/^PREFIX_SERVICE_NAME\.(?P<namespace>[a-zA-Z0-9_]+)\.(?P<serviceName>[a-zA-Z0-9_]+)$/"
    ) {
        parent::__construct($namespace, $pattern);
    }

    /**
     * Create an object.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $mappings */
        $mappings = $this->getServiceMapping($container, $requestedName);

        if (!$mappings) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $requestedName
                )
            );
        }

        if (array_key_exists('factoryInstance', $mappings)) {
            return $this->createServiceFactory($requestedName, $mappings['reflection'], $container);
        }

        if (empty($mappings['reflection'])) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $requestedName
                )
            );
        }

        /** @var ReflectionClass $reflection */
        $reflection = $mappings['reflection'];

        /** @noinspection PhpDeprecationInspection */
        if (!$reflection->implementsInterface(ZendFactoryInterface::class)) {
            throw new Exception\ServiceNotFoundException(
                sprintf(
                    'An alias "%s" should be instance of %s. The service class name is %s',
                    $requestedName,
                    ZendFactoryInterface::class,
                    $reflection->getName()
                )
            );
        }

        return $this->createServiceFactory($requestedName, $reflection, $container);
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return boolean|array
     * @throws \ReflectionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function getServiceMapping(ContainerInterface $container, $name)
    {
        if (array_key_exists($name, $this->lookupCache)) {
            return $this->lookupCache[$name];
        }

        $matches = [];
        $pattern = str_replace('PREFIX_SERVICE_NAME', $this->namespace, $this->pattern);

        if (!preg_match($pattern, $name, $matches)) {
            $this->lookupCache[$name] = false;

            return false;
        }

        if (!array_key_exists('namespace', $matches) || !$matches['namespace']) {
            $this->lookupCache[$name] = false;

            return false;
        }

        /** @var array $config */
        $config = $container->get('Config');

        if (!array_key_exists($this->namespace, $config)) {
            $this->lookupCache[$name] = false;

            return false;
        }

        $config = $config[$this->namespace];

        if (!array_key_exists('serviceManager', $config) || !$config['serviceManager']) {
            $this->lookupCache[$name] = false;

            return false;
        }

        $serviceConfig = $config['serviceManager'];
        $namespace     = $matches['namespace'];
        $serviceName   = 'default';

        if (array_key_exists('serviceName', $matches) || !$matches['serviceName']) {
            $serviceName = $matches['serviceName'];
        }

        $factory = ArrayUtils::get($serviceConfig, sprintf('%s.%s', $namespace, $serviceName));

        if (!$factory) {
            $serviceName = $namespace;
            $factory     = ArrayUtils::get($serviceConfig, sprintf('%s.%s', $namespace, $serviceName));
        }

        // We've an invalid configuration somehow
        if (!$factory) {
            $this->lookupCache[$name] = null;

            return false;
        }

        $serviceMapping = [
            'namespace'  => $namespace,
            'service'    => $serviceName,
            'factory'    => $factory,
            'reflection' => new ReflectionClass($factory)
        ];

        $this->lookupCache[$name] = $serviceMapping;

        return $serviceMapping;
    }

    /**
     * Initiate service factory from the ReflectionClass.
     *
     * @param string             $requestedName
     * @param ReflectionClass    $reflection
     * @param ContainerInterface $container
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    protected function createServiceFactory(
        $requestedName, ReflectionClass $reflection, ContainerInterface $container
    ) {
        /** @var array $mappings */
        $mappings = $this->getServiceMapping($container, $requestedName);

        if ($reflection->implementsInterface(Factory\FactoryInterface::class)) {
            $factory = $reflection->newInstance($mappings['namespace'], $mappings['service'], $this->namespace);
        } else {
            $factory = $reflection->newInstance();
        }

        $this->lookupCache[$requestedName]['factoryInstance'] = $factory;

        if ($factory instanceof Factory\FactoryInterface) {
            /** @var Factory\FactoryInterface $factory */
            $factory->setContainer($container);
        }

        return $factory($container, $requestedName);
    }
}
