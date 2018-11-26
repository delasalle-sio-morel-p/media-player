<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 */
class Genre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="genre")
     */
    private $medias;

    /**
     * @var typeMedia
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeMedia", inversedBy="genres")
     */
    private $typeMedia;


    /**
     * @return typeMedia
     */
    public function getTypeMedia(): ?typeMedia
    {
        return $this->typeMedia;
    }

    /**
     * @param typeMedia $typeMedia
     */
    public function setTypeMedia(typeMedia $typeMedia)
    {
        $this->typeMedia = $typeMedia;
    }

    /**
     * @return ArrayCollection
     */
    public function getMedias(): ArrayCollection
    {
        return $this->medias;
    }

    /**
     * @param ArrayCollection $medias
     */
    public function setMedias(ArrayCollection $medias)
    {
        $this->medias = $medias;
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
}
