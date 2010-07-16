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
 * storthands
 */
use \Gitty as G;

/**
 * ftp remote adapter
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Ftp extends G\Remote\AdapterAbstract
{
    /**
     * options for context
     */
    protected $_options = array('ftp' => array('overwrite' => true));

    /**
     * hostname
     */
    protected $_hostname = null;

    /**
     * port default for ftp 21
     */
    protected $_port = 21;

    /**
     * username
     */
    protected $_username = null;

    /**
     * password
     */
    protected $_password = null;

    /**
     * path
     */
    protected $_path = null;

    /**
     * constructor
     *
     * @param \Gitty\Config $config configuration object
     * @param Array $options options for context
     * @todo handle option when it is an array
     */
    public function __construct(G\Config $config, $options = null)
    {
        $this->_hostname = $config->hostname;
        if (isset($config->port)) {
            $this->_port = $config->port;
        }
        $this->_username = isset($config->username) ? $config->username : 'anonymous';
        $this->_password = isset($config->password) ? $config->password : '';
        $this->_path = isset($config->path) ? $config->path : '/';

        if ($config->revisitionFileName) {
            $this->_revisitionFileName = $config->revisitionFileName;
        }

        if ($options !== null) {
            $this->_options['ftp'] = $options;
        }
    }

    /**
     * __toString
     *
     * @return String representation of the object as array
     */
    public function __toString()
    {
        return '(FTP) ' . $this->_hostname . ':' . $this->_port;
    }

    /**
     * initialize adapter
     */
    public function init()
    {
        $this->_url = \sprintf('ftp://%s:%s@%s:%d%s', $this->_username, $this->_password, $this->_hostname, $this->_port, $this->_path);
        $this->_context = \stream_context_create($this->_options);
    }
}
