<?php

/**
 * @uses $vars['entity']        Entity whose comments thread is being displayed
 * @uses $vars['comment']       Comment entity being deep linked
 * @uses $vars['show_add_form'] Display a form to add a new comment
 */

use hypeJunction\Interactions\InteractionsService;

if (!elgg_is_logged_in()) {
	if (elgg_get_plugin_setting('gatekeep_comments', 'hypeInteractions')) {
		$link = elgg_view('output/url', [
			'href' => elgg_get_login_url(),
			'text' => elgg_echo('interactions:login'),
		]);

		echo elgg_format_element('div', [
			'class' => 'elgg-no-results',
		], elgg_echo('interactions:comments_gatekeeper:no_results', [$link]));

		return;
	}
}

$entity = elgg_extract('entity', $vars, false);
/* @var $entity ElggEntity */

if (!$entity instanceof ElggEntity) {
	return;
}

if ($entity instanceof ElggComment) {
	echo elgg_view('framework/interactions/replies', $vars);
	return;
}

$comments_count = elgg_get_total_comments($entity);
$can_comment = $entity->canComment() && $entity->canWriteToContainer(0, 'object', 'comment');

if (!$comments_count && !$can_comment) {
	return;
}

$comment = elgg_extract('comment', $vars);
/* @var $comment \hypeJunction\Interactions\Comment */

$svc = InteractionsService::instance();

$full_view = elgg_extract('full_view', $vars, true);
$show_form = elgg_extract('show_add_form', $vars, true) && $entity->canComment() && $entity->canWriteToContainer(0, 'object', 'comment');
$sort = $svc->getCommentsSort();
$form_position = $svc->getCommentsFormPosition();

$allow_sort = false;
if (!$entity instanceof ElggComment && elgg_get_total_comments($entity) > 20) {
	$allow_sort = (bool) elgg_get_plugin_setting('comment_sort', 'hypeInteractions');
}

$collection = elgg_get_collection('collection:object:comment', $entity, [
	'sort' => $sort,
	'full_view' => $full_view,
	'comment' => $comment,
]);

if ($allow_sort) {
	$options['form'] = elgg_view('collection/search', [
		'collection' => $collection,
		'expand_form' => false,
	]);
}

$list = $collection->render([
	'level' => elgg_extract('level', $vars, 2),
]);

$form = '';
if ($show_form) {
	$form_class = [
		'interactions-form',
		'interactions-add-comment-form',
	];

	if (!$full_view) {
		$form_class[] = 'hidden';
	}

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