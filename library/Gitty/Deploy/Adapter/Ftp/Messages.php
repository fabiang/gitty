<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2009 Fabian Grutschus
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
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */
class Gitty_Deploy_Adapter_Ftp_Messages extends Gitty_Deploy_Adapter_Messages
{
    public static function start($install, $method)
    {
        return 'Starting ' . ($install ? 'Install' : 'Update') . ' with Method "' . $method .'"';
    }

    public static function modify($file)
    {
        return 'Replace "'. $file .'"';
    }

    public static function add($file)
    {
        return 'Uploading "'. $file .'"';
    }

    public static function delete($file)
    {
        return 'Delete "'. $file .'"';
    }

    public static function copy($file)
    {
        return 'Copy "'. $file .'"';
    }

    public static function rename($file)
    {
        return 'Rename "'. $file .'"';
    }

    public static function end($install, $method)
    {
        return 'Finished ' . ($install ? 'Install' : 'Update') . ' with Method "' . $method .'"';
    }
}