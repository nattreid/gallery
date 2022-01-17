<?php

declare(strict_types=1);

namespace NAttreid\Gallery\Control;

use NAttreid\Cms\Configurator\Configurator;
use NAttreid\Cms\Configurator\IConfigurator;
use NAttreid\Gallery\Control\Image as NImage;
use NAttreid\Gallery\Lang\Translator;
use NAttreid\Gallery\Storage\ConfiguratorStorage;
use NAttreid\Gallery\Storage\IStorage;
use NAttreid\Gallery\Storage\NetteDatabaseStorage;
use NAttreid\Gallery\Storage\NextrasOrmStorage;
use NAttreid\Gallery\Storage\SessionStorage;
use NAttreid\ImageStorage\ImageStorage;
use NAttreid\Orm\Repository;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Table\Selection;
use Nette\Http\Request;
use Nette\Http\SessionSection;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;
use Nette\Utils\Image;
use Nette\Utils\Json;
use \Closure;

/**
 * Galerie
 *
 * @author Attreid <attreid@gmail.com>
 */
class Gallery extends Control
{
	/** @var ImageStorage */
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

	/** @var Closure */
	private $onSuccessUpload = null;

	public function __construct(int $maxFileSize, int $maxFiles, ImageStorage $imageStorage, Request $request)
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
	public function setTranslator(ITranslator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * Vrati Translator
	 * @return Translator
	 */
	public function getTranslator(): Translator
	{
		return $this->translator;
	}

	/**
	 * Vrati uloziste
	 * @return IStorage
	 * @throws InvalidArgumentException
	 */
	private function getStorage(): IStorage
	{
		if ($this->storage === null) {
			throw new InvalidArgumentException('Storage is not set');
		}
		return $this->storage;
	}

	/**
	 * Nastavi uloziste
	 * @param Selection|SessionSection|Repository|IConfigurator $storage
	 * @param string $name nazev sloupce nebo promenne kde se uklada nazev obrazku (sloupce v databazi, promenna pro Session a Configurator)
	 * @param string $position nazev sloupce pro pozici (pouze pro databazove Storage)
	 * @param string $key nazev sloupce pro id (pouze pro databazove Storage)
	 * @internal param string $column
	 */
	public function setStorage($storage, string $name = 'name', string $position = 'position', string $key = 'id'): void
	{
		if ($storage instanceof Selection) {
			$this->storage = new NetteDatabaseStorage($storage, $name, $position, $key);
		} elseif ($storage instanceof Repository) {
			$this->storage = new NextrasOrmStorage($storage, $name, $position, $key);
		} elseif ($storage instanceof SessionSection) {
			$this->storage = new SessionStorage($storage, $name);
		} elseif ($storage instanceof Configurator) {
			$this->storage = new ConfiguratorStorage($storage, $name);
		}
	}

	/**
	 * @return NImage[]
	 */
	public function getImages(): array
	{
		return $this->getStorage()->fetchAll();
	}

	/**
	 * Nastavi namespace
	 * @param string $namespace
	 */
	public function setNamespace(string $namespace): void
	{
		$this->namespace = $namespace;
	}

	/**
	 * Smaze vsechny obrazky z modelu
	 * @secured
	 * @throws AbortException
	 */
	public function handleDeleteAllImages(): void
	{
		if ($this->request->isAjax()) {
			$result = $this->getStorage()->delete();
			foreach ($result as $row) {
				$this->imageStorage->delete($row);
			}

			$this->redrawControl('gallery');
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Smaze vybrane obrazky
	 * @param string $json
	 * @secured
	 * @throws \Nette\Utils\JsonException
	 * @throws AbortException
	 */
	public function handleDeleteImages(string $json): void
	{
		if ($this->request->isAjax()) {
			$data = Json::decode($json);

			$result = $this->getStorage()->delete($data);
			foreach ($result as $row) {
				$this->imageStorage->delete($row);
			}

			$this->redrawControl('gallery');
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Smaze obrazek
	 * @param int $id
	 * @secured
	 * @throws AbortException
	 */
	public function handleDeleteImage(int $id): void
	{
		if ($this->request->isAjax()) {
			$result = $this->getStorage()->delete($id);
			$this->imageStorage->delete($result);

			$this->redrawControl('gallery');
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Zobrazi obrazek
	 * @param int $id
	 * @secured
	 * @throws AbortException
	 */
	public function handleShowViewer(int $id): void
	{
		if ($this->request->isAjax()) {
			$this->template->viewImage = $this->getStorage()->get($id);

			$this->redrawControl('viewer');
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Zobrazi dalsi obrazek
	 * @param int $id
	 * @secured
	 * @throws AbortException
	 */
	public function handleNextImage(int $id): void
	{
		if ($this->request->isAjax()) {
			$row = $this->getStorage()->getNext($id);
			if ($row) {
				$this->template->viewImage = $row;
				$this->redrawControl('image');
			} else {
				throw new AbortException;
			}
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Zobrazi predchozi obrazek
	 * @param int $id
	 * @secured
	 * @throws AbortException
	 */
	public function handlePreviousImage(int $id): void
	{
		if ($this->request->isAjax()) {
			$row = $this->getStorage()->getPrevious($id);
			if ($row) {
				$this->template->viewImage = $row;
				$this->redrawControl('image');
			} else {
				throw new AbortException;
			}
		} else {
			throw new AbortException;
		}
	}

	/**
	 * Aktualizuje poradi obrazku
	 * @param string $json
	 * @secured
	 * @throws \Nette\Utils\JsonException
	 * @throws AbortException
	 */
	public function handleUpdatePosition(string $json): void
	{
		if ($this->request->isAjax()) {
			$data = Json::decode($json);
			$this->getStorage()->updatePosition($data);
		}
		throw new AbortException;
	}

	/**
	 * Zmeni namespace
	 * @param string $namespace
	 */
	public function changeNamespace(string $namespace): void
	{
		$this->setNamespace($namespace);
		$result = $this->getStorage()->fetchAll();
		foreach ($result as $row) {
			$resource = $this->imageStorage->getResource($row->name);
			$resource->setNamespace($this->namespace);
			$this->imageStorage->save($resource);

			$this->getStorage()->update($row->key, $resource->getIdentifier());
		}
	}

	/**
	 * Nastavi cizi klic
	 * @param string $keyName
	 * @param int $value
	 */
	public function setForeignKey(string $keyName, int $value): void
	{
		if ($this->storage instanceof NetteDatabaseStorage) {
			$this->storage->setForeignKey($keyName, $value);
		} elseif ($this->storage instanceof NextrasOrmStorage) {
			$this->storage->setForeignKey($keyName, $value);
		} else {
			throw new InvalidArgumentException('Storage is not database');
		}
	}

	/**
	 * Smaze temp adresar
	 */
	public function clearTemp(): void
	{
		if ($this->storage instanceof SessionStorage) {
			$this->storage->clearTemp();
		} else {
			throw new InvalidArgumentException('Storage is not session');
		}
	}

	/**
	 * Upload
	 * @secured
	 * @throws AbortException
	 */
	public function handleUpload(): void
	{
		if ($this->request->isAjax()) {
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
				exit($this->translator->translate('nattreid.gallery.error.' . $msg));
			}

			if ($this->onSuccessUpload === null) {
				$save = true;
			} else {
				$func=$this->onSuccessUpload;
				$save = $func($file);
			}

			if ($save) {
				$resource = $this->imageStorage->createResource($file->temporaryFile, $file->sanitizedName);
				$resource->setNamespace($this->namespace);
				$this->imageStorage->save($resource);

				$this->getStorage()->add($resource->getIdentifier());
			}
		}
		throw new AbortException;
	}

	/**
	 * Obnovi galerii
	 * @secured
	 * @throws AbortException
	 */
	public function handleRefresh(): void
	{
		if ($this->request->isAjax()) {
			$this->redrawControl('gallery');
		} else {
			throw new AbortException;
		}
	}

	/**
	 * @param Closure $func arg FileUpload
	 * @return void
	 */
	public function onSuccessUpload(Closure $func): void
	{
		$this->onSuccessUpload = $func;
	}

	public function render(): void
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
	public function create(): Gallery;
}