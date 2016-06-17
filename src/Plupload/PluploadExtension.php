<?php

namespace NAttreid\Gallery\Plupload;

use Nette\DI\CompilerExtension;

/**
 * Register extension in DI container.
 * 
 * @author Nikolas Tsiongas
 */
class PluploadExtension extends CompilerExtension {

    /**
     * Load services, register factories.
     */
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        $config = $this->loadFromFile(__DIR__ . '/plupload.neon');
        $namespace = 'Plupload.DI';
        $this->compiler->parseServices($builder, $config, $namespace);
    }

}
