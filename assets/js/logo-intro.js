/*
 * OpenCX logo intro (the home hero).
 *
 * The header reserves a 157x42 slot for the logo -- see BACKLOG.md, "Slot del
 * logo: 157x42 en ambos headers" -- and today that slot is an empty group,
 * because the Site Editor has no site logo set, so `core/site-logo` renders
 * nothing. The hero paints the real logo at 147x35 right above the prompt box.
 *
 * This file makes the logo appear in that empty slot and then fly to the box,
 * leaving the page byte for byte as it is today.
 *
 * Why a clone and not the real logo: the two live in different branches of the
 * DOM, so no CSS transition can connect them. The clone is a bare `img` pointing
 * at the `src` the hero already downloaded, so the whole effect costs no request
 * and no bytes, and it is discarded afterwards.
 *
 * FLIP: measure both boxes, paint the clone at the start, then animate only
 * `transform` by the difference. Nothing in the flow is ever measured or
 * written, so there is no layout shift at any point -- the header keeps its
 * 157x42 and the hero its 147x35 whether the intro runs, is skipped, or is cut
 * off halfway.
 *
 * The end state is the only state that matters. `cleanup()` runs from a
 * `finally`, from `pagehide` (so a bfcache restore can never find a hidden
 * logo), and it never depends on the animation having finished.
 *
 * No dependencies, no build step.
 */
( function () {
	'use strict';

	var HERO = '.ocx-hero__logo';
	var SLOT = '.ocx-header__logo';
	var FORM = '.ocx-hero__form';
	var CLONE = 'ocx-logo-intro__clone';

	/** Session flag: the intro is a greeting, not something to replay on every reload. */
	var FLAG = 'ocx-logo-intro';

	var APPEAR = 300;
	var HOLD = 200;
	var FLIGHT = 1000;
	var FADE = 220;

	/** Peak of the landing flash, in the same unit as `--ocx-beam-glow`. */
	var GLOW = 26;

	var hero = null;
	var slot = null;
	var img = null;
	var clone = null;

	/**
	 * Sleeps.
	 *
	 * @param {number} ms Milliseconds.
	 * @return {Promise} Resolves when the time is up.
	 */
	function wait( ms ) {
		return new Promise( function ( resolve ) {
			window.setTimeout( resolve, ms );
		} );
	}

	/**
	 * Resolves once the logo is safe to clone.
	 *
	 * Cloning a half-decoded image would put a half-painted logo in the header,
	 * which reads as a glitch rather than as an entrance. `error` resolves too:
	 * a broken logo should leave the page as it is, not hang the intro.
	 *
	 * @param {HTMLImageElement} img The hero logo.
	 * @return {Promise} Resolves when the image is ready.
	 */
	function decoded( img ) {
		if ( ! img.complete ) {
			return new Promise( function ( resolve ) {
				img.addEventListener( 'load', resolve, { once: true } );
				img.addEventListener( 'error', resolve, { once: true } );
			} );
		}

		return img.decode ? img.decode().catch( function () {} ) : null;
	}

	/**
	 * Resolves once the page has stopped moving.
	 *
	 * The hero logo sits in a vertical stack under the H1, so a late web font can
	 * push it down after first paint. Flying to a stale box would land the logo in
	 * the wrong place. The cap keeps a slow Google Fonts request from holding the
	 * intro hostage.
	 *
	 * @return {Promise|null} Resolves when the page is settled, or null if there is nothing to wait for.
	 */
	function settled() {
		var fonts = document.fonts && document.fonts.ready;

		return fonts ? Promise.race( [ fonts, wait( 1200 ) ] ) : null;
	}

	/**
	 * Whether the intro has already run in this session.
	 *
	 * @return {boolean} True if it should be skipped.
	 */
	function seen() {
		try {
			return null !== window.sessionStorage.getItem( FLAG );
		} catch ( e ) {
			/* Storage can be blocked outright; then the intro simply runs per load. */
			return false;
		}
	}

	/**
	 * Marks the intro as used.
	 */
	function remember() {
		try {
			window.sessionStorage.setItem( FLAG, '1' );
		} catch ( e ) {}
	}

	/**
	 * The landing flash on the prompt box.
	 *
	 * Driven by `--ocx-beam-glow`, a registered length, so the rise and the fall
	 * are both real transitions instead of a jump. The `filter` itself lives in
	 * CSS behind `data-ocx-beam-flash` and only exists for the length of the
	 * pulse: a permanent `drop-shadow(0 0 0px)` is not quite a no-op on an
	 * antialiased 1px ring, and the box at rest should stay exactly as it was.
	 *
	 * @return {void}
	 */
	function flash() {
		var form = document.querySelector( FORM );

		if ( ! form ) {
			return;
		}

		form.setAttribute( 'data-ocx-beam-flash', '' );
		form.style.transitionDuration = '120ms';
		form.style.setProperty( '--ocx-beam-glow', GLOW + 'px' );

		window.setTimeout( function () {
			form.style.transitionDuration = '700ms';
			form.style.setProperty( '--ocx-beam-glow', '0px' );

			window.setTimeout( function () {
				form.removeAttribute( 'data-ocx-beam-flash' );
				form.style.removeProperty( 'transition-duration' );
				form.style.removeProperty( '--ocx-beam-glow' );
			}, 720 );
		}, 150 );
	}

	/**
	 * Puts the page back exactly as it was.
	 *
	 * @param {Element|null} clone The flying clone, if it exists.
	 * @return {void}
	 */
	function cleanup( clone ) {
		if ( clone && clone.parentNode ) {
			clone.parentNode.removeChild( clone );
		}

		if ( hero ) {
			hero.style.removeProperty( 'opacity' );
		}
	}

	/**
	 * The flight.
	 *
	 * The middle keyframe is offset perpendicular to the chord, not just
	 * half-way along it: that is what turns a straight slide into an arc, and the
	 * offset is capped so a long diagonal does not swing out like a pendulum.
	 *
	 * @param {Element}    clone  The clone, already sitting in the header slot.
	 * @param {DOMRect}    start  Where the clone is.
	 * @param {DOMRect}    end    Where the hero logo is.
	 * @return {Promise} Resolves when the clone has landed.
	 */
	function fly( clone, start, end ) {
		var dx = ( end.left + end.width / 2 ) - ( start.left + start.width / 2 );
		var dy = ( end.top + end.height / 2 ) - ( start.top + start.height / 2 );
		var length = Math.sqrt( dx * dx + dy * dy );
		var bow = Math.min( 28, length * 0.08 );
		var sx = end.width / start.width;
		var sy = end.height / start.height;
		var frames;

		/* Same size at both ends with the current slot, so the scale is 1 and only
		   the translation is real work. It stays in the code so a future width
		   change cannot silently stretch the logo mid-flight. */
		if ( ! length ) {
			return wait( FLIGHT );
		}

		frames = [
			{ transform: 'translate(0, 0) scale(1)' },
			{
				transform: 'translate(' + ( dx / 2 + ( dy / length ) * bow ) + 'px, ' +
					( dy / 2 - ( dx / length ) * bow ) + 'px) scale(' +
					( 1 + ( sx - 1 ) / 2 ) + ', ' + ( 1 + ( sy - 1 ) / 2 ) + ')'
			},
			{ transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(' + sx + ', ' + sy + ')' }
		];

		return clone.animate( frames, {
			duration: FLIGHT,
			easing: 'cubic-bezier(0.4, 0.05, 0.2, 1)',
			fill: 'forwards'
		} ).finished.catch( function () {} );
	}

	/**
	 * Runs the intro, if it should run at all.
	 *
	 * @return {Promise} Resolves once the page is back to its resting state.
	 */
	function play() {
		var start;
		var end;

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return null;
		}

		hero = document.querySelector( HERO );
		slot = document.querySelector( SLOT );
		img = hero ? hero.querySelector( 'img' ) : null;

		/*
		 * Three ways to legitimately do nothing, all of them normal states of the
		 * site and none of them errors:
		 *   - no hero logo (an interior page, or the block was removed)
		 *   - no reserved slot (a template without the header group)
		 *   - the slot already holds a real logo, which is what happens as soon as
		 *     someone sets a site logo in the Site Editor: the intro would put a
		 *     second logo next to the first one
		 */
		if ( ! img || ! slot || slot.querySelector( 'img' ) ) {
			return null;
		}

		remember();

		return ( decoded( img ) || Promise.resolve() )
			.then( function () {
				return settled();
			} )
			.then( function () {
				/*
				 * The slot is a fixed 157x42 flex box with the header's own
				 * `align-items: center`, so appending the clone puts it exactly where
				 * a real site logo would render: left aligned, vertically centred,
				 * with nothing in the flow moving.
				 */
				clone = img.cloneNode( true );
				clone.className = CLONE;
				clone.setAttribute( 'aria-hidden', 'true' );
				clone.setAttribute( 'alt', '' );
				slot.appendChild( clone );

				/* The real logo stays put but out of sight until the clone lands. */
				hero.style.opacity = '0';

				return clone.animate( [
					{ opacity: 0, transform: 'scale(0.94)' },
					{ opacity: 1, transform: 'scale(1)' }
				], {
					duration: APPEAR,
					easing: 'cubic-bezier(0.2, 0.8, 0.3, 1)'
				} ).finished.catch( function () {} );
			} )
			.then( function () {
				return wait( HOLD );
			} )
			.then( function () {
				start = clone.getBoundingClientRect();
				end = hero.getBoundingClientRect();

				return fly( clone, start, end );
			} )
			.then( function () {
				/* Cross-fade: the clone dissolves while the real logo comes back. */
				hero.animate( [ { opacity: 0 }, { opacity: 1 } ], {
					duration: FADE,
					easing: 'linear',
					fill: 'backwards'
				} );

				return clone.animate( [ { opacity: 1 }, { opacity: 0 } ], {
					duration: FADE,
					easing: 'linear'
				} ).finished.catch( function () {} );
			} )
			.then( function () {
				flash();
			} )
			.then( function () {
				cleanup( clone );
			} )
			.catch( function () {
				cleanup( clone );
			} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', play );
	} else {
		play();
	}

	/*
	 * bfcache: the DOM comes back frozen mid-flight, scripts do not re-run, so
	 * without this a restored page could keep the clone and a hidden logo.
	 */
	window.addEventListener( 'pagehide', function () {
		cleanup( clone );
	} );
}() );
