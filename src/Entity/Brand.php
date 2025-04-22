<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="brands")
 */
class Brand implements \JsonSerializable {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $brand_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $brand_name;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="brand")
     */
    private Collection $products;

    public function __construct() {
        $this->products = new ArrayCollection();
    }

    public function __toString(): string {
        return sprintf("Brand [ID: %d, Name: %s]", $this->brand_id, $this->brand_name);
    }

    public function jsonSerialize(): array {
        return [
            'brand_id'    => $this->brand_id,
            'brand_name'  => $this->brand_name,
            'products'    => $this->products,
        ];
    }
    
    public function getBrandId(): int {
        return $this->brand_id;
    }

    public function getBrandName(): string {
        return $this->brand_name;
    }

    public function setBrandName(string $brand_name): void {
        $this->brand_name = $brand_name;
    }
}