<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Controller;

use NuonicPluginInstaller\Service\PackageService;
use NuonicPluginInstaller\Struct\PackageVersion;
use OpenApi\Attributes as OA;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class InstallController extends AbstractController
{
    public function __construct(
        private readonly PackageService $packageService,
    ) {
    }

    #[OA\Post(
        path: '/api/_action/nuonic-plugin-installer/install',
        operationId: 'executeWebhook',
        requestBody: new OA\RequestBody(content: new OA\JsonContent()),
        tags: ['Admin Api'],
        responses: [
            new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'MediaProxy returns data'),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Invalid input data'),
        ]
    )]
    #[Route(
        path: '/api/_action/nuonic-plugin-installer/install',
        name: 'api.action.nuonic_plugin_installer.install.execute',
        defaults: ['auth_required' => false],
        methods: ['POST']
    )]
    public function execute(Request $request, Context $context): Response
    {
        $requestData = $request->toArray();
        if (!isset($requestData['package'], $requestData['version'])) {
            $this->malformedRequestError();
        }

        $this->packageService->install(new PackageVersion(
            $requestData['package'],
            $requestData['version']
        ), $context);

        return new Response(status: 201);
    }

    protected function malformedRequestError(): Response
    {
        return new JsonResponse(
            ['status' => 'error', 'message' => 'Request data is not formed correctly.'],
            status: 400
        );
    }
}
