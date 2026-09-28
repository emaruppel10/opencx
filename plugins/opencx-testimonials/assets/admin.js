/*
 * Media pickers for the OpenCX Testimonials fields.
 *
 * One delegated handler for every `.opencx-field` on the page, bound once on the document
 * rather than per field, so that adding a field in PHP needs no change here.
 */
( function ( $ ) {
	'use strict';

	var strings = window.opencxTestimonials || {};

	/**
	 * The `.opencx-field` wrapper a node belongs to, or null.
	 *
	 * @param {jQuery} $node Node to start from.
	 * @return {Element|null} Wrapper element.
	 */
	function wrapperFor( $node ) {
		return $node.closest( '.opencx-field' ).get( 0 ) || null;
	}

	/**
	 * Reflects the current value in the preview and the buttons.
	 *
	 * @param {jQuery} $wrapper Field wrapper.
	 * @param {Object} attachment Attachment attributes, or null to clear.
	 * @return {void}
	 */
	function paint( $wrapper, attachment ) {
		var $preview = $wrapper.find( '.opencx-field__preview' );
		var $clear = $wrapper.find( '.opencx-field__clear' );

		$preview.empty();

		if ( attachment ) {
			$preview.html(
				$( '<img>' )
					.attr( 'class', 'opencx-field__preview' )
					.attr( 'src', attachment.url )
					.attr( 'alt', attachment.alt || '' )
			);

			$wrapper.find( '.opencx-field__value' ).val( attachment.id );
			$clear.prop( 'hidden', false );
		} else {
			$wrapper.find( '.opencx-field__value' ).val( '' );
			$clear.prop( 'hidden', true );
		}
	}

	$( document ).on( 'click', '.opencx-field__choose', function ( event ) {
		event.preventDefault();

		var wrapper = wrapperFor( $( this ) );

		if ( ! wrapper || ! window.wp || ! window.wp.media ) {
			return;
		}

		var frame = window.wp.media( {
			title: strings.frameTitle,
			button: { text: strings.frameButton },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			paint( $( wrapper ), frame.state().get( 'selection' ).first().toJSON() );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.opencx-field__clear', function ( event ) {
		event.preventDefault();

		var wrapper = wrapperFor( $( this ) );

		if ( wrapper ) {
			paint( $( wrapper ), null );
		}
	} );
}( jQuery ) );
