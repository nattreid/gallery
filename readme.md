# Galerie pro Nette Framework
Nastavení v **config.neon**
```neon
gallery:
    maxImageSize: 2 #MB
    maxImagesSize: 50 # MB
```

Nacteni tovarny
```php
/** @var \App\CrmModule\Components\Gallery\IGalleryFactory @inject */
public $galleryFactory;
```

Použítí s databází
```php
function createComponentGalleryDB() {
    $model = $this->db->table('example');

    $gallery = $this->galleryFactory->create();
    $gallery->setModel($model);
    $gallery->setNamespace('example/class');
    $gallery->setForeignKey('foreignKey', 5);
    return $gallery;
}
```

Použití s session
```php
function createComponentGallerySession() {
    $session = $this->getSession('example/class');

    $gallery = $this->galleryFactory->create();
    $gallery->setModel($session);
    $gallery->setNamespace('example/class');
    return $gallery;
}
```

Použití ve formuláři
```php
function createComponentGallery() {
    $gallery = $this->galleryFactory->create();

    $gallery->setNamespace('example/class');

    $id = $this->getParameter('id');
    if (!empty($id)) {
        $gallery->setModel($this->db->table('example'));
        $gallery->setForeignKey('foreignKey', $id);
    }

    return $gallery;
}

function onSuccessForm(Form $form) {
    $values = $form->getValues();

    $id = $values->id;
    if (empty($id)) {
        $this->model->insert(
            $values,                        // parametry formulare
            $this['gallery']->getImages()   // vrati obrazky z galerie a vymaze temp
        );
    } else {
        $this->model->update($id, $values);
    }

    $this->redirect('list');
}
```