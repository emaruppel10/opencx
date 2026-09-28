/*
 * OpenCX stat count-up ("Layout / 55 /", the "Results that come from
 * understanding your business" section).
 *
 * Progressive enhancement only: the numbers ship as real text in the HTML, so
 * the section is complete without this file, search engines and screen readers
 * read the final values, and the Site Editor can still edit the copy. This
 * file only replaces a number with the same number while it climbs to it.
 *
 * The target is parsed out of the element's own text rather than duplicated in
 * a data attribute, because the text is the only copy the editor ever touches.
 * A mismatch between a `data-count-to` and the visible copy would be a silent
 * lie; a parse failure just means no animation.
 *
 * What it adds, and nothing else:
 *   - counts each value up once, when the card is 40% into the viewport
 *   - decelerates into the target instead of overshooting it
 *   - keeps the source's own precision, so "3.5x" never passes through "3.4781x"
 *   - keeps the suffix, so "40%" and "3x" keep their unit
 *
 * No dependencies, no build step.
 */
( function () {
	'use strict';

	var SELECTOR = '.ocx-l55__value';
	var DURATION = 1600;

	/** Leading number, then anything that follows it (the unit). */
	var PATTERN = /^(\d+(?:\.\d+)?)(\D*)$/;

	/**
	 * Reads the number and its unit out of an element's text.
	 *
	 * Only a dot is accepted as a decimal separator, on purpose: treating a
	 * comma as one would read "1,200" as 1.2.
	 *
	 * @param {string} text Trimmed text content.
	 * @return {{target: number, decimals: number, suffix: string}|null} Null if there is no number.
	 */
	function parse( text ) {
		var match = PATTERN.exec( text );
		var decimals;

		if ( ! match ) {
			return null;
		}

		decimals = match[ 1 ].indexOf( '.' ) === -1 ? 0 : match[ 1 ].split( '.' )[ 1 ].length;

		return {
			target: parseFloat( match[ 1 ] ),
			decimals: decimals,
			suffix: match[ 2 ]
		};
	}

	/**
	 * Ease-out quintic: fast at the start, long settle at the end.
	 *
	 * Deliberately not a spring. A spring overshoots its target, which for a
	 * statistic means briefly showing 42% for a card that claims 40%, and the
	 * number is the one thing on this section that has to be literally true.
	 *
	 * @param {number} p Progress, 0 to 1.
	 * @return {number} Eased progress, 0 to 1.
	 */
	function ease( p ) {
		return 1 - Math.pow( 1 - p, 5 );
	}

	/**
	 * Runs the count.
	 *
	 * @param {Element}  el     The `.ocx-l55__value` element.
	 * @param {Object}   parsed Result of `parse`.
	 */
	function count( el, parsed ) {
		var start = null;

		/**
		 * One frame.
		 *
		 * @param {number} now Timestamp from rAF.
		 */
		function frame( now ) {
			var p;

			if ( null === start ) {
				start = now;
			}

			p = Math.min( ( now - start ) / DURATION, 1 );

			el.textContent = ( parsed.target * ease( p ) ).toFixed( parsed.decimals ) + parsed.suffix;

			if ( p < 1 ) {
				window.requestAnimationFrame( frame );
			} else {
				/* Land on the exact source text, not on a re-formatted copy of it. */
				el.textContent = el.getAttribute( 'data-ocx-count-source' );
				el.removeAttribute( 'data-ocx-count-source' );
			}
		}

		window.requestAnimationFrame( frame );
	}

	/**
	 * Wires one value.
	 *
	 * @param {Element} el The `.ocx-l55__value` element.
	 */
	function init( el ) {
		var parsed = parse( ( el.textContent || '' ).trim() );
		var observer;

		if ( ! parsed || 0 === parsed.target ) {
			return;
		}

		/* Nothing to do without an observer, or when motion is not wanted: the
		   markup already says the right number, so the element is left untouched. */
		if ( ! window.IntersectionObserver || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		/* Stashed so the last frame can restore the editor's own string. */
		el.setAttribute( 'data-ocx-count-source', el.textContent.trim() );

		observer = new IntersectionObserver( function ( entries ) {
			var i;

			for ( i = 0; i < entries.length; i++ ) {
				if ( entries[ i ].isIntersecting ) {
					observer.disconnect();
					count( el, parsed );
					return;
				}
			}
		}, { threshold: [ 0.4 ] } );

		observer.observe( el );
	}

	/**
	 * Sets up every value on the page.
	 */
	function initAll() {
		var values = document.querySelectorAll( SELECTOR );
		var i;

		for ( i = 0; i < values.length; i++ ) {
			init( values[ i ] );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
