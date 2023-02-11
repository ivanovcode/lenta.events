<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define('DIR', __DIR__  . '/..');
define('APP', __DIR__  . '/../app');
define('TPL', __DIR__  . '/../app/views/default/templates');

require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once dirname(__DIR__) . "/app/includes/routes.php";
require_once dirname(__DIR__) . "/app/includes/template.php";
require_once dirname(__DIR__) . "/app/includes/auth.php";
require_once dirname(__DIR__) . "/app/includes/parse.php";
require_once dirname(__DIR__) . "/app/includes/app.php";


$dotenv = initDotenv();
$db     = initDb();
$log    = logg("", true);
$router = new Router();

$config = (object)array(
    'xpath'          => array(
        'image'      => '.serp-item',
        'imageThumb' => '.serp-item__thumb'
    )
);

$GLOBALS['db'] = $db;

$router->get('/login', function() {
    session_start();
    $staff = getAuth();
    if (isset($_SESSION['user'])?(!empty($_SESSION['user']) && $_SESSION['user'] == $staff['login']):false) reroute('/my');
    echo websun_parse_template_path([], TPL . '/login.tpl');
});
$router->post('/signup', function() {});
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
    reroute('/my/events');
});
$router->get('/my/events', function() {
    $auth = auth();

    $status_id = isset($_GET['status']) ? $_GET['status'] : 'new';

    $data = [];
    $data['user'] = $_SESSION['user'];
    $data['page'] = 'posts';

    $data['post'] = query("
        SELECT
            g.id as id_group,
            p.id,
            p.post_discription,
            p.event_discription,
            IF(p.event_image IS NOT NULL, p.event_image, p.post_image) as post_image,
            p.post_date,
            REPLACE(p.post_id, 'post-', '') as post_id,
            g.group_id,
            g.group_title,
            s.status_title
            FROM 
            posts p
        LEFT JOIN 
            groups g 
            ON g.id = p.group_id
        LEFT JOIN 
            statuses s 
            ON s.id = p.status_id
        WHERE 
            s.status_title = '" . $status_id .  "' AND
            g.status_id <> 3
        ORDER BY 
        p.post_date DESC
    ");

    query("SET sql_mode = '';");
    $data['statuses'] = convertPostStatuses(
        query("
            SELECT
                s.status_title,
                SUM(IF(g.status_id <> 3 and p.id IS NOT NULL, 1, 0)) as post_count
            FROM
                statuses s
            LEFT JOIN posts p ON p.status_id = s.id
            LEFT JOIN groups g ON g.id = p.group_id
            WHERE s.is_post IS NOT NULL
            GROUP BY s.id
            ORDER BY s.is_post ASC
        ")
    );

    $data['statuses'] = json(
        $data['post'] ? array_merge(
            $data['statuses'],
            array_count_values(
                array_column($data['post'], 'status_title')
            )
        ) :
            $data['statuses']
    );
    $data['post'] = array_slice($data['post'], 0, 50);
    $data['container'] = $data['page'].'.tpl';
    echo websun_parse_template_path($data, TPL . '/container.tpl');
});


$router->get('/my/groups', function() {
    $auth = auth();

    $status_id = isset($_GET['status']) ? $_GET['status'] : 'new';

    $data = [];
    $data['user'] = $_SESSION['user'];
    $data['page'] = 'groups';

    query("SET sql_mode = '';");
    $data['group'] = query("
        SELECT
            g.id,
            g.group_title,
            g.group_id,
            s.status_title,
            GROUP_CONCAT(k.title) as keywords
            FROM 
            groups g
        LEFT JOIN
            statuses s
            ON s.id = g.status_id
        LEFT JOIN
            groups_keywords gk
            ON gk.group_id = g.id
        LEFT JOIN
            keywords k
            ON k.id = gk.keyword_id          
        WHERE 
            s.status_title = '" . $status_id .  "'
        GROUP BY g.id
        ORDER BY 
        g.id ASC
    ");

    query("SET sql_mode = '';");
    $data['statuses'] = convertGroupStatuses(
        query("
            SELECT
                s.status_title,
                SUM(IF(g.id IS NOT NULL, 1, 0)) as group_count
            FROM
                statuses s
            LEFT JOIN groups g ON g.status_id = s.id
            WHERE s.is_group IS NOT NULL
            GROUP BY s.id
            ORDER BY s.is_group ASC
        ")
    );

    $data['statuses'] = json(
        $data['group'] ? array_merge(
            $data['statuses'],
            array_count_values(
                array_column($data['group'], 'status_title')
            )
        ) :
            $data['statuses']
    );

    $data['group'] = array_slice($data['group'], 0, 50);
    $data['container'] = $data['page'].'.tpl';
    echo websun_parse_template_path($data, TPL . '/container.tpl');
});

$router->get('/api/getcount', function() {
    if (!isset($_GET['table']) || !isset($_GET['status'])) {
        response(false);
    }

    $result = query("
        SELECT
        COUNT(*) as count
        FROM
        " . $_GET['table'] . "
        LEFT JOIN statuses s ON s.id = " . $_GET['table'] . ".status_id
        WHERE
        s.status_title = '" . $_GET['status'] . "'
    ");

    response(true, array(
        'table'     => $_GET['table'],
        'status'    => $_GET['status'],
        'count'     => $result[0]['count']
    ));
});

$router->post('/api/images', function() use ($config) {
    if (!isset($_POST['q'])) {
        response(false);
    }

    $driver = initWebDriver();
    $results = getImages($driver, $config, $_POST['q']);

    response(true, array(
        'images' => $results
    ));
});

function normalizeImageSrc($string)
{
    return str_replace("//", "https://", $string);
}

function getImages($driver, $config, $q)
{
    $result = [];
    pageOpen($driver, "https://yandex.kz/images/search/?text=" . urlencode($q));
    $images = getElementBySelector(
        $driver,
        $config->xpath["image"],
        true,
        true
    );
    if ($images) {
        foreach ($images as $image) {
            if ($image) {
                $imageThumb = getElementBySelector($image, $config->xpath["imageThumb"], false);
                if ($imageThumb) {
                    $imageThumbSrc = $imageThumb->getAttribute('src');
                    if (!empty($imageThumbSrc)) {
                        array_push($result, normalizeImageSrc($imageThumbSrc));
                    }
                }
            }
        }
    }
    return $result;
}

$router->post('/api/post', function() {
    if (isset($_POST['event_discription'])) {
        $filesave = false;
        $filename = '';
        if (isset($_POST['image'])) {
            if (strpos($_POST['image'], 'http') !== false) {
                $filename = randHash() . '.jpg';
                $path = realpath(dirname(__FILE__)) . "/images/" . $filename;
                $filesave = file_put_contents($path, file_get_contents($_POST['image']));
            }
        }

        setPostEventDiscription(
            $_POST['id'],
            $_POST['event_discription'],
            $filesave ? $filename : ''
        );
        response(true);
    }

    if (!isset($_POST['post'])) {
        response(false);
    }

    setApiPostStatus(
        $_POST['post']['id'],
        $_POST['post']['status_id']
    );
    response(true, array(
        'post' => $_POST['post']
    ));
});

$router->post('/api/apigroup', function() {

    if (!isset($_POST['post'])) {
        response(false);
    }

    setApiGroupStatus(
        $_POST['post']['id'],
        $_POST['post']['status_id']
    );
    response(true, array(
        'post' => $_POST['post']
    ));
});

$router->post('/api/group', function() {
    if (!isset($_POST['group_id'])) {
        response(false);
    }
    setGroupStatus(
        $_POST['group_id']
    );
    response(true, array(
        'group_id' => $_POST['group_id']
    ));
});
$router->get('/api/post', function() {

    if (!isset($_GET['id'])) {
        response(false);
    }
    $post = getPost(
        $_GET['id']
    );
    response(true, array(
        'post' => $post
    ));
});

$router->get('/', function() {
    die('test');
});

$router->run();