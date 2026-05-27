<?php

namespace Favorites\Controller;

use Cake\Core\Configure;

/**
 * @mixin \App\Controller\AppController
 */
trait AuthTrait {

	/**
	 * @return int|string|null
	 */
	protected function userId() {
		$userIdField = Configure::read('Favorites.userIdField') ?: 'id';
		$sessionKey = Configure::read('Favorites.sessionKey') ?? 'Auth.User';

		$uid = Configure::read($sessionKey . '.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		if ($this->components()->has('AuthUser')) {
			$authUser = $this->components()->get('AuthUser');
			if (method_exists($authUser, 'user')) {
				return $authUser->user($userIdField);
			}
		}
		if ($this->components()->has('Auth')) {
			$auth = $this->components()->get('Auth');
			if (method_exists($auth, 'user')) {
				return $auth->user($userIdField);
			}
		}

		return $this->getRequest()->getSession()->read($sessionKey . '.' . $userIdField);
	}

}
