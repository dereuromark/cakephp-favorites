<?php

use Cake\Http\ServerRequest;

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

		// Session key used to look up the current user id when no auth component
		// supplies it. The user id is read from "<sessionKey>.<userIdField>"
		// (e.g. 'Auth.User.id'). Default is 'Auth.User'; use 'Auth' for
		// CakeDC/Users. Can be overridden per component via its 'sessionKey'.
		'sessionKey' => 'Auth.User',

		// Admin access gate. REQUIRED — the host app MUST set this to a Closure
		// that receives the current request and returns literal true to grant
		// access to /admin/favorites/...; anything else (unset, non-Closure,
		// returns false, returns a truthy non-bool, or throws) yields a 403.
		// The default policy is deny.
		// Example — admin role check on the cakephp/authentication identity:
		'adminAccess' => function (ServerRequest $request): bool {
			$identity = $request->getAttribute('identity');

			return $identity !== null && in_array('admin', (array)$identity->roles, true);
		},
	],
];
