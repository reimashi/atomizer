<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Feed Entity
 *
 * @property int $id
 * @property string $url
 * @property string $web_url
 * @property string $title
 * @property string $description
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User[] $users
 */
class Feed extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'url' => true,
        'web_url' => true,
        'title' => true,
        'description' => true,
        'created' => true,
        'updated' => true,
        'modified' => true,
        'users' => true
    ];
}
