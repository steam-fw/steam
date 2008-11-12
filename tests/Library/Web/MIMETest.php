<?php
// Call Steam_Web_MIMETest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Steam_Web_MIMETest::main');
}

require_once 'PHPUnit/Framework.php';

require_once 'Steam/Web/MIME.php';

/**
 * Test class for Steam_Web_MIME.
 * Generated by PHPUnit on 2008-11-11 at 08:00:25.
 */
class Steam_Web_MIMETest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Steam_Web_MIME
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

        $suite  = new PHPUnit_Framework_TestSuite('Steam_Web_MIMETest');
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
        $this->object = new Steam_Web_MIME;
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
     * @todo Implement testGet_type().
     */
    public function testGet_type() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}

// Call Steam_Web_MIMETest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Steam_Web_MIMETest::main') {
    Steam_Web_MIMETest::main();
}
?>