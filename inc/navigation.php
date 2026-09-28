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
const OPENCX_NAV_CARET_ITEMS = array( 'Ecosystem' );


/**
 * Classes marking a core/navigation block as fed by a footer classic menu,
 * mapped to the nav menu location that holds it.
 *
 * The footer lists are marked separately from OPENCX_NAV_MENU_CLASS on purpose:
 * patterns.css sizes the header row with that class (fixed 532px track, 217px
 * offset, nowrap), which would force the footer's two short lists into the same
 * one-line track.
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
 * core/navigation has a __unstableLocation attribute that does exactly this,
 * but every function backing it (block_core_navigation_get_menu_items_at_location()
 * and friends) is declared inside `if ( defined( 'IS_GUTENBERG_PLUGIN' ) )`
 * in wp-includes/blocks/navigation.php, so it does not exist unless the
 * Gutenberg plugin is active. This theme ships no plugins, hence this filter.
 *
 * A `ref` attribute is deliberately not supported: core reads it as the id of a
 * wp_navigation post and replaces the injected inner blocks with that post's
 * content, so a classic menu would never render.
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
 * wp_get_nav_menu_items() returns every item in a single flat array related by
 * menu_item_parent, so the hierarchy is rebuilt level by level.
 *
 * @param array  $items       Menu items as returned by wp_get_nav_menu_items().
 * @param bool   $with_carets Whether the rendering navigation block asked for the
 *                           decorative carets of OPENCX_NAV_CARET_ITEMS.
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

		$attrs = array(
			'label'         => $item->title,
			'type'          => 'custom',
			'url'           => $item->url,
			'isTopLevelLink' => empty( $children ),
		);

		// Only the top level carries a caret: deeper items are already inside an
		// open submenu, where core already draws one for the submenu itself.
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
 * Appends the decorative caret to the navigation links that asked for one.
 *
 * core/navigation-link has no slot for an icon, and turning "Ecosystem" into a
 * core/navigation-submenu would render it as a toggle button that opens nothing.
 * So the same chevron core uses for real submenus is appended to the link, inside
 * a span that reuses core's `.wp-block-navigation__submenu-icon` class, which is
 * what supplies the 0.6em box, the 0.25em gap and the `stroke: currentColor`.
 * Reusing the class is the point: the caret is then the same 12x12 glyph and the
 * same colour as the one on "Industries", with no new CSS to keep in sync.
 *
 * The svg carries `aria-hidden="true"`, so the accessible name of the link stays
 * "Ecosystem" and the caret is not announced.
 *
 * @param string $block_content Rendered markup of the block.
 * @param array  $block         Parsed block.
 * @return string
 */
function opencx_navigation_decorative_caret( $block_content, $block ) {
	if ( empty( $block['attrs']['ocxCaret'] ) ) {
		return $block_content;
	}

	$caret = '<span class="wp-block-navigation__submenu-icon">'
		. block_core_shared_navigation_render_submenu_icon()
		. '</span>';

	return str_replace( '</a>', $caret . '</a>', $block_content );
}
add_filter( 'render_block', 'opencx_navigation_decorative_caret', 10, 2 );
