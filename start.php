<?php
/**
 * Moodle integration plugin.
 * 
 * Provides data through the rest API to be used in external applications.
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jaakko Naakka
 * @author Juho Jaakkola
 * @copyright (C) Mediamaisteri Group 2008-2013
 * @link http://www.mediamaisteri.com/
 */

// Register plugin init to be loaded
elgg_register_event_handler('init', 'system', 'moodle_integration_init');

require_once('moodle_elgg_api.php');

/**
 * Initialise plugin.
 */
function moodle_integration_init() {
	// Register a plugin hook to modify Elgg group profile fields
	elgg_register_plugin_hook_handler('profile:fields', 'group', 'moodle_integration_group_profile');

	// Make sure that POST variables are available if needed
	include_post_data();

	// Define the api methods for REST
	expose_function(
		'elgg.get_auth_token',
		'api_moodle_integration_get_token', array(
			'username' => array('type' => 'string'),
			'name' => array('type' => 'string'),
			'email' => array('type' => 'string'),
		), elgg_echo('moodle_integration:gettoken:description'),'POST', true, false
	);

	expose_function(
		'elgg.get_groupGUID',
		'api_moodle_integration_get_group_guid', array(
			'shortname' => array('type' => 'string'),
		), '', 'POST', true, false
	);

	expose_function(
		'elgg.get_group_discussions',
		'api_moodle_integration_get_group_discussions', array(
			'group_guid' => array('type' => 'int')
		), '', 'POST', true, false
	);

	expose_function(
		'elgg.get_objects',
		'api_moodle_integration_get_objects', array(
			'object_type' => array('type' => 'string'),
			'tag' => array('type' => 'string')
		), '', 'POST', true, false
	);
}

/**
 * Provides a token that can be used for authentication.
 *
 * If authentication is succesfull with given parameters,
 * generates an authentication token that can be used to
 * authenticate future requests.
 * 
 * @param string $username
 * @param string $microtime
 * @param string $code
 * @return string Authentication token
 */
function api_moodle_integration_get_token($username, $name, $email) {
	if (!get_user_by_username($username)) {
		// Create new user
		$user = moodle_integration_create_user($username, $name, $email);

		if (!$user) {
			// This should never happen
			throw new APIException(elgg_echo('APIException:UnknownError'));
		}
	}

	// Create token to be used for authentication in later requests
	$token = create_user_token($username);
	if ($token) {
		return $token;
	} else {
		throw new APIException(elgg_echo('APIException:TokenCreateFailed'));
	}
}

/**
 * Create a new user.
 * 
 * @param string $username
 * @param string $name
 * @param string $email
 * @return int Guid of the new user
 */
function moodle_integration_create_user($username, $name, $email) {
	// Load the configuration
	global $CONFIG;

	// no need to trim password.
	$username = trim($username);
	$name = trim(strip_tags($name));
	$email = trim($email);

	// Make sure a user with conflicting details hasn't registered and been disabled
	$access_status = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	$error = false;

	if (empty($name)) {
		$error = true;
	}

	if (!is_email_address($email)) {
		$error = true;
	}

	// The validation function may throw an exceptions which we do not
	// want to pass straight through the API so we catch it here.
	try {
		validate_username($username);
	} catch (Exception $e) {
		$error = true;
	}

	if ($error) {
		$message = <<<MSG
Plugin moodle_integration failed to create user.

Moodle provided the following data:
username => "$username"
name => "$name"
email => "$email"
MSG;

		// Add persistent notification to admin panel
		elgg_add_admin_notice('moodle_integration_create_user_failed', $message);

		// Give a very simple error message to user.
		throw new Exception(elgg_echo('APIException:UnableToCreateUser'));
	}

	access_show_hidden_entities($access_status);

	// Create user
	$user = new ElggUser();
	$user->username = $username;
	$user->email = $email;
	$user->name = $name;
	$user->access_id = ACCESS_PUBLIC;
	$user->salt = generate_random_cleartext_password(); // Note salt generated before password!
	$user->password = generate_user_password($user, $password);
	$user->owner_guid = 0; // Users aren't owned by anyone, even if they are admin created.
	$user->container_guid = 0; // Users aren't contained by anyone, even if they are admin created.
	$user->registration_method = 'moodle_integration'; // Save info how account was created
	$user->save();

	// Turn on email notifications by default
	set_user_notification_setting($user->getGUID(), 'email', true);

	return $user->getGUID();
}

/**
 * This fixes the post parameters that are munged due to page handler
 * 
 * This was removed from core in Elgg 1.8 due to adding %{QUERY_STRING} in .htaccess.
 * For some reason the POST variables aren't however available without this.
 */
function include_post_data() {
	$postdata = get_post_data();

	// In case the ampersands have been replaced with &amp;
	if (strstr($postdata, "&amp") !== false) {
		$postdata = htmlspecialchars_decode($postdata);
	}

	if (isset($postdata)) {
		$query_arr = elgg_parse_str($postdata);

		// Magic quotes is turned on so we need to strip slashes
		if (ini_get_bool('magic_quotes_gpc')) {
			if (function_exists('stripslashes_deep')) {
				// defined in input.php to handle magic quotes
				$query_arr = stripslashes_deep($query_arr);
			}
		}

		if (is_array($query_arr)) {
			foreach ($query_arr as $name => $val) {
				set_input($name, $val);
			}
		}
	}
}

/**
 * Add the short name of moodle course to group profile fields.
 * 
 * @param  string $hook
 * @param  string $entity_type
 * @param  array  $returnvalue
 * @param  array  $params
 * @return array  $returnvalue
 */
function moodle_integration_group_profile($hook, $entity_type, $returnvalue, $params){
	$returnvalue['moodle_shortname'] = 'text';
	return $returnvalue;
}