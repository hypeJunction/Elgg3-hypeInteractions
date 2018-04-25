<?php

namespace hypeJunction\Interactions;

use DatabaseException;
use Elgg\EntityNotFoundException;
use Elgg\EntityPermissionsException;
use Elgg\Http\OkResponse;
use Elgg\HttpException;
use Elgg\Request;
use Elgg\ValidationException;

class SaveCommentAction {

	/**
	 * Save comment
	 *
	 * @param Request $request
	 *
	 * @return OkResponse
	 * @throws EntityNotFoundException
	 * @throws EntityPermissionsException
	 * @throws HttpException
	 * @throws ValidationException
	 * @throws DatabaseException
	 */
	public function __invoke(Request $request) {

		$poster = elgg_get_logged_in_user_entity();

		$description = $request->getParam('generic_comment', false);
		if (empty($description)) {
			throw new ValidationException(elgg_echo('generic_comment:blank'));
		}

		$comment_guid = $request->getParam('comment_guid', null);
		$entity_guid = $request->getParam('entity_guid', null);
		$new_comment = !$comment_guid;

		if (!$new_comment) {
			$comment = get_entity($comment_guid);

			if (!$comment instanceof Comment) {
				throw new EntityNotFoundException(elgg_echo('generic_comment:notfound'));
			}

			if (!$comment->canEdit()) {
				throw new EntityNotFoundException(elgg_echo('actionunauthorized'));
			}

			$entity = $comment->getContainerEntity();
		} else {
			$entity = get_entity($entity_guid);
			if (!$entity) {
				throw new EntityNotFoundException(elgg_echo('generic_comment:notfound'));
			}

			if (!$entity->canComment()) {
				throw new EntityPermissionsException(elgg_echo('actionunauthorized'));
			}

			$comment = new Comment();
			$comment->owner_guid = $poster->guid;
			$comment->container_guid = $entity->guid;
			$comment->access_id = $entity->access_id;
		}

		$comment->description = $description;

		$title = elgg_get_title_input();
		if ($title) {
			$comment->title = $title;
		}

		if (!$comment->save()) {
			throw new HttpException(elgg_echo('generic_comment:failure'));
		}

		if (elgg_is_active_plugin('hypeAttachments')) {
			hypeapps_attach_uploaded_files($comment, 'uploads', [
				'origin' => 'comment',
				'container_guid' => $comment->guid,
				'access_id' => $comment->access_id,
			]);
		}

		if ($new_comment) {
			// Add to river
			elgg_create_river_item([
				'action_type' => 'create',
				'subject_guid' => $poster->guid,
				'object_guid' => $comment->guid,
				'target_guid' => $entity->guid,
			]);
		}

		$output = '';

		if (elgg_is_xhr()) {
			elgg_push_context('comments');
			if ($comment_guid) {
				// editing a comment
				$view = elgg_view_entity($comment, [
					'full_view' => true,
				]);
			} else {
				// new comment
				$view = elgg_view('framework/interactions/comments', [
					'entity' => $entity,
					'comment' => $comment,
				]);
			}

			$output = [
				'guid' => $entity->guid,
				'view' => $view,
				'stats' => InteractionsService::instance()->getStats($entity),
			];

			elgg_pop_context();
		}

		return elgg_ok_response($output, elgg_echo('generic_comment:posted'), $comment->getURL());
	}
}