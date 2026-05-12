<?php

namespace hypeJunction\Interactions\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Interactions\Comment;

class CommentTest extends IntegrationTestCase {

    public function testCommentSubtype() {
        $comment = new Comment();
        $this->assertEquals('comment', $comment->getSubtype());
    }

    public function testCommentSubtypeConstant() {
        $this->assertEquals('comment', Comment::SUBTYPE);
    }

    public function testGetAttachmentsFilterOptionsDefaults() {
        $comment = new Comment();
        $options = $comment->getAttachmentsFilterOptions();

        $this->assertEquals('attached', $options['relationship']);
        $this->assertFalse($options['inverse_relationship']);
    }

    public function testGetAttachmentsFilterOptionsMerge() {
        $comment = new Comment();
        $options = $comment->getAttachmentsFilterOptions(['limit' => 5]);

        $this->assertEquals('attached', $options['relationship']);
        $this->assertEquals(5, $options['limit']);
    }
}
