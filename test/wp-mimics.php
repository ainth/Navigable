<?php

function get_pages() {
    /*
    			$nav_elements[$id] = new NavigableNavElement(array(
				'id'	 	=> $elem->ID,
				'object_id' => $elem->ID,
				'order'		=> $elem->menu_order,
				'url'	 	=> get_permalink($elem->ID),
				'title'		=> $elem->post_title,
				'parent' 	=> $elem->post_parent,
                'slug'		=> $elem->post_name*/
    $dummy_data = array();
    $parent = 0;
    for ($i=1;$i<=NAV_SIZE;$i++) {
        if ($i >= NODE_SIZE && ($i % NODE_SIZE == 0)) {
            $parent++;
        }

        $dummy_data[] = (object) array(
            "ID" => $i,
            "menu_order" => $i,
            "post_title" => 'hehetitle',
            "post_parent" => $parent,
            "post_name"  => 'heheslug',
            "attr_title" => 'attr_title'
        );
    }
    return $dummy_data;
}

function get_permalink($id) {
    return 'lawl';
}

function get_queried_object() {
    return (object) array("ID" => CURRENT_POST);
}
