<?php

namespace NAttreid\Gallery;

use Nette\Application\UI\Control,
    NAttreid\Gallery\Plupload\UploadQueue,
    NAttreid\Gallery\Plupload\IPluploadControlFactory,
    Nette\Database\Table\Selection,
    WebChemistry\Images\AbstractStorage,
    Nette\Utils\Image,
    Nette\Http\SessionSection,
    NAttreid\Gallery\Storage\NetteDatabaseStorage,
    NAttreid\Gallery\Storage\SessionStorage;

/**
 * Galerie
 *
 * @author Attreid <attreid@gmail.com>
 */
class Gallery extends Control {

    /** @var IPluploadControlFactory */
    private $plupload;

    /** @var AbstractStorage */
    private $imageStorage;

    /** @var IStorage */
    private $storage;

    /** @var string */
    private $namespace;

    /** @var int */
    private $maxImagesSize;

    /** @var int */
    private $maxImageSize;

    public function __construct($maxImagesSize, $maxImageSize, IPluploadControlFactory $plupload, AbstractStorage $imageStorage) {
        $this->maxImagesSize = $maxImagesSize;
        $this->maxImageSize = $maxImageSize;
        $this->plupload = $plupload;
        $this->imageStorage = $imageStorage;
    }

    /**
     * Vrati uloziste
     * @return IStorage
     * @throws \Nette\InvalidArgumentException
     */
    private function getStorage() {
        if ($this->storage === NULL) {
            throw new \Nette\InvalidArgumentException('Neni nastaveno uloziste setStorage');
        }
        return $this->storage;
    }

    /**
     * Nastavi uloziste
     * @param Selection|SessionSection $storage
     * @param string $column
     * @param string $key
     */
    public function setStorage($storage, $image = 'image', $position = 'position', $key = 'id') {
        if ($storage instanceof Selection) {
            $this->storage = new NetteDatabaseStorage($storage, $image, $position, $key);
        } elseif ($storage instanceof SessionSection) {
            $this->storage = new SessionStorage($storage);
        }
    }

    /**
     * Vrati obrazky
     * @return Image[]
     */
    public function getImages() {
        return $this->getStorage()->fetchAll();
    }

    /**
     * Nastavi namespace
     * @param string $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * Smaze vsechny obrazky z modelu
     */
    public function handleDeleteAllImages() {
        if ($this->presenter->isAjax()) {
            $result = $this->getStorage()->delete();
            foreach ($result as $row) {
                $this->imageStorage->delete($row);
            }

            $this->redrawControl('gallery');
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Smaze vybrane obrazky
     * @param string $json
     */
    public function handleDeleteImages($json) {
        if ($this->presenter->isAjax()) {
            $data = \Nette\Utils\Json::decode($json);

            $result = $this->getStorage()->delete($data);
            foreach ($result as $row) {
                $this->imageStorage->delete($row);
            }

            $this->redrawControl('gallery');
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Smaze obrazek
     * @param int $id
     */
    public function handleDeleteImage($id) {
        if ($this->presenter->isAjax()) {
            $result = $this->getStorage()->delete($id);
            $this->imageStorage->delete($result);

            $this->redrawControl('gallery');
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Zobrazi obrazek
     * @param int $id
     */
    public function handleShowViewer($id) {
        if ($this->presenter->isAjax()) {
            $this->template->viewImage = $this->getStorage()->get($id);

            $this->redrawControl('viewer');
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Zobrazi dalsi obrazek
     * @param int $id
     */
    public function handleNextImage($id) {
        if ($this->presenter->isAjax()) {
            $row = $this->getStorage()->getNext($id);
            if ($row) {
                $this->template->viewImage = $row;
                $this->redrawControl('image');
            } else {
                $this->presenter->terminate();
            }
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Zobrazi predchozi obrazek
     * @param int $id
     */
    public function handlePreviousImage($id) {
        if ($this->presenter->isAjax()) {
            $row = $this->getStorage()->getPrevious($id);
            if ($row) {
                $this->template->viewImage = $row;
                $this->redrawControl('image');
            } else {
                $this->presenter->terminate();
            }
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Aktualizuje poradi obrazku
     * @param string $json
     */
    public function handleUpdatePosition($json) {
        if ($this->presenter->isAjax()) {
            $data = \Nette\Utils\Json::decode($json);
            $this->getStorage()->updatePosition($data);
        }
        $this->presenter->terminate();
    }

    /**
     * Vytvori komponentu upload
     * @return \Echo511\Plupload\Control\PluploadControl
     */
    protected function createComponentPlupload() {
        $plupload = $this->plupload->create();
        $plupload->maxFileSize = $this->maxImagesSize . 'mb';
        $plupload->maxChunkSize = $this->maxImageSize . 'mb';
        $plupload->onFileUploaded[] = $this->onUpload;
        $plupload->onUploadComplete[] = $this->onCompleted;

        $plupload->templateFile = __DIR__ . '/plupload.latte';
        return $plupload;
    }

    /**
     * Zmeni namespace
     * @param string $namespace
     */
    public function changeNamespace($namespace) {
        $this->setNamespace($namespace);
        $result = $this->getStorage()->fetchAll();
        foreach ($result as $row) {
            $image = $this->imageStorage->get($row->image);
            $name = $this->imageStorage->saveImage(Image::fromFile($image->getAbsolutePath()), $image->getName(), $this->namespace);
            $this->getStorage()->update($row->key, $name);
            $this->imageStorage->delete($row->image);
        }
    }

    /**
     * Po nahrani obrazku
     * @param UploadQueue $uploadQueue
     */
    public function onUpload(UploadQueue $uploadQueue) {
        if ($this->presenter->isAjax()) {
            $uploads = $uploadQueue->getAllUploads();
            foreach ($uploads as $upload) {
                $image = $this->imageStorage->saveImage(Image::fromFile($upload->getFilename()), $upload->getName(), $this->namespace);
                unlink($upload->getFilename());
                $this->getStorage()->add($image);
            }
        }
        $this->presenter->terminate();
    }

    /**
     * Po skonceni nahravani obrazku
     * @param UploadQueue $uploadQueue
     */
    public function onCompleted(UploadQueue $uploadQueue) {
        if ($this->presenter->isAjax()) {
            $this->redrawControl('gallery');
        } else {
            $this->presenter->terminate();
        }
    }

    /**
     * Nastavi cizi klic
     * @param string $key
     * @param string $value
     */
    public function setForeignKey($key, $value) {
        if ($this->storage instanceof Storage\NetteDatabaseStorage) {
            $this->storage->setForeignKey($key, $value);
        } else {
            throw new \Nette\InvalidArgumentException('Uloziste musi byt database');
        }
    }

    public function clearTemp() {
        if ($this->storage instanceof Storage\SessionStorage) {
            $this->storage->clearTemp();
        } else {
            throw new \Nette\InvalidArgumentException('Uloziste musi byt session');
        }
    }

    public function render() {
        $this->template->images = $this->getStorage()->fetchAll();

        $this->template->componentId = $this->getUniqueId();
        $this->template->imageStorage = $this->imageStorage;

        $this->template->setFile(__DIR__ . '/gallery.latte');
        $this->template->render();
    }

}

interface IGalleryFactory {

    /** @return Gallery */
    public function create();
}
