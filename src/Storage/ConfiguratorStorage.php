<?php

declare(strict_types = 1);

namespace NAttreid\Gallery\Storage;

use NAttreid\Cms\Configurator\Configurator;
use NAttreid\Gallery\Control\Image;

/**
 * Configurator Storage
 *
 * @author Attreid <attreid@gmail.com>
 */
class ConfiguratorStorage implements IStorage
{

	/** @var Configurator */
	private $configurator;

	/** @var string */
	private $name;

	/** @var string[] */
	private $images;

	public function __construct(Configurator $configurator, string $name)
	{
		$this->configurator = $configurator;
		$this->name = $name;
		$this->images = $configurator->__get($this->name);
	}

	public function add(string $image)
	{
		$this->images[] = $image;
		$this->configurator->__set($this->name, $this->images);
	}

	public function delete($keys = null): array
	{
		$result = [];
		if (is_array($keys)) {
			foreach ($keys as $value) {
				$result[] = $value;
				unset($this->images[$value]);
			}
		} elseif ($keys === null) {
			$result = $this->images;
			$this->images = [];
		} else {
			$result[] = $this->images[$keys];
			unset($this->images[$keys]);
		}
		$this->configurator->__set($this->name, $this->images);
		return $result;
	}

	public function fetchAll(): array
	{
		$result = [];
		if (!empty($this->images)) {
			foreach ($this->images as $key => $image) {
				$result[] = new Image($key, $image);
			}
		}
		return $result;
	}

	public function get(int $key): Image
	{
		return new Image($key, $this->images[$key]);
	}

	public function getPrevious(int $key): Image
	{
		reset($this->images);
		while (key($this->images) != $key) {
			$value = next($this->images);
			if (empty($value)) {
				return false;
			}
		}
		prev($this->images);
		$image = current($this->images);
		if ($image) {
			return new Image(key($this->images), $image);
		}
		return false;
	}

	public function getNext(int $key): Image
	{
		reset($this->images);
		while (key($this->images) != $key) {
			$value = next($this->images);
			if (empty($value)) {
				return false;
			}
		}
		next($this->images);
		$image = current($this->images);
		if ($image) {
			return new Image(key($this->images), $image);
		}
		return false;
	}

	public function update(int $key, string $image)
	{
		$this->images[$key] = $image;
		$this->configurator->__set($this->name, $this->images);
	}

	public function updatePosition(array $data)
	{
		$gallery = [];
		foreach ($data as $value) {
			$gallery[$value] = $this->images[$value];
		}
		$this->images = $gallery;
		$this->configurator->__set($this->name, $this->images);
	}
}
