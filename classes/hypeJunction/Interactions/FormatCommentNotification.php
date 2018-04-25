<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;
use Elgg\Notifications\Notification;

class FormatCommentNotification {

	/**
	 * Prepare a notification for when comment is created
	 *
	 * @elgg_plugin_hook prepare notification:create:object:comment
	 *
	 * @param Hook $hook Hook
	 *
	 * @return Notification|null
	 */
	public function __invoke(Hook $hook) {

		$notification = $hook->getValue();
		/* @var $notification \Elgg\Notifications\Notification */

		$event = $hook->getParam('event');
		$comment = $event->getObject();
		$recipient = $hook->getParam('recipient');
		$language = $hook->getParam('language');

		if (!$comment instanceof Comment) {
			return null;
		}

		$entity = $comment->getContainerEntity();
		if (!$entity) {
			return null;
		}

		$messages = (new NotificationFormatter($comment, $recipient, $language))->prepare();

		$notification->summary = $messages->summary;
		$notification->subject = $messages->subject;
		$notification->body = $messages->body;

		return $notification;
	}
}