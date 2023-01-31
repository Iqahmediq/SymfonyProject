<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $begin_at = null;

    #[ORM\Column(length: 255)]
    private ?string $image ;
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->begin_at;
    }

    public function setBeginAt(\DateTimeImmutable $begin_at): self
    {
        $this->begin_at = $begin_at;

        return $this;
    }
    /**
* @return mixed
*/
public function getImage()
{
return $this->image;
}
/**
* @param mixed $image
*/
public function setImage($image): void
{
$this->image = $image;
}
}
