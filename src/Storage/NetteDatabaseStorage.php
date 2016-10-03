<?php

namespace NAttreid\Gallery\Storage;

use NAttreid\Gallery\Control\Image;
use Nette\Database\Table\Selection;

/**
 * Nette Database Storage
 *
 * @author Attreid <attreid@gmail.com>
 */
class NetteDatabaseStorage implements IStorage
{

	/** @var Selection */
	private $model;

	/** @var string */
	private $name, $position, $key;

	/** @var array */
	private $foreignKey;

	public function __construct(Selection $model, $name, $position, $key)
	{
		$this->model = $model;
		$this->name = $name;
		$this->position = $position;
		$this->key = $key;
	}

	/**
	 * Nastavi cizi klic
	 * @param string $key
	 * @param int $value
	 */
	public function setForeignKey($key, $value)
	{
		$this->foreignKey = [$key, $value];
	}

	/**
	 * Vrati model
	 * @return Selection
	 */
	private function getModel()
	{
		return clone $this->model;
	}

	public function add($image)
	{
		$data = [
			$this->name => $image,
			$this->position => $this->getModel()->max($this->position) + 1
		];
		if (!empty($this->foreignKey)) {
			$data[$this->foreignKey[0]] = $this->foreignKey[1];
		}
		$this->getModel()->insert($data);
	}

	public function delete($keys = null)
	{
		$model = $this->getModel();
		if ($keys != null) {
			$model = $model->where($this->key, $keys);
		} elseif (!empty($this->foreignKey)) {
			$model->where($this->foreignKey[0], $this->foreignKey[1]);
		}
		$result = $model->fetchPairs($this->key, $this->name);
		$model->delete();
		return $result;
	}

	public function fetchAll()
	{
		$model = $this->getModel();
		if (!empty($this->foreignKey)) {
			$model->where($this->foreignKey[0], $this->foreignKey[1]);
		}
		$result = [];
		$rows = $model->order($this->position);
		foreach ($rows as $row) {
			$result[] = new Image($row[$this->key], $row[$this->name]);
		}
		return $result;
	}

	public function get($key)
	{
		$row = $this->getModel()->where($this->key, $key)->fetch();
		return new Image($row[$this->key], $row[$this->name]);
	}

	public function getPrevious($key)
	{
		$position = $this->getModel()->where($this->key, $key)->fetch()[$this->position];

		$model = $this->getModel();
		if (!empty($this->foreignKey)) {
			$model->where($this->foreignKey[0], $this->foreignKey[1]);
		}
		$row = $model->where($this->position . ' <', $position)
			->order($this->position . ' DESC')
			->limit(1)
			->fetch();
		if ($row) {
			return new Image($row[$this->key], $row[$this->name]);
		} else {
			return false;
		}
	}

	public function getNext($key)
	{
		$position = $this->getModel()->where($this->key, $key)->fetch()[$this->position];

		$model = $this->getModel();
		if (!empty($this->foreignKey)) {
			$model->where($this->foreignKey[0], $this->foreignKey[1]);
		}
		$row = $model->where($this->position . ' >', $position)
			->order($this->position . ' DESC')
			->limit(1)
			->fetch();
		if ($row) {
			return new Image($row[$this->key], $row[$this->name]);
		} else {
			return false;
		}
	}

	public function update($key, $image)
	{
		$this->getModel()->where($this->key, $key)->update([
			$this->name => $image,
		]);
	}

	public function updatePosition($data)
	{
		foreach ($data as $position => $key) {
			$this->getModel()->where($this->key, $key)->update([
				$this->position => $position + 1
			]);
		}
	}

}
