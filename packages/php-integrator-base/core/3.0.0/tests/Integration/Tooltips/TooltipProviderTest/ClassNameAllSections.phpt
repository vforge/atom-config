<?php

namespace A;

/**
 * Hi! *Bold text* **Italic** ~~Strikethrough~~
 *
 * ## Header
 * Hello!
 *
 * @var string|bool
 */
class SimpleClass { }
abstract class AbstractClass { }
trait SimpleTrait { }
interface SimpleInterface { }

// Not semantically valid, but syntactically valid for testing purposes.
$a = new SimpleClass();
$b = new AbstractClass();
$c = new SimpleTrait();
$d = new SimpleInterface();
