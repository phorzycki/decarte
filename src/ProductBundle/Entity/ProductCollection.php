<?php

namespace ProductBundle\Entity;

use AppBundle\Entity\SortableTrait;
use AppBundle\Entity\VisibilityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="\ProductBundle\Repository\ProductCollectionRepository")
 * @ORM\EntityListeners({"\AppBundle\Entity\SortListener"})
 * @ORM\Table(name="decarte_product_collections")
 */
class ProductCollection
{
    use SortableTrait;
    use VisibilityTrait;

    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    protected $id = 0;
    
    /** @ORM\Column(type="string") */
    protected $name = '';
    
    /** @ORM\Column(type="string", name="slug_name") */
    protected $slugName = '';
    
    /** @ORM\Column(type="text", name="short_description") */
    protected $shortDescription = '';
    
    /** @ORM\Column(type="text") */
    protected $description = '';
    
    /** @ORM\Column(type="string", name="image_name", nullable=true) */
    protected $imageName = '';

    /**
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="productCollections")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id")
     */
    protected $productType = null;
    
    /**
     * @ORM\OneToMany(targetEntity="ProductSeries", mappedBy="productCollection")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    protected $productSeries = null;
   
    public function __construct()
    {
        $this->productSeries = new ArrayCollection();
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getProductSeries()
    {
        return $this->productSeries;
    }
    
    public function getProductType(): ProductType
    {
        return $this->productType;
    }
    
    public function setProductType(ProductType $type)
    {
        $this->productType = $type;
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
    
    public function getSlugName(): string
    {
        return $this->slugName;
    }
    
    public function setSlugName(string $name)
    {
        $this->slugName = $name;
        return $this;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = (string) $description;
        return $this;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }
    
    public function setShortDescription($description)
    {
        $this->shortDescription = (string) $description;
        return $this;
    }

    public function getImageName()
    {
        return $this->imageName;
    }
    
    public function setImageName($image)
    {
        $this->imageName = $image;
        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}