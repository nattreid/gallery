<?php

namespace NAttreid\Gallery\Storage;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;

/**
 * Nextras Orm Storage
 *
 * @author Attreid <attreid@gmail.com>
 */
class NextrasOrmStorage implements IStorage
{

	/** @var Repository */
	private $repository;

	/** @var string */
	private $name, $position, $key;

	/** @var array */
	private $foreignKey;

	public function __construct(Repository $repository, $name, $position, $key)
	{
		$this->repository = $repository;
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

	public function add($image)
	{
		list($entityClass) = $this->repository->getEntityClassName();
		/* @var $entity IEntity */
		$entity = new $entityClass;
		$this->repository->attach($entity);
		$entity->{$this->name} = $image;
		if (!empty($this->foreignKey)) {
			$entity->{$this->foreignKey[0]} = $this->foreignKey[1];
		}
		$this->repository->persistAndFlush($entity);
	}

	public function delete($keys = NULL)
	{
		if (!empty($this->foreignKey)) {
			$rows = $this->repository->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		if ($keys != NULL) {
			$rows = $rows->findBy([$this->key => $keys]);
		}

		$result = $rows->fetchPairs($this->key, $this->name);
		foreach ($rows as $row) {
			$this->repository->remove($row);
		}
		$this->repository->flush();
		return $result;
	}

	public function fetchAll()
	{
		if (!empty($this->foreignKey)) {
			$rows = $this->repository->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position);

		$result = [];
		foreach ($rows as $row) {
			$result[] = new Image($row->{$this->key}, $row->{$this->name});
		}
		return $result;
	}

	public function get($key)
	{
		$row = $this->repository->getBy([$this->key => $key]);
		return new Image($row->{$this->key}, $row->{$this->name});
	}

	public function getPrevious($key)
	{
		$position = $this->repository->getBy([$this->key => $key])->{$this->position};

		if (!empty($this->foreignKey)) {
			$rows = $this->repository->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position, ICollection::DESC);
		$row = $rows->getBy([$this->position . '<' => $position]);

		if ($row) {
			return new Image($row->{$this->key}, $row->{$this->name});
		} else {
			return FALSE;
		}
	}

	public function getNext($key)
	{
		$position = $this->repository->getBy([$this->key => $key])->{$this->position};

		if (!empty($this->foreignKey)) {
			$rows = $this->repository->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position);
		$row = $rows->getBy([$this->position . '>' => $position]);
		if ($row) {
			return new Image($row->{$this->key}, $row->{$this->name});
		} else {
			return FALSE;
		}
	}

	public function update($key, $image)
	{
		$entity = $this->repository->getBy([$this->key => $key]);
		$entity->{$this->name} = $image;
		$this->repository->persistAndFlush($entity);
	}

	public function updatePosition($data)
	{
		foreach ($data as $position => $key) {
			$entity = $this->repository->getBy([$this->key => $key]);
			$entity->{$this->position} = $position + 1;
			$this->repository->persist($entity);
		}
		$this->repository->flush();
	}

}
