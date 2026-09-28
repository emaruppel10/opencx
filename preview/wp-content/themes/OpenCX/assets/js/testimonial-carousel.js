/*
 * OpenCX testimonial carousel ("Testimonial / 23 /").
 *
 * Progressive enhancement only: the pattern ships fully functional in HTML. The track is a
 * CSS scroll-snap container, so it scrolls and snaps with no JavaScript at all; the dots are
 * rendered as `disabled` buttons and the arrows are `display: none` until this file has wired
 * them up. If this file never runs, the section still looks and behaves exactly like the
 * static version: a scrollable row of six cards with decorative indicators.
 *
 * What it adds, and nothing else:
 *   - reveals the two arrow buttons and enables the dots
 *   - moves one step per click, with smooth scroll (or instant with reduced motion)
 *   - keeps the dots in sync with the scroll position, whichever way the scroll happened
 *   - makes the track reachable with the keyboard (it is a scroll container, so browsers
 *     do not make it focusable on their own) and wires Left/Right/Home/End on it
 *
 * One dot per reachable scroll position, not per card. Three and a half cards fit at 1440, so
 * only four of the six cards can ever reach the left edge and six dots would have three of
 * them pointing at the same offset. The dot count is therefore derived from the layout and
 * changes with it: four on desktop, six at 390 where the card is `100%` and each one is a
 * position of its own.
 *
 * No dependencies, no build step, ~8 KB.
 */
( function () {
	'use strict';

	var SELECTOR = '.ocx-t23__section';

	/**
	 * Distance between two consecutive cards, measured from the layout instead of from
	 * `offsetWidth + gap` so it does not depend on reading the computed gap back.
	 *
	 * @param {Element[]} cards Card elements.
	 * @return {number} Pixels between card starts.
	 */
	function pitch( cards ) {
		if ( cards.length < 2 ) {
			return cards[ 0 ] ? cards[ 0 ].offsetWidth : 0;
		}

		return cards[ 1 ].offsetLeft - cards[ 0 ].offsetLeft;
	}

	/**
	 * Wires one section.
	 *
	 * @param {Element} section The `.ocx-t23__section` element.
	 */
	function init( section ) {
		var track = section.querySelector( '.ocx-t23__track' );
		var cards = Array.prototype.slice.call( section.querySelectorAll( '.ocx-t23__card' ) );
		var dotsWrap = section.querySelector( '.ocx-t23__dots' );
		var dots = Array.prototype.slice.call( section.querySelectorAll( '.ocx-t23__dot' ) );
		var prev = section.querySelector( '.ocx-t23__arrow--prev' );
		var next = section.querySelector( '.ocx-t23__arrow--next' );

		if ( ! track || cards.length < 2 || ! dotsWrap || ! prev || ! next ) {
			return;
		}

		var last = 0;
		var current = 0;
		var moving = false;
		var guard = 0;
		var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );

		/**
		 * How many scroll positions the track actually has.
		 *
		 * This is the number the dots are built from, and the number the arrows walk. It is
		 * deliberately not the card count: a position is a card at the left edge, so the last
		 * one is the end of the track, and the count follows the layout instead of the markup.
		 *
		 * The rounding is the whole trick. At 1440 the track scrolls to 1278 with a pitch of
		 * 437, which is 2.92 steps: flooring it would drop the end of the track and report
		 * three positions when four are reachable, so the count ceils and the end counts as
		 * one more. The cap is the card count, which is where the positions run out.
		 *
		 * @return {number} Positions, at least one.
		 */
		function positionCount() {
			var step = pitch( cards );
			var max = Math.max( 0, track.scrollWidth - track.clientWidth );

			if ( ! step ) {
				return 1;
			}

			return Math.min( Math.ceil( max / step ) + 1, cards.length );
		}

		/**
		 * Makes the dot list match the position count, reusing the buttons in the template.
		 *
		 * The template ships four, so on desktop there is nothing to add or remove and the
		 * static markup is what runs. Below the breakpoint that turns the card into `100%` and
		 * the track grows to six positions, and the extra dots are created here.
		 *
		 * @param {number} count Target number of dots.
		 */
		function syncDots( count ) {
			var i;
			var dot;

			for ( i = dots.length; i > count; i-- ) {
				dotsWrap.removeChild( dots[ i - 1 ] );
				dots.pop();
			}

			for ( i = dots.length; i < count; i++ ) {
				dot = document.createElement( 'button' );

				/*
				 * `disabled` on creation for the same reason the template dots have it: a dot
				 * that exists but is not wired is not a tab stop. `render` hands them all back.
				 */
				dot.type = 'button';
				dot.className = 'ocx-t23__dot';
				dot.disabled = true;

				dotsWrap.appendChild( dot );
				dots.push( dot );
			}
		}

		/**
		 * Re-reads the layout and re-derives everything that depends on it.
		 */
		function update() {
			syncDots( positionCount() );

			last = dots.length - 1;
			current = Math.min( Math.max( indexAtPosition(), 0 ), last );
		}

		/**
		 * Position index that matches a scroll offset.
		 *
		 * Rounding is what makes the last position reachable: the offset at the end of the
		 * track is a fraction of a step short of the next one, and rounding snaps it back to
		 * the last index instead of leaving it one short.
		 *
		 * @return {number} Index.
		 */
		function indexAtPosition() {
			var step = pitch( cards );

			if ( ! step ) {
				return 0;
			}

			return Math.min( Math.round( track.scrollLeft / step ), last );
		}

		/**
		 * Scroll offset that brings a position to the left edge.
		 *
		 * Every index up to `last` lands inside the track by construction, so this does not
		 * need the clamp that a card index would: the last position is the one that reaches
		 * the end exactly.
		 *
		 * @param {number} index Position index.
		 * @return {number} Pixels.
		 */
		function offsetFor( index ) {
			var max = Math.max( 0, track.scrollWidth - track.clientWidth );

			return Math.min( Math.max( index, 0 ) * pitch( cards ), max );
		}

		/**
		 * Paints the state: which dot is current, and which arrows have somewhere to go.
		 */
		function render() {
			var i;

			for ( i = 0; i < dots.length; i++ ) {
				/*
				 * `disabled` comes from the template so the dots are not tab stops without
				 * this file. Handing them back here is what turns them into controls.
				 */
				dots[ i ].disabled = false;

				if ( i === current ) {
					dots[ i ].setAttribute( 'aria-current', 'true' );
				} else {
					dots[ i ].removeAttribute( 'aria-current' );
				}

				/*
				 * The count is read from the DOM, not from the label written in the template,
				 * so a breakpoint that changes the position count does not leave a stale
				 * "de 4" behind. The wording is positions, not testimonials: a dot is a
				 * window over the row, and at 1440 the fourth one shows the last three cards.
				 */
				dots[ i ].setAttribute(
					'aria-label',
					'Ir a la posición ' + ( i + 1 ) + ' de ' + dots.length
				);
			}

			prev.disabled = 0 === current;
			next.disabled = last === current;
		}

		/**
		 * Scrolls to a position and sets the index directly, rather than letting the scroll
		 * handler derive it.
		 *
		 * The index is the *intent*, and it is deliberately not re-derived when the scroll
		 * finishes: the offset of the last position is the end of the track, so a re-derive
		 * would be measuring against a step that no longer divides the track. Manual scrolling
		 * is the only thing that re-derives from the position, and by then the position is the
		 * truth.
		 *
		 * @param {number} index Position index.
		 */
		function goTo( index ) {
			current = Math.min( Math.max( index, 0 ), last );

			moving = true;
			window.clearTimeout( guard );

			track.scrollTo( {
				left: offsetFor( current ),
				behavior: reduced.matches ? 'auto' : 'smooth'
			} );

			render();

			/*
			 * `scrollend` is the accurate signal but is still missing in some browsers, so the
			 * flag is also cleared on a timer. Without it, a single programmatic scroll would
			 * freeze the dots for the rest of the session.
			 */
			guard = window.setTimeout( settle, 800 );
		}

		/**
		 * Ends a programmatic scroll and hands control back to the scroll handler.
		 */
		function settle() {
			window.clearTimeout( guard );
			moving = false;
		}

		track.addEventListener(
			'scroll',
			function () {
				if ( moving ) {
					return;
				}

				current = indexAtPosition();
				render();
			},
			{ passive: true }
		);

		if ( 'onscrollend' in window ) {
			track.addEventListener( 'scrollend', settle );
		}

		/*
		 * Delegated on the wrapper rather than bound per dot, because `syncDots` can add and
		 * remove dots as the layout changes and a listener bound to a removed dot would go
		 * with it.
		 */
		dotsWrap.addEventListener( 'click', function ( event ) {
			var dot = event.target.closest( '.ocx-t23__dot' );

			if ( ! dot ) {
				return;
			}

			goTo( dots.indexOf( dot ) );
		} );

		prev.addEventListener( 'click', function () {
			goTo( current - 1 );
		} );

		next.addEventListener( 'click', function () {
			goTo( current + 1 );
		} );

		/*
		 * Keyboard access. A scroll container is not focusable unless something makes it so,
		 * and `wp:group` exposes no attributes to do it in the template, so the attributes are
		 * set here. `aria-roledescription` is what makes a screen reader announce "carousel"
		 * instead of the vaguer "group".
		 */
		track.setAttribute( 'tabindex', '0' );
		track.setAttribute( 'role', 'group' );
		track.setAttribute( 'aria-roledescription', 'carousel' );
		track.setAttribute( 'aria-label', 'Testimonios de clientes' );

		track.addEventListener( 'keydown', function ( event ) {
			var step = 0;

			if ( 'ArrowRight' === event.key ) {
				step = 1;
			} else if ( 'ArrowLeft' === event.key ) {
				step = -1;
			} else if ( 'Home' === event.key ) {
				goTo( 0 );
				event.preventDefault();
				return;
			} else if ( 'End' === event.key ) {
				goTo( last );
				event.preventDefault();
				return;
			} else {
				return;
			}

			goTo( current + step );
			event.preventDefault();
		} );

		/*
		 * The position count depends on the layout: the pitch changes at 390px, where the
		 * cards stop being 405 and become `100%`, which is also where the track grows from
		 * four positions to six. So the dots are rebuilt and the index re-derived.
		 */
		var resize = 0;

		window.addEventListener( 'resize', function () {
			window.clearTimeout( resize );
			resize = window.setTimeout( function () {
				update();
				render();
			}, 150 );
		} );

		update();
		render();

		/* Reveal the arrows last: they must never be on screen while they do nothing. */
		section.setAttribute( 'data-ocx-carousel', 'ready' );
	}

	/**
	 * Sets up every carousel on the page.
	 */
	function initAll() {
		var sections = document.querySelectorAll( SELECTOR );
		var i;

		for ( i = 0; i < sections.length; i++ ) {
			init( sections[ i ] );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
