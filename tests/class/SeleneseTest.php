<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

$HOME = realpath(dirname(__FILE__)) . "/../..";
require_once($HOME . "/html/require.php");

class SeleneseTests extends PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory = './';

    protected function setUp() {
        //$this->setHost("localhost");
        //$this->setPort(4444);
        $this->setBrowser("*firefox");
        $this->setBrowserUrl(HTTP_URL);
        $this->setTimeout(60000);
    }
}
