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

		$builder->addDefinition($this->prefix('plupload.control'))
			->setFactory(PluploadControl::class)
			->setImplement(IPluploadControlFactory::class);

		$builder->addDefinition($this->prefix('plupload.uploader'))
			->setClass(Uploader::class)
			->setArguments([$config['temp']]);

		$builder->addDefinition($this->prefix('plupload.uploadQueue'))
			->setFactory(UploadQueue::class)
			->setImplement(IUploadQueueFactory::class)
			->setArguments([$builder->literal('$id')])
			->setParameters(['id']);

		$builder->addDefinition($this->prefix('plupload.upload'))
			->setFactory(Upload::class)
			->setImplement(IUploadFactory::class)
			->setArguments([$builder->literal('$filename'), $builder->literal('$name')])
			->setParameters(['filename', 'name']);
	}

}
