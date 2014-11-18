<?php

/**
 * Get the guid of the group associated with the Moodle course.
 * Create a new one if group is not found.
 *
 * @param $shortname string Shortname of the Moodle course
 * @return int GUID of the group
 */
function api_moodle_integration_get_group_guid($shortname) {
	global $CONFIG;

	// Authenticate user with REST API authentication token
	$token = get_input('auth_token');
	if ($user_guid = validate_user_token($token, $CONFIG->site_id)) {
		$user = get_user($user_guid);
	} else {
		throw new APIException(elgg_echo('APIException:GetGroupGuid:NoUser'));
	}

	$options = array(
		'metadata_name' => 'moodle_shortname',
		'metadata_value' => $shortname,
		'type' => 'group',
		'limit' => 1
	);

	if ($groups = elgg_get_entities_from_metadata($options)) {
		// Take the first found group
		$group = $groups[0];

		if (!$group->isMember($user)) {
			$group->join($user);
		}

		// return the guid
		return $group->getGUID();
	} else {
		// Group was not found so let's create a new one
		$group = new ElggGroup();
		$group->membership = ACCESS_PRIVATE;
		$group->access_id = ACCESS_PUBLIC;
		$group->name = $shortname;
		$group->moodle_shortname = $shortname;
		$guid = $group->save();

		if ($guid) {
			$group->join($user);

			return $guid;
		} else {
			throw new APIException(elgg_echo('APIException:GetGroupGuid:UnableToCreateGroup'));
		}
	}
}

/**
 * Get latest discussions of the group.
 *
 * Note that get only the discussions which have at least one reply.
 *
 * @param int $group_guid
 * @return array $return Array of discussion items
 */
function api_moodle_integration_get_group_discussions($group_guid){
	$return = array();

	$options = array(
		'type' => 'object',
		'subtype' => 'groupforumtopic',
		'annotation_name' => 'group_topic_post',
		'container_guid' => $group_guid,
		'limit' => 5
	);

	if ($forum = elgg_get_entities_from_annotations($options)) {
		foreach($forum as $message){
			$return[] = array(
				'title' => $message->title,
				'url' => $message->getUrl(),
				'time' => elgg_get_friendly_time($message->time_created),
				'user' => $message->getOwnerEntity()->name
			);
		}
	}

	return $return;
}

/**
 * Get any Elgg objects tagged with the shortname of Moodle course.
 *
 * @param string $object_type Sybtype of the objecy (file, blog, messages)
 * @param string $tag Moodle course shortname
 * @return array $return Array of object information
 */
function api_moodle_integration_get_objects($object_type, $tag){
	$return = array();

	$options = array(
		'metadata_name' => 'tags',
		'metadata_value' => $tag,
		'type' => 'object',
		'subtype' => $object_type,
		'metadata_case_sensitive' => false,
	);

	$objects = elgg_get_entities_from_metadata($options);

	if ($objects) {
		foreach($objects as $object) {
			$return[] = array(
				'title' => $object->title,
				'url' => $object->getURL(),
				'time' => elgg_get_friendly_time($object->time_created),
				'user' => $object->getOwnerEntity()->name
			);
		}
	}

	return $return;
}