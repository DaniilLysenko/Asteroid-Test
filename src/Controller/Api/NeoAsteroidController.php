<?php

namespace App\Controller\Api;

use App\Entity\Asteroid;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NeoAsteroidController
 * @package App\Controller\Api
 * @Route("/api/neo")
 */
class NeoAsteroidController extends AbstractController
{
    /**
     * @Route("/hazardous", methods={"GET"})
     */
    public function hazardous(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $asteroids = $this->getDoctrine()->getRepository(Asteroid::class)->findHazardousQuery();

        return $this->json([
            'asteroids' => $paginator->paginate(
                $asteroids,
                $request->query->getInt('page', 1),
                10
            )
        ]);
    }

    /**
     * @Route("/fastest", methods={"GET"})
     */
    public function fastest(Request $request)
    {
        $asteroid = $this->getDoctrine()->getRepository(Asteroid::class)->findFastest((bool) $request->query->get('hazardous', false));

        return $this->json([
            'asteroid' => $asteroid
        ]);
    }
}