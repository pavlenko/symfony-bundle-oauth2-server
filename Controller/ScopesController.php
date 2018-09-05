<?php

namespace PE\Bundle\OAuth2ServerBundle\Controller;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PE\Bundle\OAuth2ServerBundle\Form\ScopesApproveForm;
use PE\Bundle\OAuth2ServerBundle\Repository\ScopeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopesController extends Controller
{
    /**
     * @var ScopeRepositoryInterface
     */
    private $scopeRepository;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $serverName;

    /**
     * @param ScopeRepositoryInterface $scopeRepository
     * @param string                   $sessionKey
     */
    public function __construct(ScopeRepositoryInterface $scopeRepository, $sessionKey, $serverName)
    {
        $this->scopeRepository = $scopeRepository;
        $this->sessionKey      = $sessionKey;
        $this->serverName      = $serverName;
    }

    /**
     * This action handle approve scopes form & it submitting
     *
     * @param $symfonyRequest Request
     *
     * @return Response
     */
    public function __invoke(Request $symfonyRequest)
    {
        if (!$session = $symfonyRequest->getSession()) {
            throw new \RuntimeException('Session required');
        }

        /* @var $authorizationRequest AuthorizationRequest */
        if (!$authorizationRequest = $session->get($this->sessionKey)) {
            throw new \RuntimeException('Authorization request required');
        }

        $form = $this->createForm(ScopesApproveForm::class);
        $form->handleRequest($symfonyRequest);

        if ($form->isSubmitted()) {
            /* @var $approve SubmitButton */
            $approve = $form->get('approve');
            if ($approve->isClicked()) {
                $authorizationRequest->setAuthorizationApproved(true);
            }

            return $this->redirectToRoute('pe_oauth2_server__authorize');
        }

        return $this->render('@PEOAuth2Server/Scopes/approve.html.twig', [
            'client'      => $authorizationRequest->getClient(),
            'scopes'      => $this->scopeRepository->findScopes(),
            'form'        => $form->createView(),
            'server_name' => $this->serverName,
        ]);
    }
}