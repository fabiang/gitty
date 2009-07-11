<?php
/**
 *   This file is part of Gitty.
 *
 *   Gitty is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Foobar is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */
abstract class Gitty_Deploy_Adapter_Messages
{
    abstract public static function start($install, $method);
    abstract public static function modify($file);
    abstract public static function add($file);
    abstract public static function delete($file);
    abstract public static function copy($file);
    abstract public static function rename($file);
    abstract public static function end($install, $method);

    final public static function stat($stat)
    {
        return $stat;
    }

    final public static function startAdd()
    {
        return 'add files...';
    }

    final public static function startModify()
    {
        return 'modified files...';
    }

    final public static function startDelete()
    {
        return 'deleting files...';
    }

    final public static function startCopy()
    {
        return 'copy files...';
    }

    final public static function startRename()
    {
        return 'rename files...';
    }

    final public static function revFile($file)
    {
        return 'write revisition file "'.$file.'"...';
    }

    final public static function upToDate()
    {
        return 'everything up-to-date... nothing to do';
    }
}