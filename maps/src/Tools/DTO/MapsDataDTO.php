<?php declare(strict_types=1);

namespace App\Tools\DTO;

class MapsDataDTO {
    private $text;
    private $data;

    public function setText(string $text): self {
        $this->text = $text;
        return $this;
    }

    public function getText(): string {
        return $this->text;
    }

    public function setData(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function getData(): array {
        return $this->data;
    }

    public function toJSON(): string {
        return json_encode([
            'text' => $this->getText(),
            'data' => $this->getData(),
        ]);
    }
}
