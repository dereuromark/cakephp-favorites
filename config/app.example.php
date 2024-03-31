<?php

return [
	'Favorites' => [
		'modelClass' => null, // Auto-detect
		'favoriteClass' => 'Favorites.Favorites',
		'userModelAlias' => 'Users',
		'userModelClass' => 'Users',
		'userModel' => null,
		'countFavorites' => false,
		'fieldCounter' => 'favorites_count', //TODO
		// The following are allowed to use the separate controller, necessary when e.g. PRG component is in place
		'controllerModels' => [
			'Alias' => 'MyPlugin.MyModel',
		],
	],
];
