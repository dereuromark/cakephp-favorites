<?php

declare(strict_types=1);

namespace Favorites\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PostsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
    // phpcs:disable
    public array $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true],
        'title' => ['type' => 'string', 'length' => 190, 'null' => false, 'default' => null, 'comment' => ''],
        'content' => ['type' => 'string', 'length' => 190, 'null' => true, 'default' => null, 'comment' => ''],
		'count' => ['type' => 'integer', 'length' => null, 'unsigned' => true, 'null' => false, 'default' => 0, 'comment' => ''],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'title' => 'Lorem ipsum dolor sit amet',
                'content' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
