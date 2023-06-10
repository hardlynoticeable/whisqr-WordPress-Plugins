<?php
/**
 * Plugin Name:       Whisqr
 * Description:       This plugin provides businesses customer loyalty program integration through Gutenberg Blocks and WooCommerce integrations.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       whisqr
 *
 * @package           create-block
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define( 'WHISQR_VERSION', '1.0.0' );
!defined('WHISQR_PATH') && define('WHISQR_PATH', plugin_dir_path( __FILE__ )); 


function register_layout_category( $categories ) {
	
	$categories[] = array(
		'slug'  => 'whisqr',
		'title' => 'Customer Loyalty'
	);

	return $categories;
}

if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'register_layout_category' );
} else {
	add_filter( 'block_categories', 'register_layout_category' );
}


function create_block_whisqr_registration_block_init() {

	register_block_type( __DIR__ . '/build/registration' );
	register_block_type( __DIR__ . '/build/rewards' );

}
add_action( 'init', 'create_block_whisqr_registration_block_init' );



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-whisqr-activator.php
 */
function activate_whisqr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whisqr-activator.php';
	Whisqr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-whisqr-deactivator.php
 */
function deactivate_whisqr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whisqr-deactivator.php';
	Whisqr_Deactivator::deactivate();
}


register_activation_hook( __FILE__, 'activate_whisqr' );
register_deactivation_hook( __FILE__, 'deactivate_whisqr' );




function retrieveRewardsdataHook() {

	$api_public_key = get_option( 'whisqr_public_key' );

	if(!empty($api_public_key)) {

		// Get the current user ID
		$user_id = get_current_user_id();
		// Check if the data is already in the user meta
		$data = get_option( 'whisqr_rewardsdata' );
		// If the data is not present, retrieve it from the API
		if (!$data) {

			// Retrieve the JSON data from the API
			$api_endpoint = 'https://loyalty.whisqr.com/api/v1.2/business/rewards';
			$response = wp_remote_request( $api_endpoint, array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Public' => $api_public_key,
				),
			) );

			// Check if the API call was successful
			if (is_wp_error($response)) {
				return;
			}
			// Parse the JSON data
			$rewardsdata = json_decode(wp_remote_retrieve_body($response))->settings_rewards;
			// Store the data in the user meta
			update_option( 'whisqr_rewardsdata', json_encode($rewardsdata));

		}

	}

}
add_action('wp', 'retrieveRewardsdataHook');





function saveCardcode($cardcode='', $email='') {

    // Save the cardcode to the user account if the user is logged in
    if (is_user_logged_in()) {

		$user_id = get_current_user_id();
        update_user_meta($user_id, 'whisqr_cardcode', $cardcode);
        update_user_meta($user_id, 'whisqr_email', $email);

	}

	setcookie('whisqr_cardcode', '', 1, '/');
	setcookie('whisqr_email', '', 1, '/');

	if($cardcode != '') {
		setcookie('whisqr_cardcode', $cardcode, time() + (86400 * 365), '/');
	}
	if($email != '') {
		setcookie('whisqr_email', $email, time() + (86400 * 365), '/');
	}

}

function getCardcode($email='') {

	$cardcode = '';

	if(isset($_COOKIE['whisqr_cardcode'])) {

        $existingEmail = isset($_COOKIE['whisqr_email']) ? sanitize_text_field($_COOKIE['whisqr_email']) : '';
		if($email == '' || ($email != '' && $email == $existingEmail)) {
	        $cardcode = isset($_COOKIE['whisqr_cardcode']) ? sanitize_text_field($_COOKIE['whisqr_cardcode']) : '';
		}

	} else if (is_user_logged_in()) {

		$user_id = get_current_user_id();
        $existingEmail = get_user_meta($user_id, 'whisqr_email', true);
		if($email == '' || ($email != '' && $email == $existingEmail)) {
			$cardcode = get_user_meta($user_id, 'whisqr_cardcode', true);
		}

	}

    return $cardcode;

}

// Check if WooCommerce plugin is active before registering hooks
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function whisqr_purchase_hook( $order_id=0 ) {

		if($order_id == 0) {
			$order_id = WC()->session->get('order_awaiting_payment');
		}

		// Get the purchase value and order status from the order
		$order = wc_get_order( $order_id );
		$purchase_value = $order->get_total();
		$order_status = $order->get_status();
		$email = $order->get_billing_email();

		$cardcode = getCardcode($email);

		// Get API public key and private key from WordPress options
		$api_public_key = get_option( 'whisqr_public_key' );
		$api_secret_key = get_option( 'whisqr_secret_key' );

		// Determine the status of the punch
		/*
		if ($order_status === 'completed') {
			$status = 'success';
		} else {
			$status = 'pending';
		}
		*/
		$status = 'success';

		// Create content hash
		$content = array(
			'purchase_value' => $purchase_value,
			'status' => $status,
		);
		if($cardcode != '') {
			$content['cardcode'] = $cardcode;
		}
		if($email != '') {
			$content['email'] = $email;
		}

		// Post purchase value to API
		$api_endpoint = 'https://loyalty.whisqr.com/api/v1.2/punch';

		// Check if punchcode exists in order metadata
		$punchcode = get_post_meta( $order_id, 'whisqr_punchcode', true );

		// Append punchcode to URL if it exists
		if ( $punchcode ) {
			$api_endpoint .= '/' . urlencode( $punchcode );
		}

		$contentString = json_encode( $content );
		$content_hash = hash_hmac( 'sha256', $contentString, $api_secret_key );

		$response = wp_remote_request( $api_endpoint, array(
			'method'  => $punchcode ? 'PUT' : 'POST',
			'body' => $contentString,
			'headers' => array(
				'Content-Type' => 'application/json',
				'X-Public' => $api_public_key,
				'X-Hash' => $content_hash,
			),
		) );

		//TODO: Check response code

		// Parse the response and get the punchcode
		$response_data = json_decode(wp_remote_retrieve_body($response));
		$punchcode = $response_data->punchcode;
		$cardcode = $response_data->cardcode;
		$punchesPurchase = $response_data->punches;
		$punchesTotal = $response_data->punchtotal;

		saveCardcode($cardcode, $email);

		// Store the whisqr related data in the order metadata
		$order->update_meta_data('whisqr_cardcode', $cardcode);
		$order->update_meta_data('whisqr_punchcode', $punchcode);
		$order->update_meta_data('whisqr_punchesPurchase', $punchesPurchase);
		$order->update_meta_data('whisqr_punchesTotal', $punchesTotal);
		$order->save();

	}

	// Add hook for when the order is marked as completed
	add_action( 'woocommerce_order_status_changed', 'whisqr_purchase_hook' );




	function display_punch_information_on_checkout() {
		// Get the order ID
		$order_id = WC()->session->get('order_awaiting_payment');
	
		// Get the punch information from the order meta
		$punchcode = get_post_meta($order_id, 'whisqr_punchcode', true);
		$punches_awarded = get_post_meta($order_id, 'whisqr_punchesPurchase', true);
		$total_punches = get_post_meta($order_id, 'whisqr_punchesTotal', true);
	
		// Check if the punch information is available
		if ($punchcode && $punches_awarded && $total_punches) {
			// Display the punch information
	?>
			<div class="punch-information">
	
				<h1 style="margin-bottom:var(--wp--preset--spacing--40);" class="wp-block-post-title">Rewards</h1>
	
				<p><strong>Punches you will receive for this purchase:</strong> <?php echo $punches_awarded; ?></p>
				<p><strong>Your new punch total:</strong> <?php echo $total_punches; ?></p>
				<p><strong><a href="https://qrl.at/<?php echo $punchcode; ?>">Click here</a> to view your Rewards Punch Card</strong></p>
	
			</div>
	<?php
		}
	}
	
	add_action('woocommerce_checkout_before_order_review', 'display_punch_information_on_checkout');

	
	

	function display_punch_information_on_thankyou($order_id) {
	
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
	
		$order_status = $order->get_status();
		$email = $order->get_billing_email();

		$cardcode = getCardcode($email);
	
	
	
		// Get the punch information from the order meta
		$punchcode = get_post_meta($order_id, 'whisqr_punchcode', true);
		$punches_awarded = get_post_meta($order_id, 'whisqr_punchesPurchase', true);
		$total_punches = get_post_meta($order_id, 'whisqr_punchesTotal', true);

		$businessdetails = get_option('businessdetails');
		$businessdetails = (gettype($businessdetails) == 'string') ? (json_decode($businessdetails)) : ($businessdetails);
	
		$suffix = '';
		if(isset($businessdetails->businesscode)) {
			$suffix = "/{$businessdetails->businesscode}";
		}


		// Check if the punch information is available
		if ($punchcode && $punches_awarded && $total_punches) {
			// Display the punch information
	?>
			<div class="punch-information">
	
				<section class="woocommerce-order-details">
	
					<h2 class="woocommerce-order-details__title">Rewards</h2>

					<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

						<thead>
							<tr>
								<th class="woocommerce-table__product-name product-name">Rewards for This Visit</th>
								<th class="woocommerce-table__product-table product-total">Your New Total</th>
							</tr>
						</thead>

						<tbody>
							<tr class="woocommerce-table__line-item order_item">

								<td class="woocommerce-table__product-name product-name">
									<?php echo $punches_awarded; ?> Punches
								</td>

								<td class="woocommerce-table__product-total product-total">
									<span class="woocommerce-Price-amount amount"><?php echo $total_punches; ?> Punches
								</td>

							</tr>
						</tbody>

					</table>

				</section>

				<h5><a href="https://qrl.at/<?php echo $cardcode; ?>" target="_blank">Click here</a> to view your Rewards&nbsp;Punch&nbsp;Card; where&nbsp;you&nbsp;can view your punch&nbsp;total, your punch&nbsp;history, our Specials and&nbsp;Events, and where you can save your punch card to your&nbsp;mobile&nbsp;device.</h5>

			</div>
	<?php
		}
	}

	add_action('woocommerce_thankyou', 'display_punch_information_on_thankyou');

}





/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-whisqr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_whisqr() {

	$plugin = new Whisqr();
	$plugin->run();

}
run_whisqr();
