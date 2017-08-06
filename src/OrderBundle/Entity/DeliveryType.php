<?php

namespace OrderBundle\Entity;

use AppBundle\Entity\SortableTrait;
use AppBundle\Entity\VisibilityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\OrderBundle\Repository\DeliveryTypeRepository")
 * @ORM\Table(name="decarte_delivery_types")
 */
class DeliveryType
{
    use SortableTrait;
    use VisibilityTrait;

    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id = 0;

    /** @ORM\Column(type="string") */
    protected $name = '';

    /** @ORM\Column(type="integer") */
    protected $price = 0;

    /** @ORM\Column(type="boolean", name="is_personal") */
    protected $isPersonal = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price)
    {
        $this->price = $price;
        return $this->price;
    }

    public function isPersonal(): bool
    {
        return $this->isPersonal;
    }

    public function setIsPersonal(bool $flag)
    {
        $this->isPersonal = $flag;
        return $this;
    }

    public function __toString()
    {
        return $this->getName() . ' (' . ($this->getPrice() / 100) . ' PLN)';
    }
}