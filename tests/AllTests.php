<?php

error_reporting(E_ALL | E_STRICT);

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

if (!defined('PHPUnit_MAIN_METHOD'))
{
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
    chdir(dirname(__FILE__));
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'SteamTest.php';
require_once 'Library/AllTests.php';

class AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        
        $suite->addTestSuite('SteamTest');
        $suite->addTest(Library_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main')
{
    AllTests::main();
}
?>
