<?php

namespace hypeJunction\Interactions;

use ElggEntity;

if (!elgg_is_logged_in()) {
	if (elgg_get_plugin_setting('gatekeep_likes', 'hypeInteractions')) {
		$link = elgg_view('output/url', [
			'href' => elgg_get_login_url(),
			'text' => elgg_echo('interactions:login'),
		]);

		echo elgg_format_element('div', [
			'class' => 'elgg-no-results',
		], elgg_echo('interactions:likes_gatekeeper:no_results', [$link]));

		return;
	}
}

$entity = elgg_extract('entity', $vars, false);
/* @var $entity ElggEntity */

if (!elgg_instanceof($entity)) {
	return true;
}

$limit = get_input('limit', 20);
$offset_key = "likes_$entity->guid";
$offset = get_input($offset_key, 0);
$count = elgg_get_total_likes($entity);

$options = array(
	'guid' => $entity->guid,
	'annotation_names' => 'likes',
	'list_id' => "interactions-likes-{$entity->guid}",
	'list_class' => 'interactions-likes-list',
	'base_url' => "stream/likes/$entity->guid",
	'limit' => $limit,
	'offset' => $offset,
	'offset_key' => $offset_key,
	'count' => $count,
	'pagination' => true,
	'pagination_type' => 'infinite',
	'lazy_load' => 0,
	'auto_refresh' => 90,
	'data-selector-delete' => '[data-confirm]:has(.elgg-icon-delete)',
	'no_results' => elgg_echo('interactions:likes:no_results'),
	'data-guid' => $entity->guid,
	'data-trait' => 'likes',
);

echo elgg_list_annotations($options);
