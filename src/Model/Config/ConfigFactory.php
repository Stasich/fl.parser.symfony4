<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 22.07.18
 * Time: 21:38
 */

namespace App\Model\Config;


use Symfony\Component\Yaml\Exception\RuntimeException;

class ConfigFactory
{
    /**
     * @param string $confName
     * @return Config
     */
    public static function getConfig(string $confName): Config {
        $class = 'App\Model\Config\Config' . ucfirst($confName);

        if (!class_exists($class)) {
            throw new RuntimeException('Not valid config name');
        }

        return new $class;
    }
}
