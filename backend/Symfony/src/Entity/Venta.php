<?php

namespace App\Entity;

use App\Repository\VentaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VentaRepository::class)]
class Venta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Cantidad = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $Fecha = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Productos $Cod_producto = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $Cod_usuario = null;

    #[ORM\Column(nullable: true)]
    private ?float $Total = null;
    public function getCantidad(): ?int
    {
        return $this->Cantidad;
    }

    public function setCantidad(int $Cantidad): static
    {
        $this->Cantidad = $Cantidad;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->Fecha;
    }

    public function setFecha(\DateTimeInterface $Fecha): static
    {
        $this->Fecha = $Fecha;

        return $this;
    }

    public function getCodProducto(): ?Productos
    {
        return $this->Cod_producto;
    }

    public function setCodProducto(?Productos $Cod_producto): static
    {
        $this->Cod_producto = $Cod_producto;

        return $this;
    }

    public function getCodUsuario(): ?Usuario
    {
        return $this->Cod_usuario;
    }

    public function setCodUsuario(?Usuario $Cod_usuario): static
    {
        $this->Cod_usuario = $Cod_usuario;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->Total;
    }

    public function setTotal(?float $Total): static
    {
        $this->Total = $Total;

        return $this;
    }
}
