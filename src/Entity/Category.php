<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class Category implements \JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $category_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $category_name;

    /** @var Collection */
    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     */
    private Collection $products;

    public function __construct() {
        $this->products = new ArrayCollection();
    }

    public function __toString(): string {
        return sprintf("Category [ID: %d, Name: %s]", $this->category_id, $this->category_name);
    }
    
    public function jsonSerialize(): array {
        return [
            'category_id'    => $this->category_id,
            'category_name'  => $this->category_name,
            'products'       => $this->products,
        ];
    }   
    
    public function getCategoryId(): int {
        return $this->category_id;
    }

    public function getCategoryName(): string {
        return $this->category_name;
    }

    public function setCategoryName(string $category_name): void {
        $this->category_name = $category_name;
    }

    
}