<?php

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof \hypeJunction\Interactions\Comment) {
	return;
}

$body = elgg_view('output/longtext', array_merge($vars, [
	'value' => $entity->description,
	'class' => 'interactions-comment-text',
	'data-role' => 'comment-text',
]));

echo elgg_format_element('div', [
	'class' => 'interactions-comment-body',
], $body);
