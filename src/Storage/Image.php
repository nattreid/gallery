<?php

namespace NAttreid\Gallery\Storage;

/**
 * Image
 * 
 * @property-read int $key
 * @property-read string $image
 *
 * @author Attreid <attreid@gmail.com>
 */
class Image {

    use \Nette\SmartObject;

    public function __construct($key, $image) {
        $this->key = $key;
        $this->image = $image;
    }

}
