<?php

namespace NAttreid\Gallery\Control;

use Nette\SmartObject;

/**
 * Image
 *
 * @property-read int $key
 * @property-read string $name
 *
 * @author Attreid <attreid@gmail.com>
 */
class Image
{
	use SmartObject;

	/** @var int */
	private $key;

	/** @var string */
	private $name;

	public function __construct($key, $name)
	{
		$this->key = $key;
		$this->name = $name;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getName()
	{
		return $this->name;
	}

}
