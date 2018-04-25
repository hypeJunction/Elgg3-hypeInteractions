<?php

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid);

$entity = get_entity($guid);
/* @var $entity \ElggEntity */

if (elgg_is_xhr()) {
	echo elgg_view('framework/interactions/likes', array(
		'entity' => $entity,
		'active_tab' => ($comment) ? 'likes' : false,
	));
} else {
	$title = elgg_echo('interactions:likes:title', array($entity->getDisplayName()));

	if ($entity instanceof \hypeJunction\Interactions\Comment) {
		$content = elgg_view_entity($entity, [
			'full_view' => true,
		]);
	} else {
		// Show partial entity listing
		$content = elgg_view_entity($entity, [
			'full_view' => false,
		]);

		// Show comments
		$content .= elgg_view_comments($entity, true, [
			'entity' => $entity,
			'active_tab' => 'likes',
		]);
	}

	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => false,
		'sidebar' => false,
	));

	echo elgg_view_page($title, $layout);
}

