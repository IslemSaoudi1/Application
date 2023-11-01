<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $jobDescription = null;

    #[ORM\Column(length: 255)]
    private ?string $Nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passport = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $passportdeliveredOn = null;

    #[ORM\OneToOne(inversedBy: 'profile')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobDescription(): ?string
    {
        return $this->jobDescription;
    }

    public function setJobDescription(string $jobDescription): static
    {
        $this->jobDescription = $jobDescription;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->Nationality;
    }

    public function setNationality(string $Nationality): static
    {
        $this->Nationality = $Nationality;

        return $this;
    }

    public function getPassport(): ?string
    {
        return $this->passport;
    }

    public function setPassport(?string $passport): static
    {
        $this->passport = $passport;

        return $this;
    }

    public function getPassportdeliveredOn(): ?\DateTimeInterface
    {
        return $this->passportdeliveredOn;
    }

    public function setPassportdeliveredOn(?\DateTimeInterface $passportdeliveredOn): static
    {
        $this->passportdeliveredOn = $passportdeliveredOn;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): static
    {
        $this->User = $User;

        return $this;
    }
}
