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

/**
 * An {@link AbstractServiceFactory} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\ServiceManager
 */
abstract class AbstractServiceFactory implements Factory\AbstractServiceFactoryInterface
{
    /**
     * Holds the pattern value.
     *
     * @var string
     */
    protected $pattern;

    /**
     * Holds the namespace value.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Cache of canCreateServiceWithName lookup.
     *
     * @var array
     */
    protected $lookupCache = [];

    /**
     * Constructor to prevent {@link AbstractServiceFactory} from being loaded more than once.
     *
     * @param string $namespace
     * @param string $pattern
     */
    public function __construct($namespace = 'xloit', $pattern = null)
    {
        $this->namespace = $namespace;

        if (null !== $pattern) {
            $this->pattern = $pattern;
        }
    }

    /**
     * Returns the pattern value.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Sets the pattern value.
     *
     * @param string $pattern
     *
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Returns the namespace value.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the namespace value.
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * A canCreate function.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== $this->getServiceMapping($container, $requestedName);
    }
}
