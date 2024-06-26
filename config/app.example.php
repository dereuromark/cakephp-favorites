<?php

return [
	'Favorites' => [
		'model' => null, // Auto-detect
		'modelClass' => null, // Auto-detect
		'favoriteClass' => 'Favorites.Favorites',
		'userModel' => 'Users',
		'userModelClass' => 'Users',
		'userModelConfig' => null,
		'counterCache' => false, // For Starable behavior only
		'fieldCounter' => 'starred_count', // For Starable behavior only //TODO
		// The following are allowed to use the separate controller, necessary when e.g. PRG component is in place
		'models' => [
			'Alias' => 'MyPlugin.MyModel',
		],
		'userIdField' => 'id',
	],
];
