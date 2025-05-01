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
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Utility\Inflector;
use RuntimeException;

/**
 * @property \Cake\Controller\Component\FlashComponent $Flash
 *
 * @method \App\Controller\AppController getController()
 */
class FavoriteableComponent extends Component {

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'on' => 'startup',
 		'userIdField' => 'id',
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
	 * Name of actions this component should use
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var array<string>
	 */
	protected $actionNames = [
		'view', 'favorites',
	];

	/**
	 * Actions used for deleting of some model record, which doesn't use SoftDelete
	 * (so we want favorites delete directly)
	 *
	 * Causes than Favorite association will NOT be automatically unbind()ed,
	 * independently on $this->unbindAssoc
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var array<string>
	 */
	protected $deleteActions = [];

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
	 * Name of user model associated to favorite
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var string Name of the user model
	 */
	protected $userModel = 'Users';

	/**
	 * Class Name for user model in ClassRegistry format.
	 * Ex: For User model stored in User plugin need to use Users.User
	 *
	 * Customizable in beforeFilter()
	 *
	 * @var string user model class name
	 */
	protected $userModelClass = 'Users';

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
	 * Named params used internally by the component
	 *
	 * @var array
	 */
	protected array $_supportNamedParams = [
		'favorite',
		'favorite_action',
		'favorite_view_type',
		'quote',
	];

	/**
	 * Initialize Callback
	 *
	 * @param array $config
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		$this->Controller = $this->getController();

		$config += (array)Configure::read('Favorites');
		$this->setConfig($config);
	}

	/**
	 * Callback
	 *
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function startup(EventInterface $event) {
		$actions = $this->getConfig('actions');
		if ($actions) {
			$action = $this->Controller->getRequest()->getParam('action') ?: '';
			if (!in_array($action, $actions, true)) {
				return null;
			}
		}

		$model = $this->Controller->fetchTable();
		$this->modelAlias = $model->getAlias();

		$parts = explode('\\', $model->getEntityClass());
		$entityName = Inflector::classify(Inflector::underscore(array_pop($parts)));
		$this->viewVariable = Inflector::variable($entityName);
		if (!$this->Controller->{$this->modelAlias}->behaviors()->has('Favoriteable')) {
			$config = [
				'userModelClass' => $this->getConfig('userModelClass'),
				'userId' => $this->userId(),
			];
			$this->Controller->{$this->modelAlias}->behaviors()->load('Favorites.Favoriteable', $config);
		}

		if (!$this->Controller->getRequest()->is(['post', 'put', 'patch'])) {
			return null;
		}

		if ($this->getConfig('on') !== 'startup') {
			return null;
		}

		return $this->process();
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

		//TODO
		/*
		$type = $this->_call('initType');
		$this->favoriteParams = array_merge($this->favoriteParams, ['displayType' => $type]);
		$this->_call('view', [$type]);
		$this->_call('prepareParams');
		$this->Controller->set('favoriteParams', $this->favoriteParams);
		*/
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
			$modelId = $data['id'] ?? null;
		}

		$options = [
			'userId' => $this->userId(),
			'modelId' => $modelId,
			'model' => $data['alias'],
			'value' => $data['value'] !== null ? (int)$data['value'] : null,
		];
		$action = $data['action'] === 'remove' ? 'removeFavorite' : 'addFavorite';

		$result = $this->Controller->{$this->modelAlias}->$action($options);
		if ($result === null) {
			$this->Flash->error(__d('favorites', 'An error occurred.'));
		}

		return $this->prgRedirect();
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
	 * Handles controllers actions like list/add related favorites
	 *
	 * @param string $displayType
	 * @param bool $processActions
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	public function callbackView(string $displayType, bool $processActions = true) {
		/** @var \Cake\ORM\Table $table */
		$table = $this->Controller->{$this->modelAlias};
		if (
			!$table->hasAssociation($this->assocName)
		) {
			throw new RuntimeException('FavoritesComponent: model ' . $this->modelAlias . ' or association ' . $this->assocName . ' doesn\'t exist');
		}

		assert($this->viewVariable !== null);
		/** @var \Cake\Datasource\EntityInterface|null $entity */
		$entity = $this->Controller->viewBuilder()->getVar($this->viewVariable);

		if (!$entity || !$entity->get('id')) {
			/** @var string $key */
			$key = $table->getPrimaryKey();

			throw new RuntimeException('FavoritesComponent: missing view variable ' . $this->viewVariable . ' or value for primary key ' . $key . ' of model ' . $this->modelAlias);
		}

		$id = $entity->get('id');
		$options = compact('displayType', 'id');
		if ($processActions) {
			//TODO
			//$this->_processActions($options);
		}

		try {
			$data = $this->_call('fetchData' . Inflector::camelize($displayType), [$options]);
		} catch (BadMethodCallException $exception) {
			$data = $this->_call('fetchData', [$options]);
		}

		$this->Controller->set($this->viewFavorites, $data);
	}

	/**
	 * Prepare model association to fetch data
	 *
	 * @param array<string, mixed> $options
	 *
	 * @return bool
	 */
	protected function _prepareModel($options) {
		$params = [
			'userModel' => $this->userModel,
			'userId' => $this->userId(),
		];

		return $this->Controller->{$this->modelAlias}->favoriteBeforeFind(array_merge($params, $options));
	}

	/**
	 * Handle adding favorites
	 *
	 * @param int $modelId
	 * @param string $displayType
	 *
	 * @return void
	 */
	public function callbackAdd($modelId, $displayType) {
		if (!empty($this->Controller->data)) {
			$modelName = $this->Controller->{$this->modelAlias}->alias;
			if (!empty($this->Controller->{$this->modelAlias}->fullName)) {
				$modelName = $this->Controller->{$this->modelAlias}->fullName;
			}
			$options = [
				'userId' => $this->userId(),
				'modelId' => $modelId,
				'modelName' => $modelName,
			];
			$result = $this->Controller->{$this->modelAlias}->addFavorite($options);

			if ($result !== null) {
				if ($result) {
					try {
						$options['favoriteId'] = $result;
						$this->_call('afterAdd', [$options]);
					} catch (BadMethodCallException $exception) {
					}
					$this->flash(__d('favorites', 'The Favorite has been saved.'));
					$this->prgRedirect(['#' => 'favorite' . $result]);
					if (!empty($this->ajaxMode)) {
						$this->ajaxMode = null;
						$this->Controller->set('redirect', null);
						if (isset($this->Controller->passedArgs['favorite'])) {
							unset($this->Controller->passedArgs['favorite']);
						}
						$this->_call('view', [$this->favoriteParams['displayType'], false]);
					}
				} else {
					$this->flash(__d('favorites', 'The Favorite could not be saved. Please, try again.'));
				}
			}
		} else {
			if (!empty($this->Controller->passedArgs['quote'])) {
				if (!empty($this->Controller->passedArgs['favorite'])) {
					$message = $this->_call('getFormatedFavorite', [$this->Controller->passedArgs['favorite']]);
					if ($message) {
						//$this->Controller->request->data['Favorite']['body'] = $message;
					}
				}
			}
		}
	}

	/**
	 * Handles approval of favorites.
	 *
	 * @param string $modelId
	 * @param string $favoriteId
	 *
	 * @throws \Cake\Http\Exception\MethodNotAllowedException
	 *
	 * @return void
	 */
	public function callbackToggle($modelId, $favoriteId) {
		if (
			!isset($this->Controller->passedArgs['favorite_action'])
			|| !($this->Controller->passedArgs['favorite_action'] == 'toggle_approve' && $this->Controller->Auth->user('is_admin') == true)
		) {
			throw new MethodNotAllowedException(__d('favorites', 'Nonrestricted operation'));
		}
		if ($this->Controller->{$this->modelAlias}->favoriteToggle($favoriteId)) {
			$this->flash(__d('favorites', 'The Favorite status has been updated.'));
		} else {
			$this->flash(__d('favorites', 'Error appear during favorite status update. Try later.'));
		}
	}

	/**
	 * Deletes favorites
	 *
	 * @param string $modelId
	 * @param string $favoriteId
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function callbackDelete($modelId, $favoriteId) {
		if ($this->Controller->{$this->modelAlias}->deleteFavorite($favoriteId)) {
			$this->flash(__d('favorites', 'The Favorite has been deleted.'));
		} else {
			$this->flash(__d('favorites', 'Error appear during favorite deleting. Try later.'));
		}

		return $this->prgRedirect();
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

		$url = array_merge(
			array_diff_key($this->Controller->getRequest()->getParam('pass'), array_flip($this->_supportNamedParams)),
			$urlBase,
		);
		if (!$isAjax) {
			return $this->Controller->redirect($url);
		}

		$this->Controller->set('redirect', $url);
		//$this->ajaxMode = true;
		$this->Controller->set('ajaxMode', true);
	}

	/**
	 * Call action from component or overridden action from controller.
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @throws \BadMethodCallException
	 *
	 * @return mixed
	 */
	protected function _call($method, $args = []) {
		$methodName = 'callbackFavorites' . Inflector::camelize(Inflector::underscore($method));
		$localMethodName = 'callback' . $method;
		if (method_exists($this->Controller, $methodName)) {
			/** @var callable $callable */
			$callable = [$this->Controller, $methodName];

			return call_user_func_array($callable, $args);
		}
		if (method_exists($this, $localMethodName)) {
			/** @var callable $callable */
			$callable = [$this, $localMethodName];

			return call_user_func_array($callable, $args);
		}

			throw new BadMethodCallException();
	}

	/**
	 * Non view action process method
	 *
	 * @param array<string, mixed> $options
	 *
	 * @return void
	 */
	protected function _processActions(array $options) {
		//extract($options);
		$id = $options['id'];
		$displayType = $options['displayType'];

		if (isset($this->Controller->passedArgs['favorite'])) {
			if ($this->userId()) {
				if (isset($this->Controller->passedArgs['favorite_action'])) {
					$favoriteAction = $this->Controller->passedArgs['favorite_action'];
					if (!in_array($favoriteAction, ['toggle_approve', 'delete'])) {
						//return $this->Controller->blackHole("FavoritesComponent: unsupported favorite_Action '$favoriteAction'");
					}
					$this->_call(Inflector::variable($favoriteAction), [$id, $this->Controller->passedArgs['favorite']]);
				} else {
					//Configure::write('Favorite.action', 'add');
					$parent = empty($this->Controller->passedArgs['favorite']) ? null : $this->Controller->passedArgs['favorite'];
					$this->_call('add', [$id, $parent, $displayType]);
				}
			} else {
				//$this->Controller->Session->write('Auth.redirect', $this->Controller->request['url']);
				$this->Controller->redirect($this->Controller->Auth->getConfig('loginAction'));
			}
		}
	}

}
