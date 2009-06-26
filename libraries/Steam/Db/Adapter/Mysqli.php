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
    }
}

?>
