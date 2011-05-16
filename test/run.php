<?php
define('CURRENT_POST', 60);
define('NAV_SIZE', 100);
define('NODE_SIZE', 4);

require '../Navigable/class-nav.php';
require 'wp-mimics.php';

class NavTest extends PHPUnit_Framework_TestCase
{
    public $nav;

    public function __construct() {
        $this->nav = new NavigableWPPages();
    }

    public function testElemInTree() {
        $this->assertEquals($this->nav->elem_in_tree(60), 60);
        $this->assertFalse($this->nav->elem_in_tree(NAV_SIZE+1));
    }

    public function testCurrentSubnav() {
        //Current sub nav is that tier which contains the active element and whose parent is on the first tier of nav.
        $sub_nav = $this->nav->current_subnav();
        $this->assertTrue(is_array($sub_nav));
        $this->assertEquals($this->nav->elem_in_tree(60), 60);
        $parent_id = $sub_nav[0]->parent_id;
        //the parent of the first element in the sub_nav is in the first tier of navigation
        $this->assertEquals($this->nav->elem_in_tree($parent_id, $this->nav->tree, 'id', true), $parent_id);
    }

    public function testActiveTier() {
        $active_tier = $this->nav->active_tier();
        $this->assertEquals($this->nav->elem_in_tree(CURRENT_POST, $active_tier, 'id', true), CURRENT_POST);
    }

    public function testInitialization()
    {
        $this->assertTrue(is_array($this->nav->tree));
        //this means the tree isn't missing any elements.
        $this->assertEquals($this->countElements($this->nav->tree), NAV_SIZE);
    }

    public function testGetBranch() {
        $branch = $this->nav->get_branch(4);
        $this->assertEquals($branch[0]->parent_id, 4);
    }

    public function testElementFinder() {
        $testElement =  $this->nav->get_element(8);
        $this->assertInstanceOf('NavigableNavElement', $testElement);
        $this->assertEquals($testElement->id, 8);
    }

    public function testCurrentPost() {
        $currentPost = $this->nav->get_element(CURRENT_POST);
        $this->assertTrue($currentPost->if_current());
        //this is what if_current is based on
        $this->assertEquals($currentPost->active_state, 'active');
        //this is not the parent of the active, but the active itself
        $this->assertFalse($currentPost->active_parent);
    }

    public function testParentTier() {
        $currentPost = $this->nav->get_element(CURRENT_POST);
        $parentPost  = $this->nav->get_element($currentPost->parent_id);
        $this->assertEquals($parentPost->active_state, 'active');
        $this->assertTrue($parentPost->active_parent);
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
