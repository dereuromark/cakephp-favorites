<?php

namespace Favorites\Test\TestCase\Model;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\Utility\Config;
use Favorites\View\Helper\FavoritesHelper;
use Favorites\View\Helper\LikesHelper;
use Favorites\View\Helper\StarsHelper;
use InvalidArgumentException;

class LegacyStorageTest extends TestCase {

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Favorites.Favorites', 'plugin.Favorites.Users', 'plugin.Favorites.Posts'];

	/**
	 * @return void
	 */
	public function testLegacyModelKeysRemainVisibleAndUpdatesDoNotDuplicate(): void {
		Configure::write('Favorites.models', ['article' => 'Blog.Posts']);
		Configure::write('Favorites.legacyModels', ['article' => ['Blog.Posts', 'Posts']]);
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->deleteAll([]);
		foreach (['Blog.Posts', 'Posts', 'article'] as $storedModel) {
			$favorite = $favorites->add($storedModel, 1, 1, 1);
			$this->assertSame($favorite->get('id'), $favorites->add('article', 1, 1, 2)->get('id'));
			$this->assertSame(1, $favorites->find()->count());
			$this->assertSame(2, $favorites->get($favorite->get('id'))->get('value'));
			$favorites->remove('article', 1, 1);
			$this->assertSame(0, $favorites->find()->count());
		}
	}

	/**
	 * @return void
	 */
	public function testLegacyKeysWorkInBehaviorFindersAndHelpers(): void {
		Configure::write('Favorites.models', ['article' => 'Blog.Posts']);
		Configure::write('Favorites.legacyModels', ['article' => ['Blog.Posts', 'Posts']]);
		Configure::write('Auth.User.id', 1);
		$posts = $this->getTableLocator()->get('Blog.Posts', ['className' => Table::class, 'table' => 'posts']);
		$posts->addBehavior('Favorites.Favoriteable');
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->deleteAll([]);
		$favorites->add('Blog.Posts', 1, 1, 1);
		$this->assertSame(1, $posts->find('favoritesBy', userId: 1)->count());
		$this->assertCount(1, $posts->find('favorites')->firstOrFail()->get('favorites'));
		$this->assertSame(1, (new FavoritesHelper(new View()))->value('article', 1));
		$this->assertSame(1, (new LikesHelper(new View()))->value('article', 1));
		$this->assertTrue((new StarsHelper(new View()))->value('article', 1));
	}

	/**
	 * @return void
	 */
	public function testAmbiguousLegacyAliasesCannotCrossModelBoundaries(): void {
		Configure::write('Favorites.models', ['article' => 'Blog.Posts', 'product' => 'Shop.Posts']);
		$this->assertSame(['article'], Config::storageModels('article'));
		Configure::write('Favorites.legacyModels', ['article' => ['Blog.Posts']]);
		$this->assertSame(['article', 'Blog.Posts'], Config::storageModels('article'));
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->add('Shop.Posts', 1, 1, 1);
		$this->assertSame(0, $favorites->remove('article', 1, 1));
		$this->assertSame(1, $favorites->find()->where(['model' => 'Shop.Posts'])->count());
		$this->assertSame(1, $favorites->find()->where(['model' => 'Posts'])->count());
		Configure::delete('Favorites.legacyModels');
		Configure::write('Favorites.models', ['like' => ['article' => 'Blog.Posts'], 'star' => ['article' => 'Shop.Posts']]);
		$this->assertSame(['article'], Config::storageModels('article'));
	}

	/**
	 * @return void
	 */
	public function testUpdatingReactionReconcilesCanonicalAndLegacyDuplicates(): void {
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->deleteAll([]);
		$legacy = $favorites->add('Posts', 1, 1, 1);
		$canonical = $favorites->add('article', 1, 1, -1);
		Configure::write('Favorites.legacyModels', ['article' => ['Posts']]);
		$updated = $favorites->add('article', 1, 1, 1);
		$this->assertSame($canonical->get('id'), $updated->get('id'));
		$this->assertSame(1, $favorites->find()->count());
		$this->assertFalse($favorites->exists(['id' => $legacy->get('id')]));
		$this->assertSame(1, $updated->get('value'));
	}

	/**
	 * @return void
	 */
	public function testLegacyNameCannotBelongToSeveralReactionAliases(): void {
		Configure::write('Favorites.legacyModels', ['liked' => ['Posts'], 'starred' => ['Posts']]);
		$this->expectException(InvalidArgumentException::class);
		Config::storageModels('liked');
	}

	/**
	 * @return void
	 */
	public function testLegacyNameCannotBeAnActivePublicAlias(): void {
		Configure::write('Favorites.models', ['article' => 'Blog.Posts', 'Posts' => 'Shop.Posts']);
		Configure::write('Favorites.legacyModels', ['article' => ['Posts']]);
		$this->expectException(InvalidArgumentException::class);
		Config::storageModels('article');
	}

}
