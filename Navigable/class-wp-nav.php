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
    
    //If false, invalid name passed
    if ($this->raw === false) {
      trigger_error("Navigable could not find the navigation object called '{$nav_selector}'", E_USER_NOTICE);
    }
    $this-> raw = $this->raw === false ? array() : $this-> raw;
		$this->page_slugs = $this->get_page_slugs();
		parent::__construct();
		
	}
	
	/*
	 *	Converts the raw array of data given by wordpress into NavElement objects
	 *
	 *	@param  array 	Wordpress navigation elements
	 *	@return array	Nav Elements
	 */
	public function clean_objects($nav_elements) {
	
		foreach ($nav_elements as $id => $elem) {
			$slug = isset($this->page_slugs[$elem->object_id]) ? $this->page_slugs[$elem->object_id] : null;
			$nav_elements[$id] = new NavigableNavElement(array(
				'id'	 	=> $elem->ID,
				'object_id' => $elem->object_id,
				'order'		=> $elem->menu_order,
				'url'	 	=> $elem->url,
				'title'		=> $elem->title,
        'attr_title' => $elem->attr_title,
				'parent' 	=> $elem->menu_item_parent,
				'slug'		=> $slug
			));
		}
		return $nav_elements;
		
	}
	
	/*
	 *	The raw data in this case does not include the 'page_name' property of the associated page, though
	 *	we do get when we're just building with pages only. This will fetch all pages ( heavy :( ) and build
	 *	a simple array of the $id=>$slug variety to use.
	 *
	 *	@return array	$post_id=>$post_slug
	 */
	private function get_page_slugs() {
	 	$pages = get_pages();
	 	$page_slugs = array();
	 	foreach ($pages as $page) {
	 		$page_slugs[$page->ID] = $page->post_name;
	 	}
	 	return $page_slugs;
	 }

}
