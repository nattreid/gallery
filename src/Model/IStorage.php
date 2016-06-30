<?php

namespace NAttreid\Gallery;

/**
 * Storage
 * @author Attreid <attreid@gmail.com>
 */
interface IStorage {

    /**
     * Vrati obrazek
     * @param int $key
     * @return Storage\Image
     */
    public function get($key);

    /**
     * Vrati vsechny obrazky
     * @return Storage\Image[]
     */
    public function fetchAll();

    /**
     * Aktualizuje pozice
     * @param array $data [key,image]
     */
    public function updatePosition($data);

    /**
     * Vrati predchozi obrazek
     * @param int $key
     * @return Storage\Image
     */
    public function getPrevious($key);

    /**
     * Vrati nasledujici obrazek
     * @param int $key
     * @return Storage\Image
     */
    public function getNext($key);

    /**
     * Prida obrazek
     * @param string $image
     */
    public function add($image);

    /**
     * Aktualizuje obrazek
     * @param int $key
     * @param string $image
     */
    public function update($key, $image);

    /**
     * Smaze obrazek
     * @param int|NULL $keys ppokud je NULL smaze vsechny obrazky
     * @return string[] seznam smazanych polozek
     */
    public function delete($keys = NULL);
}
