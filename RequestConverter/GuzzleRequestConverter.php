<?php

namespace PE\Bundle\OAuth2ServerBundle\RequestConverter;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\UploadedFile as PSRUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuzzleRequestConverter extends BaseRequestConverter
{
    /**
     * @inheritDoc
     */
    public function createPSRRequest(SymfonyRequest $symfonyRequest)
    {
        $server  = $symfonyRequest->server->all();
        $headers = $symfonyRequest->headers->all();

        if (PHP_VERSION_ID < 50600) {
            $body = new Stream(fopen('php://temp', 'wb+'));
            $body->write($symfonyRequest->getContent());
        } else {
            $body = new Stream($symfonyRequest->getContent(true));
        }

        $request = new ServerRequest(
            $symfonyRequest->getMethod(),
            $symfonyRequest->getSchemeAndHttpHost().$symfonyRequest->getRequestUri(),
            $headers,
            $body,
            $symfonyRequest->getProtocolVersion(),
            $server
        );

        $request = $request
            ->withCookieParams($symfonyRequest->cookies->all())
            ->withQueryParams($symfonyRequest->query->all())
            ->withParsedBody($symfonyRequest->request->all())
            ->withRequestTarget($symfonyRequest->getRequestUri())
            ->withUploadedFiles(ServerRequest::normalizeFiles($this->createPSRFiles($symfonyRequest->files->all())));
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
        if (null === $symfonyResponse) {
            return new Response();
        }

        if ($symfonyResponse instanceof BinaryFileResponse) {
            $stream = new Stream(fopen($symfonyResponse->getFile()->getPathname(), 'rb'));
        } else {
            $stream = new Stream(fopen('php://temp', 'wb+'));
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

        /* @var $cookies Cookie[] */
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