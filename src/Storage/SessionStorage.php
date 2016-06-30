<?php

namespace NAttreid\Gallery\Storage;

use Nette\Http\SessionSection;

/**
 * Session Storage
 *
 * @author Attreid <attreid@gmail.com>
 */
class SessionStorage implements \NAttreid\Gallery\IStorage {

    /** @var SessionSection */
    private $session;

    public function __construct(SessionSection $session) {
        $this->session = $session;
    }

    /**
     * Smaze temp
     */
    public function clearTemp() {
        $this->session->remove();
    }

    public function add($image) {
        $this->session->gallery[] = $image;
    }

    public function delete($keys = NULL) {
        $result = [];
        if (is_array($keys)) {
            foreach ($keys as $value) {
                $result[] = $value;
                unset($this->session->gallery[$value]);
            }
        } elseif ($keys === NULL) {
            $result = $this->session->gallery;
            $this->session->gallery = [];
        } else {
            $result[] = $this->session->gallery[$keys];
            unset($this->session->gallery[$keys]);
        }
        return $result;
    }

    public function fetchAll() {
        $result = [];
        if (!empty($this->session->gallery)) {
            foreach ($this->session->gallery as $key => $image) {
                $result[] = new Image($key, $image);
            }
        }
        return $result;
    }

    public function get($key) {
        return new Image($key, $this->session->gallery[$key]);
    }

    public function getPrevious($key) {
        reset($this->session->gallery);
        while (key($this->session->gallery) != $key) {
            $value = next($this->session->gallery);
            if (empty($value)) {
                return FALSE;
            }
        }
        prev($this->session->gallery);
        return new Image(key($this->session->gallery), current($this->session->gallery));
    }

    public function getNext($key) {
        reset($this->session->gallery);
        while (key($this->session->gallery) != $key) {
            $value = next($this->session->gallery);
            if (empty($value)) {
                return FALSE;
            }
        }
        next($this->session->gallery);
        return new Image(key($this->session->gallery), current($this->session->gallery));
    }

    public function update($key, $image) {
        $this->storage->gallery[$key] = $image;
    }

    public function updatePosition($data) {
        $gallery = [];
        foreach ($data as $value) {
            $gallery[$value] = $this->storage->gallery[$value];
        }
        $this->storage->gallery = $gallery;
    }

}
