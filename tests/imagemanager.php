<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Tests for ImageManager. 
 * 
 * @package ImageManager
 * @category Tests
 * @author Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 * @copyright (c) 2012, Hète.ca Inc.
 * @todo write tests
 */
class ImageManager_Test extends Unittest_TestCase {

    public function test_store() {
        ImageManager::instance()->store(array());
    }

    public function test_delete() {
        
    }

}

?>
