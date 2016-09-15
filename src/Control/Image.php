<?php

namespace NAttreid\Gallery\Control;

/**
 * Image
 *
 * @property-read int $key
 * @property-read string $image
 *
 * @author Attreid <attreid@gmail.com>
 */
class Image
{
	use \Nette\SmartObject;

	/** @var int */
	private $key;

	/** @var string */
	private $image;

	public function __construct($key, $image)
	{
		$this->key = $key;
		$this->image = $image;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getImage()
	{
		return $this->image;
	}

}
