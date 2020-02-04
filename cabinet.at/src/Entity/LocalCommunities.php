<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocalCommunitiesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LocalCommunities
{
    /** @var int */
    const STATUS_LC_DELETED = 0;

    /** @var int */
    const STATUS_LC_ACTIVE = 1;

    /** @var int */
    const STATUS_LC_INACTIVE = 2;

    /** @var int */
    const STATUS_LC_WAITING_FOR_ACTIVATION = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="bigint")
     */
    private $edrpou;

    /**
     * @ORM\Column(type="integer")
     */
    private $registrationUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registrationDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $options;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $contactInfo = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $boundaries;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locationOld;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEdrpou(): ?string
    {
        return $this->edrpou;
    }

    public function setEdrpou(string $edrpou): self
    {
        $this->edrpou = $edrpou;

        return $this;
    }

    public function getRegistrationUser(): ?int
    {
        return $this->registrationUser;
    }

    /**
     * @param int $registrationUser
     * @return $this
     */
    public function setRegistrationUser(int $registrationUser): self
    {
        $this->registrationUser = $registrationUser;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * @ORM\PrePersist
     * @return $this
     * @throws \Exception
     */
    public function setRegistrationDate(): self
    {
        $this->registrationDate = new \DateTimeImmutable('now');

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(string $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getContactInfo(): ?array
    {
        return $this->contactInfo;
    }

    public function setContactInfo(?array $contactInfo): self
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    public function getBoundaries(): ?string
    {
        return $this->boundaries;
    }

    public function setBoundaries(string $boundaries): self
    {
        $this->boundaries = $boundaries;

        return $this;
    }

    public function getLocationOld(): ?string
    {
        return $this->locationOld;
    }

    public function setLocationOld(?string $locationOld): self
    {
        $this->locationOld = $locationOld;

        return $this;
    }
}
