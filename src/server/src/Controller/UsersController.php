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

use Cake\Network\Exception\UnauthorizedException;
use \Firebase\JWT\JWT;
use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ConflictException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\Utility\Security;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class UsersController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->RequestHandler->renderAs($this, 'json');
        $this->Auth->allow(['add', 'token']);
    }

    public function add()
    {
        $validator = new Validator();

        $validator->requirePresence('username')
            ->ascii('username')
            ->lengthBetween('username', [4, 24]);

        $validator->requirePresence("password")
            ->regex("password", '/^[0-9a-f]{40}$/i', "The password must be a sha1 hash");

        $validationErrors = $validator->errors($this->request->getData());

        if(empty($validationErrors)) {
            $body = $this->request->getParsedBody();

            $userModel = TableRegistry::get('users');

            if ($userModel->exists(array("nick" => $body["username"]))) {
                throw new ConflictException("Username already exists");
            }
            else {
                $userInstance = $userModel->newEntity(array(
                    "nick" => $body["username"],
                    "password" => Security::encrypt($body["password"], Configure::read('Encryption.db'))
                ));

                $userModel->save($userInstance);

                $this->response->statusCode(201);
                $this->set(array(
                    "id" => $userInstance->id,
                    "token" => JWT::encode(
                        [
                            'sub' => $userInstance->id,
                            'exp' =>  time() + 604800
                        ],
                        Security::salt())
                ));
            }
        }
        else {
            throw new BadRequestException(json_encode($validationErrors));
        }
    }

    public function token($id)
    {
        $user = $this->Auth->identify();

        if (!$user) {
            throw new UnauthorizedException('Invalid username or password');
        }

        $this->set([
            'token' => JWT::encode([
                'sub' => $user['id'],
                'exp' =>  time() + 604800
            ],
                Security::salt())
        ]);
        /*$validator = new Validator();

        $validator->requirePresence('username')
            ->ascii('username')
            ->lengthBetween('username', [4, 24]);

        $validator->requirePresence("password")
            ->regex("password", '/^[0-9a-f]{40}$/i', "The password must be a sha1 hash");

        $validationErrors = $validator->errors($this->request->getData());

        if(empty($validationErrors)) {
            $body = $this->request->getParsedBody();

            $userModel = TableRegistry::get('users');

            if ($userModel->exists(array("nick" => $body["username"]))) {
                throw new ConflictException("Username already exists");
            }
            else {
                throw new UnauthorizedException("User or password incorrect");
            }
        }*/
    }
}