<?php
/**
 * Steam Mysqli Class
 *
 * This class provides workarounds for the Zend Mysqli adapter.
 *
 * Copyright 2008-2011 Shaddy Zeineddine
 *
 * This file is part of Steam, a PHP application framework.
 *
 * Steam is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Steam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Frameworks
 * @package Steam
 * @copyright 2008-2011 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */


require_once 'Zend/Db/Adapter/Mysqli.php';

class Steam_Db_Adapter_Mysqli extends \Zend_Db_Adapter_Mysqli
{
    private $_transactionCount = 0;
    
    /**
     * Prepare a statement and return a PDOStatement-like object.
     * Modified to support multiple simultaneous statements.
     *
     * @param  string  $sql  SQL query
     * @return \Zend_Db_Statement_Mysqli
     */
    public function prepare($sql)
    {
        $this->_connect();
        
        $stmtClass = $this->_defaultStmtClass;
        if (!class_exists($stmtClass)) {
            require_once 'Zend/Loader.php';
            \Zend_Loader::loadClass($stmtClass);
        }
        $stmt = new $stmtClass($this, $sql);
        if ($stmt === false) {
            return false;
        }
        
        $stmt->setFetchMode($this->_fetchMode);
        $this->_stmt = $stmt;
        return $stmt;
    }
    
    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        if ($this->_transactionCount === 0)
        {
            $this->_connect();
            $this->_connection->autocommit(false);
            \Steam\Db::lock();
        }
        
        $this->_transactionCount++;
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        if ($this->_transactionCount === 0) return;
        
        $this->_transactionCount--;
        
        if ($this->_transactionCount === 0)
        {
            $this->_connect();
            $this->_connection->commit();
            $this->_connection->autocommit(true);
            \Steam\Db::unlock();
        }
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     */
    protected function _rollBack()
    {
        if ($this->_transactionCount === 0) return;
        
        #$this->_transactionCount--;
        $this->_transactionCount = 0; // This is safer because it will ensure no invalid commits
        
        if ($this->_transactionCount === 0)
        {
            $this->_connect();
            $this->_connection->rollback();
            $this->_connection->autocommit(true);
            \Steam\Db::unlock();
        }
        
        
    }

    /**
     * Creates a connection to the database.
     *
     * @return void
     * @throws Zend_Db_Adapter_Mysqli_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        if (!extension_loaded('mysqli')) {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new \Zend_Db_Adapter_Mysqli_Exception('The Mysqli extension is required for this adapter but the extension is not loaded');
        }

        if (isset($this->_config['port'])) {
            $port = (integer) $this->_config['port'];
        } else {
            $port = null;
        }

        if (isset($this->_config['socket'])) {
            $socket = (string) $this->_config['socket'];
        } else {
            $socket = null;
        }

        $this->_connection = mysqli_init();

        if(!empty($this->_config['driver_options'])) {
            foreach($this->_config['driver_options'] as $option=>$value) {
                if(is_string($option)) {
                    // Suppress warnings here
                    // Ignore it if it's not a valid constant
                    $option = @constant(strtoupper($option));
                    if($option === null)
                        continue;
                }
                mysqli_options($this->_connection, $option, $value);
            }
        }

        // Suppress connection warnings here.
        // Throw an exception instead.
        $_isConnected = @mysqli_real_connect(
            $this->_connection,
            $this->_config['host'],
            $this->_config['username'],
            $this->_config['password'],
            $this->_config['dbname'],
            $port,
            $socket
        );

        if ($_isConnected === false || mysqli_connect_errno()) {

            $this->closeConnection();
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new \Zend_Db_Adapter_Mysqli_Exception(mysqli_connect_error());
        }

        if (!empty($this->_config['charset'])) {
            mysqli_set_charset($this->_connection, $this->_config['charset']);
        }
    }
    
    public function selectDb($dbname)
    {
        $this->_connect();
        
        mysqli_select_db($this->_connection, $dbname);
    }
}

?>
