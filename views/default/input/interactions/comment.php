<?php

$defaults = [
	'rows' => 2,
	'placeholder' => elgg_echo('generic_comments:add'),
	'visual' => elgg_get_plugin_setting('comments_visual_editor', 'hypeinteractions', false),
	'editor_type' => elgg_get_plugin_setting('comments_editor_typ', 'hypeinteractions', 'simple'),
];

$vars['class'] = elgg_extract_class($vars, 'elgg-input-comment');

$vars = array_merge($defaults, $vars);

echo elgg_view('input/longtext', $vars);