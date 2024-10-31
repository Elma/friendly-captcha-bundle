<?php

declare(strict_types=1);

namespace CORS\Bundle\FriendlyCaptchaBundle\Validator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FriendlyCaptchaValidValidator extends ConstraintValidator
{
    protected $httpClient;
    protected $secret;
    protected $sitekey;
    protected $endpoint;
    protected $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        string $secret,
        string $sitekey,
        string $endpoint,
        LoggerInterface $logger
    ){
        $this->httpClient = $httpClient;
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->endpoint = $endpoint;
        $this->logger = $logger;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FriendlyCaptchaValid) {
            throw new UnexpectedTypeException($constraint, FriendlyCaptchaValid::class);
        }
        if ('' === (string) $value) {
            $this->context->addViolation($constraint->message);

            return;
        }

        try {
            $response = $this->httpClient->request('POST', $this->endpoint, [
                'body' => [
                    'sitekey' => $this->sitekey,
                    'solution' => $value,
                ],
                'headers' => [
                    'X-API-Key' => $this->secret,
                ]
            ]);
            $content = $response->getContent();
        } catch (\Exception $e) {
            if (
                $e instanceof TransportException
                || ($e instanceof ServerException && $e->getCode() === 504)
            ) {
                $this->logger->error('Captcha server not responding customer has been authorized');

                return;
            }

            $this->context->addViolation($constraint->message);

            return;
        }

        if (!$content) {
            $this->context->addViolation($constraint->message);

            return;
        }

        $result = \json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('success', $result) && $result['success'] === true) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
