-Usage
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
		$elem->if_before_active($markup); // echo markup if element is before an active element (both parents and current)
		$elem->if_after_active($markup);	// echo markup if element is after an active element (both parents and current)
	?>

Here are the variables navigation elements have: 
	<?php
		$elem->id;				// In the NavigableWP class, the id is the id of the nav menu item. In the NavigableWPPages class it is the id of the actual page
		$elem->object_id;	// In the NavigableWP class, this is the id of the page referred to by the nav menu item. In the NavigableWPPages class it is the id of the actual page...again.
		$elem->order;			// Not too useful. The menu order of the item
		$elem->url;				// Full url of the page/post.
		$elem->title;			// Name of the element.
		$elem->parent_id; // ID of the parent of the element. Root elements have a parent id of 0.
		$elem->slug;			// The slug/url title/post_title of the element's page.
	?>


-Motivation

Navigation in wordpress is handled like so:  
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

I found this a bit unsatisfactory - I'd much rather have some kind of data structure to work with that would let me iterate over nav elements so I have complete control over the markup. I also needed to assign a class to the nav element that comes before the active element and saw no way to do that with the standard wordpress function.

The problem with the whole approach is of course that if you have a whole lot of sub navigation tiers your code is going to get quite messy. But then most designs aren't going to support too many navigation tiers either, and if they did they'd likely need different classes and such to be able to support the design so this still works.