<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersItemsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersItemsTable Test Case
 */
class UsersItemsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersItemsTable
     */
    public $UsersItems;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.users_items',
        'app.users',
        'app.feeds',
        'app.users_feeds',
        'app.items',
        'app.remotes'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('UsersItems') ? [] : ['className' => UsersItemsTable::class];
        $this->UsersItems = TableRegistry::get('UsersItems', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersItems);

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
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
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
