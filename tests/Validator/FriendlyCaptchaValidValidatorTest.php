<?php

declare(strict_types=1);

namespace CORS\Tests\Bundle\FriendlyCaptchaBundle\Validator;

use CORS\Bundle\FriendlyCaptchaBundle\Validator\FriendlyCaptchaValid;
use CORS\Bundle\FriendlyCaptchaBundle\Validator\FriendlyCaptchaValidValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FriendlyCaptchaValidValidatorTest extends TestCase
{
    public function testValidateFalse(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            return new MockResponse('{"success": false}');
        });

        $context->expects(self::once())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('foo', $this->createMock(FriendlyCaptchaValid::class));
    }

    public function testValidateTrue(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            return new MockResponse('{"success": true}');
        });

        $context->expects(self::never())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('bar', $this->createMock(FriendlyCaptchaValid::class));
    }

    public function testValidateFalseWhenValueIsEmpty(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            throw new \Exception('I should not be called.');
        });
        $context->expects(self::once())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('', $this->createMock(FriendlyCaptchaValid::class));
    }

    public function testValidateTrueWhenServerNotResponding(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            throw new ServerException(
                new MockResponse('', [
                    'http_code' => 504
                ])
            );
        });

        $context->expects(self::never())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('bar', $this->createMock(FriendlyCaptchaValid::class));
    }

    public function testValidateFalseWhenServerNotResponding(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            throw new ServerException(
                new MockResponse('', [
                    'http_code' => 500
                ])
            );
        });
        $context->expects(self::once())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('foo', $this->createMock(FriendlyCaptchaValid::class));
    }

    public function testValidateTrueWhenTransportException(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $client = new MockHttpClient(static function() {
            throw new TransportException();
        });
        $context->expects(self::never())
            ->method('addViolation');
        $context->expects(self::never())
            ->method('buildViolation');

        $validator = new FriendlyCaptchaValidValidator($client, 'secret', 'sitekey', '', $logger);
        $validator->initialize($context);
        $validator->validate('foo', $this->createMock(FriendlyCaptchaValid::class));
    }
}
