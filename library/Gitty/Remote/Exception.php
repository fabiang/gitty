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
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace Gitty\Remote
 * @license http://www.gnu.org/licenses/gpl.html
 */
namespace Gitty\Remote;

/**
 * make sure Gitty\Exception is available
 */
require_once dirname(__FILE__) . '/../Exception.php';

/**
 * class for config exceptions
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Exception extends \Gitty\Exception
{
}
