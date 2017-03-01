<?php

declare(strict_types = 1);

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

	public function __construct(int $key, string $name)
	{
		$this->key = $key;
		$this->name = $name;
	}

	/**
	 * @return int
	 */
	protected function getKey(): int
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	protected function getName(): string
	{
		return $this->name;
	}

}
