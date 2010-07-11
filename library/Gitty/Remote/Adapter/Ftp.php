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
 * @namespace Gitty
 */
namespace Gitty\Remote\Adapter;

/**
 * ftp remote class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Ftp extends \Gitty\Remote\AdapterAbstract
{
    protected $options = array(
        'ftp' => array(
            'overwrite' => true
        )
    );

    protected $hostname = null;
    protected $username = null;
    protected $password = null;
    protected $path = null;

    public function __construct(\Gitty\Config $config)
    {
        $this->hostname = $config->hostname;
        $this->username = isset($config->username) ? $config->username : 'anonymous';
        $this->password = isset($config->password) ? $config->password : '';
        $this->path = isset($config->path) ? $config->path : '/';
    }

    public function init()
    {
        $this->_url = sprintf('ftp://%s:%s@%s%s', $this->username, $this->password, $this->hostname, $this->path);
        $this->_context = stream_context_create($this->options);
    }
}
