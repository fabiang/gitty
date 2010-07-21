<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2010 Fabian Grutschus
 *
 * This file is part of Gitty.
 *
 * Gitty is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gitty is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  ObserverInterface
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Observer/ObserverInterface
 */

/**
 * @namespace Gitty
 */
namespace Gitty\Observer;

/**
 * short hands
 */
use \Gitty\Deployment as Deployment;

/**
 * observer interface
 *
 * @category Gitty
 * @package  ObserverInterface
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Observer/ObserverInterface
 */
interface ObserverInterface
{
    /**
     * when transaction begins
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onStart(Deployment $deployment);

    /**
     * when remote repository is up-to-date
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onUpToDate(Deployment $deployment);

    /**
     * statistics
     *
     * @param Gitty\Deployment $deployment deployment object
     * @param Array            $files      files array
     *
     * @return Null
     */
    public function onStat(Deployment $deployment, $files);

    /**
     * when transaction ends
     *
     * @param Gitty\Deployment $deployment deployment object
     *
     * @return Null
     */
    public function onEnd(Deployment $deployment);
}
