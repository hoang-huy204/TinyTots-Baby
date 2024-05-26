<script>
    $(document).ready(function() {
        // prevent clicking behavior on current page path
        $('.admin__nav-item-link--active').on('click', function(e) {
            e.preventDefault();
        });

        // page orders
        $('.del-order-btn').on('click', function(e) {
            let option = window.confirm('Do you want to delete order ?');
            if (option) {
                window.location.href = `./order_view.php?remove=${$(this).data('id')}`;
            }
        });

        // page menu
        $('.edit-menu-btn').on('click', function(e) {
            let option = window.confirm('Do you want to edit menu ?');
            if (option) {
                window.location.href = `./edit_menu.php?edit=${$(this).data('id')}`;
            }
        });

        // page franchising
        $('.del-franchising-btn').on('click', function(e) {
            let option = window.confirm('Do you want to delete franchising ?');
            if (option) {
                window.location.href = `./franchising.php?delete=${$(this).data('id')}`;
            }
        });

        // page categories
        $('.edit-cate-btn').on('click', function(e) {
            let option = window.confirm('Do you want to restore category ?');
            if (option) {
                window.location.href = `./change_category.php?edit=${$(this).data('id')}`;
            }
        });
        $('.del-cate-btn').on('click', function(e) {
            let option = window.confirm('If a category is deleted, the products of that category will be deleted accordingly\nAre you sure ?');
            if (option) {
                window.location.href = `./change_category.php?delete=${$(this).data('id')}`;
            }
        });
        $('.restore-cate-btn').on('click', function(e) {
            let option = window.confirm('Do you want to restore category ?');
            if (option) {
                window.location.href = `./change_category.php?restore=${$(this).data('id')}`;
            }
        });

        // page products
        $('.edit-product-btn').on('click', function(e) {
            let option = window.confirm('Do you want to edit product ?');
            if (option) {
                window.location.href = `./change_product.php?edit=${$(this).data('id')}`;
            }
        });
        $('.del-product-btn').on('click', function(e) {
            let option = window.confirm('Do you want to delete product ?');
            if (option) {
                window.location.href = `./change_product.php?delete=${$(this).data('id')}`;
            }
        });
        $('.restore-product-btn').on('click', function(e) {
            let option = window.confirm('Do you want to restore product ?');
            if (option) {
                window.location.href = `./change_product.php?restore=${$(this).data('id')}`;
            }
        });

        // page brands
        $('.edit-brand-btn').on('click', function(e) {
            let option = window.confirm('Do you want to edit brand ?');
            if (option) {
                window.location.href = `./change_brand.php?edit=${$(this).data('id')}`;
            }
        });
        $('.del-brand-btn').on('click', function(e) {
            let option = window.confirm('Do you want to delete brand ?');
            if (option) {
                window.location.href = `./change_brand.php?delete=${$(this).data('id')}`;
            }
        });

        // page news
        $('.edit-news-btn').on('click', function(e) {
            let option = window.confirm('Do you want to edit news ?');
            if (option) {
                window.location.href = `./change_news.php?edit=${$(this).data('id')}`;
            }
        });
        $('.del-news-btn').on('click', function(e) {
            let option = window.confirm('Do you want to delete news ?');
            if (option) {
                window.location.href = `./change_news.php?delete=${$(this).data('id')}`;
            }
        });
    });
</script>