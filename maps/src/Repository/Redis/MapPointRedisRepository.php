<?php namespace App\Repository\Redis;

use \Redis;
use App\Repository\Interfaces\DTOStorageInterface;
use App\Tools\DTO\MapsDataDTO;

class MapPointRedisRepository implements DTOStorageInterface {

    protected $redis;

    public function __construct(\Redis $redis_client) {
        $this->redis = $redis_client;
    }

    public function saveFromDTO($data): void {
        $json = $data->toJSON();
        $this->redis->set($data->getRequest(), $json);
        $this->redis->expire($data->getRequest(), 300);
    }

    public function retrieveAsDTOByKey($key): MapsDataDTO {
        $data = $this->redis->get($key);
        $dto = new MapsDataDTO();
        return $data == FALSE ? $dto : $dto->fromJSON($data);
    }
}