<?php

class LandingpageTrackerSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'general_options_init' ) );
		add_action( 'admin_init', array( $this, 'cookie_options_init' ) );
		add_action( 'admin_init', array( $this, 'tracker_options_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Landingpage Tracker',
			'manage_options',
			'landingpage-tracker-setting-admin',
			array( $this, 'create_admin_page' )
		);

		add_submenu_page(NULL, 'Add Cookie', 'Add Cookie', 'manage_options', 'landingpage-tracker-setting-add-cookie-admin', array( $this, 'create_add_cookie_page' ));
		add_submenu_page(NULL, 'Add Tracker', 'Add Tracker', 'manage_options', 'landingpage-tracker-setting-add-tracker-admin', array( $this, 'create_add_tracker_page' ));
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->trackers = (array) get_option( 'landingpage_tracker_trackers' );
		$this->cookies = (array) get_option( 'landingpage_tracker_cookies' );
		$this->options = get_option( 'landingpage_tracker_general');

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Landingpage Tracker Settings</h2>
			<a href="<?= add_query_arg('page', 'landingpage-tracker-setting-add-cookie-admin', admin_url('options-general.php')) ?>" class="button button-secondary">Add new Cookie</a> <a href="<?= add_query_arg('page', 'landingpage-tracker-setting-add-tracker-admin', admin_url('options-general.php')) ?>" class="button button-secondary">Add new Tracker</a>

			<h3>General Options</h3>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'landingpage_tracker_general_group' );
				do_settings_sections( 'landingpage-tracker-general-admin' );
				submit_button();
				?>
			</form>

			<h3>Active Trackers</h3>

			<?php if (empty($this->trackers)) { ?>
				<p>
					Currently no trackers installed, add one ;)
				</p>
			<?php } else { ?>
				<?php foreach ($this->trackers as $tracker) { ?>
					<p><?= $tracker['name'] ?> - <?= $this->cookies[$tracker['cookie']]['name'] ?></p>
				<?php } ?>
			<?php } ?>
		</div>
	<?php
	}

	/**
	 * Options page callback
	 */
	public function create_add_cookie_page()
	{
		// Set class property
		$this->options = (array) get_option( 'landingpage_tracker_cookies' );
		$this->next_cookie_id = wp_hash_password(wp_generate_password(32));

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Landingpage Tracker Settings // Add Cookie</h2>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'landingpage_tracker_cookies_group' );
				do_settings_sections( 'landingpage-tracker-cookie-admin' );
				submit_button('Add Cookie');
				?>
			</form>

			<h3>Available Cookies</h3>
			<?php if (empty($this->options)) { ?>
				<p>
					Currently no cookies installed, add one ;)
				</p>
			<?php } else { ?>
				<?php foreach ($this->options as $cookie) { ?>
					<p><?= $cookie['name'] ?> - <?= $cookie['description'] ?></p>
				<?php } ?>
			<?php } ?>

			<a class="button" href="<?= add_query_arg('page', 'landingpage-tracker-setting-admin', admin_url('options-general.php')) ?>">Zurück</a>
		</div>
	<?php
	}

	/**
	 * Options page callback
	 */
	public function create_add_tracker_page()
	{
		// Set class property
		$this->options = (array) get_option( 'landingpage_tracker_trackers' );
		$this->next_tracker_id = wp_hash_password(wp_generate_password(32));

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Landingpage Tracker Settings</h2>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'landingpage_tracker_trackers_group' );
				do_settings_sections( 'landingpage-tracker-tracker-admin' );
				submit_button('Add Tracker');
				?>

				<a class="button" href="<?= add_query_arg('page', 'landingpage-tracker-setting-admin', admin_url('options-general.php')) ?>">Zurück</a>
			</form>
		</div>
	<?php
	}

	public function general_options_init()
	{
		register_setting(
			'landingpage_tracker_general_group', // Option group
			'landingpage_tracker_general', // Option name
			array( $this, 'sanitize_general' ) // Sanitize
		);

		add_settings_section(
			'landingpage_tracker_general_section', // ID
			null, // Title
			null, // Callback
			'landingpage-tracker-general-admin' // Page
		);

		add_settings_field(
			'default_cookie_name',
			'Default value',
			array( $this, 'default_cookie_name_callback' ),
			'landingpage-tracker-general-admin',
			'landingpage_tracker_general_section'
		);

		add_settings_field(
			'lifetime',
			'Cookie lifetime',
			array( $this, 'cookie_lifetime_callback' ),
			'landingpage-tracker-general-admin',
			'landingpage_tracker_general_section'
		);

		add_settings_field(
			'redirect',
			'Redirect on match',
			array( $this, 'redirect_callback' ),
			'landingpage-tracker-general-admin',
			'landingpage_tracker_general_section'
		);
	}

	/**
	 * Register and add settings
	 */
	public function cookie_options_init()
	{
		register_setting(
			'landingpage_tracker_cookies_group', // Option group
			'landingpage_tracker_cookies', // Option name
			array( $this, 'sanitize_cookie' ) // Sanitize
		);

		add_settings_section(
			'landingpage_tracker_cookies_section', // ID
			'Cookie Settings', // Title
			array( $this, 'print_cookie_section_info' ), // Callback
			'landingpage-tracker-cookie-admin' // Page
		);

		add_settings_field(
			'name', // ID
			'Name', // Title
			array( $this, 'cookie_name_callback' ), // Callback
			'landingpage-tracker-cookie-admin', // Page
			'landingpage_tracker_cookies_section' // Section
		);

		add_settings_field(
			'description',
			'Beschreibung',
			array( $this, 'cookie_description_callback' ),
			'landingpage-tracker-cookie-admin',
			'landingpage_tracker_cookies_section'
		);
	}

	public function tracker_options_init()
	{
		register_setting(
			'landingpage_tracker_trackers_group', // Option group
			'landingpage_tracker_trackers', // Option name
			array( $this, 'sanitize_tracker' ) // Sanitize
		);

		add_settings_section(
			'landingpage_tracker_trackers_section', // ID
			'Tracker Settings', // Title
			array( $this, 'print_tracker_section_info' ), // Callback
			'landingpage-tracker-tracker-admin' // Page
		);

		add_settings_field(
			'name', // ID
			'Name', // Title
			array( $this, 'tracker_name_callback' ), // Callback
			'landingpage-tracker-tracker-admin', // Page
			'landingpage_tracker_trackers_section' // Section
		);

		add_settings_field(
			'match', // ID
			'Match', // Title
			array( $this, 'tracker_match_callback' ), // Callback
			'landingpage-tracker-tracker-admin', // Page
			'landingpage_tracker_trackers_section' // Section
		);

		add_settings_field(
			'cookie', // ID
			'Cookie', // Title
			array( $this, 'tracker_cookie_callback' ), // Callback
			'landingpage-tracker-tracker-admin', // Page
			'landingpage_tracker_trackers_section' // Section
		);

		add_settings_field(
			'description',
			'Beschreibung',
			array( $this, 'tracker_description_callback' ),
			'landingpage-tracker-tracker-admin',
			'landingpage_tracker_trackers_section'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_cookie( $input )
	{
		$this->options = (array) get_option( 'landingpage_tracker_cookies' );

		$keys = array_keys($input);

		if (isset($keys) && isset($keys[0])) {
			$id = $keys[0];
		} else {
			return;
		}

		if (!isset($this->options[$id])) {
			$this->options[$id] = array();
		}

		if( isset( $input[$id]['name'] ) )
			$this->options[$id]['name'] = sanitize_text_field( $input[$id]['name'] );

		if( isset( $input[$id]['description'] ) )
			$this->options[$id]['description'] = sanitize_text_field( $input[$id]['description'] );

		return $this->options;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_tracker( $input )
	{
		$this->options = (array) get_option( 'landingpage_tracker_trackers' );
		$keys = array_keys($input);

		if (isset($keys) && isset($keys[0])) {
			$id = $keys[0];
		} else {
			return;
		}

		if (!isset($this->options[$id])) {
			$this->options[$id] = array();
		}

		if( isset( $input[$id]['name'] ) )
			$this->options[$id]['name'] = sanitize_text_field( $input[$id]['name'] );

		if( isset( $input[$id]['match'] ) )
			$this->options[$id]['match'] = sanitize_text_field( $input[$id]['match'] );

		if( isset( $input[$id]['cookie'] ) )
			$this->options[$id]['cookie'] = sanitize_text_field( $input[$id]['cookie'] );

		if( isset( $input[$id]['description'] ) )
			$this->options[$id]['description'] = sanitize_text_field( $input[$id]['description'] );

		return $this->options;
	}

	public function sanitize_general( $input )
	{
		foreach ( $input as $key => $value ) {
			$input[$key] = sanitize_text_field($value);
		}

		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function print_cookie_section_info()
	{
		print 'Fill in the fields below:';
	}

	public function print_tracker_section_info()
	{
		print 'Fill in the fields below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function cookie_name_callback()
	{
		printf('<input type="text" id="name" name="landingpage_tracker_cookies[%s][name]" value="" />', $this->next_cookie_id);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function cookie_description_callback()
	{
		printf('<textarea type="text" id="description" name="landingpage_tracker_cookies[%s][description]" value=""></textarea>', $this->next_cookie_id);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function tracker_name_callback()
	{
		printf('<input type="text" id="name" name="landingpage_tracker_trackers[%s][name]" value="" />', $this->next_tracker_id);
	}

	public function tracker_match_callback()
	{
		printf('<input type="text" id="match" name="landingpage_tracker_trackers[%s][match]" value="" />', $this->next_tracker_id);
	}

	public function tracker_cookie_callback()
	{
		$cookies = (array) get_option( 'landingpage_tracker_cookies' );

		$html = '<select id="cookie" name="landingpage_tracker_trackers[' . $this->next_tracker_id . '][cookie]">';

		foreach ( $cookies as $key => $value ) {
			$html .= '<option value="' . $key . '">' . $value['name'] . '</option>';
		}

		$html .= '</select>';

		echo $html;

		//printf('<textarea type="text" id="description" name="landingpage_tracker_trackers[%s][description]" value=""></textarea>', $this->next_tracker_id);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function tracker_description_callback()
	{
		printf('<textarea type="text" id="description" name="landingpage_tracker_trackers[%s][description]" value=""></textarea>', $this->next_tracker_id);
	}

	public function redirect_callback()
	{
		$options = get_option( 'landingpage_tracker_general' );

		$html = '<input type="checkbox" id="redirect" name="landingpage_tracker_general[redirect]" value="1"' . checked( 1, $options['redirect'], false ) . '/>';

		echo $html;
	}

	public function default_cookie_name_callback()
	{
		$options = get_option( 'landingpage_tracker_general' );

		$html = '<input type="text" id="default_cookie_name" name="landingpage_tracker_general[default_cookie_name]" value="' . (($options['default_cookie_name']) ? $options['default_cookie_name'] : 'Organic') . '"/>';

		echo $html;
	}

	public function cookie_lifetime_callback()
	{
		$options = get_option( 'landingpage_tracker_general' );
		$lifetimes = array(
			'hour' => '1 hour',
			'day' => '1 day',
			'week' => '1 week',
			'month' => '1 month',
			'year' => '1 year'
		);

		$html = '<select id="cookie" name="landingpage_tracker_general[lifetime]">';

		foreach ( $lifetimes as $key => $value ) {
			$html .= '<option value="' . $key . '" ' . selected($options['lifetime'], $key, false) . '>' . $value. '</option>';
		}

		$html .= '</select>';

		echo $html;

		//printf('<textarea type="text" id="description" name="landingpage_tracker_trackers[%s][description]" value=""></textarea>', $this->next_tracker_id);
	}
}

if( is_admin() )
	$landingpage_tracker_settings_page = new LandingpageTrackerSettingsPage();