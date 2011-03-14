<?php

class NavigableWP extends NavigableNav
{
	public $raw;


	/*
	 *	Fetches the raw data from wordpress, then calls parent constuctor to build stuff out.
	 *	
	 *	@param string $nav_selector		Name or id of the wordpress menu.
	 *	@param array  $params			Array of arguments accpted by wp_get_nav_menu_items
	 */
	public function __construct($nav_selector, $params = array()) {
	
		$this->raw = wp_get_nav_menu_items($nav_selector, $params);

		parent::__construct();
		
	}
	
	/*
	 *	Fetch the ID of the current post from wordpress
	 *
	 *	@return string	The id of the current post if it's possible to get it, false if not
	 */
	public function determine_current() {
		//
		if (function_exists('get_queried_object')) {
			return get_queried_object()->ID;
		} else {
			return false;
		}
	}
	
	/*
	 *	Converts the raw array of data given by wordpress into NavElement objects
	 *
	 *	@param  array 	Wordpress navigation elements
	 *	@return array	Nav Elements
	 */
	public function clean_objects($nav_elements) {
	
		foreach ($nav_elements as $id => $elem) {
			$nav_elements[$id] = new NavigableNavElement(array(
				'id'	 	=> $elem->ID,
				'object_id' => $elem->object_id,
				'order'		=> $elem->menu_order,
				'url'	 	=> $elem->url,
				'title'		=> $elem->title,
				'parent' 	=> $elem->menu_item_parent
			));
		}
		return $nav_elements;
		
	}
	
}