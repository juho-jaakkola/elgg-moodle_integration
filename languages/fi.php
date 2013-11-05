<?php

$finnish = array(
	// Plugin settings
	'moodle_integration:setting:moodle_token' => 'Moodlessa luotu verkkopalveluavain (web service token)',
	'moodle_integration:setting:moodle_domainname' => 'Moodlen osoite (ilman viimeistä kauttaviivaa)',

	// Api method descriptions
	'moodle_integration:gettoken:description' => 'Tämän kautta käyttäjä voi pyytää autentikaatiokoodin, jota voi käyttää seuraavien API-kutsujen autentikointiin. Liitä koodi kutsuihin parametrin "auth_token" arvoksi.',

	// New group profile field
	'groups:moodle_shortname' => 'Moodle-kurssin lyhytnimi',

	// API Exceptions
	'APIException:TokenCreateFailed' => 'Autentikointitunnuksen luominen epäonnistui',
	'APIException:MoodleClientNotConfigured' => 'Moodlen rajapinnan asetukset puuttuvat Elggistä. Ota yhteys ylläpitoon.',
	'APIException:UnableToCreateUser' => 'Tunnuksen luominen Elggiin epäonnistui. Ota yhteys ylläpitoon.',
	'APIException:GetGroupGuid:UnableToCreateGroup' => 'Ryhmän luominen epäonnistui',
	'APIException:UnknownError' => 'Odottamaton API-virhe. Ota yhteys ylläpitoon.',
);

add_translation('fi', $finnish);