<?php
class Gitty_Deploy
{
    static $defaultAdapter = 'Gitty_Deploy_Adapter_Ftp';

    protected $_adapterType = 'ftp';
    protected $_adapter;
    protected $_config;
    protected $_projectId = 0;
    protected $_projectConfigs;
    protected $_projectConfig;
    protected $_deploymentId = 0;
    protected $_deploymentConfig;
    protected $_branch;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_projectConfigs = array_values($config->projects);
    }

    public function setProjectId($id)
    {
        $this->_projectId = $id;
        $this->updateConfig();
    }

    public function updateConfig()
    {
        $this->_projectConfig = $this->_projectConfigs[$this->_projectId];
    }

    public function setBranchId($name)
    {
        $this->_branch = $name;
    }

    public function setDeploymentId($id)
    {
        $this->_deploymentId = $id;
        $this->setDeploymentConfig();
    }

    public function setDeploymentConfig($config = null)
    {
        if ($config) {
            $this->_deploymentConfig = $config;
        } else {
            $deployment = $this->_projectConfig['deployment'];
            $adapter = $deployment['adapter'][$this->_deploymentId];
            $config = array('adapter' => $adapter);
            $this->_adapterType = $adapter;

            foreach($deployment as $name => $value) {
                $config[$name] = $value[$this->_deploymentId];
            }

            $this->_deploymentConfig = $config;
        }
    }

    public function start()
    {
        $adapterName = 'Gitty_Deploy_Adapter_' . ucfirst(strtolower($this->_adapterType));

        try {
            $adapter = Gitty_Loader::loadClass($adapterName);
            $adapter = new $adapterName;
        } catch(Exception $e) {
            require_once 'Gitty/Deploy/Exception.php';
            throw new Gitty_Deploy_Exception("Can't create instance of adapter '$adapterName'. Does adapter with name '$this->_adapterType' exist?");
        }

        $this->_adapter = $adapter;
        $this->_adapter->start($this->_config, $this->_deploymentConfig, $this->_projectConfig);
    }

    public function open()
    {
        $this->_adapter->open();
    }

    public function close()
    {
        $this->_adapter->close();
    }

    public function hasFinished()
    {
        return $this->_adapter->hasFinished();
    }

    public function newMessage()
    {
        return $this->_adapter->newMessage();
    }

    public function getLastStatusName()
    {
        return $this->_adapter->getLastStatusName();
    }

    public function getLastStatus()
    {
        return $this->_adapter->getLastStatus();
    }

    public function setCallback($callback)
    {
        $this->_adapter->setCallback($callback);
    }

    public function getCallback()
    {
        return $this->_adapter->getCallback();
    }

    public function count()
    {
        return $this->_adapter->count();
    }
}