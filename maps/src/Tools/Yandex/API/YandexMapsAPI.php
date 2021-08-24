<?php declare(strict_types=1);

namespace App\Tools\Yandex\API;

use App\Tools\Interfaces\GeoSearchInterface;
use App\Tools\Interfaces\TransformMapsAPIResponseInterface;
use App\Tools\Yandex\Exceptions\YandexMapsAPIException;
use App\Tools\Yandex\API\MapsAPIResponseTransformer;
use App\Tools\DTO\MapsDataDTO;

class YandexMapsAPI implements GeoSearchInterface {

    const TYPES = [
        'string' => 'transformStringFilter',
        'array'  => 'transformArrayFilter',
    ];

    private $token = null;
    private $transformer = null;


    public function __construct(string $token, TransformMapsAPIResponseInterface $transformer) {
        $this
            ->setToken($token)
            ->setTransformer($transformer)
        ;
    }


    public function setToken(string $token): self {
        $this->token = $token;
        return $this;
    }


    public function setTransformer(MapsAPIResponseTransformer $transformer): self {
        $this->transformer = $transformer;
        return $this;
    }


    public function search($filter): MapsDataDTO {
        $stringFilter = $this->parseFilter($filter);
        $response = $this->callAPI($stringFilter);
        return $this->transformResponse($response);
    }


    public function callAPI(string $filter): array {
        $url = sprintf(
            'https://geocode-maps.yandex.ru/1.x/?apikey=%s&geocode=%s&format=json&results=%s',

            $this->token,

            urlencode(mb_convert_encoding($filter, 'UTF-8')),

            6
        );
        // thread safeless
        $response = file_get_contents($url);
        return json_decode($response, true);
    }


    protected function parseFilter($filter): string {
        $type = gettype($filter);
        if (array_key_exists($type, static::TYPES)) {
            return call_user_func([$this, static::TYPES[$type]], $filter);
        }
        throw new YandexMapsAPIException('Can\'t parse filter');
    }


    protected function transformStringFilter(string $filter): string {
        return $filter;
    }


    protected function transformArrayFilter(array $filter): string {
        return implode(',', $filter);
    }


    protected function transformResponse(array $response): MapsDataDTO {
        return $this->transformer->transform($response);
    }
}