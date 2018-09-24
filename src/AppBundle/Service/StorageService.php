<?php

namespace AppBundle\Service;

use Predis\Client as RedisClient;

/**
 * Class StorageService
 * @package AppBundle\Service\StorageService
 */
class StorageService
{
    /**
     * @var RedisClient
     */
    private $redis;

    /**
     *
     * @param RedisClient $redis
     */
    public function __construct(RedisClient  $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param $data
     * @param $key
     */
    public function persistDataToRedis($data, $key)
    {
        $this->redis->set($key, json_encode($data));
    }

    /**
     * @param $key
     * @return string|array
     */
    public function getDataFromRedis($key)
    {
        $data = $this->redis->get($key);

        return $data;
    }
}
