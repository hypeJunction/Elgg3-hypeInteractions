<?php

namespace hypeJunction\Interactions;

use Elgg\Event;
use Elgg\Notifications\Notification;

class FormatCommentNotification {

	/**
	 * Prepare a notification for when comment is created
	 *
	 * @elgg_event prepare notification:create:object:comment
	 *
	 * @param Event $event Event
	 *
	 * @return Notification|null
	 */
	public function __invoke(Event $event) {

		$notification = $event->getValue();
		/* @var $notification \Elgg\Notifications\Notification */

		$notification_event = $event->getParam('event');
		$comment = $notification_event->getObject();
		$recipient = $event->getParam('recipient');
		$language = $event->getParam('language');

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