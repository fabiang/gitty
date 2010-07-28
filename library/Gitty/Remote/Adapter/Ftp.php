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
 * @package  Ftp
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Remote/Adapter/Ftp
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
 * @category Gitty
 * @package  Ftp
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Remote/Adapter/Ftp
 */
class Ftp extends G\Remote\AdapterAbstract
{
    /**
     * hostname
     */
    protected $hostname = null;

    /**
     * port default for ftp 21
     */
    protected $port = 21;

    /**
     * username
     */
    protected $username = null;

    /**
     * password
     */
    protected $password = null;

    /**
     * path
     */
    protected $path = null;

    /**
     * connection handle
     */
    protected $connection = null;

    /**
     * 5MB temp size
     */
    protected $tempMaxSize = 5242880;

    /**
     * configuration object of the remote
     */
    public $config = null;

    /**
     * remove directories that are empty
     *
     * @param String $path path name
     *
     * @return Null
     */
    protected function rmDir($path)
    {
        $list = @\ftp_nlist($this->connection, $path);
        while (empty($list) && $path !== $this->path) {
            @\ftp_rmdir($this->connection, $path);
            $path = \dirname($path);
            $list = @\ftp_nlist($this->connection, $path);
        }
    }

    /**
     * constructor
     *
     * @param \Gitty\Config $config configuration object
     */
    public function __construct(G\Config $config)
    {
        $this->hostname = $config->hostname;
        if (isset($config->port)) {
            $this->port = $config->port;
        }
        $this->username = isset($config->username) ? $config->username : 'anonymous';
        $this->password = isset($config->password) ? $config->password : '';
        $this->path = isset($config->path) ? $config->path : '/';

        if ($config->revisitionFileName) {
            $this->revisitionFileName = $config->revisitionFileName;
        }

        $this->config = $config;
    }

    /**
     * destructor calling close()
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * __toString
     *
     * @return String representation of the object as array
     */
    public function __toString()
    {
        return '(FTP) ' . $this->hostname . ':' . $this->port;
    }

    /**
     * initialize adapter
     *
     * @return null
     */
    public function init()
    {
        $this->connection = @ftp_connect($this->hostname, $this->port);

        if (false === $this->connection) {
            $this->connection = null;
            include_once \dirname(__FILE__) . '/../Exception.php';
            throw new Remote\Exception(
                'Ftp adapter: Can\'t connect to\
                '.$this->hostname.' at port '.$this->port
            );
        }

        $login = @ftp_login($this->connection, $this->username, $this->password);
        if (false === $login) {
            $this->close();
            include_once \dirname(__FILE__) . '/../Exception.php';
            throw new Remote\Exception(
                'Ftp adapter: Can\'t login with username '.$this->hostname
            );
        }
    }

    /**
     * close ftp connection
     *
     * @return Null
     */
    public function close()
    {
        if (null !== $this->connection) {
            ftp_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * some clean ups
     * - closing ftp connection
     *
     * @return Null
     */
    public function cleanUp()
    {
        $this->close();
    }

    /**
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    public function getServerRevisitionId()
    {
        if (null == $this->connection) {
            $this->init();
        }

        // open memory for read/write
        $mem = \fopen('php://memory', 'r+');
        // change directory
        \ftp_chdir($this->connection, $this->path);
        // read file to memory
        // if false then close memory and return and empty revisition id
        $result = @\ftp_fget(
            $this->connection,
            $mem,
            $this->revisitionFileName,
            \FTP_BINARY
        );

        if (false === $result) {
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
     *
     * @return Null
     */
    public function putServerRevisitionId($uid)
    {
        if (null == $this->connection) {
            $this->init();
        }

        // open memory for read/write
        $mem = \fopen('php://memory', 'r+');
        // put revisition id to memory
        \fputs($mem, \trim($uid));
        // rewind memoy
        \rewind($mem);
        // change directory
        \ftp_chdir($this->connection, $this->path);
        // read from file to file on ftp server
        \ftp_fput($this->connection, $this->revisitionFileName, $mem, \FTP_BINARY);
        // close memory
        \fclose($mem);
    }

    /**
     * copy a file
     *
     * @param String $file        source file
     * @param String $destination destination
     *
     * @return Null
     */
    public function put($file, $destination)
    {
        if (null == $this->connection) {
            $this->init();
        }

        // if the file content was given
        if (\is_string($file)) {
            // write the file content to temp with a max size
            // files bigger then $this->tempMaxSize will be stored as files
            $temp = \fopen('php://temp/maxmemory:'.$this->tempMaxSize, 'r+');
            // put content to the temp memory
            \fputs($temp, $file);
            // rewind to beginning
            \rewind($temp);

            // make file a resource, so ftp_fput() can handle it
            $file = $temp;
        }

        // check if folder exists
        $dirname = \dirname($this->path . '/' . $destination);
        $dirs = array();
        while (@\ftp_chdir($this->connection, $dirname) === false) {
            $dirs[] = \basename($dirname);
            $dirname = \dirname($dirname);
        }

        // create folders that do not exist
        $dirs = \array_reverse($dirs);
        foreach ($dirs as $dir) {
            \ftp_mkdir($this->connection, $dir);
            \ftp_chdir($this->connection, $dir);
        }

        // put file from file handle to ftp server
        \ftp_fput($this->connection, basename($destination), $file, \FTP_BINARY);
        \fclose($file);
    }

    /**
     * rename/move a file
     *
     * @param String  $source                   file name
     * @param String  $destination              the destination
     * @param Boolean $remove_empty_directories remove empty directories
     *
     * @return Null
     */
    public function rename($source, $destination, $remove_empty_directories = true)
    {
        if (null == $this->connection) {
            $this->init();
        }

        @\ftp_rename(
            $this->connection,
            $this->path . '/' . $source,
            $this->path . '/' .
            $destination
        );

        // remove empty directories
        if ($remove_empty_directories === true) {
            $this->rmDir(\dirname($this->path . '/' . $source));
        }
    }

    /**
     * unlink a file
     *
     * @param String  $file                     file name
     * @param Boolean $remove_empty_directories remove empty directories
     *
     * @return Null
     */
    public function unlink($file, $remove_empty_directories = true)
    {
        if (null == $this->connection) {
            $this->init();
        }

        @\ftp_delete($this->connection, $this->path . '/' . $file);

        // remove empty directories
        if ($remove_empty_directories === true) {
            $this->rmDir(\dirname($this->path . '/' . $file));
        }
    }

    /**
     * copy a file on remote
     *
     * @param String $source      source file
     * @param String $destination the destination for copy
     *
     * @return Null
     */
    public function copy($source, $destination)
    {
        if (null == $this->connection) {
            $this->init();
        }

        $mem = \fopen('php://temp/maxmemory:'.$this->tempMaxSize, 'r+');
        $result = @\ftp_fget(
            $this->connection,
            $mem,
            $this->path . '/' .$source,
            \FTP_BINARY
        );

        if (false === $result) {
            return;
        }
        \rewind($mem);
        $this->put($mem, $destination);
    }

    /**
     * access to hostname
     *
     * @return String hostname
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * access to hostname (set)
     *
     * @param String $hostname hostname
     *
     * @return Null
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * access to port
     *
     * @return Integer port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * access to port
     *
     * @param Integer $port port
     *
     * @return Null
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
    }

    /**
     * access to username
     *
     * @return String username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * access to username
     *
     * @param String $username username
     *
     * @return Null
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * access to password
     *
     * @return String password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * access to password
     *
     * @param String $password password
     *
     * @return Null
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * access to path
     *
     * @return String path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * access to path
     *
     * @param String $path path
     *
     * @return Null
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
