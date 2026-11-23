<?php

namespace Tests\Unit\Core;

use Core\Container;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContainerTest extends TestCase
{
    public function test_resolves_bound_and_autowired_dependencies(): void
    {
        $container = new Container();
        $explicit = new stdClass();
        $container->set(stdClass::class, static fn () => $explicit);

        $result = $container->build(ContainerWithDefaults::class);
        self::assertSame($explicit, $result->explicit);
        self::assertInstanceOf(ContainerDependency::class, $result->autowired);
        self::assertSame('example', $result->label);
        self::assertSame(7, $result->port);
        self::assertSame($result->autowired, $container->get(ContainerDependency::class));
        self::assertSame($result->autowired, $container->build(ContainerWithDefaults::class)->autowired);
    }

    public function test_explains_why_a_binding_or_class_cannot_be_resolved(): void
    {
        $container = new Container();
        $cases = [
            [fn () => $container->get('missing'), 'Target binding [missing] does not exist.'],
            [fn () => $container->build('MissingContainerClass'), 'does not exist'],
            [fn () => $container->build(\Countable::class), 'is not instantiable'],
            [fn () => $container->build(ContainerUnresolvable::class), 'Unresolvable dependency'],
        ];

        foreach ($cases as [$operation, $message]) {
            try {
                $operation();
                self::fail('Expected dependency resolution to fail');
            } catch (Exception $error) {
                self::assertStringContainsString($message, $error->getMessage());
            }
        }
    }
}

final class ContainerDependency {}

final class ContainerWithDefaults
{
    public function __construct(
        public stdClass $explicit,
        public ContainerDependency $autowired,
        public $label = 'example',
        public int $port = 7,
    ) {}
}

final class ContainerUnresolvable
{
    public function __construct(public string $required) {}
}
