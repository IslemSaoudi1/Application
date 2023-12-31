<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 100)]
    private $lastname;

    #[ORM\Column(type: 'string', length: 100)]
    private $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;
    #[ORM\Column(type: 'string', length: 100)]
    private $resetToken;

    #[ORM\OneToOne(mappedBy: 'User')]
    private ?Profile $profile = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'no')]
    private ?self $manager = null;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: self::class)]
    private Collection $no;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Task::class)]
    private Collection $tasks;

    public function __construct()
    {
        $this->no = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }




    public function getManager(): ?self
    {
        return $this->manager;
    }

    /**
     * @return mixed
     */
    public function getManagedUsers()
    {
        return $this->managedUsers;
    }

    /**
     * @param mixed $managedUsers
     */
    public function setManagedUsers($managedUsers): void
    {
        $this->managedUsers = $managedUsers;
    }

    public function setManager(?self $manager): self
    {
        $this->manager = $manager;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = null;
//        $roles[] = 'ROLE_ADMIN';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }
    public function getSubordinates(): Collection
    {
        return $this->subordinates;
    }

    public function addSubordinate(User $subordinate)
    {
        if (!$this->subordinates->contains($subordinate)) {
            $this->subordinates[] = $subordinate;
        }
    }

    public function removeSubordinate(User $subordinate)
    {
        $this->subordinates->removeElement($subordinate);
    }

   public function __toString(): string
   {
       // TODO: Implement __toString() method.
       return $this->getFirstname().' '.$this->getLastname();
   }

   /**
    * @return Collection<int, self>
    */
   public function getNo(): Collection
   {
       return $this->no;
   }

   public function addNo(self $no): static
   {
       if (!$this->no->contains($no)) {
           $this->no->add($no);
           $no->setManager($this);
       }

       return $this;
   }

   public function removeNo(self $no): static
   {
       if ($this->no->removeElement($no)) {
           // set the owning side to null (unless already changed)
           if ($no->getManager() === $this) {
               $no->setManager(null);
           }
       }

       return $this;
   }

   /**
    * @return Collection<int, Task>
    */
   public function getTasks(): Collection
   {
       return $this->tasks;
   }

   public function addTask(Task $task): static
   {
       if (!$this->tasks->contains($task)) {
           $this->tasks->add($task);
           $task->setAssignedUser($this);
       }

       return $this;
   }

   public function removeTask(Task $task): static
   {
       if ($this->tasks->removeElement($task)) {
           // set the owning side to null (unless already changed)
           if ($task->getAssignedUser() === $this) {
               $task->setAssignedUser(null);
           }
       }

       return $this;
   }


}
