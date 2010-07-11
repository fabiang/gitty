<?php
/**
 * @namespace Gitty\Config
 */
namespace Gitty\Config;

class Ini implements Loader
{
    /**
     * config file data as array
     */
    protected $_data = array();

    /**
     * separator for nesting
     */
    protected $_nestSeparator = '.';

    /**
     * process a config key
     *
     * @param Array $config config as array
     * @param String $key key
     * @param String $value value
     * @return Array processed config array
     */
    protected function _processKey($config, $key, $value)
    {
        if (\strpos($key, $this->_nestSeparator) !== false) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if (\strlen($pieces[0]) && \strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    $config[$pieces[0]] = array();
                } elseif (!\is_array($config[$pieces[0]])) {
                    require_once dirname(__FILE__).'/Config/Exception.php';
                    throw new Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                require_once dirname(__FILE__).'/Config/Exception.php';
                throw new Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

    public function __construct($filename)
    {
        if (!\file_exists($filename)) {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new Exception('Config file does not exist');
        }

        if (!\is_readable($filename)) {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new Exception('Config file isn\'t readable');
        }

        try {
            $iniArray = \parse_ini_file($filename, true);
        } catch(\Exception $e) {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new Exception('Config could not be parsed');
        }

        $processArray = array();
        foreach($iniArray as $sectionName => $sectionData) {

            $sub = array();
            foreach ($sectionData as $key => $value) {
                $sub = \array_merge_recursive($sub, $this->_processKey(array(), $key, $value));
            }
            $processArray[$sectionName] = $sub;

        }

        $this->_data = $processArray;
    }

    /**
     * return data as array
     */
    public function toArray()
    {
        return $this->_data;
    }
}