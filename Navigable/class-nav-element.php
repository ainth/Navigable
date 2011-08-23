<?php

class NavigableNavElement
{
	//base data
	public $id;
	public $object_id;
	public $order;
	public $url;
	public $title;
	public $parent_id;
    public $slug;
    public $has_been_walked = false;
	
	/*
	 *	Subnav holder. Built out in build_nav_tree
	 */
	public $sub_nav = array();
	
	/*
	 *	Possible states: active (if the current post), pre, post, false(inactive). eg:
	 *	The direct parent of the current post is marked 'active', and active_parent to true.
	 *  The item to the left of the current post is marked 'pre' and active_parent to false.
	 */
	public $active_state  = false;
	
	/*
	 *	True if element is a a parent tier of the currently selected nav element.	
	 */
	public $active_parent = false;
	
	
	public function __construct($elem) {
    $this->id	 	      = $elem['id'];
    $this->object_id  = $elem['object_id'];
    $this->order	    = $elem['order'];
    $this->url	 	    = $elem['url'];
    $this->title	    = $elem['title'];
    $this->attr_title = $elem['attr_title'];
    $this->parent_id  = $elem['parent'];
    $this->slug	      = $elem['slug'];
	}
	
	
	/*
	 *	Sets the active state
	 *
	 *	@param string $state	active (if the current post), pre, post, false(inactive).
	 *	@param bool	  $parent	true if this is a parent element that is active.
	 */
	public function set_active_state($state, $parent = false) {
		$this->active_state  = $state;
		$this->active_parent = $parent; 
	}
	
	/*
	 *	Echoes the markup if this is the current post if markup passed, else passes true/false
	 *
	 *	@param  string $markup	The markup to echo if the conditions apply
	 *	@return bool whether or not this is the current post
	 */
	public function if_current($markup = false) {
		if ($this->active_state == 'active' && $this->active_parent == false) {
			if ($markup) {echo $markup;}
			return true;
		}
		return false;
	} 
	
	/*
	 *	Echoes the markup if $elem is marked active
	 *
	 *	@param string $markup	The markup to echo if the conditions apply
	 *	@output 	The markup passed 
	 */
	public function if_active($markup = false) {
		if ($this->active_state == 'active') {
			if ($markup) {echo $markup;}
			return true;
		}
	}
	
	/*
	 *	Echoes the markup if $elem is marked active and active-parent
	 *
	 *	@param string $markup	The markup to echo if the conditions apply
	 *	@output 	The markup passed
	 */	
	public function if_active_parent($markup = false) {
		if ($this->active_state == 'active' && $this->active_parent) {
			if ($markup) {echo $markup;}
			return true;		
		}
	}


	/*
	 *	Echoes the markup if $elem is marked pre
	 *
	 *	@param string $markup	The markup to echo if the conditions apply
	 *	@output 	The markup passed
	 */	
	public function if_before_active($markup = false) {
		if ($this->active_state == 'pre') {
			if ($markup) {echo $markup;}
			return true;
		}
	}

	/*
	 *	Echoes the markup if $elem is marked post
	 *
	 *	@param string $markup	The markup to echo if the conditions apply
	 *	@output 	The markup passed
	 */	
	public function if_after_active($elem, $markup) {
		if ($this->active_state == 'post') {
			if ($markup) {echo $markup;}
			return true;
		}
	}
	
	
	/*
	 *	Is this a first nav tier elment?
	 */	
	public function is_a_root_element() {
		if ($this->parent_id == 0) {
			return true;
		}
		return false;
	}

	/*
     *	Mark this element as having been dealt with by the tree builder
     */	
    public function mark_walked() {
        $this->has_been_walked = true;
    }
}
