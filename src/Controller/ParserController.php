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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Links;
use App\Model\Config\ConfigTelegram;

class ParserController extends AbstractController
{
	/** @var Config $config */
	private $config;

	private $availableArgs = [
		'fl' => TRUE,
		'freelansim' => TRUE,
	];

	/**
	 * @param string $siteName
	 * @param array $options
	 */
	public function run(string $siteName, array $options = [])
	{
		if (PHP_SAPI !== 'cli') {
			throw new \RuntimeException('It\'s not cli');
		}

		if (!array_key_exists($siteName, $this->availableArgs)) {
			throw new \RuntimeException('Available\'s site_name "fl", "freelansim"');
		}

		$this->config = ConfigFactory::getConfig($siteName);

		$newPosts = $this->getNewPosts();

		if (!empty($options['dry_run'])) {
			var_export($newPosts);
		} else {
			$this->sendPostsToTelegram(
				$newPosts,
				ConfigTelegram::TOKEN,
				ConfigTelegram::CHAT_ID,
				ConfigTelegram::OPTIONS
			);
		}
	}

	/**
	 * @return array
	 */
	private function getNewPosts(): array
	{
		$items = $this->getXmlItems();

		$patternForRegexp = '/(<br>)+/';  // шаблон для замены <br> на \n в description

		//нужна доктрина
		$entityManager = $this->getDoctrine()->getManager();

        $linksRepository = $entityManager->getRepository(Links::class);

        $newPostsInArr = [];

		/** @var \DOMElement $item */
		foreach ($items as $item) {
			$date = $item->getElementsByTagName("pubDate");
			//@TODO проверочку?
			$date = $date[0]->nodeValue;

			$title = $item->getElementsByTagName("title");
			$title = strip_tags(trim($title[0]->nodeValue), '<a>');

			$link = $item->getElementsByTagName("link");
			/** @var string $link */
			$link = $link[0]->nodeValue;

			$description = $item->getElementsByTagName("description");
			$description = strip_tags(preg_replace($patternForRegexp, "\n", trim($description[0]->nodeValue)), '<a>');

			$existTask = $linksRepository->findOneBy(
			    [
			        'link' => $link
                ]
            );

			if ($existTask) continue;

			$linkEntity = new Links();
			$linkEntity->setLink($link)
				->setServiceId($this->config->getServiceId());
			$entityManager->persist($linkEntity);
            $entityManager->flush();
			$linkEntity = NULL;

			$newPostsInArr[] = compact('date', 'title', 'link', 'description');
		}

		return $newPostsInArr;
	}

	/**
	 * @return \DOMNodeList
	 */
	private function getXmlItems(): \DOMNodeList
	{
		$dom_xml = new \DomDocument(2.0, 'UTF-8');
		$dom_xml->loadXML($this->curlLoad($this->config->getServiceLink()));
		$items = $dom_xml->getElementsByTagName("item");
		return $items;
	}

	private function curlLoad($url): string
	{
		$cookie = tmpfile();
		$userAgent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31';

		$ch = curl_init($url);

		$options = array(
			CURLOPT_CONNECTTIMEOUT => 20,
			CURLOPT_USERAGENT => $userAgent,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0
		);

		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		curl_close($ch);
		fclose($cookie);
		return $result;
	}

	public function sendPostsToTelegram($newPostsInArr, $token, $chat_id, $options)
	{
		$postsCount = count($newPostsInArr);

		if ($postsCount === 0) {
			return;
		}

		for ($i = $postsCount - 1; $i >= 0; $i--) {
			if (!isset(
				$newPostsInArr[$i]['date'],
				$newPostsInArr[$i]['title'],
				$newPostsInArr[$i]['description'],
				$newPostsInArr[$i]['link']
			)) {
				continue;
			}

			$str = "<b>" . $newPostsInArr[$i]['date'] . "</b>\n<b>" .
				$newPostsInArr[$i]['title'] . "</b>\n" .
				$newPostsInArr[$i]['description'] . "\n" .
				$newPostsInArr[$i]['link'];
			$str = urlencode($str);
			$query = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&$options&text=$str";
			echo $query;
			file($query);
		}
	}
}