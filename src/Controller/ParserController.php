<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 22.07.18
 * Time: 22:10
 */

namespace App\Controller;

use App\Model\Config\Config;
use App\Model\Config\ConfigFactory;


class ParserController {
    /** @var Config $config */
    private $config;

    private $availableArgs = [
        'fl' => TRUE,
        'freelansim' => TRUE,
    ];

    /**
     * @param string $siteName
     */
    public function run(string $siteName) {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('It\'s not cli');
        }

        if (!array_key_exists($siteName, $this->availableArgs)) {
            throw new \RuntimeException('Available\'s site_name "fl", "freelansim"');
        }

        $this->config = ConfigFactory::getConfig($siteName);
        $newPosts = $this->getNewPosts();
    }

    private function getNewPosts() {
        $items = $this->getXmlItems();

        $patternForRegexp = '/(<br>)+/';  // шаблон для замены <br> на \n в description

        //нужна доктрина
        $tableLinks = TableLinks::getInstance();
        $patternForXML = $tableLinks->getLinks($this->prefix);

        $newPostsInArr = [];
        $viewed_links = [];

        foreach ($items as $item) {
            $date = $item->getElementsByTagName("pubDate");
            $date = $date[0]->nodeValue;

            $title = $item->getElementsByTagName("title");
            $title = strip_tags(trim($title[0]->nodeValue), '<a>');

            $link = $item->getElementsByTagName("link");
            $link = $link[0]->nodeValue;

            $description = $item->getElementsByTagName("description");
            $description = strip_tags(preg_replace($patternForRegexp, "\n", trim($description[0]->nodeValue)), '<a>');

            if (array_key_exists($link, $patternForXML)) {
                break;
            }
            $viewed_links[] = $link;

            $newPostsInArr[] = compact('date', 'title', 'link', 'description');
        }

        if (count($newPostsInArr) > 0) {
            $tableLinks->addViewedLinksToDb($viewed_links, $this->prefix);
        }
        return $newPostsInArr;
    }

    /**
     * @return \DOMNodeList
     */
    private function getXmlItems(): \DOMNodeList {
        $dom_xml = new \DomDocument(2.0, 'UTF-8');
        $dom_xml->loadXML($this->curlLoad($this->config->getServiceLink()));
        $items = $dom_xml->getElementsByTagName("item");
        return $items;
    }

    private function curlLoad($url): string {
        $cookie = tmpfile();
        $userAgent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31' ;

        $ch = curl_init($url);

        $options = array(
            CURLOPT_CONNECTTIMEOUT => 20 ,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $cookie,
            CURLOPT_COOKIEJAR => $cookie ,
            CURLOPT_SSL_VERIFYPEER => 0 ,
            CURLOPT_SSL_VERIFYHOST => 0
        );

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}