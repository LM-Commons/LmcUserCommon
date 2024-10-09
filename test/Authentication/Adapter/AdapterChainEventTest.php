<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Authentication\Adapter;

use Laminas\Stdlib\RequestInterface;
use Lmc\User\Core\Authentication\Adapter\AdapterChainEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterChainEvent::class)]
class AdapterChainEventTest extends TestCase
{
    /**
     * The object to be tested.
     */
    protected AdapterChainEvent $event;

    /**
     * Prepare the object to be tested.
     */
    protected function setUp(): void
    {
        $this->event = new AdapterChainEvent();
    }

    public function testCodeAndMessages(): void
    {
        $testCode     = 103;
        $testMessages = ['Message received loud and clear.'];

        $this->event->setCode($testCode);
        $this->assertEquals($testCode, $this->event->getCode(), "Asserting code values match.");

        $this->event->setMessages($testMessages);
        $this->assertEquals($testMessages, $this->event->getMessages(), "Asserting messages values match.");
    }

    public function testIdentity(): void
    {
        $testCode     = 123;
        $testMessages = ['The message.'];
        $testIdentity = 'the_user';

        $this->event->setCode($testCode);
        $this->event->setMessages($testMessages);

        $this->event->setIdentity($testIdentity);

        $this->assertEquals($testCode, $this->event->getCode(), "Asserting the code persisted.");
        $this->assertEquals($testMessages, $this->event->getMessages(), "Asserting the messages persisted.");
        $this->assertEquals($testIdentity, $this->event->getIdentity(), "Asserting the identity matches");

        $this->event->setIdentity();

        $this->assertNull($this->event->getCode(), "Asserting the code has been cleared.");
        $this->assertEquals([], $this->event->getMessages(), "Asserting the messages have been cleared.");
        $this->assertNull($this->event->getIdentity(), "Asserting the identity has been cleared");
    }

    /**
     * @throws Exception
     */
    public function testRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $this->event->setRequest($request);

        $this->assertInstanceOf(RequestInterface::class, $this->event->getRequest());
    }
}
