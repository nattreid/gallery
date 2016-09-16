(function ($, window) {
    if (window.jQuery === undefined) {
        console.error('Plugin "jQuery" required by "gallery.js" is missing!');
        return;
    } else if (window.Dropzone === undefined) {
        console.error('Plugin "Dropzone.js" required by "gallery.js" is missing!');
        return;
    }

    window.Dropzone.autoDiscover = false;

    window.Gallery = {};

    window.Gallery.viewer;
    window.Gallery.container;
    window.Gallery.loaded;

    window.Gallery.sortable = function () {
        $('.componentGallery .gallery ul').sortable({
            placeholder: 'ui-state-highlight',
            opacity: 0.6,
            update: function (event, ui) {
                var data = new Array;
                $(this).find('img').each(function (index) {
                    data.push($(this).data('id'));
                });
                $.nette.ajax($(this).closest('ul').data('url').replace('jsonArgument', JSON.stringify(data)));
            }
        }).disableSelection();
    };

    window.Gallery.redrawViewer = function () {
        this.loaded = false;
        this.viewer = $('.componentGallery .gallery .viewer');
        this.container = this.viewer.find('.viewer-container');

        if (this.viewer.is(':visible')) {
            this.resize();
        } else {
            this.viewer.css({visibility: 'hidden', display: 'block'});
            this.resize();
            this.viewer.css({visibility: '', display: 'none'});
        }
    };

    window.Gallery.showViewer = function () {
        this.viewer.fadeIn();
    };

    window.Gallery.resize = function () {
        if (this.container) {
            var img = this.container.find('img');

            function resizeImage() {
                var limit = 30;

                img.removeAttr('style');
                var width = img.width();
                var height = img.height();

                var windowWidth = window.innerWidth - limit;
                var windowHeight = window.innerHeight - limit;

                if (width > windowWidth) {
                    height = height / (width / windowWidth);
                    width = windowWidth;
                }
                if (height > windowHeight) {
                    width = width / (height / windowHeight);
                    height = windowHeight;
                }

                img.width(width);
                img.height(height);

                window.Gallery.container.centerFixed();
            }

            if (this.loaded) {
                resizeImage();
            } else {
                img.load(function () {
                    window.Gallery.loaded = true;
                    resizeImage();
                });
            }
        }
    };

    $(document).ready(function () {
        var form = $('.componentGallery .uploader form');
        form.dropzone({
            dictDefaultMessage: form.data('dictdefaultmessage'),
            dictMaxFilesExceeded: form.data('dictmaxfilesexceeded'),
            dictFallbackMessage: form.data('dictfallbackmessage'),
            dictFallbackText: form.data('dictfallbacktext'),
            dictInvalidFileType: form.data('dictinvalidfiletype'),
            dictFileTooBig: form.data('dictfiletoobig'),
            dictResponseError: form.data('dictresponseerror'),
            dictCancelUpload: form.data('dictcancelupload'),
            dictCancelUploadConfirmation: form.data('dictcanceluploadconfirmation'),
            dictRemoveFile: form.data('dictremovefile'),
            dictMaxFilesExceeded: form.data('dictmaxfilesexceeded'),
            acceptedFiles: 'image/jpeg,image/png,image/gif',
            maxFileSize: form.data('max-file-size'),
            maxFiles: form.data('max-files')
        });

        window.Gallery.sortable();

        $(document).on('click', '.componentGallery .buttons .deleteChoosedImage', function () {
            var data = new Array;
            var link = $(this).data('url');
            $('.componentGallery .gallery ul input[type="checkbox"]:checked').each(function () {
                data.push($(this).data('id'));
            });
            $.nette.ajax(link.replace('jsonArgument', JSON.stringify(data)));
        });

        $(document).on('click', '.componentGallery .gallery ul li img', function () {
            var checkbox = $(this).closest('li').find('input[type="checkbox"]');
            checkbox.prop("checked", !checkbox.prop("checked"));
        });

        // viewer
        $(document).on('click', '.componentGallery .gallery .viewer .background, .componentGallery .gallery .viewer .viewerClose', function () {
            $(this).closest('.viewer').fadeOut();
        });

        $(window).on('resize.gallery', function () {
            window.Gallery.resize();
        });

    });

})(jQuery, window);
