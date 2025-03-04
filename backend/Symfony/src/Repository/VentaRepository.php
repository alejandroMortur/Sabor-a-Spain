<?php

namespace App\Repository;

use App\Entity\Venta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Venta>
 */
class VentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venta::class);
    }

    /**
     * Obtiene las ventas agrupadas por categoría (tipo de producto)
     *
     * @param \DateTime|null $fechaInicio
     * @param \DateTime|null $fechaFin
     * @return array
     */
    public function findVentasPorCategoria(?\DateTime $fechaInicio = null, ?\DateTime $fechaFin = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('tp.Nombre AS categoria', 'SUM(v.Total) AS total_ventas')
            ->join('v.Cod_producto', 'p') // Relación con la entidad Producto
            ->join('p.Tipo_producto', 'tp') // Relación con la entidad Tipos
            ->groupBy('tp.Nombre') // Agrupamos por nombre del tipo (categoría)
            ->orderBy('total_ventas', 'DESC'); // Ordenar por total de ventas

        // Si se proporcionan fechas, filtrar por ellas
        if ($fechaInicio) {
            $qb->andWhere('v.Fecha >= :fechaInicio')
            ->setParameter('fechaInicio', $fechaInicio);
        }

        if ($fechaFin) {
            $qb->andWhere('v.Fecha <= :fechaFin')
            ->setParameter('fechaFin', $fechaFin);
        }

        // Ejecutar la consulta y devolver los resultados
        // Cambiar 'getResult' por 'getArrayResult' para evitar cargar entidades completas
        return $qb->getQuery()->getArrayResult();
    }


    //    /**
    //     * @return Venta[] Returns an array of Venta objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Venta
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}


