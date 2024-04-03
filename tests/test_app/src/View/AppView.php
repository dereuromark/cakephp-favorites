<?php
declare(strict_types=1);

namespace TestApp\View;

use Cake\View\View;

/**
 * Fake AppView for IDE autocomplete it templates
 *
 * @property \Favorites\View\Helper\StarsHelper $Stars
 * @property \TinyAuth\View\Helper\AuthUserHelper $AuthUser
 */
class AppView extends View {

	public function initialize(): void {
		$this->loadHelper('Favorites.Stars');
		$this->loadHelper('Favorites.Likes');
		$this->loadHelper('Favorites.Favorites');
	}
}
