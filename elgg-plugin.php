<?php

return [
	'bootstrap' => \hypeJunction\Interactions\Bootstrap::class,

	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'comment',
			'class' => \hypeJunction\Interactions\Comment::class,
			'searchable' => true,
		],
		[
			'type' => 'object',
			'subtype' => 'river_object',
			'class' => \hypeJunction\Interactions\RiverObject::class,
			'searchable' => false,
		],
		// legacy subtypes
		[
			'type' => 'object',
			'subtype' => 'hjcomment',
			'class' => \hypeJunction\Interactions\Comment::class,
			'searchable' => false,
		],
		[
			'type' => 'object',
			'subtype' => 'hjstream',
			'class' => \hypeJunction\Interactions\RiverObject::class,
			'searchable' => false,
		]
	],

	'actions' => [
		'comment/save' => [
			'controller' => \hypeJunction\Interactions\SaveCommentAction::class,
		],
		'stream/like' => [
			'controller' => \hypeJunction\Interactions\ToggleLikeAction::class,
		],
		'likes/add' => [
			'controller' => \hypeJunction\Interactions\LikeAction::class,
		],
		'likes/delete' => [
			'controller' => \hypeJunction\Interactions\UnlikeAction::class,
		],
	],

	'routes' => [
		'edit:object:comment' => [
			'path' => 'stream/edit/{guid}',
			'resource' => 'interactions/edit',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'view:object:comment' => [
			'path' => 'stream/view/{guid}',
			'resource' => 'interactions/view',
		],
		'collection:object:comment' => [
			'path' => 'stream/comments/{guid}/{comment_guid?}',
			'resource' => 'interactions/comments',
		],
		'collection:annotation:likes' => [
			'path' => 'stream/likes/{guid}',
			'resource' => 'interactions/likes',
		]
	],

	'settings' => [
		'max_comment_depth' => 1,
		'comment_form_position' => 'after',
		'comments_order' => 'asc',
		'comments_load_style' => 'load_older',
		'comments_limit' => 3,
		'comments_load_limit' => 20,
		'likes_in_river' => false,
	],

	'default' => [
		'js/framework/interactions/lib.js' => __DIR__ . '/views/default/page/components/interactions.js',
	]
];