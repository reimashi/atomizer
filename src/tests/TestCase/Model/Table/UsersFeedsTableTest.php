<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersFeedsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersFeedsTable Test Case
 */
class UsersFeedsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersFeedsTable
     */
    public $UsersFeeds;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.users_feeds',
        'app.users',
        'app.feeds',
        'app.items',
        'app.remotes',
        'app.users_items'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('UsersFeeds') ? [] : ['className' => UsersFeedsTable::class];
        $this->UsersFeeds = TableRegistry::get('UsersFeeds', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersFeeds);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
