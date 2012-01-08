=== Plugin Name ===
Contributors: ainth 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=allen%40intelligible%2eca&lc=CA&item_name=Navigable&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: navigation, sub-nav, subnav, menu templating, menus, nav interface
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.39

Navigable is a WordPress plugin for template developers. It gives you an alternative to WordPress's wp_nav_menu() function.

== Description ==

Overview
----

Navigable is a WordPress plugin for template developers. It gives you an alternative to WordPress's wp_nav_menu() function. Advantages:

* Navigable gives you a data structure to work with. You have 100% markup control, and it's all in the templates.
* You can determine if a nav element occurs before/after an active element.
* Flexibility. Need the current sub-navigation separate from the main nav list? Need whichever navigation tier the current page is on all by itself? Need the sub nav tree of a specific element?

####Requires:
PHP5
Only tested on WordPress 3.1, will likely work on much earlier versions since it only uses a few Wordpress Functions

Usage
------
You need to instantiate a navigation object first:

    <?php $nav = new NavigableWP('Title of Navigation', $args); ?>

The paramaters are any arguments accepted by wp_get_nav_menu_items().

Alternatively, if you want to skip a custom navigation menu and just have all pages thrown into a nav stew you can use a different class:

    <?php $nav = new NavigableWPPages(); ?>

Now you can iterate:

    <ul>
        <?php foreach ($nav->tree as $elem): ?>
            <li><?php echo $elem->title; ?></li>
        <?php endforeach; ?>
    </ul>

If an element has a subnav:

    <ul class="main-nav">
        <?php foreach ($nav->tree as $elem): ?>
            <li>
                <?php echo $elem->title; ?>
                <?php if ($elem->sub_nav): ?>
                    <ul class="sub-nav">
                    <?php foreach ($elem->sub_nav as $sub_elem): ?>
                        <li><?php echo $sub_elem->title; ?></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
	
If you want the current sub nav tree (useful if you need this outside the main nav loop):

    <?php if ($sub_nav = $nav->current_sub_nav()): ?>
        <?php foreach ($sub_nav as $elem): ?>
            ...
        <?php endforeach; ?>
    <?php endif; ?> 

You might want whichever tier of navigation the current page is on:

    <?php if ($current_tier = $nav->active_tier()): ?>
        <?php foreach ($current_tier as $elem): ?>
            ...
        <?php endforeach; ?>
    <?php endif; ?>

You might want the sub-navigation of a particular element:

    <?php if ($some_nav = $nav->find_branch(74)): //get the subnav of element 74 ?>
        <?php foreach ($some_nav as $elem): ?>
            ...
        <?php endforeach; ?>
    <?php endif; ?>
	
or

    <?php ////get the sub_nav of the first element whose title is 'About Us'. Hopefully there's just one.  ?>
    <?php if ($some_nav = $nav->find_branch('About Us', 'title')): ?>
        <?php foreach ($some_nav as $elem): ?>
            ...
        <?php endforeach; ?>
    <?php endif; ?>


You'll probably want to mark which things are active based on which page the user is on. And if you're very fancy you'll want to mark the elements before and after that element:

    ...
        <li class="nav-item<?php $elem->if_active(' active'); ?>">
            <?php echo $elem->title; ?>
        </li>
    ...

No need to use echo - whatever string you pass to any of these functions will be echo as if by magic.

There's a bunch more that operate in much the same way:

    <?php
        $elem->if_current($markup); // echo markup if this element is the current post
        $elem->if_active($markup);	// echo markup if element is active (both the direct parent and the current element are 'active')
        $elem->if_active_parent($markup); // echo markup if element is a parent of the current element
        $elem->if_before_active($markup);	// echo markup if element is before an active element (both parents and current)
        $elem->if_after_active($markup);	// echo markup if element is after an active element (both parents and current)
    ?>

Here are the variables navigation elements have: 

    <?php
        $elem->id;				// In NavigableWP class, id of the nav menu item. In NavigableWPPages id of actual page
        $elem->object_id;	// In NavigableWP class, id of the page. In the NavigableWPPages class, same as id
        $elem->order;			// Not too useful. The menu order of the item
        $elem->url;				// Full url of the page/post.
        $elem->title;			// Name of the element.
        $elem->parent_id; // ID of the parent of the element. Root elements have a parent id of 0.
        $elem->slug;			// The slug/url title/post_title of the element's page.
    ?>

Things to know
----
What page are you on? There's two different things Navigable does to try to figure it out. The first is the obvious route, we just ask WordPress: 

    $current_post = get_queried_object()->ID;

This will work for pretty much everyone but didn't quite work for me. If you use podscms or any other plugin that means that the page you're on isn't a WordPress post that strategy won't work. So if that doesn't work Navigable will assume you are using clean urls that correspond nicely to your navigation tree, and will mark as active the last most specific thing it can find in the nav tree. For example, suppose the request uri is: *yoursite.com/about/stuff/andthings/my-pods-slug*. The *my-pods-slug* is actually a podscms slug, so WordPress is confused. Navigable will iterate through the nav tree, looking for *about*. If it finds it, it will look into *about*'s subnav if it has one and look for *stuff*, then again it will look for *andthings*. It will try to find *my-pods-slug* but can't so it will assume *andthings* is the current post.


Motivation
-----
Navigation in WordPress is handled like so: 
 
	<?php wp_nav_menu($args); ?>

Passing an array of arguments lets you control a lot of the markup that's generated:

	<?php $defaults = array(
	  'theme_location'  => ,
	  'menu'            => , 
	  'container'       => 'div', 
	  'container_class' => 'menu-{menu slug}-container', 
	  'container_id'    => , 
	  'menu_class'      => 'menu', 
	  'menu_id'         => ,
	  'echo'            => true,
	  'fallback_cb'     => 'wp_page_menu',
	  'before'          => ,
	  'after'           => ,
	  'link_before'     => ,
	  'link_after'      => ,
	  'depth'           => 0,
	  'walker'          => );
	?>

I found this a bit unsatisfactory - I'd much rather have some kind of
data structure to work with that would let me iterate over nav elements
so I have complete control over the markup. I also needed to assign a
class to the nav element that comes before the active element and saw no
way to do that with the standard WordPress function. Finally, I needed
the sub navigation on its own, apart from the main nav listing, and saw
no way to do it.

The problem (thought it's not a problem for most I bet) with the approach I'm taking with this plugin is of course that if you have a whole lot of sub navigation tiers your code is going to get quite messy. But then most designs aren't going to support too many navigation tiers either, and if they did they'd likely need different classes and such to be able to support the design so this still works.
