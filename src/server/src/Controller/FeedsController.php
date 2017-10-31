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
use Cake\Core\Exception\Exception;
use Cake\Http\Client;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\Utility\Xml;
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

    public function indedx()
    {
        $feedsModel = TableRegistry::get('feeds');
        $feeds = $feedsModel->find();
        $usersFeedsModel = TableRegistry::get('users_feeds');
        $rels = $usersFeedsModel->find();
        $this->set(array(
            "feeds" => $feeds,
            "rels" => $rels
        ));
    }

    public function index() {
        //$user = $this->Auth->identify();
        $userId = 1;

        $feedsRelModel = TableRegistry::get('UsersFeed');
        $feedsModel = TableRegistry::get('feeds');

        //Miramos en la relacion
        $feedsRel = $feedsRelModel->find()
            ->select([])
            ->where(['user_id=' => $user["id"]])
            ->limit(50);


        foreach ($feedsRel as $feedRel) {
            //Y miramos de los que tenemos en la relacion uno a uno
            $oldFeeds = $feedsModel->find()
                ->select([])
                ->where(['id=' => $feedRel["id"]])
                ->limit(50);
            foreach ($oldFeeds as $oldFeed) {
                //Lo obtenemos de nuevo
                $newFeed = $this->getFeedData($oldFeed->url);
                //Lo comparamos
                if($oldFeed  = $newFeed) {
                    //Eliminamos el repetido
                    $feedsRel.removeFromArray($oldFeed);
                }
            }
        }

        //devolver al usuario una lista de feeds con los ultimos... yo que se, 50 articulos de cada uno
        $this->set($feedOut);
    }

    public function add()
    {
        $user = $this->Auth->identify();
        var_dump($_SERVER['HTTP_AUTHORIZATION']);
        var_dump($_ENV['HTTP_AUTHORIZATION']);
        var_dump($user); die();

        $userId = 1;

        $validator = new Validator();

        $validator->requirePresence('url')
            ->url('url', 'The feed url is required')
            ->lengthBetween('url', [1, 255], 'Url size error');

        $validationErrors = $validator->errors($this->request->getData());

        if(empty($validationErrors)) {
            $body = $this->request->getParsedBody();
            $feedsModel = TableRegistry::get('feeds');

            // If not exist, create
            $feedId = null;
            if (!$feedsModel->exists(array("url" => $body["url"]))) {
                $feedData = $this->getFeedData($body["url"]);

                $feedInstance = $feedsModel->newEntity();

                $feedInstance->url = $body["url"];
                $feedInstance->remote_id = $feedData["remote_id"];
                $feedInstance->web_url = $feedData["web_url"];
                $feedInstance->title = $feedData["title"];
                $feedInstance->updated = $feedData["updated"];

                if (!$feedsModel->save($feedInstance)) {
                    throw new InternalErrorException("Feed can not be saved to database");
                }

                $feedId = $feedInstance->id;
            }
            else {
                $feeds = $feedsModel
                    ->find()
                    ->select(['id'])
                    ->where(['url =' => $body["url"]])
                    ->limit(1);

                $feedId = $feeds->first()->id;
            }

            // If not related with this user, relate
            $usersFeedsModel = TableRegistry::get('users_feeds');
            if (!$usersFeedsModel->exists(array("feed_id" => $feedId, "user_id" => $userId))) {
                $relInstance = $usersFeedsModel->newEntity();

                $relInstance->user_id = $userId;
                $relInstance->feed_id = $feedId;

                if (!$usersFeedsModel->save($relInstance)) {
                    throw new InternalErrorException("User feed can not be saved to database");
                }
            }

            $this->set($body);
        }
        else {
            throw new BadRequestException(json_encode($validationErrors));
        }
    }

    public function delete($id)
    {
        $this->set([
            'recipes' => "hola"
        ]);
    }

    private function getFeedData($url) {
        $http = new Client(["redirect" => 3]);
        $response = $http->get($url);

        if ($response->getStatusCode() == 200) {
            return $this->parseFeed($url, $response->body());
        }
        else {
            throw new Exception("Not found");
        }
    }

    private function parseFeed($url, $body) {
        $xml = Xml::build($body);
        $feed = Xml::toArray($xml);

        $data = array(
            "url" => null,
            "remote_id" => null,
            "web_url" => null,
            "title" => null,
            "description" => null,
            "updated" => null,
            "items" => array(),
        );

        $entryModel = array(
            "remote_id" => null,
            "url" => null,
            "title" => null,
            "summary" => null,
            "content" => null,
            "contentType" => null,
            "author" => null,
            "updated" => null
        );

        if (strpos(join(",", $xml->getNamespaces()), "Atom") !== false) {
            $feed = $feed["feed"];

            // Parse common data
            $data["url"] = $url;
            if (array_key_exists("id", $feed)) $data["remote_id"] = $feed["id"];
            if (array_key_exists("@href", $feed)) $data["web_url"] = $feed["@href"];
            $data["title"] = $feed["title"];
            if (array_key_exists("subtitle", $feed)) $data["description"] = $feed["subtitle"];
            if (array_key_exists("updated", $feed)) $data["updated"] = date_parse($feed["updated"]);

            // Fix toArray if only one entry
            if (array_key_exists("title", $feed["entry"])) $feed["entry"] = array($feed["entry"]);

            foreach ($feed["entry"] as $entry) {
                $entryData = $entryModel;

                if (array_key_exists("id", $entry)) $entryData["remote_id"] = $entry["id"];
                if (array_key_exists("link", $entry)) $entryData["url"] = $entry["link"]["@href"];
                if (array_key_exists("title", $entry)) $entryData["title"] = $entry["title"];
                if (array_key_exists("summary", $entry)) $entryData["summary"] = $entry["summary"];
                $entryData["content"] = NULL;
                $entryData["contentType"] = NULL;
                $entryData["author"] = NULL;
                if (array_key_exists("updated", $entry)) $entryData["updated"] = date_parse($entry["updated"]);

                array_push($data["items"], $entryData);
            }
        }
        else if (array_key_exists("rss", $feed) && array_key_exists("@version", $feed["rss"]) && $feed["rss"]["@version"] == "2.0") {
            $feed = $feed["rss"]["channel"];

            // Parse common data
            $data["url"] = $url;
            if (array_key_exists("guid", $feed)) {
                if (array_key_exists("@", $feed["guid"])) $data["remote_id"] = $feed["guid"]["@"];
                else $data["remote_id"] = $feed["guid"];
            }
            if (array_key_exists("link", $feed)) {
                $data["web_url"] = $feed["link"];
                if (!array_key_exists("remote_id", $data)) $data["remote_id"] = $feed["link"];
            }
            if (is_null($data["remote_id"])) $data["remote_id"] = $data["web_url"];
            $data["title"] = $feed["title"];
            if (array_key_exists("description", $feed)) $data["description"] = $feed["description"];
            if (array_key_exists("lastBuildDate", $feed)) $data["updated"] = date_parse($feed["lastBuildDate"]);

            // Fix toArray if only one entry
            if (array_key_exists("title", $feed["item"])) $feed["item"] = array($feed["item"]);

            foreach ($feed["item"] as $entry) {
                $entryData = $entryModel;

                if (array_key_exists("guid", $entry)) {
                    if (array_key_exists("@", $entry["guid"])) $entryData["remote_id"] = $entry["guid"]["@"];
                    else $entryData["remote_id"] = $entry["guid"];
                }
                if (array_key_exists("link", $entry)) {
                    $entryData["url"] = $entry["link"];
                    if (is_null($entryData["remote_id"])) $entryData["remote_id"] = $entry["link"];
                }
                if (array_key_exists("title", $entry)) $entryData["title"] = $entry["title"];
                if (array_key_exists("description", $entry)) $entryData["summary"] = $entry["description"];
                $entryData["content"] = NULL;
                $entryData["contentType"] = NULL;
                $entryData["author"] = NULL;
                if (array_key_exists("pubDate", $entry)) $entryData["updated"] = date_parse($entry["pubDate"]);

                array_push($data["items"], $entryData);
            }
        }

        return $data;
    }
}