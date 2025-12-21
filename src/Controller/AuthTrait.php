<?php

namespace Favorites\Controller;

use Cake\Core\Configure;

/**
 * @mixin \App\Controller\AppController
 */
trait AuthTrait {

	/**
	 * @return int|null
	 */
	protected function userId() {
		$userIdField = Configure::read('Favorites.userIdField') ?: 'id';
		$sessionKey = Configure::read('Favorites.sessionKey') ?? 'Auth.User';

		$uid = Configure::read($sessionKey . '.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		if ($this->components()->has('AuthUser')) {
			return $this->AuthUser->user($userIdField);
		}
		if ($this->components()->has('Auth')) {
			return $this->Auth->user($userIdField);
		}

		return $this->getRequest()->getSession()->read($sessionKey . '.' . $userIdField);
	}

}
