<?php
// Call Steam_DbTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Steam_DbTest::main');
}

require_once 'PHPUnit/Framework.php';

require_once 'Steam/Db.php';

/**
 * Test class for Steam_Db.
 * Generated by PHPUnit on 2008-11-11 at 07:59:05.
 */
class Steam_DbTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Steam_Db
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

        $suite  = new PHPUnit_Framework_TestSuite('Steam_DbTest');
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
        $this->object = new Steam_Db;
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
     * @todo Implement testAdd_server().
     */
    public function testAdd_server() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testConnect().
     */
    public function testConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testWrite().
     */
    public function testWrite() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRead().
     */
    public function testRead() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSearch().
     */
    public function testSearch() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}

// Call Steam_DbTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Steam_DbTest::main') {
    Steam_DbTest::main();
}
?>