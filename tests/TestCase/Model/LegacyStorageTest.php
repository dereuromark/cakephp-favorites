<?php

namespace Favorites\Test\TestCase\Model;

use Cake\Core\Configure;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Favorites\Model\Behavior\FavoriteableBehavior;
use Favorites\Model\Behavior\LikeableBehavior;
use Favorites\Model\Behavior\StarableBehavior;
use Favorites\View\Helper\FavoritesHelper;
use Favorites\View\Helper\LikesHelper;
use Favorites\View\Helper\StarsHelper;
use PHPUnit\Framework\Attributes\DataProvider;

class LegacyStorageTest extends TestCase {

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Favorites.Favorites', 'plugin.Favorites.Users', 'plugin.Favorites.Posts'];

	/**
	 * @return array<string, array<string>>
	 */
	public static function behaviors(): array {
		return [
			'favorites' => [FavoriteableBehavior::class, 'Posts'],
			'likes' => [LikeableBehavior::class, 'Posts'],
			'stars' => [StarableBehavior::class, 'wish'],
		];
	}

	/**
	 * @param class-string<\Cake\ORM\Behavior> $behaviorClass
	 * @param string $expectedModel
	 *
	 * @return void
	 */
	#[DataProvider('behaviors')]
	public function testDefaultModelKeepsReleasedStorageKey(string $behaviorClass, string $expectedModel): void {
		Configure::write('Favorites.models', ['wish' => 'Blog.Posts', 'bookmark' => 'Blog.Posts']);
		$table = $this->getTableLocator()->get('Blog.Posts', ['className' => Table::class, 'table' => 'posts']);
		$behavior = new $behaviorClass($table);
		$this->assertSame($expectedModel, $behavior->getConfig('model'));
		$this->assertSame(['Favorites.model' => $expectedModel], $table->getAssociation('Favorites')->getConditions());
		$this->assertCount($expectedModel === 'Posts' ? 1 : 0, $table->find()->contain('Favorites')->firstOrFail()->get('favorites'));
	}

	/**
	 * @param class-string<\Cake\ORM\Behavior> $behaviorClass
	 * @param string $expectedModel
	 *
	 * @return void
	 */
	#[DataProvider('behaviors')]
	public function testExplicitModelKeepsItsStorageKey(string $behaviorClass, string $expectedModel): void {
		Configure::write('Favorites.models', ['wish' => 'Blog.Posts', 'bookmark' => 'Blog.Posts']);
		$table = $this->getTableLocator()->get('Blog.Posts', ['className' => Table::class, 'table' => 'posts']);
		$behavior = new $behaviorClass($table, ['model' => 'WishlistedPosts']);
		$this->assertSame('WishlistedPosts', $behavior->getConfig('model'));
		$this->assertSame(['Favorites.model' => 'WishlistedPosts'], $table->getAssociation('Favorites')->getConditions());
		$this->assertCount(0, $table->find()->contain('Favorites')->firstOrFail()->get('favorites'));
	}

	/**
	 * @return void
	 */
	public function testSeparateRelationsOnSameTableKeepIndependentRows(): void {
		Configure::write('Favorites.models', ['WishlistedPosts' => 'WishlistedPosts', 'BookmarkedPosts' => 'BookmarkedPosts']);
		Configure::write('Auth.User.id', 1);
		$wishlisted = $this->getTableLocator()->get('WishlistedPosts', ['className' => Table::class, 'table' => 'posts']);
		$bookmarked = $this->getTableLocator()->get('BookmarkedPosts', ['className' => Table::class, 'table' => 'posts']);
		$wishlisted->addBehavior('Favorites.Favoriteable');
		$bookmarked->addBehavior('Favorites.Favoriteable');
		$this->assertSame($wishlisted->getTable(), $bookmarked->getTable());
		$this->assertSame(get_class($wishlisted), get_class($bookmarked));
		$wishlisted->getBehavior('Favoriteable')->addFavorite(['modelId' => 1, 'userId' => 1, 'value' => 1]);
		$bookmarked->getBehavior('Favoriteable')->addFavorite(['modelId' => 1, 'userId' => 1, 'value' => 2]);
		$helper = new FavoritesHelper(new View());
		$this->assertSame(1, $helper->value('WishlistedPosts', 1));
		$this->assertSame(2, $helper->value('BookmarkedPosts', 1));
		$this->assertCount(1, $wishlisted->find('favorites')->firstOrFail()->get('favorites'));
		$this->assertCount(1, $bookmarked->find('favorites')->firstOrFail()->get('favorites'));
		$wishlisted->getBehavior('Favoriteable')->removeFavorite(['modelId' => 1, 'userId' => 1]);
		$this->assertNull($helper->value('WishlistedPosts', 1));
		$this->assertSame(2, $helper->value('BookmarkedPosts', 1));
	}

	/**
	 * @return array<string, array<string>>
	 */
	public static function helpers(): array {
		return [
			'favorites' => [FavoritesHelper::class],
			'likes' => [LikesHelper::class],
			'stars' => [StarsHelper::class],
		];
	}

	/**
	 * @param class-string<\Cake\View\Helper> $helperClass
	 *
	 * @return void
	 */
	#[DataProvider('helpers')]
	public function testHelperModelFactoryStillSuppliesReactionRepository(string $helperClass): void {
		Configure::write('Auth.User.id', 1);
		$favorites = $this->fetchTable('Favorites.Favorites');
		$favorites->add('Posts', 1, 1, 1);
		$helper = new $helperClass(new View());
		$calls = [];
		$helper->modelFactory('Table', function (string $alias) use ($favorites, &$calls): Table {
			$calls[] = $alias;

			return $favorites;
		});
		$this->assertEquals(1, $helper->value('Posts', 1));
		$this->assertSame(['Favorites.Favorites'], $calls);
		$this->assertSame($favorites, $helper->fetchModel('Favorites.Favorites'));
	}

	/**
	 * @return void
	 */
	public function testReleasedFinderOverrideSignatureRemainsCompatible(): void {
		$table = $this->fetchTable('Posts');
		$behavior = new class ($table) extends FavoriteableBehavior {

			/**
			 * @param \Cake\ORM\Query\SelectQuery $query
			 * @param array<string, mixed> $options
			 *
			 * @return \Cake\ORM\Query\SelectQuery
			 */
			public function findFavorites(SelectQuery $query, array $options = []): SelectQuery {
				return parent::findFavorites($query, $options);
			}

		};
		$post = $behavior->findFavorites($table->find(), ['id' => 1])->firstOrFail();
		$this->assertCount(1, $post->get('favorites'));
	}

	public function testSharedMappingKeepsReleasedStarAliasAndHelperState(): void {
		Configure::write('Favorites.models', [
			'star' => ['chosen' => 'OtherPosts', 'fallback' => 'Posts'],
			'chosen' => 'Posts',
			'shared' => 'Posts',
		]);
		Configure::write('Auth.User.id', 1);
		$table = $this->fetchTable('Posts');
		$behavior = new StarableBehavior($table);
		$this->assertSame('chosen', $behavior->getConfig('model'));
		$this->assertSame(['Favorites.model' => 'chosen'], $table->getAssociation('Favorites')->getConditions());

		$behavior->addStar(['modelId' => 1, 'userId' => 1]);
		$helper = new StarsHelper(new View());
		$this->assertTrue($helper->value('chosen', 1));
		$this->assertFalse($helper->value('fallback', 1));
		$behavior->removeStar(['modelId' => 1, 'userId' => 1]);
		$this->assertFalse($helper->value('chosen', 1));
	}

}
