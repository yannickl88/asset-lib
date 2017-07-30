<?php

namespace Hostnet\Component\Resolver\Transpile;

/**
 * Implementations of this interface allow for wrapping of modules files in
 * such a way they can be used by require.js.
 */
interface JsModuleWrapperInterface
{
    /**
     * Wrap javascript content. The given file name should also support module names.
     *
     * @param string $file_name
     * @param string $alias
     * @param string $content
     * @return string
     */
    public function wrapModule(string $file_name, string $alias, string $content): string;
}
