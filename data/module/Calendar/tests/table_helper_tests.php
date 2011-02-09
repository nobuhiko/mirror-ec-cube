<?php
// $Id$

require_once('simple_include.php');
require_once('calendar_include.php');

class TableHelperTests extends GroupTest {
    function TableHelperTests() {
        $this->GroupTest('Table Helper Tests');
        $this->addTestFile('helper_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TableHelperTests();
    $test->run(new HtmlReporter());
}
?>
