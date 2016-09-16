<?php

namespace NAttreid\Gallery\Control;

use NAttreid\Gallery\Control\Image as NImage;
use NAttreid\Gallery\Lang\Translator;
use NAttreid\Gallery\Storage\IStorage;
use NAttreid\Gallery\Storage\NetteDatabaseStorage;
use NAttreid\Gallery\Storage\NextrasOrmStorage;
use NAttreid\Gallery\Storage\SessionStorage;
use NAttreid\Orm\Repository;
use Nette\Application\UI\Control;
use Nette\Database\Table\Selection;
use Nette\Http\Request;
use Nette\Http\SessionSection;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;
use Nette\Utils\Image;
use Nette\Utils\Json;
use WebChemistry\Images\AbstractStorage;

/**
 * Galerie
 *
 * @author Attreid <attreid@gmail.com>
 */
class Gallery extends Control
{
	/** @var AbstractStorage */
	private $imageStorage;

	/** @var IStorage */
	private $storage;

	/** @var string */
	private $namespace;

	/** @var int */
	private $maxFiles;

	/** @var int */
	private $maxFileSize;

	/** @var ITranslator */
	private $translator;

	/** @var Request */
	private $request;

	public function __construct($maxFileSize, $maxFiles, AbstractStorage $imageStorage, Request $request)
	{
		parent::__construct();
		$this->maxFileSize = $maxFileSize;
		$this->maxFiles = $maxFiles;
		$this->imageStorage = $imageStorage;
		$this->translator = new Translator;
		$this->request = $request;
	}

	/**
	 * Nastavi translator
	 * @param ITranslator $translator
	 */
	public function setTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * Vrati Translator
	 * @return Translator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}

	/**
	 * Vrati uloziste
	 * @return IStorage
	 * @throws InvalidArgumentException
	 */
	private function getStorage()
	{
		if ($this->storage === NULL) {
			throw new InvalidArgumentException('Storage is not set');
		}
		return $this->storage;
	}

	/**
	 * Nastavi uloziste
	 * @param Selection|SessionSection|Repository $storage
	 * @param string $name
	 * @param string $position
	 * @param string $key
	 * @internal param string $column
	 */
	public function setStorage($storage, $name = 'name', $position = 'position', $key = 'id')
	{
		if ($storage instanceof Selection) {
			$this->storage = new NetteDatabaseStorage($storage, $name, $position, $key);
		} elseif ($storage instanceof Repository) {
			$this->storage = new NextrasOrmStorage($storage, $name, $position, $key);
		} elseif ($storage instanceof SessionSection) {
			$this->storage = new SessionStorage($storage);
		}
	}

	/**
	 * @return NImage[]
	 */
	public function getImages()
	{
		return $this->getStorage()->fetchAll();
	}

	/**
	 * Nastavi namespace
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Smaze vsechny obrazky z modelu
	 */
	public function handleDeleteAllImages()
	{
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
	public function handleDeleteImages($json)
	{
		if ($this->presenter->isAjax()) {
			$data = Json::decode($json);

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
	public function handleDeleteImage($id)
	{
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
	public function handleShowViewer($id)
	{
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
	public function handleNextImage($id)
	{
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
	public function handlePreviousImage($id)
	{
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
	public function handleUpdatePosition($json)
	{
		if ($this->presenter->isAjax()) {
			$data = Json::decode($json);
			$this->getStorage()->updatePosition($data);
		}
		$this->presenter->terminate();
	}

	/**
	 * Zmeni namespace
	 * @param string $namespace
	 */
	public function changeNamespace($namespace)
	{
		$this->setNamespace($namespace);
		$result = $this->getStorage()->fetchAll();
		foreach ($result as $row) {
			$image = $this->imageStorage->get($row->name);
			$name = $this->imageStorage->saveImage(Image::fromFile($image->getAbsolutePath()), $image->getName(), $this->namespace);
			$this->getStorage()->update($row->key, $name);
			$this->imageStorage->delete($row->name);
		}
	}

	/**
	 * Nastavi cizi klic
	 * @param string $key
	 * @param string $value
	 */
	public function setForeignKey($key, $value)
	{
		if ($this->storage instanceof NetteDatabaseStorage) {
			$this->storage->setForeignKey($key, $value);
		} elseif ($this->storage instanceof NextrasOrmStorage) {
			$this->storage->setForeignKey($key, $value);
		} else {
			throw new InvalidArgumentException('Storage is not database');
		}
	}

	/**
	 * Smaze temp adresar
	 */
	public function clearTemp()
	{
		if ($this->storage instanceof SessionStorage) {
			$this->storage->clearTemp();
		} else {
			throw new InvalidArgumentException('Storage is not session');
		}
	}

	/**
	 *
	 */
	public function handleUpload()
	{
		if ($this->presenter->isAjax()) {
			$file = $this->request->getFile('file');
			if ($file->error !== UPLOAD_ERR_OK) {
				header('HTTP/1.1 500 Internal Server Error');
				header('Content-type: text/plain');
				switch ($file->error) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$msg = 'tooBig';
						break;
					case UPLOAD_ERR_NO_FILE:
						$msg = 'noFile';
						break;
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_TMP_DIR:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						$msg = 'failedUpload';
						break;
				}
				exit($this->translator->translate('gallery.error.' . $msg));
			}

			$image = $this->imageStorage->saveImage(Image::fromFile($file->temporaryFile), $file->sanitizedName, $this->namespace);
			$this->getStorage()->add($image);
		}
		$this->presenter->terminate();
	}

	/**
	 * Obnovi galerii
	 */
	public function handleRefresh()
	{
		if ($this->presenter->isAjax()) {
			$this->redrawControl('gallery');
		} else {
			$this->presenter->terminate();
		}
	}

	public function render()
	{
		$this->template->addFilter('translate', [$this->translator, 'translate']);

		$this->template->images = $this->getStorage()->fetchAll();

		$this->template->componentId = $this->getUniqueId();
		$this->template->imageStorage = $this->imageStorage;
		$this->template->maxFileSize = $this->maxFileSize;
		$this->template->maxFiles = $this->maxFiles;

		$this->template->setFile(__DIR__ . '/gallery.latte');
		$this->template->render();
	}

}

interface IGalleryFactory
{

	/** @return Gallery */
	public function create();
}