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
namespace Gitty\Remote\Adapter;

/**
 * storthands
 */
use \Gitty as G;
use \Gitty\Remote as Remote;

/**
 * ftp remote adapter
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Ftp extends G\Remote\AdapterAbstract
{
    /**
     * options for context
     */
    protected $_options = array('ftp' => array('overwrite' => true));

    /**
     * hostname
     */
    protected $_hostname = null;

    /**
     * port default for ftp 21
     */
    protected $_port = 21;

    /**
     * username
     */
    protected $_username = null;

    /**
     * password
     */
    protected $_password = null;

    /**
     * path
     */
    protected $_path = null;

    /**
     * connection handle
     */
    protected $_connection = null;

    /**
     * 5MB temp size
     */
    protected $_tempMaxSize = 5242880;

    /**
     * close ftp connection
     */
    protected function _close()
    {
        if ($this->_connection !== null) {
            ftp_close($this->_connection);
            $this->_connection = null;
        }
    }

    /**
     * remove directories that are empty
     *
     * @param String $path path name
     */
    protected function _rmDir($path)
    {
        $list = @\ftp_nlist($this->_connection, $path);
        while(empty($list) && $path !== $this->_path) {
            $path = \dirname($path);
            \ftp_rmdir($this->_connection, $path);
            $list = @\ftp_nlist($this->_connection, $path);
        }
    }

    /**
     * constructor
     *
     * @param \Gitty\Config $config configuration object
     * @param Array $options options for context
     * @todo handle option when it is an array
     */
    public function __construct(G\Config $config, $options = null)
    {
        $this->_hostname = $config->hostname;
        if (isset($config->port)) {
            $this->_port = $config->port;
        }
        $this->_username = isset($config->username) ? $config->username : 'anonymous';
        $this->_password = isset($config->password) ? $config->password : '';
        $this->_path = isset($config->path) ? $config->path : '/';

        if ($config->revisitionFileName) {
            $this->_revisitionFileName = $config->revisitionFileName;
        }

        if ($options !== null) {
            $this->_options['ftp'] = $options;
        }


        $this->_connection = @ftp_connect($this->_hostname, $this->_port);

        if ($this->_connection === false) {
            require_once \dirname(__FILE__) . '/../Exception.php';
            throw new Remote\Exception('Ftp adapter: Can\'t connect to '.$this->_hostname.' at port '.$this->_port);
        }

        if (@ftp_login($this->_connection, $this->_username, $this->_password) === false) {
            require_once \dirname(__FILE__) . '/../Exception.php';
            throw new Remote\Exception('Ftp adapter: Can\'t login with username '.$this->_hostname);
        }
    }

    /**
     * destructor calling _close()
     */
    public function __destruct()
    {
        $this->_close();
    }

    /**
     * __toString
     *
     * @return String representation of the object as array
     */
    public function __toString()
    {
        return '(FTP) ' . $this->_hostname . ':' . $this->_port;
    }

    /**
     * initialize adapter
     */
    public function init()
    {
        $this->_url = \sprintf('ftp://%s:%s@%s:%d%s', $this->_username, $this->_password, $this->_hostname, $this->_port, $this->_path);
        $this->_context = \stream_context_create($this->_options);
    }

    /**
     * some clean ups
     * - closing ftp connection
     */
    public function cleanUp()
    {
        $this->_close();
    }

    /**
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    public function getServerRevisitionId()
    {
        // open memory for read/write
        $mem = \fopen('php://memory', 'r+');
        // change directory
        \ftp_chdir($this->_connection, $this->_path);
        // read file to memory
        // if false then close memory and return and empty revisition id
        if (@\ftp_fget($this->_connection, $mem, $this->_revisitionFileName, \FTP_BINARY) === false) {
            \fclose($mem);
            return '';
        }

        // rewind memory 'file' to beginnig
        \rewind($mem);
        // get revisition id
        $rev_id = \trim(\fgets($mem));

        // close and return
        \fclose($mem);
        return $rev_id;
    }

    /**
     * put revisition id in file on remote server
     *
     * @param String $uid revisition id
     */
    public function putServerRevisitionId($uid)
    {
        // open memory for read/write
        $mem = \fopen('php://memory', 'r+');
        // put revisition id to memory
        \fputs($mem, \trim($uid));
        // rewind memoy
        \rewind($mem);
        // change directory
        \ftp_chdir($this->_connection, $this->_path);
        // read from file to file on ftp server
        \ftp_fput($this->_connection, $this->_revisitionFileName, $mem, \FTP_BINARY);
        // close memory
        \fclose($mem);
    }

    /**
     * copy a file
     *
     * @param String $sourceFile source file
     * @param String $destination_file desitnation
     * @todo handle strings
     */
    public function put($file, $destination)
    {
        // if the file content was given
        if (\is_string($file)) {
            // write the file content to temp with a max size
            // files bigger then $this->_tempMaxSize will be stored as files
            $temp = \fopen('php://temp/maxmemory:'.$this->_tempMaxSize, 'r+');
            // put content to the temp memory
            \fputs($temp, $file);
            // rewind to beginning
            \rewind($temp);

            // make file a resource, so ftp_fput() can handle it
            $file = $temp;
        }

        // check if folder exists
        $dirname = \dirname($this->_path . '/' . $destination);
        $dirs = array();
        while(@\ftp_chdir($this->_connection, $dirname) === false) {
            $dirs[] = \basename($dirname);
            $dirname = \dirname($dirname);
        }

        // create folders that do not exist
        $dirs = \array_reverse($dirs);
        foreach($dirs as $dir) {
            \ftp_mkdir($this->_connection, $dir);
            \ftp_chdir($this->_connection, $dir);
        }

        // put file from file handle to ftp server
        \ftp_fput($this->_connection, basename($destination), $file, \FTP_BINARY);
        \fclose($file);
    }

    /**
     * rename/move a file
     *
     * @param String $file file name
     * @param String $destination the destination
     * @param Boolean $remove_empty_directories remove empty directories
     */
    public function rename($source, $destination, $remove_empty_directories = true)
    {
        @\ftp_rename($this->_connection, $this->_path . '/' . $source, $this->_path . '/' . $destination);

        // remove empty directories
        if ($remove_empty_directories === true) {
            $this->_rmDir(\dirname($this->_path . '/' . $source));
        }
    }

    /**
     * unlink a file
     *
     * @param String $file a file
     * @param Boolean $remove_empty_directories remove empty directories
     */
    public function unlink($file, $remove_empty_directories = true)
    {
        @\ftp_delete($this->_connection, $this->_path . '/' . $file);

        // remove empty directories
        if ($remove_empty_directories === true) {
            $this->_rmDir(\dirname($this->_path . '/' . $file));
        }
    }

    /**
     * copy a file on remote
     *
     * @param String $source source file
     * @param String $destination the destination for copy
     */
    public function copy($source, $destination)
    {
        $mem = \fopen('php://temp/maxmemory:'.$this->_tempMaxSize, 'r+');
        if (@\ftp_fget($this->_connection, $mem, $this->_path . '/' .$source, \FTP_BINARY) === false) {
            return;
        }
        \rewind($mem);
        $this->put($mem, $destination);
    }
}
