$(document).ready(function () {

    // page home

    // search products
    $('.header__search-btn').on('click', function () {
        let search_val = $('#header-search').val();
        if (search_val.length < 3) {
            window.alert('Please enter at least 3 characters')
        } else {
            window.location.href = './search_products.php?value=' + search_val;
        }
    });

    // slider
    $('.slider__img-box').each(function () {
        let img_path = './assets/img/slider/' + $(this).data('image');
        $(this).css('background-image', 'url(' + img_path + ')');
    });
    
    $('.owl-carousel').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 6000,
        autoplaySpeed: 1000,
        responsive: {
            0: {
                items: 1
            }
        }
    });

    // image product
    $('.products__item-img').each(function () {
        let img_path = './assets/img/products/' + $(this).data('img');
        $(this).css('background-image', 'url(' + img_path + ')');
    });
    // action when clicking on product
    $('.products__item').on('click', function (e) {
        if (!e.target.closest('.btn-primary')) {
            window.location.replace('./product_detail.php?id=' + $(this).data('id'));
        }
    });

    // add / remove cart
    $('.btn-cart').on('click', function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).text('Add to cart');
            $.ajax({
                url: `./change_cart.php?remove=${$(this).data('id')}`,
                success: function (response) {
                    $('.header__cart-quantity').text(response);
                },
                error: function (xhr, status, error) {
                    console.error('Error add product id cart: ', error);
                }
            });
        } else {
            let product_qty = 1;
            if ($('.product__qty-input').length > 0) {
                product_qty = $('.product__qty-input').val();
                if (product_qty < 0) {
                    window.alert('The number of products should not be less than 1');
                    return false;
                }
            }
            $(this).addClass('active');
            $(this).text('Remove to cart');
            $.ajax({
                url: `./change_cart.php?add=${$(this).data('id')}&quantity=${product_qty}`,
                success: function (response) {
                    $('.header__cart-quantity').text(response);
                },
                error: function (xhr, status, error) {
                    console.error('Error add product id into cart: ', error);
                }
            });
        }
    });

    // buy product
    $('.buy-btn').on('click', function () {
        let closest_parent_elm = $(this).closest('.btns-primary');
        let cart_btn_elm = closest_parent_elm.find('.btn-cart');
        if (cart_btn_elm.hasClass('active')) {
            window.location.href = './cart.php';
        } else {
            let product_qty = 1;
            if ($('.product__qty-input').length > 0) {
                product_qty = $('.product__qty-input').val();
                if (product_qty < 0) {
                    window.alert('The number of products should not be less than 1');
                    return false;
                }
            }

            $.ajax({
                url: `./change_cart.php?add=${$(this).data('id')}&quantity=${product_qty}`,
                success: function (response) {
                    window.location.href = './cart.php';
                },
                error: function (error) {
                    console.error('Error add product id into cart: ', error);
                }
            });
        }
    });

    // Page product detail
    // load image
    // $('.product-detail__img').each(function() {
    //     let img_path = './assets/img/products/' + $(this).data('img');
    //     $(this).css('background-image', 'url(' + img_path + ')');
    // });
    $('.product-detail__img').css('background-image', 'url(' + './assets/img/products/' + $('.product-detail__img').data('img') + ')');


    // increase/decrease the number of products
    $('.product__qty-btn--decrease').on('click', function () {
        let parent_elm = $(this).parent();
        let inp_qty_elm = parent_elm.find('.product__qty-input');
        let quantity_prod = parseInt(inp_qty_elm.val()) - 1;
        if (quantity_prod >= 1) inp_qty_elm.val(quantity_prod);
    });
    $('.product__qty-btn--increase').on('click', function () {
        let parent_elm = $(this).parent();
        let inp_qty_elm = parent_elm.find('.product__qty-input');
        let quantity_prod = parseInt(inp_qty_elm.val()) + 1;
        inp_qty_elm.val(quantity_prod);
    });

    // page news
    $('.news__item-img').each(function () {
        let img_path = './assets/img/news/' + $(this).data('img');
        $(this).css('background-image', 'url(' + img_path + ')');
    });

    function calculate_bill() {
        let total_amount_payable = 0;
        $('.cart__item').each(function () {
            let product_price = $(this).find('.cart__product-price').text();
            product_price = product_price.replace(/\$|\s/g, '');
            product_price = parseFloat(product_price);
            let product_quantity = $(this).find('.cart__product-qty-input').val();
            product_quantity = parseInt(product_quantity);
            let total_price = product_price * product_quantity;
            $(this).find('.cart__money').text('$ ' + total_price.toFixed(2));
            total_amount_payable += total_price
        });
        $('.total-amount-payable').html(`<strong>the total amount: $ ${total_amount_payable.toFixed(2)}</strong>`);
    }
    calculate_bill();
    $('.cart__product-qty-input').on('change', function () {
        let product_qty = $(this).val();
        if (product_qty < 1) {
            window.alert('Invalid product quantity');
            $(this).val(1);
            return;
        }
        $.ajax({
            url: `./change_cart.php?edit=${$(this).data('id')}&quantity=${product_qty}`,
            success: function () {
                calculate_bill();
            },
            error: function (error) {
                console.error('Error add product id into cart: ', error);
            }
        });
    });

    $('.cart_close_btn').on('click', function () {
        let option = window.confirm('Do you want to remove the product from the cart?');
        if (option) {
            let closestParent = $(this).closest('.cart__item');
            $.ajax({
                url: `./change_cart.php?remove=${$(this).data('id')}`,
                success: function (response) {
                    $('.header__cart-quantity').text(response);
                    closestParent.remove();
                    calculate_bill();
                },
                error: function (error) {
                    console.error('Error add product id cart: ', error);
                }
            });
        }

    });
});