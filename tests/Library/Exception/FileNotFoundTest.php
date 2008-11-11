<?php
// Call Steam_Exception_FileNotFoundTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Steam_Exception_FileNotFoundTest::main');
}

require_once 'PHPUnit/Framework.php';

require_once 'Steam/Exception/FileNotFound.php';

/**
 * Test class for Steam_Exception_FileNotFound.
 * Generated by PHPUnit on 2008-11-11 at 08:08:54.
 */
class Steam_Exception_FileNotFoundTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Steam_Exception_FileNotFound
     * @access protected
     */
    protected $object;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Steam_Exception_FileNotFoundTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new Steam_Exception_FileNotFound;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }
}

// Call Steam_Exception_FileNotFoundTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Steam_Exception_FileNotFoundTest::main') {
    Steam_Exception_FileNotFoundTest::main();
}
?>
