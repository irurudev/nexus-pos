<?php

// This file exists only for IDE/static analysis assistance. It must NOT be executed at runtime.

namespace Pest {
    class Expectation {
        public mixed $value;
        /** @var Expectation */
        public $not;

        public function __construct(mixed $value = null) {
            $this->value = $value;
            $this->not = $this;
        }

        public function extend($name, $callable) { return $this; }

        public function toBe(mixed $expected) : self { return $this; }
        public function toBeOne() : self { return $this; }
        public function toHaveCount(int $n) : self { return $this; }
        public function toBeNull() : self { return $this; }

        public function __call($name, $arguments) { return $this; }
    }
}

namespace {
    if (false) {
        /**
         * Define Pest globals so IDEs and static analyzers don't report undefined functions.
         * These are inside `if (false)` so they are parsed by tools but never executed.
         */

        /**
         * @param callable|string $description
         * @param callable $closure
         */
        function it(string $description, callable $closure): void {}

        /**
         * @param mixed|null $value
         * @return \Pest\Expectation
         */
        function expect(mixed $value = null): \Pest\Expectation {
            return new \Pest\Expectation($value);
        }
    }

    /**
     * @param callable $closure
     */
    function beforeEach($closure) {}

    /**
     * @param callable $closure
     */
    function afterEach($closure) {}

    /**
     * @param string $name
     * @param callable $closure
     */
    function test($name, $closure) {}

    /**
     * Pest helper stub for IDE/static analysis. Returns a chainable object.
     * @return Pest\PestStub
     */
    function pest()
    {
        return new class {
            public function extend($class) { return $this; }
            public function use($trait) { return $this; }
            public function in($dir) { return $this; }
        };
    }

    /**
     * Defines Pest 'uses' global for IDEs.
     *
     * @param mixed ...$args
     * @return object
     */
    function uses(...$args) {
        return new class {
            public function use($trait) { return $this; }
            public function in($dir) { return $this; }
        };
    }
}

