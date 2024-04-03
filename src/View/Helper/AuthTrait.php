<?php

namespace Favorites\View\Helper;

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

		$uid = Configure::read('Auth.User.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		/** @var \App\View\AppView $view */
		$view = $this->_View;
		if ($view->helpers()->has('AuthUser')) {
			return $view->AuthUser->user($userIdField);
		}

		return $view->getRequest()->getSession()->read('Auth.User.' . $userIdField);
	}

}
