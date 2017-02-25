# Galerie pro Nette Framework
Nastavení v **config.neon**
```neon
extensions:
    gallery: NAttreid\Gallery\DI\GalleryExtension

gallery:
    maxFileSize: 5 #MB
    maxFiles: 50
```

### Načtení továrny
```php
/** @var \NAttreid\Gallery\Control\IGalleryFactory @inject */
public $galleryFactory;
```

### Použítí s databází
```php
function createComponentGalleryDB() {
    $model = $this->db->table('example');

    $gallery = $this->galleryFactory->create();
    $gallery->setStorage($model);
    $gallery->setNamespace('example/class');
    $gallery->setForeignKey('foreignKey', 5);
    return $gallery;
}
```

### Použití se session
```php
function createComponentGallerySession() {
    $session = $this->getSession('example/class');

    $gallery = $this->galleryFactory->create();
    $gallery->setStorage($session, 'nameOfVariableInSession');
    $gallery->setNamespace('example/class');
    return $gallery;
}
```

### Použití s cms konfigurátorem
```php
/** @var \NAttreid\Cms\Configurator\Configurator @inject */
public $configurator;

function createComponentGalleryConfigurator() {
    $gallery = $this->galleryFactory->create();
    $gallery->setStorage($this->configurator, 'nameOfVariableInSession');
    $gallery->setNamespace('example/class');
    return $gallery;
}
```

### Použití ve formuláři
```php
protected function createComponentGallery() {
    $session = $this->getSession('example/class');
    $session->setExpiration('1 hour');

    $gallery = $this->galleryFactory->create();
    $gallery->setStorage($session, 'example');
    return $gallery;
}

function onSuccessForm(Form $form, $values) {
    $createGallery = empty($values->id);
    $id = $this->model->save($values->id, $values)->getPrimary();  

    if ($createGallery) {
        /* @var $gallery \NAttreid\Gallery\Gallery */
        $gallery=$this['gallery'];

        $gallery->changeNamespace('item/' . $values->url);
        $this->imageModel->add($id, $gallery->getImages());
        $gallery->clearTemp();
    }
}
```