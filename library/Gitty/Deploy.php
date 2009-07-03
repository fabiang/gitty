<?php
class Gitty_Deploy
{
    static $defaultAdapter = 'Gitty_Deploy_Adapter_Ftp';

    protected $_adapterType = 'ftp';
    protected $_adapter;
    protected $_config;
    protected $_projectId = 0;
    protected $_projectConfig;
    protected $_deploymentId = 0;
    protected $_deploymentConfig;
    protected $_branch;
    protected $_status = 0;

    public function __construct($config)
    {
        $this->_config = array_values($config->projects);
    }

    public function setProjectId($id)
    {
        $this->_projectId = $id;
        $this->updateConfig();
    }

    public function updateConfig()
    {
        $this->_projectConfig = $this->_config[$this->_projectId];
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
            $config = array(
                'adapter' => $adapter
            );
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
            require_once 'Gitty/Exception.php';
            throw new Gitty_Exception("Can't create instance of adapter '$adapterName'. Does adapter with name '$this->_adapterType' exist?");
        }
        $this->_adapter = $adapter;
    }

    public function open()
    {

    }

    public function close()
    {

    }

    public function hasFinished()
    {
        return 1;
    }

    public function getLastStatusName()
    {

    }

    public function getLastStatus()
    {

    }
}