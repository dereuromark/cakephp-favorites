<?php

namespace Favorites\View\Helper;

use ArrayAccess;
use Cake\Core\Configure;
use Favorites\Utility\Config;

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

		$identity = $this->View->getRequest()->getAttribute('identity');
		if ($identity instanceof ArrayAccess && isset($identity[$userIdField])) {
			return Config::userId($identity[$userIdField]);
		}

		$uid = Configure::read($sessionKey . '.' . $userIdField);
		if ($uid) {
			return Config::userId($uid);
		}

		/** @var \App\View\AppView $view */
		$view = $this->View;
		if ($view->helpers()->has('AuthUser')) {
			$authUser = $view->helpers()->get('AuthUser');
			if (method_exists($authUser, 'user')) {
				return Config::userId($authUser->user($userIdField));
			}
		}

		return Config::userId($view->getRequest()->getSession()->read($sessionKey . '.' . $userIdField));
	}

}
