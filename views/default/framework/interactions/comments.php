<?php

/**
 * @uses $vars['entity']        Entity whose comments thread is being displayed
 * @uses $vars['comment']       Comment entity being deep linked
 * @uses $vars['show_add_form'] Display a form to add a new comment
 * @uses $vars['expand_form']   Collapse/expand the form
 */

namespace hypeJunction\Interactions;

use ElggEntity;

$entity = elgg_extract('entity', $vars, false);
/* @var $entity ElggEntity */

$comment = elgg_extract('comment', $vars, false);
/* @var $comment Comment */

if (!$entity instanceof ElggEntity) {
	return;
}

$full_view = elgg_extract('full_view', $vars, false);
$show_form = elgg_extract('show_add_form', $vars, true) && $entity->canComment();
$expand_form = elgg_extract('expand_form', $vars, !elgg_in_context('widgets'));

$sort = InteractionsService::instance()->getCommentsSort();
if ($comment && !in_array($sort, ['time_created::asc', 'time_created::desc'])) {
	$sort = 'time_created::desc';
}

$style = InteractionsService::instance()->getLoadStyle();
$form_position = InteractionsService::instance()->getCommentsFormPosition();
$limit = elgg_extract('limit', $vars, InteractionsService::instance()->getLimit(!$full_view));

$offset_key = "comments_$entity->guid";
$offset = get_input($offset_key, null);

$count = $entity->countComments();

if (!isset($offset)) {
	if ($comment->container_guid != $entity->guid) {
		// Comment serves as a pointer that allows us to access a specific comment within a tree
		// Sometimes the comment is erraneously passed to the view by the router,
		// e.g. when trying to a access a specific 2nd level reply in a thread,
		// we only need to calculate the offset if the comment we are trying to access is
		// a direct child of the object we are viewing
		$offset = InteractionsService::instance()->calculateOffset($count, $limit);
	} else {
		$offset = InteractionsService::instance()->calculateOffset($count, $limit, $comment);
	}
}

$level = elgg_extract('level', $vars) ? : 1;

$options = [
	'types' => 'object',
	'subtypes' => [Comment::SUBTYPE, 'hjcomment'],
	'container_guid' => $entity->guid,
	'list_id' => "interactions-comments-{$entity->guid}",
	'list_class' => 'interactions-comments-list elgg-comments',
	'base_url' => elgg_normalize_url("stream/comments/$entity->guid"),
	'limit' => $limit,
	'offset' => $offset,
	'offset_key' => $offset_key,
	'full_view' => true,
	'pagination' => true,
	'pagination_type' => 'infinite',
	'lazy_load' => 0,
	'reversed' => $sort == 'time_created::asc',
	'auto_refresh' => 90,
	'no_results' => elgg_echo('interactions:comments:no_results'),
	'data-guid' => $entity->guid,
	'data-trait' => 'comments',
	'level' => $level,
];

elgg_push_context('comments');
$allow_sort = $level == 1 && (bool) elgg_get_plugin_setting('comment_sort', 'hypeInteractions');

$collection = elgg_get_collection('collection:object:comment', $entity, $options);

echo $collection->render($options);

elgg_pop_context();

$form = '';
if ($show_form) {
	$form_class = [
		'interactions-form',
		'interactions-add-comment-form',
	];
	if (!$expand_form) {
		$form_class[] = 'hidden';
	}
	$form = elgg_view_form('comment/save', [
		'class' => implode(' ', $form_class),
		'data-guid' => $entity->guid,
		'enctype' => 'multipart/form-data',
	], [
		'entity' => $entity,
	]);
}

if ($form_position == 'before') {
	echo $form . $list;
} else {
	echo $list . $form;
}