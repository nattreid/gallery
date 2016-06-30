# Galerie pro Nette Framework
Nastavení v **config.neon**
```neon
extensions:
    - NAttreid\Gallery\DI\GalleryExtension

gallery:
    maxImageSize: 2 #MB
    maxImagesSize: 50 # MB
```

Pokud používáte bower, upravte css
```css
.plupload_logo {
    background-image: url('/images/plupload/plupload.png');
}
.plupload_thumb_loading {
    background-image: url('/images/plupload/loading.gif');
}
```

### Načtení továrny
```php
/** @var \NAttreid\Gallery\IGalleryFactory @inject */
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

### Použití s session
```php
function createComponentGallerySession() {
    $session = $this->getSession('example/class');

    $gallery = $this->galleryFactory->create();
    $gallery->setStorage($session);
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
    $gallery->setStorage($session);
    return $gallery;
}

function onSuccessForm(Form $form) {
    $values = $form->getValues();
    
    $createGallery = empty($values->id);
    $id = $this->model->save($values->id, $values)->getPrimary();  

    if ($createGallery) {
        /** @var $gallery \NAttreid\Gallery\Gallery */
        $gallery=$this['gallery'];

        $gallery->changeNamespace('item/' . $values->url);
        $this->imageModel->add($id, $gallery->getImages());
        $gallery->clearTemp();
    }
}
```