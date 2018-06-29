<?php

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', \hypeJunction\Interactions\RiverObject::SUBTYPE);

$entity = get_entity($guid);

$container = $entity->getContainerEntity();

elgg_set_page_owner_guid($container->guid);

if ($container instanceof ElggGroup) {
	elgg_push_breadcrumb($container->getDisplayName(), $container->getURL());
	elgg_push_breadcrumb(elgg_echo('activity'), "activity/group/$container->guid");
} else if ($container instanceof ElggUser) {
	elgg_push_breadcrumb($container->getDisplayName(), $container->getURL());
	elgg_push_breadcrumb(elgg_echo('activity'), "activity/owner/$container->username");
}

$title = $entity->getDisplayName();
elgg_push_breadcrumb($title);

$river = elgg_get_river([
	'ids' => (int) $entity->river_id,
	'limit' => 1,
]);

if (!$river) {
	throw new \Elgg\EntityNotFoundException();
}

$item = array_shift($river);

$content = elgg_view($item->getView(), [
	'item' => $item,
	'show_responses' => false,
	'responses' => false,
	'full_view' => true,
]);

if (!preg_match('/elgg-comments/im', $content)) {
	$content .= elgg_view_comments($entity, true, [
		'active_tab' => 'comments',
	]);
}

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => '',
]);

echo elgg_view_page($title, $layout);
