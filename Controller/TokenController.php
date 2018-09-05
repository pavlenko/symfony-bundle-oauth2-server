<?php

namespace PE\Bundle\OAuth2ServerBundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use PE\Bundle\OAuth2ServerBundle\RequestConverter\RequestConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends Controller
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var RequestConverterInterface
     */
    private $requestConverter;

    /**
     * @param AuthorizationServer $authorizationServer
     * @param RequestConverterInterface $requestConverter
     */
    public function __construct(AuthorizationServer $authorizationServer, RequestConverterInterface $requestConverter)
    {
        $this->authorizationServer = $authorizationServer;
        $this->requestConverter    = $requestConverter;
    }

    /**
     * @param Request $symfonyRequest
     *
     * @return Response
     */
    public function __invoke(Request $symfonyRequest)
    {
        $request  = $this->requestConverter->createPSRRequest($symfonyRequest);
        $response = $this->requestConverter->createPSRResponse();

        try {
            return $this->requestConverter->createSymfonyResponse(
                $this->authorizationServer->respondToAccessTokenRequest($request, $response)
            );
        } catch (OAuthServerException $exception) {
            return $this->requestConverter->createSymfonyResponse(
                $exception->generateHttpResponse($response)
            );
        } catch (\Exception $exception) {
            return new Response($exception->getMessage(), 500);
        }
    }
}