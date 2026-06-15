<?php

$entity = elgg_extract('entity', $vars);
/* @var $entity ElggPlugin */

$user_guid = (int) elgg_extract('user_guid', $vars, elgg_get_logged_in_user_guid());

echo elgg_view_field([
	'#type' => 'select',
	'name' => 'params[comment_form_position]',
	'value' => elgg_get_plugin_user_setting('comment_form_position', $user_guid, $entity->getID(), 'after'),
	'options_values' => array(
		'before' => elgg_echo('interactions:settings:comment_form_position:before'),
		'after' => elgg_echo('interactions:settings:comment_form_position:after'),
	),
	'#label' => elgg_echo('interactions:settings:comment_form_position'),
	'#help' => elgg_echo('interactions:settings:comment_form_position:help'),
]);

echo elgg_view_field([
	'#type' => 'select',
	'name' => 'params[comments_order]',
	'value' => hypeJunction\Interactions\InteractionsService::instance()->getCommentsSort(),
	'options_values' => [
		'time_created::desc' => elgg_echo('sort:object:time_created::desc'),
		'time_created::asc' => elgg_echo('sort:object:time_created::asc'),
		'likes_count::desc' => elgg_echo('sort:object:likes_count::desc'),
	],
	'#label' => elgg_echo('interactions:settings:comments_order'),
	'#help' => elgg_echo('interactions:settings:comments_order:help'),
]);

echo elgg_view_field([
	'#type' => 'select',
	'name' => 'params[comments_load_style]',
	'value' => hypeJunction\Interactions\InteractionsService::instance()->getLoadStyle(),
	'options_values' => array(
		'load_newer' => elgg_echo('interactions:settings:comments_load_style:load_newer'),
		'load_older' => elgg_echo('interactions:settings:comments_load_style:load_older'),
	),
	'#label' => elgg_echo('interactions:settings:comments_load_style'),
]);

