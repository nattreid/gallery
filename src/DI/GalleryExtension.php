<?php

declare(strict_types=1);

namespace NAttreid\Gallery\DI;

use NAttreid\Cms\Factories\LoaderFactory;
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

	public function setMaxFiles(int $maxFiles): void
	{
		$this->defaults['maxFiles'] = $maxFiles;
	}

	public function setMaxFileSize(int $maxFileSize): void
	{
		$this->defaults['maxFileSize'] = $maxFileSize;
	}

	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults, $this->getConfig());
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('gallery'))
			->setFactory(Gallery::class)
			->setImplement(IGalleryFactory::class)
			->setArguments([$config['maxFileSize'], $config['maxFiles']]);
	}

	public function beforeCompile(): void
	{
		$path = __DIR__ . '/../../assets/';
		$builder = $this->getContainerBuilder();
		$loader = $builder->getByType(LoaderFactory::class);
		try {
			$builder->getDefinition($loader)
				->addSetup('addFile', [$path . 'css/gallery.boundled.min.css'])
				->addSetup('addFile', [$path . 'js/gallery.boundled.js']);
		} catch (MissingServiceException $ex) {

		}
	}
}
