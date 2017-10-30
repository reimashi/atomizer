<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity
 *
 * @property int $id
 * @property string $remote_id
 * @property string $url
 * @property string $title
 * @property string $summary
 * @property string $content
 * @property string $content_type
 * @property string $author
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Remote $remote
 * @property \App\Model\Entity\User[] $users
 */
class Item extends Entity
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
        'remote_id' => true,
        'url' => true,
        'title' => true,
        'summary' => true,
        'content' => true,
        'content_type' => true,
        'author' => true,
        'created' => true,
        'updated' => true,
        'modified' => true,
        'remote' => true,
        'users' => true
    ];
}
