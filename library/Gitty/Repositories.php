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
use \Gitty as Gitty;
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
    protected static $defaultAdapter = null;

    /**
     * namespaces
     */
    protected static $registeredAdapterNamespaces = array();

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
     */
    public static function setDefaultAdapter($adapter)
    {
        self::$defaultAdapter = $adapter;
    }

    /**
     * get default adapter
     *
     * @return String default adapter string
     */
    public static function getDefaultAdapter()
    {
        if (null === self::$defaultAdapter) {
            self::$defaultAdapter = __NAMESPACE__.'\\Repositories\\Adapter\\Git';
        }

        return self::$defaultAdapter;
    }

    /**
     * register a namespace for remotes
     *
     * @param String $namespace namespace name
     *
     * @return Boolean false when already registered, overwise true
     */
    public static function registerAdapterNamespace($adapter)
    {
        if (!\in_array($adapter, self::$registeredAdapterNamespaces)) {
            self::$registeredAdapterNamespaces[] = $adapter;
            return true;
        }

        return false;
    }

    /**
     * unregsiter a namespace for remotes
     *
     * @param String $namespace namespace name
     *
     * @return Boolean ture when removed, overwise false
     */
    public static function unregisterAdapterNamespace($adapter)
    {
        $index = \array_search($adapter, self::$registeredAdapterNamespaces);
        if (false !== $index) {
            unset(self::$registeredAdapterNamespaces[$index]);
            return true;
        }

        return false;
    }

    /**
     * returns all registered namespaces
     *
     * @return Array list of namespaces
     */
    public static function getAdapterNamespaces()
    {
        return self::$registeredAdapterNamespaces;
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

                $adapter = __NAMESPACE__.'\\Repositories\\Adapter\\' .
                    ucfirst($project_data->adapter);

                // first try loading any of the own adapters
                try {
                    Loader::loadClass($adapter);
                } catch(Exception $e) {
                    // throw exception when there are no registered namespaces
                    if (0 === \count(self::getAdapterNamespaces())) {
                        include_once \dirname(__FILE__).'/Repositories/Exception.php';
                        throw new Repositories\Exception(
                            "can't load adapter '{$config->adapter}' and no namespaces \
                            registered for lookup"
                        );
                    }

                    // try loading the adapter from the registered namespaces
                    $found = false;
                    $search = 0;
                    foreach(self::getAdapterNamespaces() as $namespace) {
                        try {
                            $class = $namespace.'\\'.\ucfirst($config->adapter);
                            Loader::loadClass($class);
                            $adapter = $class;
                            $found = true;
                        } catch (Exception $e) {
                            $search++;
                        }
                    }

                    // didn't find the adapter in any registered namespace
                    // throw exception
                    if (false === $found) {
                        include_once \dirname(__FILE__).'/Repositories/Exception.php';
                        throw new Repositories\Exception(
                            "can't load any adapter of the name {$config->adapter}. \
                            searched in $search namespaces: " .
                            \implode(\PATH_SEPARATOR, self::getAdapterNamespaces())
                        );
                    }
                }
            } else {
                $adapter = self::getDefaultAdapter();

                try {
                    Loader::loadClass($adapter);
                } catch(Exception $e) {
                    include_once \dirname(__FILE__).'/Repositories/Exception.php';
                    throw new Repositories\Exception(
                        "default adapter '$adapter' can't be found"
                    );
                }
            }

            $repository = new $adapter($project_data);

            if (!($repository instanceof Repo\AdapterAbstract)) {
               include_once \dirname(__FILE__).'/Repositories/Exception.php';
               throw new Repo\Exception(
                   "$adapter does not extend Gitty\Repositories\AdapterInterface"
               );
            }

            if (isset($project_data->deployment)) {
                foreach ($project_data->deployment as $deployment_name =>
                         $deployment_data) {

                    $remote = new \Gitty\Remote($deployment_data);
                    $repository->registerRemote($remote);

                }
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
