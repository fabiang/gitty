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
class Gitty_Git_Repository
{
    protected $_config;
    protected $_info = array(
        'name' => null,
        'path' => null,
        'description' => null,
        'owner' => null,
        'lastChange' => null,
        'branches' => array(),
        'remotes' => array()
    );

    public function __construct($projectName, $path, $config)
    {
        $this->_config = $config;

        $this->_info['name'] = $projectName;
        $this->_info['directory'] = $this->_info['repository'] = $this->_info['path'] = $path;

        $projectConfig = $config->$projectName;
        if (isset($projectConfig['description'])) {
            $this->_info['description'] = $projectConfig['description'];
        } else {
            $gitDirectory = $config->global['git']['defaultGitDir'];
            $descFile = $path . '/' . $gitDirectory . '/' . $config->global['git']['descriptionFile'];
            if (is_file($descFile)) {

                if (!is_readable($descFile)) {
                    require_once 'Gitty/Exception.php';
                    throw new Gitty_Exception("'$projectName' contains a description file, but it's not readable");
                }

                $this->_info['description'] = trim(strip_tags(file_get_contents($descFile)));
            }
        }

        $this->_info['owner'] = $this->getOwner();
        $this->_info['lastChangeTimestamp'] = $this->getLastChange();
        $this->_info['lastChange'] = date($config->global['gitty']['dateFormat'], $this->_info['lastChangeTimestamp']);
        $this->_info['branches'] = $this->getBranches();
        $this->_info['remotes'] = $this->getRemotes();
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function get($name, $default = null)
    {
        $result = $default;
        if (array_key_exists($name, $this->_info)) {
            $result = $this->_info[$name];
        }
        return $result;
    }

    public function getOwner()
    {
        $ownerString = Gitty_Git_Command::exec(Gitty_Git_Command::REVLIST_OWNER(), $this->get('path'), $this->_config);

        foreach ($ownerString as $output) {
            if (substr($output, 0, 9) == 'committer') {
                $results = array();
                preg_match('/^ (.+) \d+ \+\d{4}$/', substr($output, 9), $results);

                $owner = trim($results[1]);
            }
        }

        return $owner;
    }

    public function getLastChange()
    {
        $dateString = Gitty_Git_Command::exec(Gitty_Git_Command::REVLIST_LAST_CHANGE(), $this->get('path'), $this->_config);

        foreach ($dateString as $output) {
            if (substr($output, 0, 9) == 'committer') {
                $results = array();
                preg_match('/^ .+ (\d+) \+\d{4}$/', substr($output, 9), $results);

                $date = trim($results[1]);
            }
        }

        return $date;
    }

    public function getBranches()
    {
        $branchesString = Gitty_Git_Command::exec(Gitty_Git_Command::BRANCHES(), $this->get('path'), $this->_config);

        $branches = array();
        foreach($branchesString as $branch) {
            if (substr($branch, 0 ,1) == '*') {
                $branches[] = array(
                    'name' => trim(substr($branch, 1)),
                    'default' => 1
                );
            } else {
                $branches[] = array(
                    'name' => trim($branch),
                    'default' => 0
                );
            }
        }
        return $branches;
    }

    public function getRemotes()
    {
        $projectName = $this->get('name');
        $config = $this->_config;
        $projectConfig = $config->projects[$projectName];
        $deployments = $projectConfig['deployment'];

        $deploymentsData = array();
        foreach ($deployments['adapter'] as $i => $adapter) {

            $adapterName = 'Gitty_Deploy_Adapter_' . ucfirst($adapter);

            try {
                $adapterMethod = constant("$adapterName::METHOD");
            } catch(Exception $e) {
                require_once 'Gitty/Exception.php';
                throw new Gitty_Exception("'$adapter' is unknown");
            }

            $deploymentsData[] = array(
                'adapter'       => $adapter,
                'method' => $adapterMethod,
                'hostname' => $deployments['hostname'][$i],
                'username' => $deployments['username'][$i],
                'password' => $deployments['password'][$i],
                'path' => $deployments['path'][$i]
            );
        }

        return $deploymentsData;
    }
}