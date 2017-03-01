<?php

declare(strict_types = 1);

namespace NAttreid\Gallery\Storage;

use NAttreid\Gallery\Control\Image;

/**
 * Storage
 * @author Attreid <attreid@gmail.com>
 */
interface IStorage
{

	/**
	 * Vrati obrazek
	 * @param int $key
	 * @return Image
	 */
	public function get(int $key);

	/**
	 * Vrati vsechny obrazky
	 * @return Image[]
	 */
	public function fetchAll(): array;

	/**
	 * Aktualizuje pozice
	 * @param string[] $data [key,image]
	 */
	public function updatePosition(array $data);

	/**
	 * Vrati predchozi obrazek
	 * @param int $key
	 * @return Image
	 */
	public function getPrevious(int $key): image;

	/**
	 * Vrati nasledujici obrazek
	 * @param int $key
	 * @return Image
	 */
	public function getNext(int $key): Image;

	/**
	 * Prida obrazek
	 * @param string $image
	 */
	public function add(string $image);

	/**
	 * Aktualizuje obrazek
	 * @param int $key
	 * @param string $image
	 */
	public function update(int $key, string $image);

	/**
	 * Smaze obrazek
	 * @param int|int[]|null $keys pokud je null smaze vsechny obrazky
	 * @return string[] seznam smazanych polozek
	 */
	public function delete($keys = null): array;
}
