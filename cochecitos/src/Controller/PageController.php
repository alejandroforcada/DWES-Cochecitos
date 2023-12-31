<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Coche;

class PageController extends AbstractController
{
    #[Route('/page', name: 'app_page')]
    public function index(): Response
    {
        return $this->render('page/index.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    #[Route('/', name:'inicio')]
    public function buscar(ManagerRegistry $doctrine ):Response
        {
            $repositorio=$doctrine->getRepository(Coche::class);
            $coches=$repositorio->findAll();
            return $this -> render ('lista_coches.html.twig', [
                'coches' => $coches
            ]);
        }
}
