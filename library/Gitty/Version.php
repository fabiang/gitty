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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Version
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Version
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * Gitty version
 *
 * @category Gitty
 * @package  Gitty
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/version
 */
final class Version
{
    const VERSION = '0.2';

    /**
     * compare version with Gittys version
     *
     * @param String $version the version to compare with
     *
     * @see http://php.net/version_compare
     * @return Mixed
     */
    public static function compareVersion($version)
    {
        return \version_compare($version, self::VERSION);
    }
}
