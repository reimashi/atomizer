<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UsersFeeds Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\FeedsTable|\Cake\ORM\Association\BelongsTo $Feeds
 *
 * @method \App\Model\Entity\UsersFeed get($primaryKey, $options = [])
 * @method \App\Model\Entity\UsersFeed newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\UsersFeed[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UsersFeed|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UsersFeed patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UsersFeed[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\UsersFeed findOrCreate($search, callable $callback = null, $options = [])
 */
class UsersFeedsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users_feeds');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey(['user_id', 'feed_id']);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Feeds', [
            'foreignKey' => 'feed_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['feed_id'], 'Feeds'));

        return $rules;
    }
}
