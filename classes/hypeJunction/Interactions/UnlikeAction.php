<?php

namespace hypeJunction\Interactions;

use Elgg\Request;

class UnlikeAction {

	/**
	 * Remove likes
	 *
	 * @param Request $request Request
	 *
	 * @return \Elgg\Http\ErrorResponse|\Elgg\Http\OkResponse
	 */
	public function __invoke(Request $request) {

		$id = (int) $request->getParam('id');

		$likes = [];
		$like = null;
		$entity = null;

		if ($id) {
			$like = elgg_get_annotation_from_id($id);
			$entity = $like ? get_entity((int) $like->entity_guid) : null;
		}

		if ($like) {
			$likes[] = $like;
		} else {
			$guid = (int) $request->getParam('guid');
			$entity = $guid ? get_entity($guid) : null;

			if ($entity) {
				$likes = elgg_get_annotations([
					'guid' => $entity->guid,
					'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
					'annotation_name' => 'likes',
					'limit' => 0,
				]);
			}
		}

		$error = true;

		foreach ($likes as $like) {
			if ($like->canEdit()) {
				$error = false;
				$like->delete();
			}
		}

		if ($error) {
			return elgg_error_response(elgg_echo('likes:notdeleted'));
		} else {
			$data = [
				'guid' => $entity->guid,
				'stats' => InteractionsService::instance()->getStats($entity)
			];

			$msg = elgg_echo('likes:deleted');

			return elgg_ok_response($data, $msg);
		}
	}
}