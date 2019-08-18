<?php

// Add the Meta Box
function gravixar_meta_box() {
	add_meta_box(
		'gravixar_meta_box',
		'Gallery',
		'gravixar_portfolio_show_gravixar_meta_box',
		'authors',
		'normal',
		'low' );
}

add_action( 'add_meta_boxes', 'gravixar_meta_box' );

// Field Array
$prefix             = 'gravixar_gallery_';
$custom_meta_fields = [
	[
		'label' => 'Main Gallery Image',
		'desc'  => 'This is the main image that is shown in the grid and at the top of the single item page.',
		'id'    => $prefix . 'image',
		'type'  => 'media',
	],
	[
		'label' => 'Gallery Images',
		'desc'  => 'This is the gallery images on the single item page.',
		'id'    => $prefix . 'gallery',
		'type'  => 'gallery',
	],
];

// The Callback
function gravixar_portfolio_show_gravixar_meta_box( $object ) {
	global $custom_meta_fields, $post;
	// Use nonce for verification
	echo '<input type="hidden" name="gravixar_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

	// Begin the field table and loop
	echo '<table class="form-table">';
	foreach ( $custom_meta_fields as $field ) {
		// get value of this field if it exists for this post
		$meta = get_post_meta( $post->ID, $field['id'], true );
		// begin a table row with
		echo '<tr>
                <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
                <td>';
		switch ( $field['type'] ) {
			case 'media':
				$close_button = null;
				if ( $meta ) {
					$close_button = '<span class="gravixar_portfolio_close"></span>';
				}
				echo '<input id="gravixar_portfolio_image" type="hidden" name="gravixar_portfolio_image" value="' . esc_attr( $meta ) . '" />
                        <div class="gravixar_portfolio_image_container">' . $close_button . '<img id="gravixar_portfolio_image_src" src="' . wp_get_attachment_thumb_url( gravixar_portfolio_get_image_id( $meta ) ) . '"></div>
                        <input id="gravixar_portfolio_image_button" type="button" value="Add Image" />';
				break;

			case 'gallery':
				$meta_html = null;
				if ( $meta ) {
					$meta_html  .= '<ul class="gravixar_portfolio_gallery_list">';
					$meta_array = explode( ',', $meta );
					foreach ( $meta_array as $meta_gall_item ) {
						$meta_html .= '<li><div class="gravixar_portfolio_gallery_container"><span class="gravixar_portfolio_gallery_close"><img id="' . esc_attr( $meta_gall_item ) . '" src="' . wp_get_attachment_thumb_url( $meta_gall_item ) . '"></span></div></li>';
					}
					$meta_html .= '</ul>';
				}
				echo '<input id="gravixar_portfolio_gallery" type="hidden" name="gravixar_portfolio_gallery" value="' . esc_attr( $meta ) . '" />
                        <span id="gravixar_portfolio_gallery_src">' . $meta_html . '</span>
                        <div class="gravixar_gallery_button_container"><input id="gravixar_portfolio_gallery_button" type="button" value="Add Gallery" /></div>';
				break;
		} //end switch
		echo '</td></tr>';
	} // end foreach
	echo '</table>'; // end table
}

// Save the Data
function gravixar_portfolio_save_custom_meta( $post_id ) {

	if ( $_POST ) {
		global $custom_meta_fields;

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['gravixar_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}
		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Loop through meta fields
		foreach ( $custom_meta_fields as $field ) {
			$new_meta_value = esc_attr( $_POST[ $field['id'] ] );
			$meta_key       = $field['id'];
			$meta_value     = get_post_meta( $post_id, $meta_key, true );

			// If theres a new meta value and the existing meta value is empty
			if ( $new_meta_value && $meta_value == null ) {
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
				// If theres a new meta value and the existing meta value is different
			} elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			} elseif ( $new_meta_value == null && $meta_value ) {
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}
}

add_action( 'save_post', 'gravixar_portfolio_save_custom_meta' );
