<?php
// $Id$

require_once('simple_include.php');
require_once('calendar_include.php');

class CalendarEngineTests extends GroupTest {
    function CalendarEngineTests() {
        $this->GroupTest('Calendar Engine Tests');
        $this->addTestFile('peardate_engine_test.php');
        $this->addTestFile('unixts_engine_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new CalendarEngineTests();
    $test->run(new HtmlReporter());
}
?>
