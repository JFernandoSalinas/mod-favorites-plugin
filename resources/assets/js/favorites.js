/**
 * Favorite Form
 *
 * @since 1.0.0
 */
( function( window, undefined ) {

	window.wp = window.wp || {};

	var document = window.document;
	var $ = window.jQuery;
	var wp = window.wp;

	/**
	 * @since 1.0.0
	 */
	var $document = $(document);

	/**
	 * Toggle List Field
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.toggleListField = function( el ) {
		if ( 'new' === el.val() ) {
			el.siblings( '.astoundify_favorites_list_new' ).show();
		}
		else {
			el.siblings( '.astoundify_favorites_list_new' ).hide();
		}
	};

	/**
	 * Open Pop Up
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.openPopUp = function( content ) {

		if ( ! content ) {
			return;
		}

		$.magnificPopup.open( {
			items: {
				src: astoundifyFavorites.config.popupHtml.replace( '%%CONTENT%%', content ),
				type: 'inline'
			}
		} );
	};

	/**
	 * Close Pop Up
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.closePopUp = function() {
		$.magnificPopup.close();
	};

	/**
	 * Create Favorite
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.favoriteCreate = function( el ) {
		wp.ajax.post( 'astoundify_favorites_favorite_create', el.data() )
			.done( function( data ) {
				if ( data.link ) {
					var $oldLink = $( '.astoundify-favorites-link[data-af_data="' + el.data( 'af_data' ) + '"][data-af_type="' + el.data( 'af_type' ) + '"]' );
					var $newLink = $(data.link);
					
					// Update individual link attributes to allow animations to take place.
					var oldAtts = $oldLink[0].attributes;
					var newAtts = $newLink[0].attributes;

					$.each(newAtts, function(i, attr) {
						$oldLink.attr(attr.name, attr.value);
					});

					// Replace inner HTML on a delay to allow for animations.
					setTimeout(function() {
						$oldLink.html($newLink.html());
					}, 300);
				}
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Open Edit Form Favorite
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.favoriteEditForm = function( el ) {
		var data = {};

		$.each(el[0].attributes, function(i, attr) {
			data[attr.name.replace('data-', '')] = attr.value;
		});

		wp.ajax.post( 'astoundify_favorites_favorite_edit_form', data )
			.done( function( data ) {
				astoundifyFavorites.openPopUp( data.form );
			} )
			.fail( function( data ) {
				if ( data.link ) {
					$( '.astoundify-favorites-link[data-af_data="' + el.data( 'af_data' ) + '"][data-af_type="' + el.data( 'af_type' ) + '"]' ).replaceWith( $( data.link ) );
				}
				$( '#astoundify-favorite-' + el.data( 'af_favorite_id' ) ).remove();
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Update Favorite
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.favoriteEdit = function( el ) {

		// Create formData
		var formData = new FormData( el[0] );
		formData.append( 'action', 'astoundify_favorites_favorite_edit' );

		// AJAX
		wp.ajax.send( {
				dataType: 'json',
				data : formData,
				contentType: false, // required
				processData: false  // required
			} )
			.done( function( data ) {
				if ( data.template ) {
					$( '#astoundify-favorite-' + data.favorite_id ).replaceWith( data.template );
				}
				astoundifyFavorites.openPopUp( data.notices );
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Remove Favorite
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.favoriteRemove = function( el ) {
		wp.ajax.post( 'astoundify_favorites_favorite_remove', el.data() )
			.done( function( data ) {
				if ( data.link ) {
					$( '.astoundify-favorites-link[data-af_data="' + data.target_id + '"][data-af_type="' + el.data( 'af_type' ) + '"]' ).replaceWith( $( data.link ) );
				}
				$( '#astoundify-favorite-' + data.favorite_id ).remove();
				astoundifyFavorites.closePopUp();
			} )
			.fail( function( data ) {
				$( '#astoundify-favorite-' + el.data( 'af_favorite_id' ) ).remove();
				astoundifyFavorites.openPopUp( data.notices );
			} );
		if ( ! $( '.astoundify-favorite' ).length ) {
			$( '#astoundify-favorite-0' ).show();
		}
	};

	/**
	 * Open Create Form List
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.listCreateForm = function( el ) {
		wp.ajax.post( 'astoundify_favorites_list_create_form', el.data() )
			.done( function( data ) {
				astoundifyFavorites.openPopUp( data.form );
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Open Edit Form List
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.listEditForm = function( el ) {
		wp.ajax.post( 'astoundify_favorites_list_edit_form', el.data() )
			.done( function( data ) {
				astoundifyFavorites.openPopUp( data.form );
			} )
			.fail( function( data ) {
				$( '#astoundify-favorite-list-' + el.data( 'af_list_id' ) ).remove();
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Create List
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.listCreate = function( el ) {

		// Create formData
		var formData = new FormData( el[0] );
		formData.append( 'action', 'astoundify_favorites_list_create' );
		formData.append( 'list_name', el.find( 'input[name="list_name"]' ).val() );

		// AJAX
		wp.ajax.send( {
				dataType: 'json',
				data : formData,
				contentType: false, // required
				processData: false  // required
			} )
			.done( function( data ) {
				if ( data.template ) {
					$( data.template ).insertBefore( '#astoundify-favorite-list-new' );
				}
				astoundifyFavorites.openPopUp( data.notices );
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};

	/**
	 * Edit List
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.listEdit = function( el ) {

		// Create formData
		var formData = new FormData( el[0] );
		formData.append( 'action', 'astoundify_favorites_list_edit' );
		formData.append( 'list_name', el.find( 'input[name="list_name"]' ).val() );

		// AJAX
		wp.ajax.send( {
				dataType: 'json',
				data : formData,
				contentType: false, // required
				processData: false  // required
			} )
			.done( function( data ) {
				if ( data.template ) {
					$( '#astoundify-favorite-list-' + data.list_id ).replaceWith( data.template );
				}
				astoundifyFavorites.openPopUp( data.notices );
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};


	/**
	 * Remove List
	 * 
	 * @since 1.0.0
	 */
	astoundifyFavorites.listRemove = function( el ) {
		wp.ajax.post( 'astoundify_favorites_list_remove', el.data() )
			.done( function( data ) {
				$( '#astoundify-favorite-list-' + data.list_id ).remove();
				astoundifyFavorites.openPopUp( data.notices );
			} )
			.fail( function( data ) {
				astoundifyFavorites.openPopUp( data.notices );
			} );
	};


	/***********************************
	 * Wait for DOM ready.
	 *
	 * @since 1.0.0
	 ***********************************/
	$document.ready( function() {

		// FAVORITE ACTION

		// Add favorite
		$document.on( 'click', '.astoundify-favorites-link.astoundify-favorites-link--inactive', function(e) {
			if ( 0 !== $( this ).data( 'user_id' ) ) {
				e.preventDefault();
				astoundifyFavorites.favoriteCreate( $( this ) );
				return;
			}
		} );

		// Open edit favorite form
		$document.on( 'click', '.astoundify-favorites-link.astoundify-favorites-link--active, .astoundify-favorites-edit-favorite', function(e) {
			e.preventDefault();
			astoundifyFavorites.favoriteEditForm( $( this ) );
		} );

		// Edit favorite
		$document.on( 'submit', '.astoundify-favorites-form-favorite-edit', function(e) {
			e.preventDefault();
			astoundifyFavorites.favoriteEdit( $( this ) );
		} );

		// Remove favorite
		$document.on( 'click', '.astoundify-favorites-remove-favorite', function(e) {
			e.preventDefault();
			var confirmDelete = window.confirm( astoundifyFavorites.i18n.confirmRemove );
			if ( true === confirmDelete ) {
				astoundifyFavorites.favoriteRemove( $( this ) );
			}
		} );

		// LIST ACTIONS

		// Open create list form
		$document.on( 'click', '.astoundify-favorites-create-list', function(e) {
			e.preventDefault();
			astoundifyFavorites.listCreateForm( $( this ) );
		} );

		// Open edit list form
		$document.on( 'click', '.astoundify-favorites-edit-list', function(e) {
			e.preventDefault();
			astoundifyFavorites.listEditForm( $( this ) );
		} );

		// Edit/Create List
		$document.on( 'submit', '.astoundify-favorites-form-list-edit', function(e) {
			e.preventDefault();
			var list_id = $( this ).find( 'input[name="_list"]' ).val();
			if ( '0' === list_id ) { // Create List
				astoundifyFavorites.listCreate( $( this ) );
			} else { // Edit List
				astoundifyFavorites.listEdit( $( this ) );
			}
		} );

		// Remove List
		$document.on( 'click', '.astoundify-favorites-remove-list', function(e) {
			e.preventDefault();
			var confirmDelete = window.confirm( astoundifyFavorites.i18n.confirmRemove );
			if ( true === confirmDelete ) {
				astoundifyFavorites.listRemove( $( this ) );
			}
		} );

		// SELECT LIST

		// Select list field
		$document.on( 'change', '.astoundify_favorites_list', function() {
			astoundifyFavorites.toggleListField( $( this ) );
		} );

	} );

}( window ) );
