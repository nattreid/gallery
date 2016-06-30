<?php

namespace NAttreid\Gallery;

use Nette\Database\Table\Selection,
    Nette\Http\Session,
    Nette\Http\SessionSection;

/**
 * Model galerie
 * 
 * @property int $id id
 * @property string $image adresa obrazku
 * @property int $position pozice
 *
 * @author Attreid <attreid@gmail.com>
 */
class Model {

    /** @var array */
    private $foreignKey;

    /** @var Selection|Session|SessionSection */
    private $storage;

    public function __construct($storage, $image, $position, $key) {
        $this->setStorage($storage);
    }

    /**
     * Vrati objekt obrazku
     * @param int $key
     * @return \stdClass|boolean
     */
    private function createObject($key) {
        if ($key === NULL) {
            return FALSE;
        }
        $obj = new \stdClass;
        $obj->id = $key;
        $obj->image = $this->storage->gallery[$key];
        return $obj;
    }

    public function __get($name) {
        if (method_exists($this, $name)) {
            return [$this, $name];
        } else {
            return $name;
        }
    }

    /**
     * Nastavi uloziste
     * @param Selection|Session|SessionSection $storage
     * @throws \Nette\InvalidArgumentException
     */
    public function setStorage($storage) {
        if ($storage instanceof Selection) {
            $this->storage = $storage;
        } elseif ($storage instanceof Session || $storage instanceof SessionSection) {
            $this->storage = $storage;
            if (!isset($this->storage->gallery)) {
                $this->storage->gallery = [];
            }
        } else {
            throw new \Nette\InvalidArgumentException('Model musi byt Selection, Session nebo SessionSection');
        }
    }

    /**
     * Smaze obrazek
     * @param int $keys
     * @return string|array seznam smazanych polozek
     * @throws \Nette\InvalidStateException
     */
    public function delete($keys = NULL) {
        $result = [];
        if ($this->storage instanceof Selection) {
            if (!empty($keys) || $keys === NULL) {
                $model = clone $this->storage;
                if ($keys != NULL) {
                    $model = $model->wherePrimary($keys);
                } elseif (!empty($this->foreignKey)) {
                    $model->where($this->foreignKey[0], $this->foreignKey[1]);
                }
                $result = $model->fetchPairs($this->id, $this->image);
                $model->delete();
            }
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            if (is_array($keys)) {
                foreach ($keys as $value) {
                    $result[] = $value;
                    unset($this->storage->gallery[$value]);
                }
            } elseif ($keys === NULL) {
                $result = $this->storage->gallery;
                $this->storage->gallery = [];
            } else {
                $result = $this->storage->gallery[$keys];
                unset($this->storage->gallery[$keys]);
            }
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
        return $result;
    }

    /**
     * Vrati vsechny obrazky
     * @param bool $remove pokud je model Session, tak ji vymaze
     * @return Selection|array
     * @throws \Nette\InvalidStateException
     */
    public function fetchAll($remove = FALSE) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            if (!empty($this->foreignKey)) {
                $model->where($this->foreignKey[0], $this->foreignKey[1]);
            }
            return $model->order($this->position)->fetchAll();
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            $result = [];
            if (!empty($this->storage->gallery)) {
                foreach ($this->storage->gallery as $key => $value) {
                    $result[] = $this->createObject($key);
                }
            }
            if ($remove) {
                $this->storage->remove();
            }
            return $result;
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Vrati obrazek
     * @param int $key
     * @return Selection|Object
     * @throws \Nette\InvalidStateException
     */
    public function get($key) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            return $model->wherePrimary($key)->fetch();
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            return $this->createObject($key);
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Aktualizuje pozice
     * @param array $data
     * @throws \Nette\InvalidStateException
     */
    public function updatePosition($data) {
        if ($this->storage instanceof Selection) {
            foreach ($data as $key => $value) {
                $model = clone $this->storage;
                $model->wherePrimary($value)
                        ->update([
                            $this->position => $key + 1
                ]);
            }
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            $gallery = [];
            foreach ($data as $value) {
                $gallery[$value] = $this->storage->gallery[$value];
            }
            $this->storage->gallery = $gallery;
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Vrati predchozi obrazek
     * @param int $key
     * @return Selection|Object|FALSE
     * @throws \Nette\InvalidStateException
     */
    public function getPrevious($key) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            $position = $model->wherePrimary($key)->fetch()[$this->position];

            $model = clone $this->storage;
            if (!empty($this->foreignKey)) {
                $model->where($this->foreignKey[0], $this->foreignKey[1]);
            }
            return $model->where($this->position . ' <', $position)
                            ->order($this->position . ' DESC')
                            ->limit(1)
                            ->fetch();
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            reset($this->storage->gallery);
            while (key($this->storage->gallery) != $key) {
                $value = next($this->storage->gallery);
                if (empty($value)) {
                    return FALSE;
                }
            }
            prev($this->storage->gallery);
            return $this->createObject(key($this->storage->gallery));
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Vrati nasledujici obrazek
     * @param int $key
     * @return Selection|Object|FALSE
     * @throws \Nette\InvalidStateException
     */
    public function getNext($key) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            $position = $model->wherePrimary($key)->fetch()[$this->position];

            $model = clone $this->storage;
            if (!empty($this->foreignKey)) {
                $model->where($this->foreignKey[0], $this->foreignKey[1]);
            }
            return $model->where($this->position . ' >', $position)
                            ->order($this->position)
                            ->limit(1)
                            ->fetch();
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            reset($this->storage->gallery);
            while (key($this->storage->gallery) != $key) {
                $value = next($this->storage->gallery);
                if (empty($value)) {
                    return FALSE;
                }
            }
            next($this->storage->gallery);
            return $this->createObject(key($this->storage->gallery));
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Nastavi cizi klic u Selection modelu
     * @param string $key
     * @param int $value
     */
    public function setForeignKey($key, $value) {
        $this->foreignKey = [$key, $value];
    }

    /**
     * Prida obrazek
     * @param string $image
     * @throws \Nette\InvalidStateException
     */
    public function add($image) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            $data = [
                $this->image => $image,
                $this->position => $model->max($this->position) + 1
            ];
            if (!empty($this->foreignKey)) {
                $data[$this->foreignKey[0]] = $this->foreignKey[1];
            }
            $this->storage->insert($data);
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            $this->storage->gallery[] = $image;
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

    /**
     * Aktualizuje obrazek
     * @param int $key
     * @param string $name
     * @throws \Nette\InvalidStateException
     */
    public function update($key, $name) {
        if ($this->storage instanceof Selection) {
            $model = clone $this->storage;
            $data = [
                $this->image => $name,
            ];
            $model->get($key)->update($data);
        } elseif ($this->storage instanceof Session || $this->storage instanceof SessionSection) {
            $this->storage->gallery[$key] = $name;
        } else {
            throw new \Nette\InvalidStateException('Musi byt nastaven model pres metodu setModel()');
        }
    }

}
