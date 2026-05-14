<?php

$guid = elgg_extract('guid', $vars);
$comment = get_entity($guid);
/* @var \hypeJunction\Interactions\Comment $comment */

if (!$comment instanceof \hypeJunction\Interactions\Comment) {
	throw new \Elgg\EntityNotFoundException();
}

if (!$comment->canEdit()) {
	throw new \Elgg\EntityPermissionsException();
}

$entity = $comment->getContainerEntity();

$content = elgg_view_form('comment/save', [
	'class' => [
		'interactions-form',
		'interactions-edit-comment-form',
		'interactions-form-edit',
	],
	'data-guid' => $entity->guid,
	'data-comment-guid' => $comment->guid,
	'enctype' => 'multipart/form-data',
], [
	'entity' => $entity,
	'comment' => $comment,
]);

if (elgg_is_xhr()) {
	echo $content;
} else {
	$title = elgg_echo('interactions:comments:edit:title');
	$layout = elgg_view_layout('content', [
		'title' => $title,
		'content' => $content,
		'filter' => false,
		'sidebar' => false,
	]);

	echo elgg_view_page($title, $layout);
}

