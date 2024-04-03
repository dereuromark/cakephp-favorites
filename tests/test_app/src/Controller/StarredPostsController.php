<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * @property \TinyAuth\Controller\Component\AuthUserComponent $AuthUser
 * @property \TinyAuth\Controller\Component\AuthComponent $Auth
 */
class StarredPostsController extends Controller {

	protected ?string $defaultTable = 'Posts';

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Flash');
		$this->loadComponent('Favorites.Starable');
	}

	public function view($id = null) {
		$post = $this->fetchTable('Posts')->get($id, contain: ['Starred']);

        $this->set('post', $post);
	}

}
