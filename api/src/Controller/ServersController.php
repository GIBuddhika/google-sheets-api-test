<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Handler\ServersHandler;
use Symfony\Component\HttpFoundation\Request;

class ServersController extends AbstractController
{
    private $serversHandler;

    public function __construct(ServersHandler $serversHandler)
    {
        $this->serversHandler = $serversHandler;
    }

    #[Route('/get-locations', name: 'get_locations')]
    public function getLocations()
    {
        try {
            $serverData = $this->serversHandler->readGoogleSheet();

            $uniqueLocations = $this->serversHandler->getLocations($serverData);

            return new JsonResponse($uniqueLocations);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'errorCode' => 400,
                'message' => 'Invalid request'
            ]);
        }
    }

    #[Route('/filter-servers', name: 'filter_servers')]
    public function filterServers(Request $request)
    {
        try {
            $filters = $request->query->all();

            $serverData = $this->serversHandler->readGoogleSheet();

            $filteredServers = $this->serversHandler->filterServers($filters, $serverData);

            return new JsonResponse($filteredServers);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'errorCode' => 400,
                'message' => 'Invalid request'
            ]);
        }
    }
}
