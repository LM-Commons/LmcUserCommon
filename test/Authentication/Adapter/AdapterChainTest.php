<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Authentication\Adapter;

use Laminas\Authentication\Adapter\Exception\ExceptionInterface;
use Laminas\Authentication\Result;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ResponseCollection;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\Response;
use Laminas\Stdlib\ResponseInterface;
use Lmc\User\Common\Authentication\Adapter\AdapterChain;
use Lmc\User\Common\Authentication\Adapter\AdapterChainEvent;
use Lmc\User\Common\Authentication\Adapter\ChainableAdapterInterface;
use Lmc\User\Common\Authentication\Storage\Db;
use Lmc\User\Common\Exception\AuthenticationEventException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function call_user_func;

#[CoversClass(AdapterChain::class)]
class AdapterChainTest extends TestCase
{
    /**
     * The object to be tested.
     */
    protected AdapterChain $adapterChain;

    /**
     * Mock event manager.
     */
    protected MockObject|EventManagerInterface $eventManager;

    /**
     * Mock event manager.
     */
    protected MockObject|SharedEventManagerInterface $sharedEventManager;

    /**
     * For tests where an event is required.
     */
    protected MockObject|EventInterface|null $event;

    /**
     * Used when testing prepareForAuthentication.
     */
    protected MockObject|RequestInterface|null $request;

    /**
     * Prepare the objects to be tested.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->event   = null;
        $this->request = null;

        $this->adapterChain = new AdapterChain();

        $this->sharedEventManager = $this->createMock(SharedEventManagerInterface::class);
        //$this->sharedEventManager->expects($this->any())->method('getListeners')->will($this->returnValue([]));

        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->eventManager->expects($this->any())->method('getSharedManager')->willReturn($this->sharedEventManager);
        $this->eventManager->expects($this->any())->method('setIdentifiers');

        $this->adapterChain->setEventManager($this->eventManager);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testAuthenticate(): void
    {
        $event = $this->createMock(AdapterChainEvent::class);
        $event->expects($this->once())
            ->method('getCode')
            ->willReturn(123);
        $event->expects($this->once())
            ->method('getIdentity')
            ->willReturn('identity');
        $event->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->sharedEventManager->expects($this->once())
            ->method('getListeners')
            ->with($this->equalTo(['authenticate']), $this->equalTo('authenticate'))
            ->willReturn([]);

        $this->adapterChain->setEvent($event);
        $result = $this->adapterChain->authenticate();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(123, $result->getCode());
        $this->assertEquals('identity', $result->getIdentity());
        $this->assertEquals([], $result->getMessages());
    }

    public function testResetAdapters(): void
    {
        $listeners = [];

        for ($i = 1; $i <= 3; $i++) {
            $storage = $this->createMock(Db::class);
            $storage->expects($this->once())
                ->method('clear');

            $adapter = $this->createMock(ChainableAdapterInterface::class);
            $adapter->expects($this->once())
                ->method('getStorage')
                ->willReturn($storage);

            $callback    = [$adapter, 'authenticate'];
            $listeners[] = $callback;
        }

        $this->sharedEventManager->expects($this->once())
            ->method('getListeners')
            ->with($this->equalTo(['authenticate']), $this->equalTo('authenticate'))
            ->willReturn($listeners);

        $result = $this->adapterChain->resetAdapters();

        $this->assertInstanceOf(AdapterChain::class, $result);
    }

    /**
     * Get through the first part of SetUpPrepareForAuthentication
     *
     * @throws Exception
     */
    protected function setUpPrepareForAuthentication(): ResponseCollection
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->event   = $this->createMock(AdapterChainEvent::class);

        $this->event->expects($this->once())->method('setRequest')->with($this->request);

        $this->event->setName(AdapterChainEvent::AUTHENTICATE_PRE);
        $this->eventManager->expects($this->atLeastOnce())->method('triggerEvent')->with($this->event);

        /** @var ResponseCollection $responses */
        $responses = $this->createMock(ResponseCollection::class);

        $this->event->setName(AdapterChainEvent::AUTHENTICATE);
        $this->eventManager->expects($this->atLeastOnce())
            ->method('triggerEventUntil')
            ->with(
                function ($test) {
                    return $test instanceof Response;
                },
                $this->event
            )
            ->willReturnCallback(
                function ($callback) use ($responses) {
                    if (call_user_func($callback, $responses->last())) {
                        $responses->setStopped(true);
                    }
                    return $responses;
                }
            );

        $this->adapterChain->setEvent($this->event);

        return $responses;
    }

    /**
     * Provider for testPrepareForAuthentication()
     */
    public static function identityProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * Tests prepareForAuthentication when falls through events.
     *
     * @param mixed $identity
     * @param bool $expected
     * @throws Exception
     */
    #[DataProvider('identityProvider')]
    public function testPrepareForAuthentication($identity, $expected): void
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->once())->method('stopped')->willReturn(false);

        $this->event->expects($this->once())->method('getIdentity')->willReturn($identity);

        $this->assertEquals(
            $expected,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting prepareForAuthentication() returns true'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     */
    public function testPrepareForAuthenticationWithStoppedEvent(): void
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->once())->method('stopped')->willReturn(true);

        $lastResponse = $this->createMock(ResponseInterface::class);
        $result->expects($this->atLeastOnce())->method('last')->willReturn($lastResponse);

        $this->assertEquals(
            $lastResponse,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting the Response returned from the event is returned'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     */
    public function testPrepareForAuthenticationWithBadEventResult(): void
    {
        $this->expectException(AuthenticationEventException::class);
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->once())->method('stopped')->willReturn(true);

        $lastResponse = 'random-value';
        $result->expects($this->atLeastOnce())->method('last')->willReturn($lastResponse);

        $this->adapterChain->prepareForAuthentication($this->request);
    }

    /**
     * Test getEvent() when no event has previously been set.
     */
    public function testGetEventWithNoEventSet(): void
    {
        $event = $this->adapterChain->getEvent();

        $this->assertInstanceOf(
            AdapterChainEvent::class,
            $event,
            'Asserting the adapter in an instance of LmcUser\Authentication\Adapter\AdapterChainEvent'
        );
        $this->assertEquals(
            $this->adapterChain,
            $event->getTarget(),
            'Asserting the Event target is the AdapterChain'
        );
    }

    /**
     * Test getEvent() when an event has previously been set.
     */
    public function testGetEventWithEventSet(): void
    {
        $event = new AdapterChainEvent();

        $this->adapterChain->setEvent($event);

        $this->assertEquals(
            $event,
            $this->adapterChain->getEvent(),
            'Asserting the event fetched is the same as the event set'
        );
    }

    /**
     * Tests the mechanism for casting one event type to AdapterChainEvent
     */
    public function testSetEventWithDifferentEventType(): void
    {
        $testParams = ['testParam' => 'testValue'];

        $event = new Event();
        $event->setParams($testParams);

        $this->adapterChain->setEvent($event);
        $returnEvent = $this->adapterChain->getEvent();

        $this->assertInstanceOf(
            AdapterChainEvent::class,
            $returnEvent,
            'Asserting the adapter in an instance of LmcUser\Authentication\Adapter\AdapterChainEvent'
        );

        $this->assertEquals(
            $testParams,
            $returnEvent->getParams(),
            'Asserting event parameters match'
        );
    }

    /**
     * Test the logoutAdapters method.
     *
     * @depends testGetEventWithEventSet
     */
    public function testLogoutAdapters(): void
    {
        $event = new AdapterChainEvent();
        $event->setName('logout');
        $this->eventManager
            ->expects($this->once())
            ->method('triggerEvent')
            ->with($event);

        $this->adapterChain->setEvent($event);
        $this->adapterChain->logoutAdapters();
    }
}
