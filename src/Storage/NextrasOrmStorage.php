<?php

declare(strict_types = 1);

namespace NAttreid\Gallery\Storage;

use NAttreid\Gallery\Control\Image;
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

	public function __construct(Repository $repository, string $name, string $position, string $key)
	{
		$this->repository = $repository;
		$this->name = $name;
		$this->position = $position;
		$this->key = $key;
	}

	/**
	 * Nastavi cizi klic
	 * @param string $keyName
	 * @param int $value
	 */
	public function setForeignKey(string $keyName, int $value)
	{
		$this->foreignKey = [$keyName, $value];
	}

	public function add(string $image)
	{
		$entityClass = $this->repository->getEntityClassName([]);
		/* @var $entity IEntity */
		$entity = new $entityClass;
		$this->repository->attach($entity);
		$entity->{$this->name} = $image;
		if (!empty($this->foreignKey)) {
			$entity->{$this->foreignKey[0]} = $this->foreignKey[1];
		}
		$this->repository->persistAndFlush($entity);
	}

	public function delete($keys = null): array
	{
		$rows = $this->repository->findAll();
		if (!empty($this->foreignKey)) {
			$rows = $rows->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		if ($keys != null) {
			$rows = $rows->findBy([$this->key => $keys]);
		}

		$result = $rows->fetchPairs($this->key, $this->name);
		foreach ($rows as $row) {
			$this->repository->remove($row);
		}
		$this->repository->flush();
		return $result;
	}

	public function fetchAll(): array
	{
		$rows = $this->repository->findAll();
		if (!empty($this->foreignKey)) {
			$rows = $rows->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position);

		$result = [];
		foreach ($rows as $row) {
			$result[] = new Image($row->{$this->key}, $row->{$this->name});
		}
		return $result;
	}

	public function get(int $key): Image
	{
		$row = $this->repository->getBy([$this->key => $key]);
		return new Image($row->{$this->key}, $row->{$this->name});
	}

	public function getPrevious(int $key): Image
	{
		$position = $this->repository->getBy([$this->key => $key])->{$this->position};
		$rows = $this->repository->findAll();

		if (!empty($this->foreignKey)) {
			$rows = $rows->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position, ICollection::DESC);
		$row = $rows->getBy([$this->position . '<' => $position]);

		if ($row) {
			return new Image($row->{$this->key}, $row->{$this->name});
		} else {
			return false;
		}
	}

	public function getNext(int $key): Image
	{
		$position = $this->repository->getBy([$this->key => $key])->{$this->position};
		$rows = $this->repository->findAll();

		if (!empty($this->foreignKey)) {
			$rows = $rows->findBy([$this->foreignKey[0] => $this->foreignKey[1]]);
		}
		$rows = $rows->orderBy($this->position);
		$row = $rows->getBy([$this->position . '>' => $position]);
		if ($row) {
			return new Image($row->{$this->key}, $row->{$this->name});
		} else {
			return false;
		}
	}

	public function update(int $key, string $image)
	{
		$entity = $this->repository->getBy([$this->key => $key]);
		$entity->{$this->name} = $image;
		$this->repository->persistAndFlush($entity);
	}

	public function updatePosition(array $data)
	{
		foreach ($data as $position => $key) {
			$entity = $this->repository->getBy([$this->key => $key]);
			$entity->{$this->position} = $position + 1;
			$this->repository->persist($entity);
		}
		$this->repository->flush();
	}

}
