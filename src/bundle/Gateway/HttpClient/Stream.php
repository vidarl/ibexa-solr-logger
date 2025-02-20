<?php

namespace VidarL\IbexaSolrLoggerBundle\Gateway\HttpClient;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\HttpClient;
use Ibexa\Solr\Gateway\HttpClient\ConnectionException;
use Ibexa\Solr\Gateway\Message;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Stream implements HttpClient, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param int $timeout Timeout for connection in seconds.
     */
    public function __construct(
        readonly HttpClient $inner,
        readonly bool $logSuccess,
        readonly bool $logError,
    )
    {
        $this->setLogger(new NullLogger());
    }

    public function request($method, Endpoint $endpoint, $path, Message $message = null): Message
    {
        try {
            $result = $this->inner->request($method, $endpoint, $path, $message);
            if ($this->logSuccess) {
                $this->logger->critical("Solr Request succeeded, dumping request information : URL : {$endpoint->getURL()}{$path}, message : " . var_export($message, true));
            }
            return $result;
        } catch (\Exception $e) {
            if ($this->logError) {
                $this->logger->critical("Error in Solr Request, dumping request information : URL : {$endpoint->getURL()}{$path}, message : " . var_export($message, true));
            }
            throw $e;
        }
    }
}
