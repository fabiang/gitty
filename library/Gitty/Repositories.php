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
namespace Gitty;

/**
 * short hands
 */
use \Gitty\Repositories as Repo;
use \Gitty\Config as Config;
use \Gitty\Loader as Loader;

/**
 * repository class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Repositories
{
    /**
     * default adapter
     */
    protected static $_default_adapter = null;

    /**
     * instance of the adapter
     */
    protected $_adapter = null;

    /**
     * this class registers all repos to this variable
     */
    protected $_repositories = array();

    /**
     * config object
     */
    protected $_config = null;

    protected static function _getDefaultAdapter()
    {
        if (self::$_default_adapter === null) {
            self::$_default_adapter = __NAMESPACE__.'\\Repositories\\Adapter\\Git';
        }

        return self::$_default_adapter;
    }

    /**
     * set default adapter
     */
    public static function setDefaultAdapter($adapter)
    {
        if (!(new $adapter instanceof Repo\AdapterInterface)) {
            require_once dirname(__FILE__).'/Repositories/Exception.php';
            throw new Repo\Exception(get_class($data).' does not implement Gitty\Repository\AdapterInterface interface');
        }

        self::$_default_adapter = $adapter;
    }

    /**
     * get default adapter
     */
    public static function getDefaultAdapter()
    {
        return self::_getDefaultAdapter();
    }

    public function getRepositories()
    {
        return $this->_repositories;
    }

    public function register(Repo\AdapterAbstract $repository)
    {
        $this->_repositories[] = $repository;
    }

    public function __construct(Config $config)
    {
        $projects = $config->projects;

        foreach($projects as $project_uniqname => $project_data) {

            if (isset($project_data->adapter)) {

                try {
                    $adapter = __NAMESPACE__.'\\Repositories\\Adapter\\' . ucfirst($project_data->adapter);
                    Loader::loadClass($adapter);
                } catch(\Gitty\Exception $e) {
                    require_once dirname(__FILE__).'/Repositories/Exception.php';
                    throw new Repo\Exception("adapter '$adapter' is unknown");
                }

                $repository = new $adapter($project_data);

            } else {
                $adapter = self::_getDefaultAdapter();
            }

            Loader::loadClass($adapter);
            $repository = new $adapter($project_data);

            foreach($project_data->deployment as $deployment_name => $deployment_data) {

                $remote = new \Gitty\Remote($deployment_data);
                $repository->registerRemote($remote);

            }

            $this->register($repository);
        }
    }
}
