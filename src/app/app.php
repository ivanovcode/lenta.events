<?php

ini_set('error_reporting', E_ALL); ini_set('display_errors', 1); ini_set('display_startup_errors', 1);
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: *");

define('DIR', __DIR__  . '/..'); define('APP', __DIR__  . '/../app'); define('TPL', __DIR__  . '/../app/views/default/templates');

foreach (glob(APP . '/includes/*.php') as $file) require_once($file);

require_once(DIR  . '/vendor/autoload.php');

if (!class_exists('Dotenv\Dotenv')) die('Dotenv\Dotenv don`t exists');

$dotenv = Dotenv\Dotenv::create(DIR); $dotenv->load();
$config = parse_ini_file(APP . '/config.ini', true);

try { 
    $db = new PDO("mysql:host=".getenv('DB_HOST').";dbname=".getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD')); 
    $db->query("SET NAMES utf8");  
} catch (PDOException $error) { 
    die('MySQL don`t connect');
}

$router = new Router();

$router->get('/login', function() {
    session_start();
    $staff = getAuth();
    if (isset($_SESSION['user'])?(!empty($_SESSION['user']) && $_SESSION['user'] == $staff['login']):false) reroute('/my');
    echo websun_parse_template_path([], TPL . '/login.tpl');
});

$router->post('/signup', function() {
});

$router->post('/signin', function() {
    if(isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        $staff = $GLOBALS['db']->query("
            SELECT
            s.login,
            s.password
            FROM
            staffs as s
            WHERE
            s.login = '".$_SERVER['PHP_AUTH_USER']."'
        ");

        $staff = $staff->fetch(PDO::FETCH_ASSOC);

        if (count($staff)>0) {
            if (!($_SERVER['PHP_AUTH_USER'] ==  $staff['login']
                && md5($_SERVER['PHP_AUTH_PW']) ==  $staff['password'])) {
                header('HTTP/1.1 401 Unauthorized'); exit;
            }
        } else {
            header('HTTP/1.1 401 Unauthorized'); exit;
        }
    }
    session_start();
    $_SESSION['user'] = $_SERVER['PHP_AUTH_USER']; //$_SESSION['user_session'] = md5(rand());

    echo json_encode(array('success'=>true, 'message'=>''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
});

$router->get('/signout', function() {
    session_start(); unset($_SESSION['user']); session_destroy();
    reroute('/login');
});

$router->get('/my', function() {
    reroute('/my/posts');
});

$router->get('/my/posts', function() {
    $staff = auth();

    $data = [];
    $data['user'] = $_SESSION['user']; 
    $data['page'] =  'posts';

    $posts = $GLOBALS['db']->query("
        SELECT
        p.id,
        p.datecreate,
        p.datepublic,
        p.id_group,
        p.type,
        p.name,
        p.content
        FROM posts p
    ");

    $data['post'] = $posts->fetchAll(PDO::FETCH_ASSOC);

    $data['container'] = $data['page'].'.tpl';
    //print("<pre>".print_r($data, true)."</pre>"); die();
    echo websun_parse_template_path($data, TPL . '/container.tpl');
});

$router->run();