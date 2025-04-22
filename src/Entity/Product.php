<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Product implements \JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $product_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $product_name;

    /**
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="brand_id", nullable=false)
     */
    private Brand $brand;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id", nullable=false)
     */
    private Category $category;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $model_year;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $list_price;

    /**
     * @ORM\OneToMany(targetEntity="Stock", mappedBy="product")
     */
    private Collection $stocks;

    public function __construct() {
        $this->stocks = new ArrayCollection();
    }

    public function __toString(): string {
        return sprintf(
            "Product [ID: %d, Name: %s, Brand ID: %d, Category ID: %d, Model Year: %d, Price: %.2f]",
            $this->product_id,
            $this->product_name,
            $this->brand,
            $this->category,
            $this->model_year,
            $this->list_price
        );
    }

    public function jsonSerialize(): array {
        return [
            'product_id'    => $this->product_id,
            'product_name'  => $this->product_name,
            'brand_id'      => $this->brand,
            'category_id'   => $this->category,
            'model_year'    => $this->model_year,
            'list_price'    => $this->list_price,
        ];
    }
    
    public function getProductId(): int {
        return $this->product_id;
    }

    public function getProductName(): string {
        return $this->product_name;
    }

    public function setProductName(string $product_name): void {
        $this->product_name = $product_name;
    }

    public function getBrand(): Brand {
        return $this->brand;
    }

    public function setBrand(Brand $brand): void {
        $this->brand = $brand;
    }

    public function getCategory(): Category {
        return $this->category;
    }

    public function setCategory(Category $category): void {
        $this->category = $category;
    }

    public function getModelYear(): int {
        return $this->model_year;
    }

    public function setModelYear(int $model_year): void {
        $this->model_year = $model_year;
    }

    public function getListPrice(): float {
        return $this->list_price;
    }

    public function setListPrice(float $list_price): void {
        $this->list_price = $list_price;
    }

    public function getStocks(): Collection {
        return $this->stocks;
    }    
}