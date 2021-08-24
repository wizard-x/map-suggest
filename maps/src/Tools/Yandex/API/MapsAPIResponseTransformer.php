<?php declare(strict_types=1);

namespace App\Tools\Yandex\API;

use App\Tools\Interfaces\TransformMapsAPIResponseInterface;
use App\Tools\DTO\MapsDataDTO;

class MapsAPIResponseTransformer implements TransformMapsAPIResponseInterface {
    public function transform(array $response): MapsDataDTO {
        $_response = $response['response'];
        $_geoCollection = $_response['GeoObjectCollection'];
        $_metaDataProperty = $_geoCollection['metaDataProperty'];
        $_geocoderResponseMetaData = $_metaDataProperty['GeocoderResponseMetaData'];
        $_featureMember = $_geoCollection['featureMember'];

        $requestText = $_geocoderResponseMetaData['request'];

        $data = [];
        foreach ($_featureMember as $member) {
            $geoObject = $member['GeoObject'];
            $geocoderMetaData = $geoObject['metaDataProperty']['GeocoderMetaData'];
            $text = $geocoderMetaData['Address']['formatted'];
            $point = explode(' ', $geoObject['Point']['pos']);

            $data[] = [
                'text' => $text,
                'point' => $point,
            ];
        }

        $result = new MapsDataDTO();
        $result
            ->setText($requestText)
            ->setData($data)
        ;
        return $result;
    }
}