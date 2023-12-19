<?php

namespace Zeantar\Injection;

use Zeantar\Injection\Attributes\Patch;
use Zeantar\Injection\Attributes\Postfix;
use Zeantar\Injection\Attributes\Prefix;

final class SingularInjectionContainer implements InjectionContainerInterface
{
    private \ReflectionClass $reflection;

    public function __construct(private object $obj)
    {
        $this->reflection = new \ReflectionClass($this->obj);
    }

    public function __call(string $name, array $args)
    {
        $method = $this->reflection->getMethod($name);
        $attributes = array_filter($method->getAttributes(), function ($attribute) {
            return $attribute->getName() == Patch::class;
        });
        foreach ($attributes as $attribute) {
            $injectionClass = $attribute->getArguments()[0];
            $injectionReflection = new \ReflectionClass($injectionClass);

            $prefixes = $postfixes = [];
            foreach ($injectionReflection->getMethods() as $injectionMethod) {
                foreach ($injectionMethod->getAttributes() as $methodAttribute) {
                    $attributeName = $methodAttribute->getName();

                    if ($attributeName == Prefix::class) {
                        $prefixes[] = $injectionMethod->getName();
                    } elseif ($attributeName == Postfix::class) {
                        $postfixes[] = $injectionMethod->getName();
                    }
                }
            }

            $injectionReflection->setStaticPropertyValue('instance', $this->obj);

            $prefixReturn = $injectionClass::prefix();
            if ($prefixReturn === true) {
                return;
            }
            $originalReturn = $this->obj->{$name}(...$args);
            $modifiedReturn = $injectionClass::postfix($originalReturn);
        }

        if ($modifiedReturn !== null) {
            return $modifiedReturn;
        }
        return $originalReturn;
    }
}
