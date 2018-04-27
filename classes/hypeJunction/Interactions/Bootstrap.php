<?php

namespace hypeJunction\Interactions;

use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_register_collection('collection:object:comment', DefaultCommentCollection::class);

		elgg_extend_view('elgg.css', 'page/components/interactions.css');

		// URL and page handling
		elgg_register_plugin_hook_handler('entity:url', 'object', [Router::class, 'urlHandler']);
		elgg_register_plugin_hook_handler('entity:icon:url', 'object', [Router::class, 'iconUrlHandler']);

		// Replace comments block
		elgg_register_plugin_hook_handler('comments', 'all', ReplaceCommentsBlock::class);

		// Create an actionable river object
		elgg_register_event_handler('created', 'river', CreateRiverObject::class);
		elgg_register_event_handler('delete:after', 'river', DeleteRiverObject::class);
		elgg_register_plugin_hook_handler('update:after', 'all', SyncRiverObjectAccess::class);

		// Setup subscriptions
		elgg_register_event_handler('create', 'object', SubscribeToCommentNotifications::class);

		// Configure permissions
		elgg_register_plugin_hook_handler('container_logic_check', 'object', CanCommentOnComment::class);
		elgg_register_plugin_hook_handler('permissions_check', 'annotation', CanEditLikeAnnotation::class);

		// Setup menus
		elgg_register_plugin_hook_handler('register', 'menu:interactions', InteractionsMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:river', RiverMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:social', SocialMenu::class);

		// Prepare notifications
		elgg_register_notification_event('object', 'comment', ['create']);
		elgg_register_plugin_hook_handler('prepare', 'notification:create:object:comment', FormatCommentNotification::class);
		elgg_register_plugin_hook_handler('get', 'subscriptions', GetCommentSubscribers::class);

		// Custom logic for blogs
		elgg_extend_view('object/blog', 'object/blog/interactions');

		// Actionable river items
		elgg_register_plugin_hook_handler('likes:is_likable', 'object:river_object', [\Elgg\Values::class, 'getTrue']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {
		// Clean up
		elgg_unregister_plugin_hook_handler('register', 'menu:social', '_elgg_comments_social_menu_setup');
		elgg_unregister_plugin_hook_handler('register', 'menu:social', 'likes_social_menu_setup');
		elgg_unextend_view('elgg.css', 'likes/css');
	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}
}