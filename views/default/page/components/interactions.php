<?php

/**
 * @uses $vars['entity'] Entity whose interactions are being displayed
 * @uses $vars['full_view'] Is this is full entity listing
 * @uses $vars['active_tab'] Current active tab
 */

use hypeJunction\Interactions\InteractionsService;

$entity = elgg_extract('entity', $vars, false);
/* @var $entity ElggEntity */

if (!$entity instanceof ElggEntity) {
	return;
}

$full_view = elgg_extract('full_view', $vars, false);

$active_tab = elgg_extract('active_tab', $vars, get_input('active_tab'));

if (!isset($active_tab)) {
	if ($full_view || elgg_get_plugin_setting('default_expand', 'hypeInteractions')) {
	    $active_tab = 'comments';
	}
}

if ($active_tab === 'comments') {
	$comments_count = elgg_get_total_comments($entity);
	$can_comment = $entity->canComment() && $entity->canWriteToContainer(0, 'object', 'comment');

	if (!$comments_count && !$can_comment) {
		unset($active_tab);
	}
}

$menu = elgg_view_menu('interactions', [
	'entity' => $entity,
	'class' => [
		'elgg-menu-hz',
		$entity instanceof \hypeJunction\Interactions\Comment ? 'interactions-menu-sub' : 'interactions-menu-top',
	],
	'sort_by' => 'priority',
	'active_tab' => $active_tab,
]);

if (empty($menu)) {
	return;
}

$controls = elgg_format_element('div', [
	'class' => 'interactions-controls',
], $menu);

$class = elgg_extract_class($vars, ['interactions'], 'interactions_class');

$level = elgg_extract('level', $vars, 0) + 1;

if ($level > 1) {
	$class[] = 'interactions-sub';
} else {
	$class[] = 'interactions-top';
}

if ($active_tab) {
    $params = [
        'entity' => $entity,
        'level' => $level,
        'full_view' => $full_view,
        'active_tab' => $active_tab,
        'deferred' => true,
    ];

	$content = elgg_view("framework/interactions/$active_tab", $params);

	$component = elgg_format_element('div', [
		'class' => 'interactions-component elgg-state-selected',
		'data-trait' => $active_tab,
	], $content);

	$class[] = 'interactions-has-active-tab';
}

echo elgg_format_element('div', [
	'class' => $class,
], $controls . $component);
?>

<script>
	require(['page/components/interactions'])
</script>