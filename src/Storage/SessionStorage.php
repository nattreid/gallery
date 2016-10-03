<?php

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

	public function __construct(SessionSection $session)
	{
		$this->session = $session;
	}

	/**
	 * Smaze temp
	 */
	public function clearTemp()
	{
		$this->session->remove();
	}

	public function add($image)
	{
		$this->session->gallery[] = $image;
	}

	public function delete($keys = null)
	{
		$result = [];
		if (is_array($keys)) {
			foreach ($keys as $value) {
				$result[] = $value;
				unset($this->session->gallery[$value]);
			}
		} elseif ($keys === null) {
			$result = $this->session->gallery;
			$this->session->gallery = [];
		} else {
			$result[] = $this->session->gallery[$keys];
			unset($this->session->gallery[$keys]);
		}
		return $result;
	}

	public function fetchAll()
	{
		$result = [];
		if (!empty($this->session->gallery)) {
			foreach ($this->session->gallery as $key => $image) {
				$result[] = new Image($key, $image);
			}
		}
		return $result;
	}

	public function get($key)
	{
		return new Image($key, $this->session->gallery[$key]);
	}

	public function getPrevious($key)
	{
		reset($this->session->gallery);
		while (key($this->session->gallery) != $key) {
			$value = next($this->session->gallery);
			if (empty($value)) {
				return false;
			}
		}
		prev($this->session->gallery);
		$image = current($this->session->gallery);
		if ($image) {
			return new Image(key($this->session->gallery), $image);
		}
		return false;
	}

	public function getNext($key)
	{
		reset($this->session->gallery);
		while (key($this->session->gallery) != $key) {
			$value = next($this->session->gallery);
			if (empty($value)) {
				return false;
			}
		}
		next($this->session->gallery);
		$image = current($this->session->gallery);
		if ($image) {
			return new Image(key($this->session->gallery), $image);
		}
		return false;
	}

	public function update($key, $image)
	{
		$this->session->gallery[$key] = $image;
	}

	public function updatePosition($data)
	{
		$gallery = [];
		foreach ($data as $value) {
			$gallery[$value] = $this->session->gallery[$value];
		}
		$this->session->gallery = $gallery;
	}

}
