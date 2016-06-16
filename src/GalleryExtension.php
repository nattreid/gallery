<?php

namespace nattreid\gallery;

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

        $builder->addDefinition($this->prefix('gallery'))
                ->setImplement('nattreid\gallery\IGalleryFactory')
                ->setFactory('nattreid\gallery\Gallery')
                ->setArguments([$config['maxImageSize'], $config['maxImagesSize']])
                ->setAutowired(TRUE);
    }

}
