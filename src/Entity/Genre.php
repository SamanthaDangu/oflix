<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GenreRepository::class)
 */
class Genre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_genre"})
     * @Groups({"show_genre"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"list_genre", "list_movie", "show_genre"})
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Your genre name must be at least {{ limit }} characters long",
     *      maxMessage = "Your genre name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Movie::class, mappedBy="genres")
     * @Groups({"list_genre"})
     */
    private $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Movie[]
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): self
    {
        if (!$this->movies->contains($movie)) {
            $this->movies[] = $movie;
            $movie->addGenre($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): self
    {
        if ($this->movies->removeElement($movie)) {
            $movie->removeGenre($this);
        }

        return $this;
    }

    /**
     * Spécial pour l'API
     * Renvoit le nombre de films associés 
     * 
     * !Groups on "App\Entity\Genre::countMovie()" cannot be added. 
     * !Groups can only be added on methods beginning with "get", "is", "has" or "set".
     * 
     * @Groups({"show_genre"})
     */    
    public function getCountMovie(): int
    {
        return count($this->movies);
    }
}
