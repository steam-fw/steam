<?php
// Call Steam_ErrorTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Steam_ErrorTest::main');
}

require_once 'PHPUnit/Framework.php';

require_once 'Steam/Error.php';

/**
 * Test class for Steam_Error.
 * Generated by PHPUnit on 2008-11-11 at 07:59:20.
 */
class Steam_ErrorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Steam_Error
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

        $suite  = new PHPUnit_Framework_TestSuite('Steam_ErrorTest');
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
        $this->object = new Steam_Error;
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

    /**
     * @todo Implement testError_handler().
     */
    public function testError_handler() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testException_handler().
     */
    public function testException_handler() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testLog().
     */
    public function testLog() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testReport().
     */
    public function testReport() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testShutdown().
     */
    public function testShutdown() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}

// Call Steam_ErrorTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Steam_ErrorTest::main') {
    Steam_ErrorTest::main();
}
?>