<?php

use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Models\Category;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;

$router = new RouteCollector();

# định nghĩa filter
$router->filter('auth', function () {
    if (!isset($_SESSION[AUTH]) || empty($_SESSION[AUTH])) {
        header('location: ' . BASE_URL . 'login');
        die;
    }
});

# kết thúc định nghĩa filter
$router->group(['prefix' => 'admin', 'before' => 'auth'], function ($router) {
    $router->get('/', [HomeController::class, "index"]);
    $router->group(['prefix' => 'danh-muc'], function ($router) {

        $router->get('/', [HomeController::class, "index"]);
        // Route có áp dụng filter auth được định nghĩa ở phía trên
        $router->get('/add', [CategoryController::class, "addNew"]);
        $router->post('/add', [CategoryController::class, "saveCate"]);
        $router->post('/check-cate-name', [CategoryController::class, 'checkNameExisted']);
    });

    $router->group(['prefix' => 'san-pham'], function ($router) {

        $router->get('/', [ProductController::class, 'index']);
    });
});

$router->get('/', function () {
    return "Trang chủ";
});

// tham số tùy chọn: {name}?
// tham số bắt buộc: {id}
$router->get('/thong-tin-san-pham/{id}', [ProductController::class, "detail"]);
$router->get('/danh-muc-san-pham/{id}', [CategoryController::class, 'listProduct']);

// giỏ hàng
$router->get('/add-to-cart/{id}', [HomeController::class, 'addToCart']);
$router->get('/gio-hang', [HomeController::class, 'cartDetail']);
$router->get('/clear-gio-hang', [HomeController::class, 'clearCart']);

# Authenticate
$router->any('/logout', [HomeController::class, "logout"]);
$router->get('/login', [HomeController::class, 'loginForm']);
$router->post('/login', [HomeController::class, 'postLogin']);
# End Authenticate

$router->get('/error-404', function () {
    return "đường dẫn không tồn tại";
});


$router->get('fake-product-gallery', [ProductController::class, 'fakeGallery']);
$router->get('fake-users', [HomeController::class, 'fakeUser']);


$dispatcher = new Dispatcher($router->getData());
try {
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($url, PHP_URL_PATH));
} catch (HttpRouteNotFoundException $ex) {
    header('location: ' . BASE_URL . 'error-404');
    die;
}


// Print out the value returned from the dispatched function
echo $response;
