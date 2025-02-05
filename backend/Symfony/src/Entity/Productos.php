<?php

namespace App\Entity;

use App\Repository\ProductosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductosRepository::class)]
class Productos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nombre = null;

    #[ORM\Column]
    private ?int $Precio = null;

    #[ORM\Column]
    private ?int $Stock = null;

    #[ORM\Column(length: 255)]
    private ?string $Descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $Imagen = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tipos $Tipo_producto = null;


    public function __construct()
    {
        $this->Tipo_producto = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): static
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->Precio;
    }

    public function setPrecio(int $Precio): static
    {
        $this->Precio = $Precio;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->Stock;
    }

    public function setStock(int $Stock): static
    {
        $this->Stock = $Stock;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(string $Descripcion): static
    {
        $this->Descripcion = $Descripcion;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->Imagen;
    }

    public function setImagen(string $Imagen): static
    {
        $this->Imagen = $Imagen;

        return $this;
    }

    /**
     * @return Collection<int, Tipos>
     */
    public function getTipoProducto(): Collection
    {
        return $this->Tipo_producto;
    }

    public function addTipoProducto(Tipos $tipoProducto): static
    {
        if (!$this->Tipo_producto->contains($tipoProducto)) {
            $this->Tipo_producto->add($tipoProducto);
            $tipoProducto->setProductos($this);
        }

        return $this;
    }

    public function removeTipoProducto(Tipos $tipoProducto): static
    {
        if ($this->Tipo_producto->removeElement($tipoProducto)) {
            // set the owning side to null (unless already changed)
            if ($tipoProducto->getProductos() === $this) {
                $tipoProducto->setProductos(null);
            }
        }

        return $this;
    }

    public function setTipoProducto(?Tipos $Tipo_producto): static
    {
        $this->Tipo_producto = $Tipo_producto;

        return $this;
    }
}
