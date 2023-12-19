<?php

namespace Zeantar\Injection\Patch;

abstract class AbstractPatch
{
    /**
     * Instance of patched method class
     *
     * @var object|null
     */
    protected static ?object $instance = null;

    /**
     * Method to be executed before original method execution
     * If true is returned original method will not be executed
     *
     */
    public static function prefix()
    {}

    /**
     * Method to be executed after origion method execution
     * If value is returned this value will be returned from patched method instead of original one
     *
     * @param mixed $originalReturn Original return of method
     */
    public static function postfix(mixed $originalReturn)
    {
    }
}