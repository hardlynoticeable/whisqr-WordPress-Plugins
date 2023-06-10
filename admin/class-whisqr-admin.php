<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://whisqr.com
 * @since      1.0.0
 *
 * @package    Whisqr
 * @subpackage Whisqr/admin
 */

 
class Whisqr_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $option_name = 'whisqr_setting';
	private $businesscode;
	private $public_key;
	private $secret_key;
	private $businessdetails;
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	private function getRequestHeader($headerName) {
		$headerValue = null;
		// Try to get the header value from $_SERVER (for Apache)
		if (isset($_SERVER[$headerName])) {
			$headerValue = $_SERVER[$headerName];
		}
		// If not found, try to get the header value from getallheaders() (for Nginx)
		else if (function_exists('getallheaders')) {
			$headers = getallheaders();
			$headerName = str_replace('-', '_', strtolower($headerName));
			if (isset($headers[$headerName])) {
				$headerValue = $headers[$headerName];
			}
		}
		return $headerValue;
	}

	/**
	 * Register the parameters
	 *
	 * @since  	1.0.0
	 * @access 	public
	*/
	public function register_whisqr_plugin_settings() {

		// Add a General section
		add_settings_section(
			$this->plugin_name. '-general',
			__( 'Plugin Settings', 'whisqr' ),
			array(
				$this,
				$this->option_name . '_general_cb'
			),
			$this->plugin_name,
			array(
			)
		);

		add_settings_section( 
			$this->plugin_name. '-api', 
			'Whisqr API Key', 
			array(
				$this,
				$this->option_name . '_api_cb'
			),
			$this->plugin_name,
		);


		add_settings_field(
			$this->option_name . '_public_key',
			__( 'Public Key', 'whisqr' ),
			array(
				$this,
				$this->option_name . '_public_key_cb'
			),
			$this->plugin_name,
			$this->plugin_name . '-api',
			array(
				'label_for' => $this->option_name . '_public_key'
			)
		);

		register_setting(
			$this->plugin_name, 
			$this->option_name . '_public_key', 
			'string'
		);


		add_settings_field(
			$this->option_name . '_secret_key',
			__( 'Secret Key', 'whisqr' ),
			array(
				$this,
				$this->option_name . '_secret_key_cb'
			),
			$this->plugin_name,
			$this->plugin_name . '-api',
			array(
				'label_for' => $this->option_name . '_secret_key'
			)
		);

		register_setting(
			$this->plugin_name, 
			$this->option_name . '_secret_key', 
			'string'
		);

	}

	public function whisqr_setting_welcome() {

		include('partials/whisqr-welcome.php');

	}

	/**
	 * Render the text for the general section
	 *
	 * @since  	1.0.0
	 * @access 	public
	*/
	public function whisqr_setting_general_cb() {

		echo '<p class="standout">' . __( 'This plugin includes a loyalty program registration block, which, when added to a page, will provide customers with the means to register for your business\'s loyalty program.<br /><em><strong>How do I find and add the registration block?</strong> Edit any page, <a href="https://wordpress.org/documentation/article/adding-a-new-block/" target="_blank">add a block</a>, and search for "whisqr".</em>' ) . '</p>';

		if(
			($this->public_key == null || $this->public_key == '') && 
			($this->secret_key == null || $this->secret_key == '')
		) {

			// Keys have not been submitted

			include('partials/whisqr-welcome.php');

		} else if(
			(
				$this->businessdetails == null || 
				(
					isset($this->businessdetails->status) && 
					$this->businessdetails->status != 'success'
				)
			)
		) {

			// Keys have been submitted, but they are not correct
?>

			<table style="width: 100%;"><tr><td style="width: 220px;">

				<img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/x.png" style="width: 100px; height: auto;" />

			</td><td style="vertical-align: top; width: 100%;">

				<p style="font-size:1.2em; padding-left: 15px; margin-top: 10px;">

					Your API key pair is incorrect.  Please visit the <a href="https://loyalty.whisqr.com/api/v1.2">API Administration section</a> of your Whisqr Administration Page, generate a App key pair if you have not done so already, and then click the "Details" button beside the Public Key of the pair that you generated.

				</p>

				<p style="font-size:1.2em; padding-left: 15px; margin-top: 10px;">

					The Key Pair has a Public Key that starts with the prefix "pk_live_", and a Secret Key that starts with "sk_live_".  Make sure you paste these keys into the correct fields below.

				</p>

			</td></tr></table>

<?php

		} else {

			// A valid key pair has been submitted.

?>

			<table style="width: 100%;"><tr><td style="width: 220px;">

				<img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/checkmark.png" style="width: 100px; height: auto;" />

			</td><td style="vertical-align: top; width: 100%;">

				<p style="font-size:1.3em; padding-left: 15px; margin-top: 10px;">
				
					Business Name: <strong><?php echo($this->businessdetails->businessname); ?></strong><br />
					
					Business Code: <strong><?php echo($this->businesscode); ?></strong>

				</p>

			</td></tr></table>
<?php
		}

	} 



	// Render the heading for the API settings section
	public function whisqr_setting_api_cb() {
?>
		<p>Generate a public and private key in the <a href="https://loyalty.whisqr.com/api/v1.2">API Administration section</a> of your Whisqr Administration Console</p>

<?php
	}


	/**
	 * Render the input for the private key
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function whisqr_setting_secret_key_cb() {
?>
		<input type="text" name="<?php echo($this->option_name); ?>_secret_key" id="<?php echo($this->option_name); ?>_secret_key" value="<?php echo($this->secret_key); ?>" maxlength="72" size="72" />

<?php

		echo "\n<script>console.log(" . json_encode(wp_load_alloptions()) . ");</script>\n";

	}

	/**
	 * Render the input for the public key
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function whisqr_setting_public_key_cb() {
?>
		<input type="text" name="<?php echo($this->option_name); ?>_public_key" id="<?php echo($this->option_name); ?>_public_key" value="<?php echo($this->public_key); ?>" maxlength="72" size="72" />

<?php

		echo "\n<script>console.log(" . json_encode(wp_load_alloptions()) . ");</script>\n";

	}


	public function whisqr_plugin_setup_menu() {

		$this->businesscode = get_option('businesscode', '');
		$this->businessdetails = get_option('businessdetails', (object)[
			'status' => 'failure',
			'businesscode' => '',
		]);
		$this->businessdetails = (gettype($this->businessdetails) === 'string') ? (json_decode($this->businessdetails)) : ($this->businessdetails);
		$this->secret_key = get_option('whisqr_secret_key', '');
		$this->public_key = get_option('whisqr_public_key', '');

		if($_SERVER['REQUEST_METHOD'] == 'POST') {

			// Got a new secret_key?
			if(
				isset($_POST['whisqr_setting_secret_key'])
			) {

				// TODO: check to see if the key is properly formatted

				$this->secret_key = strtolower(preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['whisqr_setting_secret_key']));
				update_option('whisqr_secret_key', $this->secret_key);

			}

			// Got a new public_key?
			if(
				isset($_POST['whisqr_setting_public_key'])
			) {

				// TODO: check to see if the key is properly formatted

				$this->public_key = strtolower(preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['whisqr_setting_public_key']));
				update_option('whisqr_public_key', $this->public_key);

			}


			// Do we have both a public and private key?
			if($this->public_key != '' && $this->secret_key != '') {

				$hash_content = hash_hmac('sha256', '', $this->secret_key);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://loyalty.whisqr.com/api/v1.2/businessdetails?X-Public={$this->public_key}&X-Hash=$hash_content");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, true);

				$response = curl_exec($ch);
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$headers = substr($response, 0, $header_size);
				$body = substr($response, $header_size);
				curl_close($ch);

				$content = json_decode($body);

				if($content->status == 'success') {

					update_option('businesscode', $content->businesscode);
					$this->businesscode = $content->businesscode;
					update_option('businessdetails', json_encode($content));
					$this->businessdetails = $content;

				} else {

					// bad key(s).  Clear the businesscode and businessdetails
					delete_option('businesscode');
					$this->businesscode = '';
					delete_option('businessdetails');
					$this->businessdetails = null;

				}

			} else {

				// no key(s).  Clear the businesscode and businessdetails
				delete_option('businesscode');
				$this->businesscode = '';
				delete_option('businessdetails');
				$this->businessdetails = null;

			}

		}


		wp_enqueue_script('whisqr-settings', 'whisqr-settings.js');
		wp_add_inline_script(
			'whisqr-settings', 
			'const whisqr_settings = ' . json_encode(
				array(
					'businesscode' => $this->businesscode,
					'businessdetails' => $this->businessdetails,
					'secret_key' => $this->secret_key,
					'public_key' => $this->public_key,
				)
			), 
			'before'
		);



		//echo "<script>alert('$this->businessdetails');</script>";
		$validBusinesscode = (
			(
				$this->businessdetails == null
			) || 
			(
				!isset($this->businessdetails->status) || 
				$this->businessdetails->status != 'success'
			)
		) ? (false) : (true);

		add_menu_page(
			'The Loyalty Program that focuses on Building Better Relationships', 
			($validBusinesscode) ? ('Whisqr') : ('Whisqr <span class="awaiting-mod">1</span>'), 
			'manage_options', 
			'whisqr', 
			array($this, 'whisqr_init'), 
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI5MS4yNjQiIGhlaWdodD0iOTEuMjY0IiB2aWV3Qm94PSIwIDAgMjQuMTQ3IDI0LjE0NyI+PHBhdGggZmlsbD0iIzU0NTQ1NCIgZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNNi40OC4zQTYuMTY2IDYuMTY2IDAgMCAwIC4zIDYuNDh2LjQwOGMuMjU0LS4zMzQuNTM2LS43MzQuODk1LTEuMTE0Ljg3My0uOTg3IDIuNzczLTEuNjg4IDQuODM0LTEuNjM1IDIuMjE0LS4wOTQgNC4zNTQuOTExIDUuOTI1IDIuNDM2IDEuOSAxLjg0NyAzLjU3NiAzLjkyIDUuNDkyIDUuNzU4LjkxLjc4IDEuNjgxIDEuMTU0IDIuODMyLjg5Ny44NTItLjIxIDEuNDg3LS43MDYgMS42MjUtMS43NS4xMjEtLjk4My0uNTAyLTEuODA3LTEuNTM4LTEuOTAzYTEuMTczIDEuMTczIDAgMCAwLTEuMDg4IDEuOWMuNzUuNTE0LjI2Mi45MS0uNDUzLjU1Mi0uODU0LS40MTUtMS4wOTYtMS42MTEtLjkwNS0yLjQ3OS4xNjMtLjk0NiAxLjAyMy0xLjY1MyAxLjkzNi0xLjc4NS44MDktLjA1NSAxLjQ5OS4wNDkgMi4yNzcuNTIgMS4yODEuODY1IDEuNzMgMi4wMiAxLjcxIDMuODYtLjAzNiAxLjkyNy0uOTMyIDMuNjctMi4wNDUgNC45OTUtMS4xMTYgMS4yODYtMi41NTQgMi4zMDgtNC4yMjIgMi43MjctMS44NzYuNTg2LTMuODguNTE2LTUuODI0LjUzNC0yLjYxNy0uMTM0LTUuMjI1LS42ODItNy41ODgtMS44NEMyLjcxIDE3LjkwMSAxLjQ3IDE2Ljg5OC4zIDE1LjgyNXYxLjg0MmE2LjE2NiA2LjE2NiAwIDAgMCA2LjE4IDYuMThoMTEuMTg3YTYuMTY3IDYuMTY3IDAgMCAwIDYuMTgtNi4xOFY2LjQ3OUE2LjE2NiA2LjE2NiAwIDAgMCAxNy42NjcuM3oiLz48L3N2Zz4=' );
		
	}



	/**
	 * Include the setting page
	 *
	 * @since  1.0.0
	 * @access public
	*/
	function whisqr_init(){

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include WHISQR_PATH . 'admin/partials/whisqr-admin-display.php';
		
	} 



	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Whisqr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Whisqr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/whisqr-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Whisqr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Whisqr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/whisqr-admin.js', array( 'jquery' ), $this->version, false );

	}

}



