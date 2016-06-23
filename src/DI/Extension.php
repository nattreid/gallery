<?php

namespace NAttreid\Gallery\DI;

/**
 * Nastaveni Gallery
 * 
 * @author Attreid <attreid@gmail.com>
 */
class Extension extends \Nette\DI\CompilerExtension {

    private $default = [
        'maxImageSize' => 5,
        'maxImagesSize' => 20
    ];

    public function loadConfiguration() {
        $config = $this->getConfig($this->default);

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('gallery'))
                ->setImplement('NAttreid\Gallery\IGalleryFactory')
                ->setFactory('NAttreid\Gallery\Gallery')
                ->setArguments([$config['maxImageSize'], $config['maxImagesSize']])
                ->setAutowired(TRUE);
    }

}
