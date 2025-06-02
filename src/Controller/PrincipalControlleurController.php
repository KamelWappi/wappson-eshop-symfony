<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrincipalControlleurController extends AbstractController
{
    #[Route('/principal/controlleur', name: 'app_principal_controlleur')]

    public function index(): Response
    {
        return $this->render('principal_controlleur/index.html.twig', [
            'controller_name' => 'PrincipalControlleurController',
        ]);
    }
}
