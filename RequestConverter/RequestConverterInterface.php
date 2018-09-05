<?php

namespace PE\Bundle\OAuth2ServerBundle\RequestConverter;

use Psr\Http\Message\ResponseInterface as PSRResponse;
use Psr\Http\Message\ServerRequestInterface as PSRRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

interface RequestConverterInterface
{
    /**
     * Converts symfony request to PSR-7 request
     *
     * @param SymfonyRequest $symfonyRequest
     *
     * @return PSRRequest
     */
    public function createPSRRequest(SymfonyRequest $symfonyRequest);

    /**
     * Converts symfony response to PSR-7 response
     *
     * @param SymfonyResponse|null $symfonyResponse
     *
     * @return PSRResponse
     */
    public function createPSRResponse(SymfonyResponse $symfonyResponse = null);

    /**
     * Converts PSR-7 request to symfony request
     *
     * @param PSRRequest $psrRequest
     *
     * @return SymfonyRequest
     */
    public function createSymfonyRequest(PSRRequest $psrRequest);

    /**
     * Converts PSR-7 response to symfony response
     *
     * @param PSRResponse $psrResponse
     *
     * @return SymfonyResponse
     */
    public function createSymfonyResponse(PSRResponse $psrResponse);
}