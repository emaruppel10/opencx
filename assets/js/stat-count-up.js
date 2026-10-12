/*
 * OpenCX stat count-up (modern AI smooth counter animation).
 *
 * Progressive enhancement:
 * - Real numbers ship in HTML for SEO, screen readers, and Site Editor copy.
 * - Respects prefers-reduced-motion (skips animation entirely).
 * - Triggers smoothly via IntersectionObserver when entering the viewport.
 * - Re-triggers every time the section enters the viewport (re-animates on every pass).
 * - Ultra-smooth custom ease-out curve (fluid deceleration without stalling).
 * - Staggered micro-timing across cards in the same row.
 * - Clean teardown & reset when completely scrolled off-screen (hysteresis prevents flicker).
 *
 * Zero external dependencies.
 */
( function () {
	'use strict';

	var SELECTOR = '.ocx-business-outcomes__card-value, .ocx-l55__value, [data-ocx-counter]';
	var DURATION = 1450; // Milliseconds per counter animation

	/** Matches optional prefix, the number (with optional decimals), and unit suffix. */
	var PATTERN = /^([^\d.]*)(\d+(?:\.\d+)?)(\D*)$/;

	/**
	 * Reads prefix, number, decimals and suffix from an element's text.
	 *
	 * @param {string} text Raw text content.
	 * @return {{prefix: string, target: number, decimals: number, suffix: string, raw: string}|null}
	 */
	function parse( text ) {
		var match = PATTERN.exec( text );
		if ( ! match ) {
			return null;
		}

		var numStr = match[ 2 ];
		var decimals = numStr.indexOf( '.' ) === -1 ? 0 : numStr.split( '.' )[ 1 ].length;

		return {
			prefix: match[ 1 ] || '',
			target: parseFloat( numStr ),
			decimals: decimals,
			suffix: match[ 3 ] || '',
			raw: text
		};
	}

	/**
	 * Custom smooth ease-out curve (power 3.2):
	 * Starts with an energetic fluid sweep, then smoothly decelerates
	 * and glides gently into the target value like modern AI landing pages.
	 *
	 * @param {number} p Progress from 0 to 1.
	 * @return {number} Eased progress from 0 to 1.
	 */
	function easeOut( p ) {
		return 1 - Math.pow( 1 - p, 3.2 );
	}

	/**
	 * Computes a subtle stagger delay for cards in the same horizontal row.
	 *
	 * @param {Element} cardEl Card or trigger element.
	 * @return {number} Delay in milliseconds.
	 */
	function getStaggerDelay( cardEl ) {
		var parent = cardEl.parentElement;
		if ( ! parent ) {
			return 0;
		}

		var siblings = Array.prototype.slice.call( parent.children );
		var index = siblings.indexOf( cardEl );
		if ( index <= 0 ) {
			return 0;
		}

		// Only apply stagger if elements share the same vertical baseline (horizontal row)
		var firstRect = siblings[ 0 ].getBoundingClientRect();
		var thisRect = cardEl.getBoundingClientRect();
		if ( Math.abs( firstRect.top - thisRect.top ) > 35 ) {
			return 0; // Stacked vertically (mobile / 1 column)
		}

		return Math.min( index * 65, 300 );
	}

	/**
	 * Starts the smooth count-up animation for an item.
	 *
	 * @param {Object} item State object.
	 */
	function startCount( item ) {
		if ( item.isAnimating || item.hasAnimated ) {
			return;
		}

		item.isAnimating = true;

		var delay = getStaggerDelay( item.triggerEl );

		item.staggerTimer = setTimeout( function () {
			var startTime = null;

			function frame( now ) {
				if ( null === startTime ) {
					startTime = now;
				}

				var elapsed = now - startTime;
				var p = Math.min( elapsed / DURATION, 1 );
				var eased = easeOut( p );

				var currentNum;
				if ( item.parsed.decimals === 0 ) {
					currentNum = Math.round( item.parsed.target * eased );
				} else {
					currentNum = ( item.parsed.target * eased ).toFixed( item.parsed.decimals );
				}

				item.el.textContent = item.parsed.prefix + currentNum + item.parsed.suffix;

				if ( p < 1 ) {
					item.rafId = window.requestAnimationFrame( frame );
				} else {
					// Final frame lands precisely on the author's original string
					item.el.textContent = item.parsed.raw;
					item.isAnimating = false;
					item.hasAnimated = true;
					item.rafId = null;
				}
			}

			item.rafId = window.requestAnimationFrame( frame );
		}, delay );
	}

	/**
	 * Resets an item's counter back to zero when scrolled completely out of view.
	 *
	 * @param {Object} item State object.
	 */
	function resetCount( item ) {
		if ( item.staggerTimer ) {
			clearTimeout( item.staggerTimer );
			item.staggerTimer = null;
		}
		if ( item.rafId ) {
			window.cancelAnimationFrame( item.rafId );
			item.rafId = null;
		}

		item.isAnimating = false;
		item.hasAnimated = false;

		// Reset value to 0 while off-screen so the next entrance starts cleanly
		var zeroNum = ( 0 ).toFixed( item.parsed.decimals );
		item.el.textContent = item.parsed.prefix + zeroNum + item.parsed.suffix;
	}

	/**
	 * Initializes all counter elements on the page.
	 */
	function initAll() {
		/* Accessibility check: users requesting reduced motion keep original copy */
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		/* Without IntersectionObserver, keep static numbers */
		if ( ! window.IntersectionObserver ) {
			return;
		}

		var elements = document.querySelectorAll( SELECTOR );
		if ( ! elements.length ) {
			return;
		}

		var items = [];
		var triggerMap = new Map();

		for ( var i = 0; i < elements.length; i++ ) {
			var el = elements[ i ];
			var text = ( el.textContent || '' ).trim();
			var parsed = parse( text );

			if ( ! parsed || 0 === parsed.target ) {
				continue;
			}

			var triggerEl = el.closest( '.ocx-business-outcomes__card' ) ||
			                el.closest( '.ocx-l55__card' ) ||
			                el;

			var item = {
				el: el,
				triggerEl: triggerEl,
				parsed: parsed,
				rafId: null,
				staggerTimer: null,
				isAnimating: false,
				hasAnimated: false
			};

			items.push( item );

			var list = triggerMap.get( triggerEl );
			if ( ! list ) {
				list = [];
				triggerMap.set( triggerEl, list );
			}
			list.push( item );

			// If initially outside the viewport, initialize to 0 so first scroll animates
			var rect = triggerEl.getBoundingClientRect();
			var inView = rect.top < window.innerHeight && rect.bottom > 0;
			if ( ! inView ) {
				var zeroNum = ( 0 ).toFixed( parsed.decimals );
				el.textContent = parsed.prefix + zeroNum + parsed.suffix;
			}
		}

		if ( ! items.length ) {
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				var targetItems = triggerMap.get( entry.target );
				if ( ! targetItems ) {
					return;
				}

				// When entering viewport (at least 15% visible)
				if ( entry.isIntersecting && entry.intersectionRatio >= 0.15 ) {
					targetItems.forEach( function ( it ) {
						if ( ! it.hasAnimated && ! it.isAnimating ) {
							startCount( it );
						}
					} );
				}
				// When completely scrolled off-screen (0% visible)
				else if ( ! entry.isIntersecting || entry.intersectionRatio === 0 ) {
					targetItems.forEach( function ( it ) {
						resetCount( it );
					} );
				}
			} );
		}, {
			root: null,
			threshold: [ 0, 0.15 ]
		} );

		triggerMap.forEach( function ( _, triggerEl ) {
			observer.observe( triggerEl );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
