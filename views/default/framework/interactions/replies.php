<?php

/**
 * @uses $vars['entity']        Entity whose comments thread is being displayed
 * @uses $vars['comment']       Comment entity being deep linked
 * @uses $vars['show_add_form'] Display a form to add a new comment
 */

$entity = elgg_extract('entity', $vars, false);
/* @var $entity ElggEntity */

if (!$entity instanceof ElggComment) {
	return;
}

$comment = elgg_extract('comment', $vars);
/* @var $comment \hypeJunction\Interactions\Comment */

$svc = \hypeJunction\Interactions\InteractionsService::instance();

$show_form = elgg_extract('show_add_form', $vars, true) && $entity->canComment();
$sort = $svc->getCommentsSort();
$form_position = $svc->getCommentsFormPosition();

$collection = elgg_get_collection('collection:object:comment', $entity, [
	'sort' => $sort,
	'comment' => $comment,
]);

$list = $collection->render([
	'level' => elgg_extract('level', $vars, 2),
]);

$form = '';
if ($show_form) {
	$form_class = [
		'interactions-form',
		'interactions-add-comment-form',
		'hidden',
	];

	$form = elgg_view_form('comment/save', [
		'class' => $form_class,
	], [
		'entity' => $entity,
	]);
}

if ($form_position == 'before') {
	echo $form . $list;
} else {
	echo $list . $form;
}