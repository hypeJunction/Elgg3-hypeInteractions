<?php

/**
 * River item footer
 *
 * @uses $vars['item'] ElggRiverItem
 * @uses $vars['responses'] Alternate override for this item
 */

if (elgg_in_context('substream-view')) {
	return true;
}

// allow river views to override the response content
$responses = elgg_extract('responses', $vars, null);

if ($responses === false) {
	return true;
}

if ($responses) {
	echo $responses;
	return true;
}

$item = elgg_extract('item', $vars, false);

if (!$item instanceof ElggRiverItem) {
	return true;
}

$object = \hypeJunction\Interactions\InteractionsService::instance()->getRiverObject($item);

echo elgg_view_comments($object);
