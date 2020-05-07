<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsumerRepository")
  * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "user_details",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 exclusion = @Hateoas\Exclusion(groups={"detail"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 exclusion = @Hateoas\Exclusion(groups={"detail"})
 * )
 * @Hateoas\Relation(
 *     "Clien id",
 *     embedded = @Hateoas\Embedded("expr(object.getClientId())"),
 *   exclusion = @Hateoas\Exclusion(groups={"detail", "list"})
 * )
 * @Hateoas\Relation(
 *     "Client Fullname",
 *     embedded = @Hateoas\Embedded("expr(object.getClientName())"),
 *   exclusion = @Hateoas\Exclusion(groups={"detail", "list"})
 * )
 */
class Consumer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list", "detail"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private $fullname;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail"})
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail"})
     */
    private $city;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"detail"})
     */
    private $addedOn;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clientName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddedOn(): ?\DateTimeInterface
    {
        return $this->addedOn;
    }

    public function setAddedOn(\DateTimeInterface $addedOn): self
    {
        $this->addedOn = $addedOn;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }
}
