<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

try {
    $sql = 'SELECT COUNT(id) as qty_news FROM news';
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $number_of_result = $row['qty_news'];
        $results_per_page = 3;
        $number_of_page = ceil($number_of_result / $results_per_page);
        $page = 1;
        if (isset($_GET['page'])) {
            $page = intval(sanitize($_GET['page']));
            $page = $conn->real_escape_string($page);
        }
        $page_first_result = ($page - 1) * $results_per_page;
        $sql = 'SELECT * FROM news LIMIT ?, ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $page_first_result, $results_per_page);
        $stmt->execute();
        $res = $stmt->get_result();
        $news_data = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    die('Error getting data from database: ' . $e->getMessage());
}

require_once './layout/header.php';
?>

<div id="contents-with-footer">
    <div id="content-app">
        <div class="container py-4">
            <div class="news">
                <?php
                foreach ($news_data as $new) :
                ?>
                    <div class="shadow-sm bg-white rounded overflow-hidden row mx-0 align-items-center news__item">
                        <div class="col-4 ps-0">
                            <div class="news__item-img" data-img="<?= $new['image'] ?>"></div>
                        </div>
                        <div class="col-8 pe-4">
                            <div class="py-3 news__item-content">
                                <h4 class="news__item-heading"><?= $new['name'] ?></h4>
                                <p class="ms-3 news__item-desc"><?= $new['description'] ?></p>
                                <a class="d-inline-block px-2 btn-primary" href="./<?= $new['url'] ?>">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php
                endforeach;
                ?>
            </div>
            <nav aria-label="Page navigation example" class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= ($page - 1) < 1 ? $number_of_page : $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php
                    for ($i = 0; $i < $number_of_page; $i++) :
                    ?>
                        <li class="page-item"><a class="page-link<?= (($i + 1) == $page) ? ' active' : '' ?>" href="?page=<?= $i + 1 ?>"><?= $i + 1 ?></a></li>
                    <?php
                    endfor;
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= ($page + 1) > $number_of_page ? 1 : $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <?php
    require_once './layout/footer.php';
    ?>