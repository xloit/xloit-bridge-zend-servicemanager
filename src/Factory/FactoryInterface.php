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

namespace Xloit\Bridge\Zend\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface as ZendFactoryInterface;

/**
 * A {@link FactoryInterface} interface.
 *
 * @package Xloit\Bridge\Zend\ServiceManager\Factory
 */
interface FactoryInterface extends ZendFactoryInterface
{
    /**
     *
     *
     * @param ContainerInterface $container
     *
     * @return static
     */
    public function setContainer(ContainerInterface $container);

    /**
     *
     *
     * @param string  $name
     * @param boolean $factory
     *
     * @return mixed
     */
    public function getOption($name, $factory = true);

    /**
     *
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasOption($name);

    /**
     * Gets options from configuration based on name.
     *
     * @param boolean $nullAble
     *
     * @return array
     */
    public function getOptions($nullAble = true);
}
