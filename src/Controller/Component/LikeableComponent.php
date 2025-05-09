<?php

/**
 * FavoritesComponent
 *
 * Helps handle 'view' action of controller so it can list/add related favorites.
 * In related controller action there is no need to fetch associated data for favorites - this
 * component is fetching them separately (needed different result from model in dependency of
 * used displayType).
 *
 * Needs Router::connectNamed(array('favorite', 'favorite_view', 'favorite_action)) in config/routes.php.
 *
 * It is also usable to define (in controller, to not fetch unnecessary data
 * in used Controller::paginate() method):
 * var $paginate = array('Favorite' => array(
 *  'order' => array('Favorite.created' => 'desc'),
 *  'recursive' => 0,
 *  'limit' => 10
 * ));
 *
 * Includes helpers TextWidget and FavoriteWidget for controller, uses method
 * AppController::blackHole().
 *
 * Most of component methods possible to override in controller
 * for it need to create method with prefix _favorites
 * Ex. : _add -> _favoritesAdd, _fetchData -> _favoritesFetchData
 * Callbacks also need to prefix with '_favorites' in controller.
 *
 * callbacks
 * afterAdd
 *
 * params
 *  favorite
 *  favorite_view_type
 *  favorite_action
 */

namespace Favorites\Controller\Component;

use BadMethodCallException;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Inflector;

/**
 * @property \Cake\Controller\Component\FlashComponent $Flash
 *
 * @method \App\Controller\AppController getController()
 */
class LikeableComponent extends Component {

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'on' => 'startup',
		'userModelClass' => 'Users',
		'userIdField' => 'id',
		'useEntity' => false,
		'viewVariable' => null,
	];

	/**
	 * Components
	 *
	 * @var array
	 */
	protected array $components = [
		'Flash',
	];

	/**
	 * Controller
	 *
	 * @var \App\Controller\AppController
	 */
	protected $Controller;

	/**
	 * Name of 'favoriteable' model
	 *
	 * Customizable in beforeFilter(), or default controller's model name is used
	 *
	 * @var string|null Model name
	 */
	protected $modelName;

	/**
	 * Name of 'favoriteable' model
	 *
	 * Customizable in beforeFilter(), or default controller's model name is used
	 *
	 * @var string|null Model name
	 */
	protected $modelAlias;

	/**
	 * Name of association for favorites
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var string Association name
	 */
	protected $assocName = 'Favorites';

	/**
	 * Flag if this component should permanently unbind association to Favorite model in order to not
	 * query model for not necessary data in Controller::view() action
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var bool
	 */
	protected $unbindAssoc = false;

	/**
	 * Parameters passed to view
	 *
	 * @var array
	 */
	protected array $favoriteParams = [];

	/**
	 * Name of view variable which contains model data for view() action
	 *
	 * Needed just for PK value available in it
	 *
	 * Customizable in beforeFilter(), or default Inflector::variable($this->modelAlias)
	 *
	 * @var string|null
	 */
	protected $viewVariable;

	/**
	 * Name of view variable for favorites data
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var string
	 */
	protected $viewFavorites = 'favoritesData';

	/**
	 * Settings to use when FavoritesComponent needs to do a flash message with SessionComponent::setFlash().
	 * Available keys are:
	 *
	 * - `element` - The element to use, defaults to 'default'.
	 * - `key` - The key to use, defaults to 'flash'
	 * - `params` - The array of additional params to use, defaults to array()
	 *
	 * @var array
	 */
	protected array $flash = [
		'element' => 'default',
		'key' => 'flash',
		'params' => [],
	];

	/**
	 * @param array $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		$this->Controller = $this->getController();

		$config += (array)Configure::read('Favorites');
		$this->setConfig($config);

		if (!$this->getConfig('userModel')) {
			[, $alias] = pluginSplit($this->getConfig('userModelClass'));
			$this->setConfig('userModel', $alias);
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return;
			}
		}

		$model = $this->Controller->fetchTable();
		$this->modelName = $model->getRegistryAlias();
		$this->modelAlias = $model->getAlias();

		$parts = explode('\\', $model->getEntityClass());
		$entityName = Inflector::classify(Inflector::underscore(array_pop($parts)));
		$this->viewVariable = $this->getConfig('viewVariable') ?? Inflector::variable($entityName);
		if (!$this->Controller->{$this->modelAlias}->behaviors()->has('Likeable')) {
			$config = [
				'userModelClass' => $this->getConfig('userModelClass'),
				'userId' => $this->userId(),
			];
			$this->Controller->{$this->modelAlias}->behaviors()->load('Favorites.Likeable', $config);
		}
	}

	/**
	 * Callback
	 *
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return void
	 */
	public function startup(EventInterface $event): void {
		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return;
			}
		}

		if (!$this->Controller->getRequest()->is(['post', 'put', 'patch'])) {
			return;
		}

		if ($this->getConfig('on') !== 'startup') {
			return;
		}

		$event->setResult($this->process());
	}

	/**
	 * Callback
	 *
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return void
	 */
	public function beforeRender(EventInterface $event): void {
		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return;
			}
		}

		if ($this->getConfig('on') !== 'beforeRender') {
			return;
		}

		$event->setResult($this->process());
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	protected function process() {
		$data = $this->Controller->getRequest()->getData();
		if (empty($data['favorite'])) {
			return null;
		}

		assert($this->viewVariable !== null);

		/** @var \Cake\Datasource\EntityInterface $entity */
		$entity = $this->Controller->viewBuilder()->getVar($this->viewVariable);

		if ($this->getConfig('useEntity')) {
			$modelId = $entity->get('id');
		} else {
			$modelId = $data['id'];
		}

		$options = [
			'userId' => $this->userId(),
			'modelId' => $modelId,
			'model' => $data['alias'],
		];
		$action = $this->action($data['action']);

		/** @var \Favorites\Model\Behavior\LikeableBehavior $table */
		$table = $this->Controller->{$this->modelAlias};
		/**
		 * @uses \Favorites\Model\Behavior\LikeableBehavior::addLike()
		 * @uses \Favorites\Model\Behavior\LikeableBehavior::addDislike()
		 * @uses \Favorites\Model\Behavior\LikeableBehavior::remove()
		 */
		$result = $table->$action($options);
		if ($result === null) {
			$this->Flash->error(__d('favorites', 'An error occurred.'));
		}

		return $this->prgRedirect();
	}

	/**
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function model(string $alias): string {
		$model = Configure::read('Favorites.models.' . $alias);
		if (!$model) {
			throw new NotFoundException('Invalid alias');
		}

		return $model;
	}

	/**
	 * @return int|null
	 */
	protected function userId() {
		$userIdField = Configure::read('Favorites.userIdField') ?: 'id';

		$uid = Configure::read('Auth.User.' . $userIdField);
		if ($uid) {
			return $uid;
		}

		$userId = $this->getConfig('userId') ?: null;
		if (!$userId && $this->Controller->components()->has('AuthUser')) {
			$userId = $this->Controller->AuthUser->user($userIdField);
		} elseif (!$userId && $this->Controller->components()->has('Auth')) {
			$userId = $this->Controller->Auth->user($userIdField);
		} elseif (!$userId) {
			$userId = $this->Controller->getRequest()->getSession()->read('Auth.User.' . $userIdField);
		}

		return $userId;
	}

	/**
	 * Flash message - for ajax queries, sets 'messageTxt' view variable,
	 * otherwise uses the Session component and values from FavoritesComponent::$flash.
	 *
	 * @param string $message The message to set.
	 *
	 * @return void
	 */
	public function flash($message) {
		$isAjax = $this->Controller->params['isAjax'] ?? false;
		if ($isAjax) {
			$this->Controller->set('messageTxt', $message);
		} else {
			$options = [];
			// $this->flash['element'], $this->flash['params'], $this->flash['key']
			$this->Controller->Flash->set($message, $options);
		}
	}

	/**
	 * Redirect
	 * Redirects the user to the wanted action by persisting passed args excepted
	 * the ones used internally by the component
	 *
	 * @param array $urlBase
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function prgRedirect($urlBase = []) {
		$isAjax = $this->Controller->getRequest()->getParam('isAjax') ?? false;

		$url = $this->Controller->getRequest()->getUri()->getPath();
		if (!$isAjax) {
			return $this->Controller->redirect($url);
		}

		$this->Controller->set('redirect', $url);
		//$this->ajaxMode = true;
		$this->Controller->set('ajaxMode', true);
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	protected function action(string $action): string {
		return match ($action) {
			'like' => 'addLike',
			'dislike' => 'addDislike',
			'remove' => 'remove',
			default => throw new BadMethodCallException('Not implemented: ' . $action)
		};
	}

}
