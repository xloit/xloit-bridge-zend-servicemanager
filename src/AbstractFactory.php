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
use ReflectionProperty;
use Xloit\Std\ArrayUtils;

/**
 * An {@link AbstractFactory} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\ServiceManager
 */
abstract class AbstractFactory implements Factory\FactoryInterface
{
    /**
     * Holds the prefixServiceName value.
     *
     * @var string
     */
    protected $namespace = 'xloit';

    /**
     *
     *
     * @var string
     */
    protected $category;

    /**
     *
     *
     * @var string
     */
    protected $serviceName = 'default';

    /**
     *
     *
     * @var array
     */
    protected $options;

    /**
     *
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor to prevent {@link AbstractFactory} from being loaded more than once.
     *
     * @param string $category
     * @param string $serviceName
     * @param string $namespace
     */
    public function __construct($category, $serviceName = 'default', $namespace = 'xloit')
    {
        $this->category    = $category;
        $this->serviceName = $serviceName;
        $this->namespace   = $namespace;
    }

    /**
     *
     *
     * @param ContainerInterface $container
     *
     * @return $this
     * @throws \ReflectionException
     */
    public function setContainer(ContainerInterface $container)
    {
        $creationContext = static::getPeerContainer($container);

        if (!$creationContext) {
            $creationContext = $container;
        }

        $this->container = $creationContext;

        return $this;
    }

    /**
     * Sets the value of Options.
     *
     * @param array $options
     *
     * @return static
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param bool $nullAble
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function getOptions($nullAble = true)
    {
        if ($this->options) {
            return $this->options;
        }

        $namespace   = $this->namespace;
        $category    = $this->category;
        $serviceName = $this->serviceName;

        $config = $this->container->get('Config');
        /** @var array $options */
        $options = ArrayUtils::get(
            $config,
            sprintf('%s.%s.%s', $namespace, $category, $serviceName),
            $nullAble ? [] : null,
            $nullAble
        );

        if (!$options && !$nullAble) {
            throw new Exception\StateException(
                sprintf('Options could not be found in "%s.%s".', $namespace, $category)
            );
        }

        $this->options = $options;

        return $options;
    }

    /**
     *
     *
     * @param string $name
     *
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function hasOption($name)
    {
        $options = $this->getOptions();

        return array_key_exists($name, $options);
    }

    /**
     *
     *
     * @param string $name
     * @param bool   $factory
     *
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     */
    public function getOption($name, $factory = true)
    {
        if (!$this->hasOption($name)) {
            return null;
        }

        $options = $this->options;
        $option  = null;

        if (array_key_exists($name, $options)) {
            $option = $options[$name];

            if ($factory && is_string($option)) {
                $option = $this->container->get($option);
            }
        }

        return $option;
    }

    /**
     *
     *
     * @param ContainerInterface $container
     *
     * @return ContainerInterface
     * @throws \ReflectionException
     */
    public static function getPeerContainer(ContainerInterface $container)
    {
        $peerContext = null;

        // v3
        if (method_exists($container, 'configure')) {
            $r = new ReflectionProperty($container, 'creationContext');

            $r->setAccessible(true);

            $peerContext = $r->getValue($container);
        } elseif (class_exists('\\Zend\\ServiceManager\\ServiceLocatorAwareInterface')) {
            // v2
            $peerContext = $container;

            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            while ($peerContext instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                /** @noinspection PhpUndefinedMethodInspection */
                $peerContext = $peerContext->getServiceLocator();
            }
        }

        return $peerContext;
    }
}
