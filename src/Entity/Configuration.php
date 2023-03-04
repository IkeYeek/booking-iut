<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $placeName = null;

    #[ORM\Column(length: 255)]
    private ?string $placeAddress = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\Column]
    private int $nbRows;

    #[ORM\Column]
    private int $nbSeatsPerRow;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaceName(): ?string
    {
        return $this->placeName;
    }

    public function setPlaceName(string $placeName): self
    {
        $this->placeName = $placeName;

        return $this;
    }

    public function getPlaceAddress(): ?string
    {
        return $this->placeAddress;
    }

    public function setPlaceAddress(string $placeAddress): self
    {
        $this->placeAddress = $placeAddress;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getNbRows(): ?int
    {
        return $this->nbRows;
    }

    public function setNbRows(int $nbRows): self
    {
        $this->nbRows = $nbRows;

        return $this;
    }

    public function getNbSeats(): ?int {
        return $this->nbRows * $this->nbSeatsPerRow;
    }

    public function getNbSeatsPerRow(): ?int
    {
        return $this->nbSeatsPerRow;
    }

    public function setNbSeatsPerRow(int $nbSeatsPerRow): self
    {
        $this->nbSeatsPerRow = $nbSeatsPerRow;

        return $this;
    }
}
