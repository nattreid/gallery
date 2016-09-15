<?php

namespace NAttreid\Gallery\DI;

use NAttreid\Gallery\Gallery;
use NAttreid\Gallery\IGalleryFactory;
use NAttreid\Gallery\Plupload\IPluploadControlFactory;
use NAttreid\Gallery\Plupload\IUploadFactory;
use NAttreid\Gallery\Plupload\IUploadQueueFactory;
use NAttreid\Gallery\Plupload\PluploadControl;
use NAttreid\Gallery\Plupload\Upload;
use NAttreid\Gallery\Plupload\Uploader;
use NAttreid\Gallery\Plupload\UploadQueue;

/**
 * Nastaveni Gallery
 *
 * @author Attreid <attreid@gmail.com>
 */
class GalleryExtension extends \Nette\DI\CompilerExtension
{

	private $defaults = [
		'maxImageSize' => 5,
		'maxImagesSize' => 20,
		'temp' => '%tempDir%/plupload'
	];

	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults, $this->getConfig());
		$builder = $this->getContainerBuilder();

		$config['temp'] = \Nette\DI\Helpers::expand($config['temp'], $builder->parameters);

		$builder->addDefinition($this->prefix('gallery'))
			->setFactory(Gallery::class)
			->setImplement(IGalleryFactory::class)
			->setArguments([$config['maxImageSize'], $config['maxImagesSize']]);
	}
	/*
		public function beforeCompile()
		{
			$path = __DIR__ . '/../../assets/';
			$builder = $this->getContainerBuilder();
			$loader = $builder->getByType(LoaderFactory::class);
			try {
				$builder->getDefinition($loader)
					->addSetup('addFile', [$path . 'css/gallery.boundled.min.css'])
					->addSetup('addFile', [$path . 'js/gallery.boundled.min.js'])
					->addSetup('addFile', [$path . 'js/i18n/gallery.cs.min.js', 'cs']);
			} catch (\Nette\DI\MissingServiceException $ex) {

			}
		}
	*/
}
