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
        readonly ?string $logToFile,
    )
    {
        $this->setLogger(new NullLogger());
    }

    public function request($method, Endpoint $endpoint, $path, Message $message = null): Message
    {
        try {
            $result = $this->inner->request($method, $endpoint, $path, $message);
            if ($this->logSuccess) {
                $logMessage = "Successful Solr Request, dumping request information : URL : {$endpoint->getURL()}{$path}, URL size : " . strlen($endpoint->getURL() . $path) . ", Header and Content size (approx) : " . strlen(var_export($message, true)) . ", message : " . var_export($message, true);
                if ($this->logToFile === null) {
                    $this->logger->critical($logMessage);
                } else {
                    file_put_contents($this->logToFile, $logMessage . "\n", FILE_APPEND);
                }
            }
            return $result;
        } catch (\Exception $e) {
            if ($this->logError) {
                $logMessage = "Error in Solr Request, dumping request information : URL : {$endpoint->getURL()}{$path}, URL size : " . strlen($endpoint->getURL() . $path) . ", Header and Content size (approx) : " . strlen(var_export($message, true)) . ", message : " . var_export($message, true);
                if ($this->logToFile === null) {
                    $this->logger->critical($logMessage);
                } else {
                    file_put_contents($this->logToFile, $logMessage . "\n", FILE_APPEND);
                }
            }
            throw $e;
        }
    }
}
