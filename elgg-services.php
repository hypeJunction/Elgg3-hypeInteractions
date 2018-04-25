<?php

return [
	'interactions' => \DI\object(\hypeJunction\Interactions\InteractionsService::class)
		->constructor(\DI\get('hooks')),
];