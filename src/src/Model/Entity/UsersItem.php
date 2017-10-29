<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsersItem Entity
 *
 * @property int $user_id
 * @property int $item_id
 * @property bool $read_later
 * @property bool $readed
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Item $item
 */
class UsersItem extends Entity
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
        'read_later' => true,
        'readed' => true,
        'user' => true,
        'item' => true
    ];
}
