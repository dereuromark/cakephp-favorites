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

		$uid = Configure::read('Auth.User.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		if ($this->components()->has('AuthUser')) {
			return $this->AuthUser->user($userIdField);
		}
		if ($this->components()->has('Auth')) {
			return $this->Auth->user($userIdField);
		}

		return $this->getRequest()->getSession()->read('Auth.User.' . $userIdField);
	}

}
