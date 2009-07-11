<?php
abstract class Gitty_Deploy_Adapter_Abstract
{
    protected $_stream;
    protected $_options;
    protected $_url;
    protected $_newestRevisitionId;
    protected $_oldestRevisitionId;
    protected $_remoteRevistionId;

    protected $_config;
    protected $_deploymentConfig;
    protected $_projectConfig;

    protected $_files = array(
        'added'    => array(),
        'modified' => array(),
        'deleted'  => array(),
        'copied'   => array(),
        'renamed'  => array()
    );

    protected $_finished = false;
    protected $_newMessage = false;
    protected $_message;

    protected $_callback;
    protected $_count = 0;
    protected $_tempDir;
    protected $_branch = 'master';

    public $install = false;

    protected function _listFiles($remoteRev)
    {
        $newestRev = $this->_newestRevisitionId;

        if ($newestRev == $remoteRev && !$this->install) {
            // same revisition; up-to-date
            $this->setMessage(Gitty_Deploy_Adapter_Messages::upToDate());
            $this->_finished = true;
            return false;
        }

        $diff = Gitty_Git_Command::exec(Gitty_Git_Command::DIFF($remoteRev, $newestRev), $this->_projectConfig['repository'], $this->_config);

        foreach ($diff as $file) {
            $fileInfo = preg_split('#\s+#', $file);
            switch ($fileInfo[0]) {
                case 'D':
                    array_push($this->_files['deleted'], $fileInfo[1]);
                    break;
                case 'M':
                    array_push($this->_files['modified'], $fileInfo[1]);
                    break;
                case 'C':
                    array_push($this->_files['copied'], $fileInfo[1]);
                    break;
                case 'R':
                    array_push($this->_files['renamed'], $fileInfo[1]);
                    break;
                case 'A':
                default:
                    array_push($this->_files['added'], $fileInfo[1]);
                    break;
            }
        }

        return true;
    }
    protected function _deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!$this->_deleteDirectory($dir . "/" . $item)) {
                    chmod($dir . "/" . $item, 0777);
                    if (!$this->_deleteDirectory($dir . "/" . $item)) return false;
                };
            }
            return rmdir($dir);
    }
    protected function _createTemp()
    {
        $this->_tempDir = $tempDir = $this->_config->global['gitty']['tempDir'] . '/' . uniqid() . '/';

        $cur = getcwd();
        chdir(dirname($tempDir));

        Gitty_Git_Command::exec(Gitty_Git_Command::CLONEREPO($this->_projectConfig['repository']), $this->_projectConfig['repository'], $this->_config);
        Gitty_Git_Command::exec(Gitty_Git_Command::CHECKOUT($this->_branch), $this->_projectConfig['repository'], $this->_config);

        $proj = str_replace('.git', '', basename($this->_projectConfig['repository']));

        rename(dirname($tempDir) . '/' . $proj, $tempDir);

        $this->_deleteDirectory($this->_tempDir . '.git');

        chdir($cur);
    }
    protected function _deleteTemp()
    {
        $this->_deleteDirectory($this->_tempDir);
    }
    protected function _writeRevFile()
    {
        $revFile = $this->_url . $this->_config->global['gitty']['revistionFile'];
        file_put_contents($revFile, $this->_newestRevisitionId, null, $this->_stream);
    }

    public function start($config, $deploymentConfig, $projectConfig)
    {
        $this->_config = $config;
        $this->_deploymentConfig = $deploymentConfig;
        $this->_projectConfig = $projectConfig;
        $this->_setUrl();

        $revs = Gitty_Git_Command::exec(Gitty_Git_Command::REVLIST_ORDER_DESC(), $this->_projectConfig['repository'], $this->_config);
        $this->_newestRevisitionId = $revs[0];
        $this->_oldestRevisitionId = $revs[(count($revs) - 1)];
    }

    public function open()
    {
        $this->_stream = stream_context_create($this->_options);

        $revFile = $this->_url . $this->_config->global['gitty']['revistionFile'];

        if (file_exists($revFile) && !$this->install) {
            $lastRev = trim(file_get_contents($revFile , false, $this->_stream));

            $this->_remoteRevistionId = $lastRev;
        } else {
            $lastRev = $this->_oldestRevisitionId;
            $this->install = true;
        }

        if ($this->_listFiles($lastRev)) {
            $this->_deploy();
        }
    }

    public function close()
    {
    }

    public function hasFinished()
    {
        return $this->_finished;
    }

    public function newMessage()
    {
        return $this->_newMessage;
    }

    public function setMessage($message)
    {
        $this->_newMessage = true;
        $this->_message = $message;

        $this->_count++;

        if ($this->_callback) {
            call_user_func($this->_callback, $this);
        }
    }

    public function message()
    {
        return $this->getMessage();
    }

    public function getMessage()
    {
        $this->_newMessage = false;
        return $this->_message;
    }

    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    public function getCallback()
    {
        return $this->_callback;
    }

    public function count()
    {
        return $this->_count;
    }

    abstract protected function _setUrl();
    abstract protected function _deploy();
    abstract public function getLastStatusName();
    abstract public function getLastStatus();
}