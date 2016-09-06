<?php

namespace NAttreid\Gallery\DI;

use NAttreid\Gallery\Gallery;
use NAttreid\Gallery\IGalleryFactory;

/**
 * Nastaveni Gallery
 *
 * @author Attreid <attreid@gmail.com>
 */
class GalleryExtension extends \Nette\DI\CompilerExtension
{

	private $defaults = [
		'maxImageSize' => 5,
		'maxImagesSize' => 20
	];

	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults, $this->getConfig());

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('gallery'))
			->setImplement(IGalleryFactory::class)
			->setFactory(Gallery::class)
			->setArguments([$config['maxImageSize'], $config['maxImagesSize']])
			->setAutowired(TRUE);

		$plupload = $this->loadFromFile(__DIR__ . '/plupload.neon');
		$this->compiler->parseServices($builder, $plupload, 'Plupload.DI');
	}

}
