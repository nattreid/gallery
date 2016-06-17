<?php

namespace NAttreid\Gallery;

/**
 * Nastaveni Gallery
 * 
 * @author Attreid <attreid@gmail.com>
 */
class GalleryExtension extends \Nette\DI\CompilerExtension {

    private $default = [
        'maxImageSize' => 5,
        'maxImagesSize' => 20
    ];

    public function loadConfiguration() {
        $config = $this->getConfig($this->default);

        $builder = $this->getContainerBuilder();
        
        $neon = $this->loadFromFile(__DIR__ . '/gallery.neon');
        $namespace = 'Gallery';
        $this->compiler->parseServices($builder, $neon, $namespace);

        $builder->addDefinition($this->prefix('gallery'))
                ->setImplement('NAttreid\Gallery\IGalleryFactory')
                ->setFactory('NAttreid\Gallery\Gallery')
                ->setArguments([$config['maxImageSize'], $config['maxImagesSize']])
                ->setAutowired(TRUE);
    }

}
