<?php

return [
	'interactions' => \DI\create(\hypeJunction\Interactions\InteractionsService::class)
		->constructor(\DI\get('hooks')),
];