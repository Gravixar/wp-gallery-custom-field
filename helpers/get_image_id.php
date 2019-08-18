<?php
// Get image ID from URL
function gravixar_portfolio_get_image_id( $image_url ) {
	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );
	if ( sizeof( $attachment ) > 0 ) {
		return $attachment[0];
	}

	return - 1;
}
