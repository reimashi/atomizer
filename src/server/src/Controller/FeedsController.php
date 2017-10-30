<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

class FeedsController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->RequestHandler->renderAs($this, 'json');
        $this->Auth->allow(['index', 'add']); // Permite sin auth
    }

    public function index()
    {
        $feedOut = array();

        $feedsModel = TableRegistry::get('feeds');
        $feeds = $feedsModel->find();

        foreach ($feeds as $feed) {
            // Each row is now an instance of our Article class.

            array_push($feedOut, $feed);
            $feedOut["hola"] = "adios";
        }

        $this->set($feedOut);
    }

    public function add()
    {
    }

    public function delete($id)
    {
        $this->set([
            'recipes' => "hola"
        ]);
    }
}