<?php
namespace {

    class foo {

        function bar($arg, $arg2) {
            return __METHOD__ . " got $arg and $arg2";
        }
    }

    class myclass {

        static function say_hello() {
            return "Hello!";
        }
    }

    class A {

        public static function who() {
            return "A";
        }
    }

    class B extends A {

        public static function who() {
            return "B";
        }
    }

    class C {

        public function __invoke($name) {
            return "Hello {$name}";
        }
    }
}

namespace Foobar {

    class Foo {

        static public function test($name) {
            return "Hello {$name}!";
        }
    }
}
