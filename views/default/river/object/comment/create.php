<?php

/**
 * Post comment river view
 */
$item = $vars['item'];
/* @var ElggRiverItem $item */

$comment = $item->getObjectEntity();
$subject = $item->getSubjectEntity();
$target = $item->getTargetEntity();

$subject_link = elgg_view('output/url', [
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
]);

$target_link = elgg_view('output/url', [
	'href' => $comment->getURL(),
	'text' => $target->getDisplayName(),
	'class' => 'elgg-river-target',
	'is_trusted' => true,
]);

$type = $target->getType();
$subtype = $target->getSubtype() ? $target->getSubtype() : 'default';
$key = "river:$type:$subtype:comment";

if (!elgg_language_key_exists($key)) {
	$key = "river:$type:default:comment";
}

$params = [
	'entity' => $comment,
	'full_view' => true,
	'strip_shortcodes' => true,
];

$body = elgg_view('object/comment/elements/body', $params);

$attachments = elgg_view('object/comment/elements/attachments', $params);

if (elgg_get_plugin_setting('enable_url_preview', 'hypeInteractions')) {
	$attachments .= elgg_view('object/comment/elements/embeds', $params);
}

$summary = elgg_echo($key, [$subject_link, $target_link]);

echo elgg_view('river/elements/layout', [
	'item' => $vars['item'],
	'message' => $body,
	'summary' => $summary,
	'attachments' => $attachments,
]);