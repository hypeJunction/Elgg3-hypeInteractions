<?php

namespace hypeJunction\Interactions\Tests\Unit;

use hypeJunction\Interactions\RiverObject;
use PHPUnit\Framework\TestCase;

class RiverObjectTest extends TestCase {

    public function testSubtypeConstant() {
        $this->assertEquals('river_object', RiverObject::SUBTYPE);
    }
}
