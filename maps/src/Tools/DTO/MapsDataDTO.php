<?php declare(strict_types=1);

namespace App\Tools\DTO;

use App\Tools\Interfaces\TransformToJSONInterface;

class MapsDataDTO implements TransformToJSONInterface {
    private $request = '';
    private $data = [];

    public function setRequest(string $text): self {
        $this->request = $text;
        return $this;
    }

    public function getRequest(): string {
        return $this->request;
    }

    public function setData(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function getData(): array {
        return $this->data;
    }

    public function isEmpty(): bool {
        return count($this->getData()) == 0;
    }

    public function toJSON(): string {
        return json_encode([
            'request' => $this->getRequest(),
            'data' => $this->getData(),
        ]);
    }

    public function fromJSON(string $json): self {
        $data = json_decode($json, true);
        return $this
            ->setRequest($data['request'])
            ->setData($data['data'])
        ;
    }
}
