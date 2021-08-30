<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Tools\Yandex\API\YandexMapsAPI;
use App\Tools\Yandex\API\MapsAPIResponseTransformer;
use App\Repository\Redis\MapPointRedisRepository;

/**
 * @Route("/api")
*/
class MapPointRESTController extends AbstractController
{
    protected $repo = null;

    public function __construct(MapPointRedisRepository $repository) {
        $this->repo = $repository;
    }

    /**
     * @Route("/search/{filter}", name="api_search")
     */
    public function index(Request $request): Response
    {
        $transformer = new MapsAPIResponseTransformer();
        $yandexMapsApi = new YandexMapsAPI(getenv('YANDEX_MAPS_API_TOKEN'), $transformer, $this->repo);
        $dto = $yandexMapsApi->search($request->get('filter'));
        // internal serializer returns 'empty' field
        return $this->json($dto);
    }

    public function error(Request $request): Response {
        return new Response('{}', Response::HTTP_NOT_FOUND, ['content-type' => 'application/json']);
    }
}
