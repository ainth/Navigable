<?php
define('CURRENT_POST', 7);
define('NAV_SIZE', 300);
define('NODE_SIZE', 5);

require '../Navigable/class-nav.php';
require 'wp-mimics.php';

class NavTest extends PHPUnit_Framework_TestCase
{
    public $nav;

    public function __construct() {
        $this->nav = new NavigableWPPages();
    }
    public function testInitialization()
    {
        $this->assertTrue(is_array($this->nav->tree));
        //this means the tree isn't missing any elements.
        $this->assertEquals($this->countElements($this->nav->tree), NAV_SIZE);
    }

    public function testElementFinder() {
        $testElement =  $this->nav->get_element_by_id(8);
        $this->assertInstanceOf('NavigableNavElement', $testElement);
        $this->assertEquals($testElement->id, 8);
    }
    public function testCurrentPost() {
        $currentPost = $this->nav->get_element_by_id(CURRENT_POST);
        $this->assertTrue($currentPost->if_current());
        //this is what if_current is based on
        $this->assertEquals($currentPost->active_state, 'active');
        //this is not the parent of the active, but the active itself
        $this->assertFalse($currentPost->active_parent);
    }

    private function countElements($nav) {
        $count = 0;
        foreach($nav as $element) {
            if (!empty($element->sub_nav)) {
                $count += $this->countElements($element->sub_nav);
            }
            $count++;
        }
        return $count;
    }
}
