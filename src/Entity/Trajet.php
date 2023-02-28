<?php

namespace App\Entity;


use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $kms = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $start_hour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $arrival_hour = null;

    #[ORM\Column]
    private ?int $available_places = null;

    #[ORM\ManyToOne(inversedBy: 'trajets_conducteur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $conducteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $start_city = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $arrival_city = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $voiture = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'trajets_reserves')]
    private Collection $passagers;

    public function __construct()
    {
        $this->passagers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKms(): ?int
    {
        return $this->kms;
    }

    public function setKms(int $kms): self
    {
        $this->kms = $kms;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->start_hour;
    }

    public function setStartHour(\DateTimeInterface $start_hour): self
    {
        $this->start_hour = $start_hour;

        return $this;
    }

    public function getArrivalHour(): ?\DateTimeInterface
    {
        return $this->arrival_hour;
    }

    public function setArrivalHour(\DateTimeInterface $arrival_hour): self
    {
        $this->arrival_hour = $arrival_hour;

        return $this;
    }

    public function getAvailablePlaces(): ?int
    {
        return $this->available_places;
    }

    public function setAvailablePlaces(int $available_places): self
    {
        $this->available_places = $available_places;

        return $this;
    }

    public function getConducteur(): ?Personne
    {
        return $this->conducteur;
    }

    public function setConducteur(?Personne $conducteur): self
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function getStartCity(): ?City
    {
        return $this->start_city;
    }

    public function setStartCity(?City $start_city): self
    {
        $this->start_city = $start_city;

        return $this;
    }

    public function getArrivalCity(): ?City
    {
        return $this->arrival_city;
    }

    public function setArrivalCity(?City $arrival_city): self
    {
        $this->arrival_city = $arrival_city;

        return $this;
    }

    public function getVoiture(): ?Car
    {
        return $this->voiture;
    }

    public function setVoiture(?Car $voiture): self
    {
        $this->voiture = $voiture;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPassagers(): Collection
    {
        return $this->passagers;
    }

    public function addPassager(Personne $passager): self
    {
        if (!$this->passagers->contains($passager)) {
            $this->passagers->add($passager);
        }

        return $this;
    }

    public function removePassager(Personne $passager): self
    {
        $this->passagers->removeElement($passager);

        return $this;
    }
}
