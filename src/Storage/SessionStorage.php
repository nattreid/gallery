<?php

declare(strict_types = 1);

namespace NAttreid\Gallery\Storage;

use NAttreid\Gallery\Control\Image;
use Nette\Http\SessionSection;

/**
 * Session Storage
 *
 * @author Attreid <attreid@gmail.com>
 */
class SessionStorage implements IStorage
{

	/** @var SessionSection */
	private $session;

	/** @var string */
	private $name;

	/** @var string[] */
	private $variable;

	public function __construct(SessionSection $session, string $name)
	{
		$this->session = $session;
		$this->name = $name;
		$this->variable = &$session->$name;
	}

	/**
	 * Smaze temp
	 */
	public function clearTemp()
	{
		$this->session->remove();
	}

	public function add(string $image)
	{
		$this->variable[] = $image;
	}

	public function delete($keys = null): array
	{
		$result = [];
		if (is_array($keys)) {
			foreach ($keys as $value) {
				$result[] = $value;
				unset($this->variable[$value]);
			}
		} elseif ($keys === null) {
			$result = $this->variable;
			$this->variable = [];
		} else {
			$result[] = $this->variable[$keys];
			unset($this->variable[$keys]);
		}
		return $result;
	}

	public function fetchAll(): array
	{
		$result = [];
		if (!empty($this->variable)) {
			foreach ($this->variable as $key => $image) {
				$result[] = new Image($key, $image);
			}
		}
		return $result;
	}

	public function get(int $key): Image
	{
		return new Image($key, $this->variable[$key]);
	}

	public function getPrevious(int $key): Image
	{
		reset($this->variable);
		while (key($this->variable) != $key) {
			$value = next($this->variable);
			if (empty($value)) {
				return false;
			}
		}
		prev($this->variable);
		$image = current($this->variable);
		if ($image) {
			return new Image(key($this->variable), $image);
		}
		return false;
	}

	public function getNext(int $key): Image
	{
		reset($this->variable);
		while (key($this->variable) != $key) {
			$value = next($this->variable);
			if (empty($value)) {
				return false;
			}
		}
		next($this->variable);
		$image = current($this->variable);
		if ($image) {
			return new Image(key($this->variable), $image);
		}
		return false;
	}

	public function update(int $key, string $image)
	{
		$this->variable[$key] = $image;
	}

	public function updatePosition(array $data)
	{
		$gallery = [];
		foreach ($data as $value) {
			$gallery[$value] = $this->variable[$value];
		}
		$this->variable = $gallery;
	}
}
