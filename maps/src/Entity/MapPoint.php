<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use App\Tools\Serializers\MapPointSerializer;

/**
 * @ORM\Entity(repositoryClass=MapPointRepository::class)
 * @ORM\Table(indexes = {
 *  @ORM\Index(name="request_idx", columns={"request"})
 * })
 */
class MapPoint {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"all_point"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"all_point"})
     */
    private $request;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"all_point", "list_point"})
     */
    private $text;

    /**
     * @ORM\Column(type="float")
     * @Groups({"all_point", "list_point"})
     */
    private $lat;

    /**
     * @ORM\Column(type="float")
     * @Groups({"all_point", "list_point"})
     */
    private $lng;

    public function getId(): ?int {
        return $this->id;
    }

    public function getRequest(): ?string {
        return $this->request;
    }

    public function setRequest(string $request): self {
        $this->request = $request;
        return $this;
    }

    public function getText(): ?string {
        return $this->text;
    }

    public function setText(?string $text): self {
        $this->text = $text;
        return $this;
    }

    public function getLat(): ?float {
        return $this->lat;
    }

    public function setLat(float $lat): self {
        $this->lat = $lat;
        return $this;
    }

    public function getLng(): ?float {
        return $this->lng;
    }

    public function setLng(float $lng): self {
        $this->lng = $lng;
        return $this;
    }

    public function serialize(): string
    {
        $serializer = new MapPointSerializer();
        return $serializer->serialize($this, ['list_point']);
    }
}
