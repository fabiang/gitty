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
 * @namespace Gitty\Repository
 */
namespace Gitty\Repositories;

/**
 * Adapters need to implement this interface
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
abstract class AdapterAbstract
{
    protected $_name = '';
    protected $_description = '';
    protected $_path = '';
    protected $_showBranches = true;

    protected $_remotes = array();

    public function __construct($options)
    {
        $this->setOptions($options);

        $this->_showBranches = isset($options->showBranches) && !!$options->showBranches ? true : false;
    }

    public function setOptions($options)
    {
        $methods = \get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);
            if (\in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function getOptions()
    {
        $instance_methods = \get_class_methods($this);
        $inherited_methods = \array_flip(\get_class_methods(__CLASS__));

        $options = array();
        $options['id'] = $this->getId();

        foreach($instance_methods as $method_name) {
            if (\substr($method_name, 0, 3) === 'get' && !isset($inherited_methods[$method_name])) {
                $column_name = \substr($method_name, 3);
                $column_name = \strtolower(\substr($column_name, 0, 1)) . \substr($column_name, 1);
                $options[$column_name] = $this->$method_name();
            }
        }

        return $options;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getDescription()
    {
        return $this->_description;
    }
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function getPath()
    {
        return $this->_path;
    }
    public function setPath($path)
    {
        $this->_path = $path;
    }

    public function showBranches()
    {
        return $this->_showBranches;
    }

    public function getRemotes()
    {
        return $this->_remotes;
    }
    public function registerRemote(\Gitty\Remotes\AdapterAbstract $remote)
    {
        $this->_remotes[] = $remote;
    }
    public function unregisterRemote(\Gitty\Remotes\AdapterAbstract $remote)
    {
        $index = array_search($remote, $this->_remotes, true);
        unset($this->_remotes[$index]);
        return !!$index;
    }

    abstract public function getOwner();
    abstract public function setOwner($owner);

    abstract public function getLastChange();
    abstract public function setLastChange(\DateTime $datetime);

    abstract public function getBranches();
    abstract public function setBranches($branches);
}
