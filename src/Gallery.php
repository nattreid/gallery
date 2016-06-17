<?php

namespace NAttreid\Gallery;

use Nette\Application\UI\Control,
    NAttreid\Gallery\Plupload\UploadQueue,
    NAttreid\Gallery\Plupload\IPluploadControlFactory,
    Nette\Database\Table\Selection,
    WebChemistry\Images\AbstractStorage,
    Nette\Utils\Image;

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

    /** @var Model */
    private $model;

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

    private function getModel() {
        if ($this->model === NULL) {
            $session = $this->presenter->getSession('gallery/temp');
            $session->setExpiration('1 hour');
            $this->model = new Model($session);
        }
        return $this->model;
    }

    /**
     * Nastavi uloziste
     * @param $storage Selection | \Nette\Http\Session | \Nette\Http\SessionSection $model
     */
    public function setModel($storage) {
        $this->model = new Model($storage);
    }

    /**
     * Vrati obrazky a pokud je model Session tak ji vymaze
     * @return Selection|array
     */
    public function getImages() {
        return $this->getModel()->fetchAll(TRUE);
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
            $result = $this->getModel()->delete();
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

            $result = $this->getModel()->delete($data);
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
            $result = $this->getModel()->delete($id);
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
            $this->template->viewImage = $this->getModel()->get($id);

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
            $row = $this->getModel()->getNext($id);
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
            $row = $this->getModel()->getPrevious($id);
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
            $this->getModel()->updatePosition($data);
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
        $result = $this->getModel()->fetchAll();
        foreach ($result as $row) {
            $image = $this->imageStorage->get($row->{$this->model->image});
            $name = $this->imageStorage->saveImage(Image::fromFile($image->getAbsolutePath()), $image->getName(), $this->namespace);
            $this->getModel()->update($row->{$this->model->id}, $name);
            $this->imageStorage->delete($row->{$this->model->image});
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
                $this->getModel()->add($image);
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
        if ($this->model === NULL) {
            throw new \Nette\InvalidArgumentException('Model neni nastaven');
        }
        $this->model->setForeignKey($key, $value);
    }

    public function render() {
        $model = $this->getModel();
        $this->template->images = $model->fetchAll();
        $this->template->model = $model;

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
