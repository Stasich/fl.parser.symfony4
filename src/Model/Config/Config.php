<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 22.07.18
 * Time: 21:45
 */

namespace App\Model\Config;


abstract class Config
{
    /** @var string */
    protected $serviceLink;
    /** @var integer */
    protected $serviceId;

    /**
     * @return string
     */
    function getServiceLink(): string {
        return $this->serviceLink;
    }

    /**
     * @return integer
     */
    function getServiceId(): int {
        return $this->serviceId;
    }
}