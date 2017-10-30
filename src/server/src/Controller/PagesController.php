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
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\Utility\Xml;
use Cake\Network\Http\Client;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->RequestHandler->renderAs($this, 'json');
        $this->Auth->allow(['display']); // Permite sin auth
    }

    /**
     * Displays a view
     *
     * @return \Cake\Http\Response|null
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function displayDefault()
    {
        $this->set([
            "message" => "This is an api REST. Use the methods."
        ]);
    }

    public function display()
    {
        $url = "https://www.xataka.com/index.xml";
        var_dump($this->getFeedData($url)); die();

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
        $data = array();

        if (strpos(join(",", $xml->getNamespaces()), "Atom") !== false) {
            $feed = $feed["feed"];

            // Parse common data
            $data["url"] = $url;
            if (array_key_exists("id", $feed)) $data["remote_id"] = $feed["id"];
            if (array_key_exists("@href", $feed)) $data["web_url"] = $feed["@href"];
            $data["title"] = $feed["title"];
            if (array_key_exists("subtitle", $feed)) $data["description"] = $feed["subtitle"];
            if (array_key_exists("updated", $feed)) $data["updated"] = date_parse($feed["updated"]);
            $data["items"] = array();

            // Fix toArray if only one entry
            if (array_key_exists("title", $feed["entry"])) $feed["entry"] = array($feed["entry"]);

            foreach ($feed["entry"] as $entry) {
                $entryData = array();

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
            $data["title"] = $feed["title"];
            if (array_key_exists("description", $feed)) $data["description"] = $feed["description"];
            if (array_key_exists("lastBuildDate", $feed)) $data["updated"] = date_parse($feed["lastBuildDate"]);
            $data["items"] = array();

            // Fix toArray if only one entry
            if (array_key_exists("title", $feed["item"])) $feed["item"] = array($feed["item"]);

            foreach ($feed["item"] as $entry) {
                $entryData = array();

                if (array_key_exists("guid", $entry)) {
                    if (array_key_exists("@", $entry["guid"])) $entryData["remote_id"] = $entry["guid"]["@"];
                    else $entryData["remote_id"] = $entry["guid"];
                }
                if (array_key_exists("link", $entry)) {
                    $entryData["url"] = $entry["link"];
                    if (!array_key_exists("remote_id", $entryData)) $entryData["remote_id"] = $entry["link"];
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