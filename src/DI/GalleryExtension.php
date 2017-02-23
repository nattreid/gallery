<?php

namespace NAttreid\Gallery\DI;

use NAttreid\Cms\LoaderFactory;
use NAttreid\Gallery\Control\Gallery;
use NAttreid\Gallery\Control\IGalleryFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;


/**
 * Nastaveni Gallery
 *
 * @author Attreid <attreid@gmail.com>
 */
class GalleryExtension extends CompilerExtension
{

	private $defaults = [
		'maxFileSize' => 5,
		'maxFiles' => 20,
	];

	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults, $this->getConfig());
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('gallery'))
			->setFactory(Gallery::class)
			->setImplement(IGalleryFactory::class)
			->setArguments([$config['maxFileSize'], $config['maxFiles']]);
	}

	public function beforeCompile()
	{
		$path = __DIR__ . '/../../assets/';
		$builder = $this->getContainerBuilder();
		$loader = $builder->getByType(LoaderFactory::class);
		try {
			$builder->getDefinition($loader)
				->addSetup('addFile', [$path . 'css/gallery.boundled.min.css'])
				->addSetup('addFile', [$path . 'js/gallery.boundled.min.js']);
		} catch (MissingServiceException $ex) {

		}
	}
}
