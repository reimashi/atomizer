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

            if ($userModel->exists(array("username" => $body["username"]))) {
                throw new ConflictException("Username already exists");
            }
            else {
                $userInstance = $userModel->newEntity(array(
                    "username" => $body["username"],
                    "password" => $body["password"]//Security::encrypt($body["password"], Configure::read('Encryption.db'))
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

    // gettoken and login functions
    public function token()
    {
        $user = $this->Auth->identify();

        // If token authenticate the user
        if ($user) {
            $this->set([
                'token' => JWT::encode([
                    'sub' => $user['id'],
                    'exp' =>  time() + 604800
                ],
                    Security::salt())
            ]);
        }
        else {
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

                if ($userModel->exists(array("username" => $body["username"], "password" => $body["password"]))) {
                    $users = $userModel
                        ->find()
                        ->select(['id', 'password'])
                        ->where(['username =' => $body["username"]])
                        ->limit(1);

                    if ($users->first()->password == $body["password"]) {
                        $this->set(array(
                            "id" => $users->first()->id,
                            "token" => JWT::encode(
                                [
                                    'sub' => $users->first()->id,
                                    'exp' =>  time() + 604800
                                ],
                                Security::salt())
                        ));
                    }
                    else {
                        throw new UnauthorizedException("Password incorrect");
                    }
                }
                else {
                    throw new UnauthorizedException("User or password incorrect");
                }
            }
        }
    }
}