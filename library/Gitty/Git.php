<?php
class Gitty_Git
{
    protected $_gittyConfig;
    protected $_repositoriesDirectories = array();
    protected $_repositories = array();

    public function __construct($config)
    {
        $this->_gittyConfig = $config;

        if (isset($config->global['git']) && isset($config->global['git']['directories'])) {
            $global_directories = $config->global['git']['directories'];
            foreach ($global_directories as $global_directory) {
                if (!is_dir($global_directory)) {
                    require_once 'Gitty/Exception.php';
                    throw new Gitty_Exception("global directory $global_directory doesn't exist");
                }

                if (!is_readable($global_directory)) {
                    require_once 'Gitty/Exception.php';
                    throw new Gitty_Exception("global directory $global_directory isn't readable");
                }

                $subdirectories = scandir($global_directory);

                foreach($subdirectories as $subdirectory) {
                    $dir = $global_directory . '/' . $subdirectory;
                    if ($subdirectory != '.' && $subdirectory != '..' && is_dir($dir)) {
                        if (is_dir($dir . '/' . $config->global['git']['defaultGitDir'])) {
                            $this->loadRepository($dir);
                        }
                    }
                }
            }
        }

        if (isset($config->projects)) {
            foreach ($config->projects as $projectName => $projectConfig) {
                if (!isset($projectConfig['repository']) && !isset($projectConfig['directory'])) {
                    require_once 'Gitty/Exception.php';
                    throw new Gitty_Exception("no directory for '$projectName' defined");
                }

                $dir = isset($projectConfig['repository']) ? $projectConfig['repository'] : $projectConfig['directory'];
                $gitDirectory = $config->global['git']['defaultGitDir'];

                if (!is_dir($dir . '/' . $gitDirectory)) {
                    require_once 'Gitty/Exception.php';
                    throw new Gitty_Exception("Not a git repository '$projectName': $gitDirectory");
                }

                $this->loadRepository($dir, $projectName);
            }
        }
    }

    public function getRepositoriesDirectories()
    {
        return $this->_repositoriesDirectories;
    }

    public function getRepositories()
    {
        return $this->_repositories;
    }

    public function loadRepository($path, $projectName = null)
    {
        if (!$projectName) {
            $projectName = basename($path);
        }

        $this->_repositoriesDirectories[$projectName] = $path;

        array_push($this->_repositories, new Gitty_Git_Repository($projectName, $path, $this->_gittyConfig));
    }
}