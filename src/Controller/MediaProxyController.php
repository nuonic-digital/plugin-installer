<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class MediaProxyController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    #[OA\Post(
        path: '/api/_action/nuonic-plugin-installer/proxy',
        operationId: 'executeWebhook',
        tags: ['Admin Api'],
        parameters: [new OA\Parameter(
            parameter: 'url',
            name: 'url',
            in: 'query',
            schema: new OA\Schema(type: 'string')
        )],
        responses: [
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'MediaProxy returns data'),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Invalid input data'),
        ]
    )]
    #[Route(
        path: '/api/_action/nuonic-plugin-installer/proxy',
        name: 'api.action.nuonic_plugin_installer.proxy.execute',
        defaults: ['auth_required' => false],
        methods: ['GET']
    )]
    public function execute(Request $request): Response
    {
        /** @var string|null $url */
        $url = $request->query['url'];
        if (is_null($url) || !str_starts_with($url, 'https://raw.githubusercontent.com/')) {
            return $this->malformedRequestError();
        }

        $response = $this->client->request('GET', $url);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return new Response(status: $response->getStatusCode());
        }

        return new Response($response->getContent());
    }

    protected function malformedRequestError(): Response
    {
        return new JsonResponse(
            ['status' => 'error', 'message' => 'Request data is not formed correctly.'],
            status: 400
        );
    }
}
