<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProtectedController extends AbstractController
{
    #[Route('/api/protected', name: 'app_protected')]
    public function index(): Response
    {


    }

    #[Route('/api/protected/admin', name: 'app_protected_admin')]
    public function admin(): Response
    {


    }
}
