<?php

declare(strict_types=1);

namespace LaminasTest\Test\PHPUnit\Controller;

use Laminas\Router\RouteMatch;
use Laminas\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use LaminasTest\Test\ExpectedExceptionTrait;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @group      Laminas_Test
 */
class AbstractConsoleControllerTestCaseTest extends AbstractConsoleControllerTestCase
{
    use ExpectedExceptionTrait;

    protected function setUp(): void
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.php'
        );
        parent::setUp();
    }

    public function testUseOfRouter(): void
    {
        $this->assertEquals(true, $this->useConsoleRequest);
    }

    public function testAssertResponseStatusCode(): void
    {
        $this->dispatch('--console');
        $this->assertResponseStatusCode(0);

        $this->expectedException(
            ExpectationFailedException::class,
            'actual status code is "0"' // check actual status code is display
        );
        $this->assertResponseStatusCode(1);
    }

    public function testAssertNotResponseStatusCode(): void
    {
        $this->dispatch('--console');
        $this->assertNotResponseStatusCode(1);

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotResponseStatusCode(0);
    }

    public function testAssertResponseStatusCodeWithBadCode(): void
    {
        $this->dispatch('--console');
        $this->expectedException(
            ExpectationFailedException::class,
            'Console status code assert value must be O (valid) or 1 (error)'
        );
        $this->assertResponseStatusCode(2);
    }

    public function testAssertNotResponseStatusCodeWithBadCode(): void
    {
        $this->dispatch('--console');
        $this->expectedException(
            ExpectationFailedException::class,
            'Console status code assert value must be O (valid) or 1 (error)'
        );
        $this->assertNotResponseStatusCode(2);
    }

    public function testAssertConsoleOutputContains(): void
    {
        $this->dispatch('--console');
        $this->assertConsoleOutputContains('foo');
        $this->assertConsoleOutputContains('foo, bar');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "foo, bar"' // check actual content is display
        );
        $this->assertConsoleOutputContains('baz');
    }

    public function testNotAssertConsoleOutputContains(): void
    {
        $this->dispatch('--console');
        $this->assertNotConsoleOutputContains('baz');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotConsoleOutputContains('foo');
    }

    public function testAssertMatchedArgumentsWithValue(): void
    {
        $this->dispatch('filter --date="2013-03-07 00:00:00" --id=10 --text="custom text"');
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertInstanceOf(RouteMatch::class, $routeMatch, 'Did not receive a route match?');
        $this->assertEquals("2013-03-07 00:00:00", $routeMatch->getParam('date'));
        $this->assertEquals("10", $routeMatch->getParam('id'));
        $this->assertEquals("custom text", $routeMatch->getParam('text'));
    }

    /**
     * @group 6837
     */
    public function testAssertMatchedArgumentsWithMandatoryValue(): void
    {
        $this->dispatch("foo --bar='FOO' --baz='ARE'");
        /** @var \Laminas\Mvc\Router\Console\RouteMatch $routeMatch */
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertNotNull($routeMatch);
        $this->assertEquals('arguments-mandatory', $routeMatch->getMatchedRouteName());

        $this->reset();

        $this->dispatch('foo --bar="FOO" --baz="ARE"');
        /** @var \Laminas\Mvc\Router\Console\RouteMatch $routeMatch */
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertNotNull($routeMatch);
        $this->assertEquals('arguments-mandatory', $routeMatch->getMatchedRouteName());
    }

    public function testAssertMatchedArgumentsWithValueWithoutEqualsSign(): void
    {
        $this->dispatch('filter --date "2013-03-07 00:00:00" --id=10 --text="custom text"');
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertInstanceOf(RouteMatch::class, $routeMatch, 'Did not receive a route match?');
        $this->assertEquals("2013-03-07 00:00:00", $routeMatch->getParam('date'));
        $this->assertEquals("10", $routeMatch->getParam('id'));
        $this->assertEquals("custom text", $routeMatch->getParam('text'));
    }

    public function testAssertMatchedArgumentsWithLiteralFlags(): void
    {
        $this->dispatch('literal --foo --bar');
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertInstanceOf(RouteMatch::class, $routeMatch, 'Did not receive a route match?');
        $this->assertMatchedRouteName('arguments-literal');
        $this->assertTrue($routeMatch->getParam('foo'));
        $this->assertTrue($routeMatch->getParam('bar'));
        $this->assertFalse($routeMatch->getParam('optional'));
        $this->assertNull($routeMatch->getParam('doo'));

        $this->reset();

        $this->dispatch('literal --foo --bar --doo test');
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertInstanceOf(RouteMatch::class, $routeMatch, 'Did not receive a route match?');
        $this->assertMatchedRouteName('arguments-literal');
        $this->assertTrue($routeMatch->getParam('foo'));
        $this->assertTrue($routeMatch->getParam('bar'));
        $this->assertFalse($routeMatch->getParam('optional'));
        $this->assertSame('test', $routeMatch->getParam('doo'));
    }
}
