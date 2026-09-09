<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private $email;

    #[ORM\Column(type: 'string', length: 50, nullable: true, unique: true)]
    private $pseudo;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Movie::class)]
    #[ORM\JoinTable(name: 'user_favorite_movie')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'movie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $favoriteMovies;

    public function __construct()
    {
        $this->favoriteMovies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Nom affiché publiquement : le pseudo, ou l'email à défaut (comptes créés avant son ajout)
     */
    public function getDisplayName(): string
    {
        return $this->pseudo ?: $this->email;
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Display the most powerful role of the user
     */
    public function getDisplayRole(): string
    {
        $roleToDisplay = '';
        if (in_array('ROLE_ADMIN', $this->roles)) {
            $roleToDisplay = 'Adm.';
        } elseif (in_array('ROLE_MANAGER', $this->roles)) {
            $roleToDisplay = 'Man.';
        } elseif (in_array('ROLE_USER', $this->roles)) {
            $roleToDisplay = 'Util.';
        }
        return $roleToDisplay;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFavoriteMovies(): Collection
    {
        return $this->favoriteMovies;
    }

    public function addFavoriteMovie(\App\Entity\Movie $movie): self
    {
        if (!$this->favoriteMovies->contains($movie)) {
            $this->favoriteMovies->add($movie);
        }

        return $this;
    }

    public function removeFavoriteMovie(\App\Entity\Movie $movie): self
    {
        $this->favoriteMovies->removeElement($movie);

        return $this;
    }

    public function hasFavoriteMovie(\App\Entity\Movie $movie): bool
    {
        return $this->favoriteMovies->contains($movie);
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
