<?php

namespace hypeJunction\Interactions;

use Elgg\Request;

class ToggleLikeAction {

	/**
	 * Toggle like
	 *
	 * @param Request $request
	 *
	 * @return \Elgg\Http\ResponseBuilder
	 */
	public function __invoke(Request $request) {

		$entity = $request->getEntityParam();

		if (elgg_annotation_exists($entity->guid, 'likes')) {
			$controller = new UnlikeAction();
		} else {
			$controller = new LikeAction();
		}

		return $controller($request);
	}
}