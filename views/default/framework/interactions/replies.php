<?php

/**
 * @uses $vars['entity']        Entity whose comments thread is being displayed
 * @uses $vars['comment']       Comment entity being deep linked
 * @uses $vars['show_add_form'] Display a form to add a new comment
 */

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

if (!$entity instanceof ElggComment) {
	return;
}

$comment = elgg_extract('comment', $vars);
/* @var $comment \hypeJunction\Interactions\Comment */

$comments_count = elgg_get_total_comments($entity);
$can_comment = $entity->canComment() && $entity->canWriteToContainer(0, 'object', 'comment');

if (!$comments_count && !$can_comment) {
	return;
}

$svc = \hypeJunction\Interactions\InteractionsService::instance();

$show_form = elgg_extract('show_add_form', $vars, true) && $entity->canComment() && $entity->canWriteToContainer(0, 'object', 'comment');
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