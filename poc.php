<?php

require_once __DIR__ . '/vendor/autoload.php';

use Zeantar\Injection\Injection;
use Zeantar\Injection\Attributes\Patch;

use Zeantar\Injection\Patch\AbstractPatch;

# User code

/**
 * Класс патча (типа декоратор)
 */
class EchoPatch extends AbstractPatch
{
    /**
     * Метод, который будет вызван до вызова оригинального метода класса
     * Если метод возвращает false, оригинальный метод не будет вызван
     */
    public static function prefix()
    {
        echo 'this should not be called!' . PHP_EOL;
        return true;
    }

    /**
     * Метод, который будет вызван после вызова оригинального метода класса
     * Если метод возращает значение, это значение будет возвращено вместо оригинального
     *
     * @param mixed $originalReturn Возвращаемое значение оригинального метода
     */
    public static function postfix(mixed $originalReturn)
    {
        echo 'postfix' . PHP_EOL;
    }
}

class MyClass
{
    #[Patch(EchoPatch::class)]
    public function printSomething()
    {
        echo 'hello world!' . PHP_EOL;
    }
}

# Создание обертки класса
$class = Injection::wrap(MyClass::class);
# Вызов метода класса
$class->printSomething();

# Output:
# this should not be called!


# Another

class MilkyWay
{
    public function answer()
    {
        # NOT TRUE!
        return 21;
    }
}

#[Patch(class: MilkyWay::class, method: 'answer')]
// #[Patch(class: AnotherUniverse::class, method: 'answer')]
class UniversePatch extends AbstractPatch
{
    public static function postfix(mixed $originalReturn)
    {
        return 42;
    }
}

Injection::apply(UniversePatch::class);

$class = Injection::wrap(MilkyWay::class);
echo $class->answer() . PHP_EOL;