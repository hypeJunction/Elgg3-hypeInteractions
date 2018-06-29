<?php

$entity_guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($entity_guid);

$entity = get_entity($entity_guid);
/* @var $entity ElggEntity */

$comment_guid = elgg_extract('comment_guid', $vars);

$comment = get_entity($comment_guid);
/* @var $comment \hypeJunction\Interactions\Comment */

if (elgg_is_xhr()) {
	// List comments for ajax loading
	// We do not need the entire page shell
	echo elgg_view('framework/interactions/comments', [
		'entity' => $entity,
		'comment' => $comment,
		'active_tab' => ($comment_guid) ? 'comments' : false,
		'show_add_form' => !get_input('sort') && !get_input('query') && !get_input('filter'),
	]);
} else {
	$title = elgg_echo('interactions:comments:title', [$entity->getDisplayName()]);

	if ($entity instanceof \hypeJunction\Interactions\Comment) {
		$content = elgg_view_entity($entity, [
			'full_view' => true,
		]);
	} else {
		// Show partial entity listing
		$content = elgg_view_entity($entity, [
			'full_view' => true,
			'show_responses' => true,
		]);

		if (!preg_match('/\id=\"comments\"/im', $content)) {
			$content .= elgg_view_comments($entity, true, [
				'entity' => $entity,
				'comment' => $comment,
				'active_tab' => 'comments',
				'show_add_form' => true,
				'expand_form' => true,
			]);;
		}
	}

	$layout = elgg_view_layout('content', [
		'title' => $title,
		'content' => $content,
		'entity' => $entity,
		'filter' => false,
	]);

	echo elgg_view_page($title, $layout, 'default', [
		'entity' => $entity,
	]);
}

