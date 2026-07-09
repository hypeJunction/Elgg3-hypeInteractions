<?php

namespace hypeJunction\Interactions\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Interactions\Comment;
use hypeJunction\Interactions\RiverObject;

/**
 * Exercises Router::urlHandler through the real entity:url event chain on a
 * booted Elgg 7 site. Proves the \Elgg\Event-typed handler (ported from the
 * removed \Elgg\Hook) actually returns the interaction deep-links at runtime.
 */
class RouterTest extends IntegrationTestCase {

	public function testRiverObjectUrlRoutesToStreamView() {
		$user = $this->createUser();

		$object = $this->createObject([
			'subtype' => 'river_object',
			'owner_guid' => $user->guid,
			'container_guid' => $user->guid,
			'access_id' => ACCESS_PUBLIC,
		]);

		$this->assertInstanceOf(RiverObject::class, $object);
		$this->assertStringContainsString(
			"stream/view/{$object->guid}",
			$object->getURL()
		);
	}

	public function testCommentUrlRoutesToStreamCommentsDeepLink() {
		$user = $this->createUser();

		$comment = $this->createObject([
			'subtype' => 'comment',
			'owner_guid' => $user->guid,
			// container is a non-Comment entity, so Router builds a deep link
			'container_guid' => $user->guid,
			'access_id' => ACCESS_PUBLIC,
		]);

		$this->assertInstanceOf(Comment::class, $comment);

		$url = $comment->getURL();
		$this->assertStringContainsString("stream/comments/{$user->guid}/{$comment->guid}", $url);
		$this->assertStringContainsString("#elgg-object-{$comment->guid}", $url);
	}
}
