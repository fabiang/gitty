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
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories
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
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories
 */
class Repositories
{
    /**
     * default adapter
     */
    public static $defaultAdapter = null;

    /**
     * instance of the adapter
     */
    protected $adapter = null;

    /**
     * this class registers all repos to this variable
     */
    protected $repositories = array();

    /**
     * config object
     */
    protected $config = null;

    /**
     * set default adapter
     *
     * @param String $adapter adapter name as string
     *
     * @return Null
     * @todo make the test better
     */
    public static function setDefaultAdapter($adapter)
    {
        if (!(new $adapter instanceof Repo\AdapterInterface)) {
            include_once dirname(__FILE__).'/Repositories/Exception.php';
            throw new Repo\Exception(
                get_class($data).' does not implement\
                Gitty\Repository\AdapterInterface interface'
            );
        }

        self::$defaultAdapter = $adapter;
    }

    /**
     * get default adapter
     *
     * @return String default adapter string
     */
    public static function getDefaultAdapter()
    {
        return self::$defaultAdapter;
    }

    /**
     * constructor
     *
     * @param Gitty\Config $config an configuration object
     */
    public function __construct(Config $config)
    {
        $projects = $config->projects;

        foreach ($projects as $project_uniqname => $project_data) {

            if (isset($project_data->adapter)) {

                try {
                    $adapter = __NAMESPACE__.'\\Repositories\\Adapter\\'
                        . ucfirst($project_data->adapter);
                    Loader::loadClass($adapter);
                } catch(\Gitty\Exception $e) {
                    include_once dirname(__FILE__).'/Repositories/Exception.php';
                    throw new Repo\Exception("adapter '$adapter' is unknown");
                }

                $repository = new $adapter($project_data);

            } else {
                $adapter = self::getDefaultAdapter();
            }

            Loader::loadClass($adapter);
            $repository = new $adapter($project_data);

            foreach ($project_data->deployment as $deployment_name =>
                     $deployment_data) {

                $remote = new \Gitty\Remote($deployment_data);
                $repository->registerRemote($remote);

            }

            $this->register($repository);
        }
    }

    /**
     * get registered repository adapters
     *
     * @return Array repositoriy adapter objects in array
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * register an adapter
     *
     * @param Gitty\Repositories\AdapterAbstract $repository an repository object
     *
     * @return Null
     */
    public function register(Repo\AdapterAbstract $repository)
    {
        $this->repositories[] = $repository;
    }

    /**
     * unregister an adapter
     *
     * @param Gitty\Repositories\AdapterAbstract $repository an repository object
     *
     * @return Boolean true if adapter is found, otherwise false
     */
    public function unregister(Repo\AdapterAbstract $repository)
    {
        $index = array_search($repository, $this->repositories, true);
        if ($index !== false) {
            unset($this->repositories[$index]);
            return true;
        }

        return false;
    }
}

$class = __NAMESPACE__.'\\Repositories';
$class::$defaultAdapter = __NAMESPACE__.'\\Repositories\\Adapter\\Git';
