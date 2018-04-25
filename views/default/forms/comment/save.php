<?php

/**
 * Form for adding and editing comments
 *
 * @package Elgg
 *
 * @uses    ElggEntity  $vars['entity']       The entity being commented
 * @uses    ElggComment $vars['comment']      The comment being edited
 * @uses    bool        $vars['inline']       Show a single line version of the form?
 * @uses    bool        $vars['is_edit_page'] Is this form on its own page?
 */

if (!elgg_is_logged_in()) {
	return;
}

$entity = elgg_extract('entity', $vars);
/* @var $entity \ElggEntity */

$comment = elgg_extract('comment', $vars);
/* @var $comment \hypeJunction\Interactions\Comment */

if ($comment instanceof \hypeJunction\Interactions\Comment) {
	$owner = $comment->getOwnerEntity();
} else {
	$owner = elgg_get_logged_in_user_entity();
}

$icon = elgg_view_entity_icon($owner, 'small', [
	'use_hover' => false,
	'use_link' => false,
]);

$body = elgg_view_field([
	'#type' => 'interactions/comment',
	'name' => 'generic_comment',
	'value' => $comment->description,
]);

$buttons = [];

if (\hypeJunction\Interactions\InteractionsService::instance()->canAttachFiles()) {
	$field = elgg_view_field([
		'#type' => 'attachments',
		'#class' => 'comment-form-input-attachments hidden',
	]);

	if ($field) {
		$body .= $field;
		$buttons[] = [
			'name' => 'attach',
			'href' => 'javascript:',
			'data-target' => '.comment-form-input-attachments',
			'link_class' => 'comment-form-toggler',
			'text' => elgg_echo('attachments:upload'),
			'icon' => 'paperclip',
			'priority' => 100,
		];
	}
}

$buttons[] = [
	'name' => 'submit',
	'text' => elgg_view_field([
		'#type' => 'submit',
		'value' => $comment ? elgg_echo('interactions:reply:create') : elgg_echo('generic_comments:post'),
	]),
	'href' => false,
	'priority' => 900,
];

if ($comment instanceof \hypeJunction\Interactions\Comment) {
	$buttons [] = [
		'name' => 'cancel',
		'text' => elgg_view_field([
			'#type' => 'button',
			'value' => elgg_echo('cancel'),
			'class' => 'elgg-button-cancel',
			'href' => $comment->getURL(),
		]),
		'priority' => 800,
	];
}

$footer = elgg_view_menu('comment:form:footer', [
	'items' => $buttons,
	'class' => 'elgg-menu-hz',
	'entity' => $comment,
]);

$footer = elgg_format_element('div', [
	'class' => 'elgg-foot',
], $footer);

echo elgg_view_image_block($icon, $body . $footer, [
	'class' => 'interactions-image-block',
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'comment_guid',
	'value' => $comment->guid,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'entity_guid',
	'value' => ($comment) ? $comment->container_guid : $entity->guid,
]);

