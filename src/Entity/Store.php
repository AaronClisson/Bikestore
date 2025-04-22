<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="stores")
 */
class Store implements \JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $store_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $store_name;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private ?string $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $state;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private ?string $zip_code;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="store")
     */
    private Collection $employees;

    /**
     * @ORM\OneToMany(targetEntity="Stock", mappedBy="store")
     */
    private Collection $stocks;

    public function __construct() {
        $this->employees = new ArrayCollection();
        $this->stocks = new ArrayCollection();
    }

    public function __toString(): string {
        return sprintf(
            "Store [ID: %d, Name: %s, Phone: %s, Email: %s, Address: %s, %s, %s, %s]",
            $this->store_id,
            $this->store_name,
            $this->phone ?? 'N/A',
            $this->email ?? 'N/A',
            $this->street ?? 'N/A',
            $this->city ?? 'N/A',
            $this->state ?? 'N/A',
            $this->zip_code ?? 'N/A'
        );
    }

    public function jsonSerialize(): array {
        return [
            'store_id' => $this->store_id,
            'store_name' => $this->store_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ];
    }
    public function getStoreId(): int {
        return $this->store_id;
    }

    public function getStoreName(): string {
        return $this->store_name;
    }

    public function setStoreName(string $store_name): void {
        $this->store_name = $store_name;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getStreet(): ?string {
        return $this->street;
    }

    public function setStreet(?string $street): void {
        $this->street = $street;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $city): void {
        $this->city = $city;
    }

    public function getState(): ?string {
        return $this->state;
    }

    public function setState(?string $state): void {
        $this->state = $state;
    }

    public function getZipCode(): ?string {
        return $this->zip_code;
    }

    public function setZipCode(?string $zip_code): void {
        $this->zip_code = $zip_code;
    }
    public function getEmployees(): Collection {
        return $this->employees;
    }

    public function getStocks(): Collection {
        return $this->stocks;
    }    
}