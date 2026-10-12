<?php
/**
 * Navigation: renders a classic WordPress menu inside core/navigation.
 *
 * Lets Appearance -> Menus stay the single source of truth for the header
 * navigation and for the footer's two link lists, while core/navigation keeps
 * its native markup, overlay and Interactivity API behaviour. Child menu items
 * become core/navigation-submenu, so dropdowns need no extra configuration.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class marking a core/navigation block as fed by the `primary` classic menu.
 */
const OPENCX_NAV_MENU_CLASS = 'ocx-nav--menu';

/**
 * Extra class on a `core/navigation` block that asks for the decorative carets.
 *
 * Core only draws a caret on items that really have a submenu, which is right for
 * a control that opens something. Figma's Ecosystem navbar (#10227:110690) also
 * puts one on "Ecosystem", which has no children in the menu, so the caret there
 * is purely decorative and has to be asked for.
 *
 * It is scoped to the new navbar rather than applied to the `primary` menu
 * wholesale because the pages that still render the older header have not been
 * migrated yet, and a caret that only exists in the new design should not appear
 * in the old one. Adding the class to the block in `parts/header.html` is all it
 * takes to make it global.
 */
const OPENCX_NAV_CARET_CLASS = 'ocx-nav--caret';

/**
 * Top-level menu labels that get a decorative caret, with or without children.
 *
 * Matched on the label, not on the URL, so it survives a slug change.
 */
const OPENCX_NAV_CARET_ITEMS = array( 'Ecosystem', 'Industries' );

/**
 * Classes marking a core/navigation block as fed by a footer classic menu,
 * mapped to the nav menu location that holds it.
 */
const OPENCX_NAV_FOOTER_CLASSES = array(
	'ocx-nav--footer--a' => 'footer_a',
	'ocx-nav--footer--b' => 'footer_b',
);

/**
 * Resolves which registered nav menu location feeds a navigation block.
 *
 * @param string $class_name className attribute of the core/navigation block.
 * @return string Nav menu location slug, empty when the block is not menu-fed.
 */
function opencx_navigation_location( $class_name ) {
	$class_name = (string) $class_name;

	if ( str_contains( $class_name, OPENCX_NAV_MENU_CLASS ) ) {
		return 'primary';
	}

	foreach ( OPENCX_NAV_FOOTER_CLASSES as $class => $location ) {
		if ( str_contains( $class_name, $class ) ) {
			return $location;
		}
	}

	return '';
}

/**
 * Injects a registered menu as inner blocks of the marked navigation blocks.
 *
 * Runs on render_block_data so the injection happens before
 * render_block_core_navigation() builds the markup. Doing it on render_block
 * instead would mean replacing the <nav> element by hand and losing the
 * overlay menu that core ships.
 *
 * @param array $block Parsed block about to be rendered.
 * @return array
 */
function opencx_navigation_inject_menu( $block ) {
	if ( 'core/navigation' !== $block['blockName'] ) {
		return $block;
	}

	$class_name = $block['attrs']['className'] ?? '';
	$location   = opencx_navigation_location( $class_name );

	if ( ! $location ) {
		return $block;
	}

	// A hand-edited navigation block in the Site Editor wins over the menu.
	if ( ! empty( $block['innerBlocks'] ) ) {
		return $block;
	}

	$menu_id = get_nav_menu_locations()[ $location ] ?? 0;

	if ( ! $menu_id ) {
		return $block;
	}

	$menu = wp_get_nav_menu_object( $menu_id );

	if ( ! $menu ) {
		return $block;
	}

	$items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	if ( empty( $items ) ) {
		return $block;
	}

	$children = opencx_navigation_group_items( $items, str_contains( $class_name, OPENCX_NAV_CARET_CLASS ) );

	$block['innerBlocks']  = $children;
	$block['innerHTML']    = '';
	$block['innerContent'] = array_fill( 0, count( $children ), null );

	return $block;
}
add_filter( 'render_block_data', 'opencx_navigation_inject_menu' );

/**
 * Converts the flat list of menu items into a block tree.
 *
 * @param array $items       Menu items as returned by wp_get_nav_menu_items().
 * @param bool  $with_carets Whether the rendering navigation block asked for decorative carets.
 * @return array[] Block definitions for core/navigation-link|Submenu.
 */
function opencx_navigation_group_items( array $items, $with_carets = false ) {
	$by_parent = array();

	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}

	return opencx_navigation_build_level( $by_parent, 0, $with_carets );
}

/**
 * Builds one level of the menu tree.
 *
 * @param array $by_parent   Menu items indexed by parent ID.
 * @param int   $parent_id   Parent ID to build, 0 for the top level.
 * @param bool  $with_carets See opencx_navigation_group_items().
 * @return array[] Block definitions.
 */
function opencx_navigation_build_level( array $by_parent, $parent_id, $with_carets = false ) {
	if ( empty( $by_parent[ $parent_id ] ) ) {
		return array();
	}

	$blocks = array();

	foreach ( $by_parent[ $parent_id ] as $item ) {
		$children = opencx_navigation_build_level( $by_parent, (int) $item->ID, $with_carets );

		// Detect if this link points to the current page.
		$is_current = false;
		if ( ! empty( $_SERVER['REQUEST_URI'] ) && ! empty( $item->url ) ) {
			$req_path  = untrailingslashit( (string) wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
			$item_path = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
			if ( '' !== $item_path && $req_path === $item_path ) {
				$is_current = true;
			}
		}

		$attrs = array(
			'label'          => $item->title,
			'type'           => 'custom',
			'url'            => $item->url,
			'isTopLevelLink' => empty( $children ),
			'className'      => $is_current ? 'current-menu-item ocx-nav-link--active' : '',
		);

		// Top level decorative caret for items in OPENCX_NAV_CARET_ITEMS.
		if ( $with_carets && 0 === $parent_id && in_array( $item->title, OPENCX_NAV_CARET_ITEMS, true ) ) {
			$attrs['ocxCaret'] = true;
		}

		if ( $children ) {
			$blocks[] = array(
				'blockName'    => 'core/navigation-submenu',
				'attrs'        => $attrs,
				'innerBlocks'  => $children,
				'innerHTML'    => '',
				'innerContent' => array_fill( 0, count( $children ), null ),
			);

			continue;
		}

		$blocks[] = array(
			'blockName'    => 'core/navigation-link',
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}

	return $blocks;
}

/**
 * Appends the OpenCX plus caret to the navigation links that asked for one.
 *
 * @param string $block_content Rendered markup of the block.
 * @param array  $block         Parsed block.
 * @return string
 */
function opencx_navigation_decorative_caret( $block_content, $block ) {
	if ( empty( $block['attrs']['ocxCaret'] ) ) {
		return $block_content;
	}

	$caret = '<span class="ocx-nav-plus" aria-hidden="true" style="display:inline-flex;align-items:center;margin-left:6px;">'
		. '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">'
		. '<path d="M5 12H19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>'
		. '<path d="M12 5V19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg></span>';

	return str_replace( '</a>', $caret . '</a>', $block_content );
}
add_filter( 'render_block', 'opencx_navigation_decorative_caret', 10, 2 );

/**
 * Ensures header template parts dynamically render links from the Primary menu
 * for both desktop navbar and mobile drawer.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function opencx_dynamic_header_nav( $block_content, $block ) {
	if ( 'core/template-part' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$slug = $block['attrs']['slug'] ?? '';
	if ( ! str_contains( $slug, 'header' ) ) {
		return $block_content;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = $locations['primary'] ?? 0;
	if ( ! $menu_id ) {
		return $block_content;
	}

	$items = wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) );
	if ( empty( $items ) ) {
		return $block_content;
	}

	// Group items by parent ID.
	$by_parent = array();
	foreach ( $items as $it ) {
		$parent_id = (int) $it->menu_item_parent;
		$by_parent[ $parent_id ][] = $it;
	}

	$top_items = $by_parent[0] ?? array();
	$current_path = ! empty( $_SERVER['REQUEST_URI'] ) ? untrailingslashit( (string) wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) : '';

	// Build desktop HTML.
	$desktop_links = '';
	foreach ( $top_items as $item ) {
		$item_path  = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
		$is_active  = ( '' !== $item_path && $current_path === $item_path );
		$active_cls = $is_active ? ' ocx-nav-link--active' : '';

		$children   = $by_parent[ (int) $item->ID ] ?? array();
		$has_caret  = ! empty( $children ) || in_array( $item->title, OPENCX_NAV_CARET_ITEMS, true );
		$caret_svg  = '';
		if ( $has_caret ) {
			$caret_svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="ocx-nav-plus" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
				. '<path d="M5 12H19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>'
				. '<path d="M12 5V19" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>'
				. '</svg>';
		}

		if ( ! empty( $children ) ) {
			$dropdown_links = '';
			foreach ( $children as $child ) {
				$child_path       = untrailingslashit( (string) wp_parse_url( $child->url, PHP_URL_PATH ) );
				$is_child_active  = ( '' !== $child_path && $current_path === $child_path );
				$child_active_cls = $is_child_active ? ' ocx-nav-dropdown__link--active' : '';

				$dropdown_links .= sprintf(
					'<a href="%s" class="ocx-nav-dropdown__link%s" role="menuitem"><span>%s</span></a>',
					esc_url( $child->url ),
					esc_attr( $child_active_cls ),
					esc_html( $child->title )
				);
			}

			$desktop_links .= sprintf(
				'<div class="ocx-nav-item ocx-nav-item--has-dropdown">' .
					'<a href="%s" class="ocx-nav-link%s" aria-haspopup="true" aria-expanded="false"><span>%s</span>%s</a>' .
					'<div class="ocx-nav-dropdown" role="menu" aria-label="%s Submenu">%s</div>' .
				'</div>',
				esc_url( $item->url ),
				esc_attr( $active_cls ),
				esc_html( $item->title ),
				$caret_svg,
				esc_attr( $item->title ),
				$dropdown_links
			);
		} else {
			$desktop_links .= sprintf(
				'<div class="ocx-nav-item">' .
					'<a href="%s" class="ocx-nav-link%s"><span>%s</span>%s</a>' .
				'</div>',
				esc_url( $item->url ),
				esc_attr( $active_cls ),
				esc_html( $item->title ),
				$caret_svg
			);
		}
	}

	// Build mobile drawer HTML.
	$mobile_items = '';
	foreach ( $top_items as $item ) {
		$item_path  = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
		$is_active  = ( '' !== $item_path && $current_path === $item_path );
		$active_cls = $is_active ? ' ocx-mobile-nav__link--active' : '';

		$children   = $by_parent[ (int) $item->ID ] ?? array();
		$has_caret  = ! empty( $children ) || in_array( $item->title, OPENCX_NAV_CARET_ITEMS, true );
		$caret_img  = '';
		if ( $has_caret ) {
			$caret_img = '<img src="/wp-content/themes/OpenCX/assets/images/icon-nav-plus.svg" alt="" class="ocx-mobile-nav__plus" width="18" height="18" aria-hidden="true" />';
		}

		if ( ! empty( $children ) ) {
			$mobile_sublinks = '';
			foreach ( $children as $child ) {
				$child_path       = untrailingslashit( (string) wp_parse_url( $child->url, PHP_URL_PATH ) );
				$is_child_active  = ( '' !== $child_path && $current_path === $child_path );
				$child_active_cls = $is_child_active ? ' ocx-mobile-nav__sublink--active' : '';

				$mobile_sublinks .= sprintf(
					'<li class="ocx-mobile-nav__subitem"><a href="%s" class="ocx-mobile-nav__sublink%s">%s</a></li>',
					esc_url( $child->url ),
					esc_attr( $child_active_cls ),
					esc_html( $child->title )
				);
			}

			$mobile_items .= sprintf(
				'<li class="ocx-mobile-nav__item ocx-mobile-nav__item--has-dropdown">' .
					'<div class="ocx-mobile-nav__row">' .
						'<a href="%s" class="ocx-mobile-nav__link%s"><span>%s</span></a>' .
						'<button type="button" class="ocx-mobile-nav__toggle" aria-expanded="false" aria-label="Toggle %s submenu">' .
							'%s' .
						'</button>' .
					'</div>' .
					'<ul class="ocx-mobile-nav__submenu" aria-hidden="true">%s</ul>' .
				'</li>',
				esc_url( $item->url ),
				esc_attr( $active_cls ),
				esc_html( $item->title ),
				esc_attr( $item->title ),
				$caret_img,
				$mobile_sublinks
			);
		} else {
			$mobile_items .= sprintf(
				'<li class="ocx-mobile-nav__item">' .
					'<a href="%s" class="ocx-mobile-nav__link%s"><span>%s</span>%s</a>' .
				'</li>',
				esc_url( $item->url ),
				esc_attr( $active_cls ),
				esc_html( $item->title ),
				$caret_img
			);
		}
	}

	// Replace inside <nav class="ocx-nav--menu"...> or <div class="ocx-nav--menu"...>
	$block_content = preg_replace(
		'/(<(?:nav|div)[^>]*class="[^"]*ocx-nav--menu[^"]*"[^>]*>).*?(<\/(?:nav|div)>(?=\s*(?:<!--.*?-->\s*)?<div[^>]*class="[^"]*ocx-header__actions))/s',
		'$1' . $desktop_links . '$2',
		$block_content
	);

	// Replace inside <ul class="ocx-mobile-nav__list"...>
	$block_content = preg_replace(
		'/(<ul[^>]*class="[^"]*ocx-mobile-nav__list[^"]*"[^>]*>).*?(<\/ul>(?=\s*<div[^>]*class="[^"]*ocx-mobile-nav__actions))/s',
		'$1' . $mobile_items . '$2',
		$block_content
	);

	return $block_content;
}
add_filter( 'render_block', 'opencx_dynamic_header_nav', 20, 2 );
