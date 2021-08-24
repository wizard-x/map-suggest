<?php declare(strict_types=1);

namespace App\Tools\Interfaces;

use App\Tools\DTO\MapsDataDTO;

interface TransformMapsAPIResponseInterface {
    public function transform(array $response): MapsDataDTO;
}