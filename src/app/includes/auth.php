<?php

function reroute($url) {
    header("Location: ".$url);
    exit;
}

function error_page($path, $messages='') {
    $file = $path . '/404.tpl';
    $replace = array('{messages}'); //foreach ($replacers as $key => $replace) {}
    $with = array((is_array($messages)?json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES):''));
    ob_start(); include($file); $ob = ob_get_clean();
    die(str_replace($replace, $with, $ob));
}

function getAuth() {
    $staff = [];
    if(!empty($_SESSION['user'])) {
        $staff = $GLOBALS['db']->query("
                SELECT
                s.id,
                s.login,
                s.password
                FROM
                staffs as s
                WHERE
                s.login = '" . $_SESSION['user'] . "'
                ");

        $staff = $staff->fetch(PDO::FETCH_ASSOC);
    }
    return $staff;
}

function auth() {
    session_start();
    $staff = getAuth();
    if (!isset($_SESSION['user']) || (isset($_SESSION['user'])?(empty($_SESSION['user']) && $_SESSION['user'] !== $staff['login']):false)) reroute('/login');
    return $staff;
}