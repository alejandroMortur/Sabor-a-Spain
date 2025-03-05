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

/**
 * Controlador para gestionar operaciones relacionadas con tipos de productos
 * 
 * Provee endpoints API REST para obtener información de categorías de productos
 */
final class TiposController extends AbstractController
{

    /**
     * Repositorio de Tipos inyectado para acceso a datos
     * @var TiposRepository
     */
    private $tiposRepository;

    /**
     * Constructor que inyecta las dependencias necesarias
     * @param TiposRepository $tiposRepository Repositorio para operaciones con la entidad Tipos
     */
    public function __construct(TiposRepository $tiposRepository)
    {
        $this->tiposRepository = $tiposRepository;
    }

    /**
     * Obtiene todos los tipos de productos con campos específicos
     * 
     * @Route("/api/tipos", name="app_tipos", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @return JsonResponse Respuesta JSON estructurada con formato:
     * {
     *     "tipos": [
     *         {
     *             "id": int,
     *             "Nombre": string,
     *             "Descripcion": string,
     *             "Imagen": string|null
     *         },
     *         ...
     *     ]
     * }
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/tipos', name: 'app_tipos', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // 1. Construye consulta DQL para seleccionar campos específicos
        $tipos = $this->tiposRepository->createQueryBuilder('p')
            ->select('p.id' ,'p.Nombre', 'p.Descripcion','p.Imagen')
            ->getQuery();
    
        // 2. Ejecuta consulta y obtener resultados
        $resultados = $tipos->getResult(); 
        
        // 3. Devuelve respuesta JSON con estructura definida
        return $this->json([
            'tipos' => $resultados, 
        ]);
    }

}
