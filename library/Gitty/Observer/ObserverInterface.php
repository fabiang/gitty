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
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
interface ObserverInterface
{
    /**
     * when transaction begins
     */
    public function onStart(Deployment $deployment);

    /**
     * when remote repository is up-to-date
     */
    public function onUpToDate(Deployment $deployment);

    /**
     * statistics
     */
    public function onStat(Deployment $deployment, $files);

    /**
     * when transaction ends
     */
    public function onEnd(Deployment $deployment);
}
