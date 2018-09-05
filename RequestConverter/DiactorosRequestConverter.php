<?php

namespace PE\Bundle\OAuth2ServerBundle\RequestConverter;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Zend\Diactoros\UploadedFile as PSRUploadedFile;

class DiactorosRequestConverter extends BaseRequestConverter
{
    /**
     * @inheritDoc
     */
    public function createPSRRequest(SymfonyRequest $symfonyRequest)
    {
        $server = ServerRequestFactory::normalizeServer($symfonyRequest->server->all());
        $headers = $symfonyRequest->headers->all();

        if (PHP_VERSION_ID < 50600) {
            $body = new Stream('php://temp', 'wb+');
            $body->write($symfonyRequest->getContent());
        } else {
            $body = new Stream($symfonyRequest->getContent(true));
        }

        $request = new ServerRequest(
            $server,
            ServerRequestFactory::normalizeFiles($this->createPSRFiles($symfonyRequest->files->all())),
            $symfonyRequest->getSchemeAndHttpHost() . $symfonyRequest->getRequestUri(),
            $symfonyRequest->getMethod(),
            $body,
            $headers
        );

        $request = $request
            ->withCookieParams($symfonyRequest->cookies->all())
            ->withQueryParams($symfonyRequest->query->all())
            ->withParsedBody($symfonyRequest->request->all())
            ->withRequestTarget($symfonyRequest->getRequestUri())
        ;

        foreach ($symfonyRequest->attributes->all() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function createPSRResponse(SymfonyResponse $symfonyResponse = null)
    {
        if ($symfonyResponse instanceof BinaryFileResponse) {
            $stream = new Stream($symfonyResponse->getFile()->getPathname(), 'r');
        } else {
            $stream = new Stream('php://temp', 'wb+');
            if ($symfonyResponse instanceof StreamedResponse) {
                ob_start(function ($buffer) use ($stream) {
                    $stream->write($buffer);

                    return false;
                });

                $symfonyResponse->sendContent();
                ob_end_clean();
            } else {
                $stream->write($symfonyResponse->getContent());
            }
        }

        $headers = $symfonyResponse->headers->all();

        $cookies = $symfonyResponse->headers->getCookies();
        if (!empty($cookies)) {
            $headers['Set-Cookie'] = array();

            foreach ($cookies as $cookie) {
                $headers['Set-Cookie'][] = $cookie->__toString();
            }
        }

        $response = new Response(
            $stream,
            $symfonyResponse->getStatusCode(),
            $headers
        );

        $protocolVersion = $symfonyResponse->getProtocolVersion();
        if ('1.1' !== $protocolVersion) {
            $response = $response->withProtocolVersion($protocolVersion);
        }

        return $response;
    }

    /**
     * Converts Symfony uploaded files array to the PSR one.
     *
     * @param array $uploadedFiles
     *
     * @return array
     */
    private function createPSRFiles(array $uploadedFiles)
    {
        $files = array();

        foreach ($uploadedFiles as $key => $value) {
            if (null === $value) {
                $files[$key] = new PSRUploadedFile(null, 0, UPLOAD_ERR_NO_FILE, null, null);
                continue;
            }
            if ($value instanceof SymfonyUploadedFile) {
                $files[$key] = new PSRUploadedFile(
                    $value->getRealPath(),
                    $value->getClientSize(),
                    $value->getError(),
                    $value->getClientOriginalName(),
                    $value->getClientMimeType()
                );
            } else {
                $files[$key] = $this->createPSRFiles($value);
            }
        }

        return $files;
    }
}