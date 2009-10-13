<?php

require_once 'Zend/Db/Adapter/Mysqli.php';

class Steam_Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
    private $_transactionCount = 0;
    
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
        }
        
        $this->_transactionCount++;
        
        Steam_Db::lock();
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        $this->_transactionCount--;
        
        if ($this->_transactionCount === 0)
        {
            $this->_connect();
            $this->_connection->commit();
            $this->_connection->autocommit(true);
        }
        
        Steam_Db::unlock();
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
            throw new Zend_Db_Adapter_Mysqli_Exception('The Mysqli extension is required for this adapter but the extension is not loaded');
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
            throw new Zend_Db_Adapter_Mysqli_Exception(mysqli_connect_error());
        }

        if (!empty($this->_config['charset'])) {
            mysqli_set_charset($this->_connection, $this->_config['charset']);
        }
    }
}

?>
