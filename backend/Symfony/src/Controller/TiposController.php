<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Tipos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TiposRepository;

final class TiposController extends AbstractController
{

    private $tiposRepository;

    public function __construct(TiposRepository $tiposRepository)
    {
        $this->tiposRepository = $tiposRepository;
    }

    #[Route('/api/tipos', name: 'app_tipos', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Crear la consulta para obtener solo el nombre y la descripcion
        $tipos = $this->tiposRepository->createQueryBuilder('p')
            ->select('p.id' ,'p.Nombre', 'p.Descripcion','p.Imagen') // Asegúrate de que sean los nombres correctos de las propiedades
            ->getQuery();
    
        // Obtener los resultados
        $resultados = $tipos->getResult(); // Devuelve todos los registros de tipos con los campos seleccionados
        
        // Devolver solo los campos nombre y descripcion
        return $this->json([
            'tipos' => $resultados, // Devuelve solo los resultados con nombre y descripcion
        ]);
    }
    
}
