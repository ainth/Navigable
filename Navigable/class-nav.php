<?php
/*
     Plugin Name: Navigable
     Plugin URI: 
     Description: Provides an object oriented navigation interface for templating as an alternative to wp_nav_menu(). PHP5+ required.
     Version: 0.3
     Author: Allen Hebden
     Author URI: http://intelligible.ca
     License: GPL2
*/

require_once 'class-nav-element.php';
require_once 'class-wp-nav.php';
require_once 'class-wp-pages.php';


abstract class NavigableNav
{
     public  $current_post;
     public  $cleaned_objects;
     public  $page_slugs;
     public  $tree;

 /*------------------------------------------------------
  * Abstract Methods
  * ------------------------------------------------------
  */

     /*
      *     Iterates through raw data, converts to consistent data model.
      *
      *     @param  array      raw nav elements
      *     @return array       NavElement objects
      */
     abstract function clean_objects($nav_elements);

     /*
      *     Determines id of the current page
      *
      *     @return string      the id of the current post
      */
     abstract function determine_current();

 /*------------------------------------------------------
  * Public Methods
  *------------------------------------------------------
  */

    /*
     *     Called by the concrete class. Builds the data out.
     */
    public function __construct() {

        $this->current_post        = $this->determine_current();
        $this->cleaned_objects = $this->clean_objects($this->raw); 
        $this->tree              = $this->build_nav_tree($this->cleaned_objects);

        if ($this->current_post) {
            $this->tree = $this->mark_active($this->tree);
        } else if ($this->current_post = $this->guess_active()) {
            $this->tree = $this->mark_active($this->tree);
        }

    }

     /*
      *     The current subnav is the sub_nav element of whichever item in the 
      *     first tier of navigation is marked 'active' 
      *
      *     @return array Navigation elements, or false if not found
      */
    public function current_subnav() {

        foreach ($this->tree as $elem) {
            if ($elem->if_active()
                && !empty($elem->sub_nav)) {
                return $elem->sub_nav;
            }
        }
        return false;

    }


     /*
      *     Returns whichever tier of navigation the current post can be found on. Recursively searches subnavs
      *
      *     @param  array      A tier of navigation to test for the presence of the current page
      *     @return array/bool     The tier of navigation where the current page is, or false
      */
    public function active_tier($tier = false) {

        if (!$tier) {$tier = $this->tree;}

        foreach ($tier as $elem) {
            if ($elem->if_current()) {
                return $tier;
            }
            if (!empty($elem->sub_nav)) {
                $child_tier = $this->active_tier($elem->sub_nav);
                if (!empty($child_tier)) {return $child_tier;}
            }
        }
        return false;

    }


     /*
      *     Checks if the id passed is somewhere in the nav passed
      *
      *     @param array  $nav_tree     A tree of nav elements to look in
      *     @param string $id          The id to look for     
      *     @param string $mode          Which property to compare against, either id or slug
      *     @param bool       $wall          Whether or not to recurse into subnav elements
      */
    public function elem_in_tree($nav_tree, $val, $mode = 'id', $wall = false) {

        foreach ($nav_tree as $elem) {
            if ($elem->$mode == $val) {
                return $elem->id;
            }

            if (!empty($elem->sub_nav) && !$wall) {
                $sub_nav_test = $this->elem_in_tree($elem->sub_nav, $val, $mode);
                if ($sub_nav_test) {
                    return $sub_nav_test;
                }
            }
        }
        return false;

     }

     /*
      * Find a branch (or sub_nav) by a given property (defaults to id, which is the only one that makes sense really.
      *
      * @param string $id   The value to look for in the given property
      * @param string $prop Which property to test elements for for the given id
      * @return array               A navigation tree, or false.
      */

    public function find_branch($id, $prop = 'id', $tree = false) {

        $tree = !$tree ? $this->tree : $tree;
        foreach ($tree as $elem) {
            if ($elem->$prop == $id && !empty($elem->sub_nav)) {
                return $elem->sub_nav;
            } else if (!empty($elem->sub_nav)) {
                $sub_nav_test = $this->find_branch($slug, $prop, $elem->sub_nav);
                if ($sub_nav_test) {
                  return $sub_nav_test;
                }
            }
        }
        return false;

    }

 /*------------------------------------------------------
  * Private Methods
  *------------------------------------------------------
  */

     /*
      *     Constructs a multidimensional nav tree from flat data
      *
      *      Iterate once. On each element, iterate through the lot of them and pull all children of that element
      *      into its subnav, recursively. (So each element we touch will have a fully completed subnav by the end 
      *      of its cycle) If the element has a parent, then at the end of the iteration, add the element to its
      *      parent's subnav. Root elements are added to the array that will be the output.
      *
      *     @param  array     An array of NavElement objects
      *     @return array     Nav element tree
      */
    private function build_nav_tree($nav_elements) {

        $new_nav = array();

        foreach ($nav_elements as $elem) {
            //returns an array containing all of the element's children. Recursive.
            $elem->sub_nav = $this->find_children($nav_elements, $elem->id);

            if ($elem->is_a_root_element()) {
                $new_nav[$elem->id] = $elem;
            } else if ($this->elem_in_tree($new_nav, $elem->parent_id)) {
                $this->append_to_subnav($new_nav, $elem);
            } else {
                $this->append_to_subnav($nav_elements, $elem);
            }
        }
        return $this->sort_and_clean($new_nav);

    }

     /*
      *     Recursively fix up the order and keys of the tree
      *
      *     @param  array $nav     Full multidimensional tree, with keys as ids.
      *     @return array           Tree and subtrees sorted on the order property, numerical keys.
      */
    private function sort_and_clean($nav) {

        foreach($nav as $key => $elem) {
           if (!empty($elem->sub_nav)) {
                $nav[$key]->sub_nav = $this->sort_and_clean($elem->sub_nav);
           }
        }
        usort($nav, array($this, 'order_sort'));
        return $nav;

    }


     /*
      *     usort function. Sorts on order property
      *
      *     @param mixed $a      An array element
      *     @param mixed $b      An array element
      *     @return int               Truth value of the test - order property higher/lower
      */
    private function order_sort($a, $b) {
        return $a->order == $b->order ? 0 : ( $a->order > $b->order ) ? 1 : -1;
    }


     /*
      *     Recursively find children in a flat array, forming a multidimensional array
      *
      *     @param array  $nav_elements         Flat array of nav elements to look through.
      *     @param string $id                   Parent's id
      *     @return array                       Tree of nav elements that are children of given id
      */
    private function find_children($nav_elements, $id) {

        $children = array();
        foreach ($nav_elements as $elem) {
           if ($elem->parent_id == $id) {
                $elem->sub_nav = $this->find_children($nav_elements, $elem->id);
                $children[$elem->id] = $elem;
           }
        }
        return $children;

    }


     /*
      *     Add $this_elem to its parent's subnav in the given nav tree. Recursively looks for parent.
      *
      *     @param array nav_tree     Tree to look for parent element
      *     @param NavElement          Element to add to its parent
      *
      */
    private function append_to_subnav(&$nav_tree, $this_elem) {

        $parent_id = $this_elem->parent_id;

        foreach ($nav_tree as $elem) {
           if ($elem->id == $parent_id) {
                $nav_tree[$elem->id]->sub_nav[$this_elem->id] = $this_elem;
           } else if (!empty($elem->sub_nav)) {
                $this->append_to_subnav($elem->sub_nav, $this_elem);
           }
        }

    }
     /*
      *     Assumes clean urls which match navigation elements, makes the best assumption it can 
      *     about what pages you're on. eg: you're on /about/stuff/things/3. If 3 is not in the nav we don't know
      *     what to do, this will effectively mark 'things' since it's the next nearest thing in the tree.
      */
    private function guess_active() {

        $tree = $this->tree;
        $path = parse_url($_SERVER['REQUEST_URI']);
        $path = explode('/',$path['path']);
        array_shift($path);     //first element always an empty string because of leading slash.

        foreach ($path as $page_slug) {
           if ($id = $this->elem_in_tree($tree, $page_slug, 'slug', true)) {
                $elem = $this->get_element_by_id($tree, $id);
                if (!empty($elem->sub_nav)) {
                     $tree = $elem->sub_nav;
                } else {
                     return $id;
                }
           }
        }
        return false;

    }

      /*
       *     Returns the element in the given tree with the given id, recursive.
       *
       *     @param array $tree     A tree to look for the id in
       *     @param int      $id     The id to look for
       *     @return NavigableNavElement     The element matching the id.
       */
    private function get_element_by_id($tree, $id) {

        foreach ($tree as $elem) {
            if ($elem->id == $id) {
                 return $elem;
            } else if (!empty($elem->sub_nav)) {
                $sub_nav_test = $this->get_element_by_id($elem->sub_nav, $id);
                if ($sub_nav_test) {
                    return $sub_nav_test;
                }
            }
        }
        return false;

    }

     /*
      *     Traverse tree and mark nav elements based on current page
      *
      *     @param array $nav_tier       An array of nav elemets to mark, recursively into the sub_nav
      */
    private function mark_active($nav_tier) {

        foreach ($nav_tier as $key => $elem) {

            if ($elem->object_id == $this->current_post) {
                $nav_tier = $this->flag($nav_tier, $key);
                return $nav_tier;
            }

            if (!empty($elem->sub_nav)) {
                $nav_tier[$key]->sub_nav = $this->mark_active($elem->sub_nav);   
                //we tested the sub_nav. Now we iterate through that sub_nav, to determine if a child is the active element, and if it is we mark the parent. This bubbles up the active state to all parents.
                foreach ($nav_tier[$key]->sub_nav as $elem) {
                    if ($elem->if_active()) {
                        $nav_tier = $this->flag($nav_tier, $key, true);
                    }
                }
            }
        }
        return $nav_tier;

    }

     /*
      *     Mark a tier of navigation with active entities
      *
      *     @param array  $nav_tier          Nav elements to mark
      *     @param int       $key               Key of the active element in tier
      *     @param bool       $parent          If true, will set parent state to true 
      *     @return array                     Tier of navigation with entities marked as needed
      */
    private function flag($nav_tier, $key, $parent = false) {
        $nav_tier[$key]->set_active_state('active', $parent);
        if (isset($nav_tier[$key-1])) {$nav_tier[$key-1]->set_active_state('pre', $parent);}
        if (isset($nav_tier[$key+1])) {$nav_tier[$key+1]->set_active_state('post', $parent);}
        return $nav_tier;
    }

}
