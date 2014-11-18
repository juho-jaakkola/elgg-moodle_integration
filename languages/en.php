<?php

$english = array(
	// Plugin settings
	'moodle_integration:setting:moodle_token' => 'Web services token provided by Moodle',
	'moodle_integration:setting:moodle_domainname' => 'Moodle domain name',

	// Api method description
	'moodle_integration:gettoken:description' => 'This API call lets a user obtain a user authentication token which can be used for authenticating future API calls. Pass it as the parameter auth_token.',

	// New field to group profile
	'groups:moodle_shortname' => 'Moodle course shortname',

	// API Exceptions
	'APIException:TokenCreateFailed' => 'Creation of authentication token failed',
	'APIException:MoodleClientNotConfigured' => 'The Moodle API settings are missing from Elgg plugin. Contact site administrator.',
	'APIException:UnableToCreateUser' => 'System is unable to create an Elgg account. Please contact site administrator.',
	'APIException:GetGroupGuid:UnableToCreateGroup' => 'Unable to create Elgg group. Please contact site administrator.',
	'APIException:UnknownError' => 'Unknown API error. Please contact site administrator.',
);

add_translation('en', $english);