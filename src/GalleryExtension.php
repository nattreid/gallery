<?php

namespace App\CrmModule\Components\Gallery;

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
                ->setImplement('\App\CrmModule\Components\Gallery\IGalleryFactory')
                ->setFactory('\App\CrmModule\Components\Gallery\Gallery')
                ->setArguments([$config['maxImageSize'], $config['maxImagesSize']])
                ->setAutowired(TRUE);
    }

}
