<?php

namespace hypeJunction\Interactions;

use DatabaseException;
use Elgg\EntityNotFoundException;
use Elgg\EntityPermissionsException;
use Elgg\Http\ResponseBuilder;
use Elgg\Request;
use NotificationException;

class LikeAction {

	/**
	 * Add a like
	 *
	 * @param Request $request Request
	 *
	 * @return ResponseBuilder
	 * @throws EntityNotFoundException
	 * @throws EntityPermissionsException
	 * @throws NotificationException
	 * @throws DatabaseException
	 */
	public function __invoke(Request $request) {

		$entity = $request->getEntityParam();

		if (!$entity) {
			throw new EntityNotFoundException(elgg_echo('likes:notfound'));
		}

		$user = elgg_get_logged_in_user_entity();

		if (elgg_annotation_exists($entity->guid, 'likes')) {
			throw new EntityPermissionsException(elgg_echo('likes:alreadyliked'));
		}

		$id = $entity->annotate('likes', 1, '', $user->guid, $entity->access_id);

		if (!$id) {
			return elgg_error_response(elgg_echo('likes:failure'));
		}

		$annotation = elgg_get_annotation_from_id($id);

		$this->notifyUser($annotation);

		if (elgg_get_plugin_setting('likes_in_river', 'hypeInteractions')) {
			elgg_create_river_item([
				'view' => 'framework/river/stream/like',
				'action_type' => 'stream:like',
				'subject_guid' => $user->guid,
				'object_guid' => $entity->guid,
				'annotation_id' => $id,
			]);
		}

		$data = [
			'guid' => $entity->guid,
			'stats' => InteractionsService::instance()->getStats($entity),
		];

		$msg = elgg_echo('likes:likes');
		$url = "stream/likes/$entity->guid";

		return elgg_ok_response($data, $msg, $url);
	}

	/**
	 * Notify owner that user liked their content
	 *
	 * @param \ElggAnnotation $annotation Like annotation
	 *
	 * @return array
	 * @throws NotificationException
	 */
	protected function notifyUser(\ElggAnnotation $annotation) {
		$user = $annotation->getOwnerEntity();
		$entity = $annotation->getEntity();

		if ($entity->owner_guid == $user->guid) {
			return [];
		}

		$language = $user->language;
		$user_url = elgg_view('output/url', [
			'text' => $user->name,
			'href' => $user->getURL(),
		]);

		if ($entity instanceof Comment) {
			$target = elgg_echo('interactions:comment');
		} else {
			$target = elgg_echo('interactions:post');
		}

		$entity_title = $entity->getDisplayName();

		$entity_url = elgg_view('output/url', [
			'text' => $entity_title,
			'href' => elgg_http_add_url_query_elements($entity->getURL(), [
				'active_tab' => 'likes',
			]),
		]);

		$entity_url = elgg_echo('interactions:ownership:your', [$target], $language) . ' ' . $entity_url;

		$entity_ownership = elgg_echo('interactions:ownership:your', [$target], $language);
		$entity_ownership_url = elgg_view('output/url', [
			'text' => $entity_ownership,
			'href' => elgg_http_add_url_query_elements($entity->getURL(), [
				'active_tab' => 'likes',
			]),
		]);

		$summary = elgg_echo('interactions:likes:notifications:subject', [
			$user_url,
			$entity_ownership_url,
		], $language);

		$subject = strip_tags($summary);

		$owner = $entity->getOwnerEntity();

		$body = elgg_echo('interactions:likes:notifications:body', [
			$user_url,
			$entity_url,
			$entity->getURL(),
			$user->getDisplayName(),
			$user->getURL(),
		], $owner->language);

		return notify_user($entity->owner_guid, $user->guid, $subject, $body, [
			'action' => 'create',
			'object' => $annotation,
			'summary' => $summary,
		]);
	}
}