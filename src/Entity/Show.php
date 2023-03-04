<?php

namespace App\Entity;

use App\Repository\ShowRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: ShowRepository::class)]
#[ORM\Table(name: '`show`')]
#[Uploadable]
class Show
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Constraints\GreaterThan(propertyPath: "date_start", message: "La date de fin doit être après la date de début")]

    private ?\DateTimeInterface $date_end = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'shows')]
    private Collection $categories;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $showPosterImage = null;


    #[UploadableField(mapping: "show_poster_images", fileNameProperty: "showPosterImage")]
    private ?File $showPosterImageFile = null;

    /** @var \DateTimeImmutable|null */
    protected $updatedAt;

    #[ORM\OneToMany(mappedBy: 'correspondingShow', targetEntity: Reservation::class)]
    private Collection $reservations;



    public function __construct()
    {
        $this->categories = new ArrayCollection();

        $this->date_start = new DateTime('now');
        $this->date_end = new DateTime('now +1hours');
        $this->reservations = new ArrayCollection();
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

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getShowPosterImage(): ?string
    {
        return $this->showPosterImage;
    }

    public function setShowPosterImage(?string $showPosterImage): self
    {
        $this->showPosterImage = $showPosterImage;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function setShowPosterImageFile(File $showPosterImageFile = null)
    {
        if ($showPosterImageFile) {
            $this->showPosterImageFile = $showPosterImageFile;
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getShowPosterImageFile()
    {
        return $this->showPosterImageFile;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setCorrespondingShow($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCorrespondingShow() === $this) {
                $reservation->setCorrespondingShow(null);
            }
        }

        return $this;
    }

    public function remainingPlaces(): int {
        return $this->getReservations()->reduce(function($acc, $val) {
            return $acc + $val->getSeats()->count();
        }, 0);
    }

    public function __toString(): string
    {
        return $this->getName();
    }



}
