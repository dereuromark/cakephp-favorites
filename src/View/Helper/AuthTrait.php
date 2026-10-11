<?php

namespace Favorites\View\Helper;

use ArrayAccess;
use Cake\Core\Configure;

/**
 * @mixin \Cake\View\Helper
 */
trait AuthTrait {

	/**
	 * @return int|null
	 */
	protected function userId(): ?int {
		$userIdField = Configure::read('Favorites.userIdField') ?: 'id';
		$sessionKey = Configure::read('Favorites.sessionKey') ?? 'Auth.User';

		$identity = $this->_View->getRequest()->getAttribute('identity');
		if ($identity instanceof ArrayAccess && isset($identity[$userIdField])) {
			return (int)$identity[$userIdField];
		}

		$uid = Configure::read($sessionKey . '.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		/** @var \App\View\AppView $view */
		$view = $this->_View;
		if ($view->helpers()->has('AuthUser')) {
			$authUser = $view->helpers()->get('AuthUser');
			if (method_exists($authUser, 'user')) {
				return $authUser->user($userIdField);
			}
		}

		return $view->getRequest()->getSession()->read($sessionKey . '.' . $userIdField);
	}

}
