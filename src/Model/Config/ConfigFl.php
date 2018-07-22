<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 22.07.18
 * Time: 21:53
 */

namespace App\Model\Config;


class ConfigFl extends Config
{
    /** @var string $serviceLink */
    protected $serviceLink = 'https://www.fl.ru/rss/all.xml?category=5';
    /** @var integer $serviceId */
    protected $serviceId = 1;
}
