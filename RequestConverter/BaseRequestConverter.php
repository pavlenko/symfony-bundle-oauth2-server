<?php

namespace PE\Bundle\OAuth2ServerBundle\RequestConverter;

use Psr\Http\Message\ResponseInterface as PSRResponse;
use Psr\Http\Message\ServerRequestInterface as PSRRequest;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class BaseRequestConverter implements RequestConverterInterface
{
    /**
     * @inheritDoc
     */
    public function createSymfonyRequest(PSRRequest $psrRequest)
    {
        $server = array();
        $uri = $psrRequest->getUri();

        if ($uri instanceof UriInterface) {
            $server['SERVER_NAME']  = $uri->getHost();
            $server['SERVER_PORT']  = $uri->getPort();
            $server['REQUEST_URI']  = $uri->getPath();
            $server['QUERY_STRING'] = $uri->getQuery();
        }

        $server['REQUEST_METHOD'] = $psrRequest->getMethod();

        $server = array_replace($server, $psrRequest->getServerParams());

        $parsedBody = $psrRequest->getParsedBody();
        $parsedBody = is_array($parsedBody) ? $parsedBody : array();

        $request = new SymfonyRequest(
            $psrRequest->getQueryParams(),
            $parsedBody,
            $psrRequest->getAttributes(),
            $psrRequest->getCookieParams(),
            $this->createSymfonyFiles($psrRequest->getUploadedFiles()),
            $server,
            $psrRequest->getBody()->__toString()
        );

        $request->headers->replace($psrRequest->getHeaders());

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function createSymfonyResponse(PSRResponse $psrResponse)
    {
        $response = new SymfonyResponse(
            $psrResponse->getBody()->__toString(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );

        $response->setProtocolVersion($psrResponse->getProtocolVersion());

        foreach ($psrResponse->getHeader('Set-Cookie') as $cookie) {
            $response->headers->setCookie($this->createCookie($cookie));
        }

        return $response;
    }

    /**
     * Creates a Cookie instance from a cookie string.
     *
     * Some snippets have been taken from the Guzzle project: https://github.com/guzzle/guzzle/blob/5.3/src/Cookie/SetCookie.php#L34
     *
     * @param string $cookie
     *
     * @return Cookie
     *
     * @throws \InvalidArgumentException
     */
    private function createCookie($cookie)
    {
        $cookieValue = null;

        foreach (explode(';', $cookie) as $part) {
            $part  = trim($part);
            $data  = explode('=', $part, 2);
            $name  = $data[0];
            $value = isset($data[1]) ? trim($data[1], " \n\r\t\0\x0B\"") : null;

            if (!isset($cookieName)) {
                $cookieName  = $name;
                $cookieValue = $value;
                continue;
            }

            if ('expires' === strtolower($name) && null !== $value) {
                $cookieExpire = new \DateTime($value);
                continue;
            }

            if ('path' === strtolower($name) && null !== $value) {
                $cookiePath = $value;
                continue;
            }

            if ('domain' === strtolower($name) && null !== $value) {
                $cookieDomain = $value;
                continue;
            }

            if ('secure' === strtolower($name)) {
                $cookieSecure = true;
                continue;
            }

            if ('httponly' === strtolower($name)) {
                $cookieHttpOnly = true;
                continue;
            }
        }

        if (!isset($cookieName)) {
            throw new \InvalidArgumentException('The value of the Set-Cookie header is malformed.');
        }

        return new Cookie(
            $cookieName,
            $cookieValue,
            isset($cookieExpire) ? $cookieExpire : 0,
            isset($cookiePath) ? $cookiePath : '/',
            isset($cookieDomain) ? $cookieDomain : null,
            isset($cookieSecure),
            isset($cookieHttpOnly)
        );
    }

    /**
     * Converts to the input array to $_FILES structure.
     *
     * @param array $uploadedFiles
     *
     * @return array
     */
    private function createSymfonyFiles(array $uploadedFiles)
    {
        $files = array();

        foreach ($uploadedFiles as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $temporaryPath  = '';
                $clientFileName = '';

                if (UPLOAD_ERR_NO_FILE !== $value->getError()) {
                    $temporaryPath = tempnam(sys_get_temp_dir(), uniqid('symfony', true));
                    $value->moveTo($temporaryPath);

                    $clientFileName = $value->getClientFilename();
                }

                $files[$key] = new UploadedFile(
                    $temporaryPath,
                    null === $clientFileName ? '' : $clientFileName,
                    $value->getClientMediaType(),
                    $value->getSize(),
                    $value->getError(),
                    true
                );
            } else {
                $files[$key] = $this->createSymfonyFiles($value);
            }
        }

        return $files;
    }
}