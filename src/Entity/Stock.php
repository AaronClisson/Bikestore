<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="stocks")
 */
class Stock implements \JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $stock_id;

    /**
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="stocks")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=false)
     */
    private Store $store;

    /**
     * @ORM\Column(type="integer")
     */
    private int $product_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity;
    

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stocks")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    private Product $product;

    public function __toString(): string {
        return sprintf(
            "Stock [ID: %d, Store ID: %d, Product ID: %d, Quantity: %s]",
            $this->stock_id,
            $this->store,
            $this->product_id,
            $this->quantity !== null ? $this->quantity : 'N/A'
        );
    }

    public function jsonSerialize(): array {
        return [
            'stock_id'    => $this->stock_id,
            'store_id'    => $this->store,
            'product_id'  => $this->product_id,
            'quantity'    => $this->quantity,
        ];
    }

    public function getStockId(): int {
        return $this->stock_id;
    }

    public function getStore(): Store {
        return $this->store;
    }

    public function setStore(Store $store): void {
        $this->store = $store;
    }

    public function getProductId(): int {
        return $this->product_id;
    }

    public function setProductId(int $product_id): void {
        $this->product_id = $product_id;
    }

    public function getQuantity(): ?int {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void {
        $this->quantity = $quantity;
    }

    public function getProduct(): Product {
        return $this->product;
    }

    public function setProduct(Product $product): void {
        $this->product = $product;
    }

    
}