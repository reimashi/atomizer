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

use Cake\Core\Exception\Exception;
use Cake\Http\Client;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\InternalErrorException;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\Utility\Xml;

class FeedsController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->RequestHandler->renderAs($this, 'json');
        $this->Auth->allow(['index', 'add', 'tag', 'delete']); // TODO: Remove to enable login
    }

    /**
     * Get a list of feeds for the actual user
     */
    public function index() {
        //$userId = $this->Auth->identify()["id"];
        $userId = 1;
        $update = boolval($this->request->getQuery("update"));

        $feedsRelModel = TableRegistry::get('users_feeds');
        $feedsModel = TableRegistry::get('feeds');
        $itemsModel = TableRegistry::get('items');
        $userItemsModel = TableRegistry::get('users_items');

        // Get user feed ids
        $feedsRel = $feedsRelModel->find()
            ->select(['feed_id'])
            ->where(['user_id =' => $userId]);

        $userFeedsIds = array();
        foreach ($feedsRel as $userFeeds) {
            array_push($userFeedsIds, $userFeeds->feed_id);
        }

        // Get feed instances
        $userFeeds = array();
        foreach ($userFeedsIds as $userFeedId) {
            $feedsQuery = $feedsModel->find()
                ->where(['id =' => $userFeedId]);

            array_push($userFeeds, $feedsQuery->first()->toArray());
        }

        // If update required
        if ($update) {
            foreach ($userFeeds as $userFeed) {
                // Get old and new values
                $updatedData = $this->getFeedData($userFeed["url"])["items"];
                $feedItemsQuery = $itemsModel->find()
                    ->where(['feed_id =' => $userFeed["id"]]);

                // Get the remote ids of all items in database
                $localIds = array();
                foreach ($feedItemsQuery as $feedItemInstance) {
                    array_push($localIds, $feedItemInstance->remoteid);
                }

                // Filter the list of items not saved in database yet
                $notUpdatedYet = array();
                foreach ($updatedData as $item) {
                    if (!in_array($item["remote_id"], $localIds)) array_push($notUpdatedYet, $item);
                }

                // Save the new items
                foreach ($notUpdatedYet as $newItem) {
                    $newItemEntity = $itemsModel->newEntity();

                    $newItemEntity->feed_id = $userFeed["id"];
                    $newItemEntity->remoteid = $newItem["remote_id"];
                    $newItemEntity->url = $newItem["url"];
                    $newItemEntity->title = $newItem["title"];
                    $newItemEntity->summary = substr(strip_tags($newItem["summary"], "<a><p><b><i>"), 0, 512) . "...";
                    $newItemEntity->content = $newItem["content"];
                    $newItemEntity->content_type = $newItem["content_type"];
                    $newItemEntity->author = $newItem["author"];
                    if($newItem["updated"] instanceof \DateTime) $newItemEntity->updated = $newItem["updated"];

                    if (!$itemsModel->save($newItemEntity)) {
                        throw new InternalErrorException("Server can not save a feed update");
                    }

                    $newItemUserEntity = $userItemsModel->newEntity();

                    $newItemUserEntity->item_id = $newItemEntity->id;
                    $newItemUserEntity->user_id = $userId;

                    if (!$userItemsModel->save($newItemUserEntity)) {
                        throw new InternalErrorException("Server can not save a feed relation with user");
                    }
                }
            }
        }

        // Populate each feed with items
        foreach ($userFeeds as &$userFeed) {
            $itemsQuery = $itemsModel->find()
                ->where(['feed_id =' => $userFeed["id"]]);

            $feedItems = $itemsQuery->toArray();

            foreach ($feedItems as &$feedItem) {
                $feedUserItemQuery = $userItemsModel->find()
                    ->where(['item_id =' => $feedItem["id"], 'user_id =' => $userId]);

                if ($feedUserItemQuery->first()) {
                    $feedItem["readed"] = $feedUserItemQuery->first()->readed;
                    $feedItem["read_later"] = $feedUserItemQuery->first()->read_later;
                }
                else {
                    $feedItem["readed"] = false;
                    $feedItem["read_later"] = false;
                }
            }

            $userFeed["items"] = $feedItems;
        }

        $this->set($userFeeds);
    }

    /**
     * Add new feed to the database
     */
    public function add()
    {
        //$userId = $this->Auth->identify()["id"];
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
                $feedInstance->remoteid = $feedData["remote_id"];
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

    /**
     * Delete a feed in the database
     * NotImplementedYet!!
     * @param $id
     */
    public function delete($id)
    {
        $this->set([]);
    }

    /**
     * Tag a feed_user property
     */
    public function tag()
    {
        //$userId = $this->Auth->identify()["id"]; TODO: Uncomment to enable login
        $userId = 1;

        $body = $this->request->getParsedBody();

        // If item_id id if set
        if (isset($body["item_id"])) {
            $usersItemsModel = TableRegistry::get('users_items');

            // Find the relation
            $usersItemsQuery = $usersItemsModel->find()
                ->where(["user_id =" => $userId, "item_id =" => $body["item_id"]]);

            // If relation don't exists, create
            $relInstance = null;
            if ($usersItemsQuery->first()) {
                $relInstance = $usersItemsQuery->first();
            }
            else {
                $relInstance =  $usersItemsModel->newEntity();

                $relInstance->item_id = $body["item_id"];
                $relInstance->user_id = $userId;
            }

            // Set flags
            if (isset($body["readed"])) {
                $relInstance->readed = boolval($body["readed"]);
            }

            if (isset($body["read_later"])) {
                $relInstance->read_later = boolval($body["read_later"]);
            }

            // At last, save
            if (!$usersItemsModel->save($relInstance)) {
                throw new InternalErrorException("Server can't save the item tag");
            }

            $this->set($body);
        }
        else {
            throw new BadRequestException("Request need the item id.");
        }
    }

    /**
     * Download remote feed and parse the data
     * @param $url Url of feed
     * @return array Feed data
     */
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

    /**
     * Parse feed xml to a more friendly format
     * @param $url Url of feed
     * @param $body XML code
     * @return array Feed data
     */
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
            "content_type" => null,
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