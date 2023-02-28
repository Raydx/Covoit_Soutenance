<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: PersonneRepository::class)]
class Personne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['info'])]
    private ?string $tel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info'])]
    private ?string $email = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['info'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user = null;

    #[ORM\OneToMany(mappedBy: 'conducteur', targetEntity: Car::class, cascade: ['remove'])]
    private ?Collection $voiture;

    #[ORM\ManyToMany(targetEntity: Trajet::class, inversedBy: 'passagers')]
    private Collection $trajets_reserves;

    #[ORM\OneToMany(mappedBy: 'conducteur', targetEntity: Trajet::class, orphanRemoval: true)]
    private ?Collection $trajets_conducteur;

    public function __construct()
    {
        $this->voiture = new ArrayCollection();
        $this->trajets_reserves = new ArrayCollection();
        $this->trajets_conducteur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    /**
     * @return Collection<int, Car>
     */
    public function getVoiture(): Collection
    {
        return $this->voiture;
    }

    public function addVoiture(Car $voiture): self
    {
        if (!$this->voiture->contains($voiture)) {
            $this->voiture->add($voiture);
            $voiture->setConducteur($this);
        }

        return $this;
    }

    public function removeVoiture(Car $voiture): self
    {
        if ($this->voiture->removeElement($voiture)) {
            // set the owning side to null (unless already changed)
            if ($voiture->getConducteur() === $this) {
                $voiture->setConducteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsReserves(): Collection
    {
        return $this->trajets_reserves;
    }

    public function addTrajetsReserf(Trajet $trajetsReserf): self
    {
        if (!$this->trajets_reserves->contains($trajetsReserf)) {
            $this->trajets_reserves->add($trajetsReserf);
            $trajetsReserf->addPassager($this);
        }

        return $this;
    }

    public function removeTrajetsReserf(Trajet $trajetsReserf): self
    {
        $this->trajets_reserves->removeElement($trajetsReserf);
        $trajetsReserf->removePassager($this);
        return $this;
    }

    /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsConducteur(): Collection
    {
        return $this->trajets_conducteur;
    }

    public function addTrajetsConducteur(Trajet $trajetsConducteur): self
    {
        if (!$this->trajets_conducteur->contains($trajetsConducteur)) {
            $this->trajets_conducteur->add($trajetsConducteur);
            $trajetsConducteur->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajetsConducteur(Trajet $trajetsConducteur): self
    {
        if ($this->trajets_conducteur->removeElement($trajetsConducteur)) {
            // set the owning side to null (unless already changed)
            if ($trajetsConducteur->getConducteur() === $this) {
                $trajetsConducteur->setConducteur(null);
            }
        }

        return $this;
    }

    
}
