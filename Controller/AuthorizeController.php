<?php

namespace PE\Bundle\OAuth2ServerBundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use PE\Bundle\OAuth2ServerBundle\Event\UserEvent;
use PE\Bundle\OAuth2ServerBundle\Model\User;
use PE\Bundle\OAuth2ServerBundle\Repository\ScopeRepositoryInterface;
use PE\Bundle\OAuth2ServerBundle\RequestConverter\RequestConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeController extends Controller
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var ScopeRepositoryInterface
     */
    private $scopeRepository;

    /**
     * @var RequestConverterInterface
     */
    private $requestConverter;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $loginPath;

    /**
     * @param AuthorizationServer       $authorizationServer
     * @param ScopeRepositoryInterface  $scopeRepository
     * @param RequestConverterInterface $requestConverter
     * @param string                    $sessionKey
     * @param string                    $loginPath
     */
    public function __construct(
        AuthorizationServer $authorizationServer,
        ScopeRepositoryInterface $scopeRepository,
        RequestConverterInterface $requestConverter,
        $sessionKey,
        $loginPath
    ) {
        $this->authorizationServer = $authorizationServer;
        $this->scopeRepository     = $scopeRepository;
        $this->requestConverter    = $requestConverter;

        $this->sessionKey = $sessionKey;
        $this->loginPath  = $loginPath;
    }

    /**
     * @param Request $symfonyRequest
     *
     * @return Response
     */
    public function authorizeAction(Request $symfonyRequest)
    {
        $request  = $this->requestConverter->createPSRRequest($symfonyRequest);
        $response = $this->requestConverter->createPSRResponse();

        try {
            // Check session enabled
            if (!$session = $symfonyRequest->getSession()) {
                throw new \RuntimeException('Session required');
            }

            // Check authorization request in progress, else validate new
            if (!$authorizationRequest = $session->get($this->sessionKey)) {
                $authorizationRequest = $this->authorizationServer->validateAuthorizationRequest($request);
                $session->set($this->sessionKey, $authorizationRequest);
            }

            $hasScopes = $this->scopeRepository->countScopes();

            // Check user is logged in, else redirect to login
            if (!is_object($user = $this->getUser())) {
                $login = Request::create($this->loginPath);
                $login->query->set('_target_path', $this->generateUrl('pe_oauth2_server__authorize'));

                return $this->redirect($login->getUri());
            }

            // Check user is converted
            if (!$authorizationRequest->getUser()) {
                $event = new UserEvent(null, null, $user);

                // Dispatch user event to resolve identifier
                $this->get('event_dispatcher')->dispatch(UserEvent::GET_USER_BY_OBJECT, $event);

                // Set user instance only if identifier resolved
                $authorizationRequest->setUser($event->getIdentifier() ? new User($event->getIdentifier()) : null);
            }

            // Check if has scopes to approve, else force approve
            if (!$hasScopes) {
                $authorizationRequest->setAuthorizationApproved(true);
            }

            // Check if need to approve authorization
            if (!$authorizationRequest->isAuthorizationApproved()) {
                return $this->redirectToRoute('pe_oauth2_server__scopes_approve');
            }

            // Session no more need
            $session->remove($this->sessionKey);

            // All done
            return $this->requestConverter->createSymfonyResponse(
                $this->authorizationServer->completeAuthorizationRequest($authorizationRequest, $response)
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