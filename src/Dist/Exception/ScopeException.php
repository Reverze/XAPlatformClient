<?php

namespace XA\PlatformClient\Dist\Exception;

class ScopeException extends  \Exception
{
    /**
     * Throws an exception when scope's name is empty.
     * @return ScopeException
     */
    public static function emptyScopeName() : ScopeException
    {
        return (new ScopeException("Scope's name cannot be empty!"));
    }

    /**
     * Throws an exception when scope's name is not defined (is null)
     * @return ScopeException
     */
    public static function scopeNameNotDefined() : ScopeException
    {
        return (new ScopeException("Scope's name is not defined!"));
    }

    /**
     * Throws an exception when user tries to change name of already named scope
     * @return ScopeException
     */
    public static function scopeHasDefinedName() : ScopeException
    {
        return (new ScopeException("You cannot override scope's name!"));
    }

    /**
     * Throws an exception when user tries to recompile scope's name.
     * @return ScopeException
     */
    public static function recompileScopeName() : ScopeException
    {
        return (new ScopeException("You cannot recompile scope's name"));
    }
}

?>