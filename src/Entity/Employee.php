<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
/**
 * @ORM\Entity
 * @ORM\Table(name="employees")
 */
class Employee implements \JsonSerializable{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $employee_id;

    /**
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="employees")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=true)
     */
    private ?Store $store;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $employee_name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $employee_email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $employee_password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $employee_role;
    
    public function __construct() {
        $this->store = null;
    }

    public function __toString(): string {
        return sprintf(
            "Employee [ID: %d, Store ID: %s, Name: %s, Email: %s, Role: %s]",
            $this->employee_id,
            $this->store !== null ? $this->store->getStoreName() : 'N/A',
            $this->employee_name,
            $this->employee_email,
            $this->employee_role
        );
    }        

    public function jsonSerialize(): array {
        return [
            'employee_id'    => $this->employee_id,
            'store_id'       => $this->store,
            'employee_name'  => $this->employee_name,
            'employee_email' => $this->employee_email,
            'employee_role'  => $this->employee_role,
        ];
    }   
    
    public function getEmployeeId(){
        return $this->employee_id;
    }
    public function getStore(): ?Store {
        return $this->store;
    }

    public function setStore(?Store $store): void {
        $this->store = $store;
    }

    public function getEmployeeName(): string {
        return $this->employee_name;
    }

    public function setEmployeeName(string $employee_name): void {
        $this->employee_name = $employee_name;
    }

    public function getEmployeeEmail(): string {
        return $this->employee_email;
    }

    public function setEmployeeEmail(string $employee_email): void {
        $this->employee_email = $employee_email;
    }

    public function getEmployeePassword(): string {
        return $this->employee_password;
    }

    public function setEmployeePassword(string $employee_password): void {
        $this->employee_password = $employee_password;
    }

    public function getEmployeeRole(): string {
        return $this->employee_role;
    }

    public function setEmployeeRole(string $employee_role): void {
        $this->employee_role = $employee_role;
    }
}
?>