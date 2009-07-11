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
class Gitty_Config_Default
{
    static $DEFAULT = array(
        'global' => array(
            'git' => array(
                'defaultGitDir' => '.git',
                'descriptionFile' => 'description',
                'binLocation'=> '/usr/bin/git'
            ),
            'gitty' => array(
                'readDirectories' => '0',
                'dateFormat' => 'Y-m-d H:i:s',
                'revistionFile' => 'revision.txt',
                'tempDir' => '/tmp'
            )
        )
    );
}