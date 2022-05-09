<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Movie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"show_genre"})
     * @Groups({"show_movie"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @link https://symfony.com/doc/5.4/serializer.html#using-serialization-groups-annotations
     * @Groups({"list_movie"})
     * @Groups({"list_genre"})
     * @Groups({"show_genre"})
     * @Groups({"show_movie"})
     */
    private $title;

    /**
     * @ORM\Column(type="date")
     * @Groups({"list_movie"})
     * @Groups({"show_movie"})
     * @Assert\NotBlank(message = "The release date is mandatory")
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"show_movie"})
     * @Assert\NotBlank(message = "The duration is mandatory")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"list_movie"})
     * @Groups({"show_movie"})
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $synopsis;

    /**
     * @ORM\Column(type="text")
     */
    private $summary;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list_movie"})
     */
    private $rating;

    /**
     * @ORM\Column(type="text")
     * @Groups({"list_movie", "show_genre"})
     */
    private $poster;

    /**
     * mappedBy="movie" référence la propriété dans l'autre classe (Season)
     * 
     * @ORM\OneToMany(targetEntity=Season::class, mappedBy="movie", orphanRemoval=true)
     */
    private $seasons;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="movie", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\ManyToMany(targetEntity=Genre::class, inversedBy="movies")
     * @ORM\OrderBy({"name" = "DESC"})
     * @Groups({"list_movie"})        
     * 
     * * Si on souhaite qu'un film ait au moins 1 genre
     * @Assert\Count(min=1)  
     */
    private $genres;

    /**
     * @ORM\OneToMany(targetEntity=Casting::class, mappedBy="movie", cascade={"remove"})
     * @link https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/reference/annotations-reference.html#orderby
     * @ORM\OrderBy({"creditOrder" = "ASC"})
     */
    private $castings;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"list_movie"})
     */
    private $updatedAt;


    public function __construct()
    {
        /// ArrayCollection est un tableau classique mais à la sauce POO
        // @link https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html
        $this->seasons = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->genres = new ArrayCollection();
        $this->castings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
/*
    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

        return $this;
    }
*/
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setMovie($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getMovie() === $this) {
                $season->setMovie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setMovie($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMovie() === $this) {
                $review->setMovie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres[] = $genre;
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection|Casting[]
     */
    public function getCastings(): Collection
    {
        return $this->castings;
    }

    public function addCasting(Casting $casting): self
    {
        if (!$this->castings->contains($casting)) {
            $this->castings[] = $casting;
            $casting->setMovie($this);
        }

        return $this;
    }

    public function removeCasting(Casting $casting): self
    {
        if ($this->castings->removeElement($casting)) {
            // set the owning side to null (unless already changed)
            if ($casting->getMovie() === $this) {
                $casting->setMovie(null);
            }
        }

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    // Si on veux pouvoir écrire un objet
    /*
    public function __toString(): string
    {
        return $this->title;
    } 
    */

    public function getSomethingForCastingForm()
    {
        return $this->title . ' (' . $this->duration .' min.)';
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    //! ne pas oublier au niveau de la classe : @ ORM\HasLifecycleCallbacks()
    /**
     * @ORM\PreUpdate
     */
    public function setValuesOnPreUpdate(): void
    {
        // cette function sera appelle avant chaque update

        $this->updatedAt = new DateTime('now');
        // et autre ...
        // eg : re-calcul du rating en fonction des critiques
        // enregistrer l'utilisateur qui a fait la modif, plus compliqué car on a pas de User ici
    }
}
