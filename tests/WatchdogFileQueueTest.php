<?php

require_once "WatchdogFileQueue.php";
require_once "WatchdogEntry.php";

class WatchdogFileQueueTest extends PHPUnit_Framework_TestCase {


  protected function setUp() {
    define('DRUPAL_ROOT', '../../../..');
    require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    // Bootstrap Drupal.
    drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
    fwrite(STDERR, print_r("Drupal bootstrapped.\n", TRUE));
  }

  public function testCanBeInstantiated() {
    $wfq = new WatchdogFileQueue();
    $this->assertTrue($wfq instanceof WatchdogFileQueue ? TRUE : FALSE);

    $e = new WatchdogEntry('site_installer', "Testing message", array("a", "b", "c"), 5);
    // @TODO
  }

}
