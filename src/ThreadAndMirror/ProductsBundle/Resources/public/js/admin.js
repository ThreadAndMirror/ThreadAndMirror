/**
 * Callback to add a product gallery product html to it's relevant section
 */
function insertProductGalleryProduct(data, originator) {
    $('.section-product-gallery').each(function () {
        if ($(this).data('id') == data.meta.section) {
            var container = $(this).children('ul.sortable');
            var exists = false;

            container.children('li').each(function () {
                if ($(this).data('id') == data.meta.product) {
                    exists = true;
                }
            });

            if (!exists) {
                container.append(data.html);
            }
        }
    });
}

/**
 * Callback to update a product gallery product html
 */
function updateProductGalleryProduct(data, originator) {
    $('.section-product-gallery').each(function () {
        if ($(this).data('id') == data.meta.section) {
            var products = $(this).find('.section-product-gallery-form__item');
            products.each(function () {
                if ($(this).data('id') == data.meta.product) {
                    $(this).replaceWith(data.html);
                }
            });
        }
    });
}

$(document).ready(function () {

    /**
     * Update product gallery product popup picture preview. Peter piper picked...
     */
    $('body').on('change', '#section_productgalleryproduct_type_image', function (e) {
        $('.popup-image-preview').css('background-image', 'url(\'' + $(this).val() + '\')');
    });

    /**
     * Add product to product gallery
     */
    $('.admin-editor').on('click', '.section-product-gallery-form__item-btn.from-link', function (e) {
        e.preventDefault();
        var add_dialogue = $(this).parent();
        var add_url = add_dialogue.children('input[type=text]').val();

        if (add_url.length > 3) {
            add_dialogue.children().hide();
            add_dialogue.children('.loader').css('display', 'block');

            $.post('/rest/products/add-product-to-gallery-section/' + add_dialogue.data('productgallery-id'), {
                url: add_url
            }).done(function (data) {
                add_dialogue.parent().children('ul').append($(data.html));
                add_dialogue.children().show();
                add_dialogue.children('.loader').css('display', 'none');
                add_dialogue.children('input[type=text]').val('');
                window[data.callback](data, $(this));
            }).fail(function () {
                add_dialogue.children().show();
                add_dialogue.children('.loader').css('display', 'none');
            });
        }
    });

    /**
     * Add product to product section
     */
    $('.admin-editor').on('click', 'section.section-product .add-product .from-link', function (e) {

        e.preventDefault();
        var originator = $(this);

        var add_dialogue = originator.parent();
        var add_url = add_dialogue.children('input[type=text]').val();

        if (add_url.length > 3) {
            add_dialogue.children().hide();
            add_dialogue.children('.loader').css('display', 'block');

            $.post($(this).attr('href'), {
                url: add_url
            }).done(function (data) {
                if (data.status == 'success') {
                    window[data.callback](data, originator);
                } else {
                    add_dialogue.children().show();
                    add_dialogue.children('.loader').hide();
                }
            }).fail(function () {
                add_dialogue.children().show();
                add_dialogue.children('.loader').hide();
            });
        }
    });

    /**
     * Remove product from product gallery
     */
    $('.admin-editor').on('click', '.remove-productgalleryproduct', function (e) {
        e.preventDefault();
        $(this).parent().remove();
    });

});