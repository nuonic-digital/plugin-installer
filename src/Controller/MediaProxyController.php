<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class MediaProxyController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
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

        $response = $this->cache->get($url, function (ItemInterface $item) use ($url): array {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();

            if (Response::HTTP_OK !== $statusCode) {
                // do not cache error / not found responses
                $item->expiresAfter(0);
            }

            return [
                'statusCode' => $statusCode,
                'content' => $response->getContent(false),
            ];
        });

        if (Response::HTTP_OK !== $response['statusCode']) {
            new Response(status: $response['statusCode']);
        }

        return new Response($response['content']);
    }

    protected function malformedRequestError(): Response
    {
        return new JsonResponse(
            ['status' => 'error', 'message' => 'Request data is not formed correctly.'],
            status: 400
        );
    }
}
