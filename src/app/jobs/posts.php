<?php

require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
require_once dirname(__DIR__, 2) . "/app/includes/parse.php";

function insertPost($db, $post)
{
    //echo $post->post_id . " | " . $post->text . "\n";
    $sql =
        '
    INSERT IGNORE INTO posts (
        `id`, `post_id`, `post_date`, `post_discription`, `post_image`, `group_id`, `status_id`, `post_updated`, `post_created`
    ) VALUES (
        NULL,
        "' . $post->post_id          . '",
        "' . $post->post_date        . '",
        "' . $post->post_discription . '",
        "' . $post->post_image       . '",
        '  . $post->group_id         . ',
        '  . $post->status_id        . ',
        "' . $post->post_updated     . '",
        "' . $post->post_created     . '"
    );
    ';
    logg($sql, false, "sql.txt");
    $db->query($sql);
}



function strReplaceForArray($list, $string)
{
    $find       = array_keys($list);
    $replace    = array_values($list);
    $new_string = str_ireplace($find, $replace, $string);
    return $new_string;
}

function normalizePostDate($string)
{
    return str_replace("&nbsp;", " ", $string);
}

function normalizePostDiscription($string)
{
    $string = strip_tags(!empty($string) ? $string : "");
    return str_replace("\"", "'", $string);
}

function getMonthDate($string)
{
    $listMonth = array(
        "янв" => "01", "фев" => "02", "мар" => "03", "апр" => "04", "мая" => "05", "июн" => "06", "июл" => "07", "авг" => "08", "сен" => "09", "окт" => "10", "ноя" => "11", "дек" => "12",
        "jan" => "01", "feb" => "02", "mar" => "03", "apr" => "04", "may" => "05", "jun" => "06", "jul" => "07", "aug" => "08", "sep" => "09", "oct" => "10", "nov" => "11", "dec" => "12"
    );

    $listMonthForDay = array(
        "вчера" => date('m', strtotime("-1 days")),
        "сегодня" => date("m"),
        "назад" => date("m"),
        "yesterday" => date('m', strtotime("-1 days")),
        "today" => date("m"),
        "ago" => date("m")
    );

    $words = explode(" ", $string);

    foreach ($words as $key => $word) {
        $isMonth = array_key_exists(strtolower($word), $listMonth);
        $isMonthForDay = array_key_exists($word, $listMonthForDay);
        $month = ($isMonth ? strReplaceForArray($listMonth, $word) : ($isMonthForDay ? strReplaceForArray($listMonthForDay, $word) : ""));
        if (!empty($month)) return $month;
    }
    return "";
}

function getYearDate($string)
{
    $month = intval(getMonthDate($string));
    $todayMonth = intval(date("m"));
    $words = explode(" ", $string);
    foreach ($words as $key => $word) {
        if (is_numeric($word) && strlen($word) === 4) {
            return $word;
        }
    }
    return $todayMonth < $month ? date('Y', strtotime("-1 year")) : date("Y");
}

function getTimeDate($string)
{
    $listNumber = array(
        "час" => "1",
        "один" => "1",
        "два" => "2",
        "три" => "3",
        "четыре" => "4",
        "пять" => "5",
        "шесть" => "6",
        "семь" => "7",
        "восемь" => "8",
        "девять" => "9",
        "hour" => "1",
        "one" => "1",
        "two" => "2",
        "three" => "3",
        "four" => "4",
        "five" => "5",
        "six" => "6",
        "seven" => "7",
        "eight" => "8",
        "nine" => "9"
    );

    $timeType = "";
    $words = explode(" ", $string);

    foreach ($words as $word) {

        $number = array_key_exists($word, $listNumber) ? strReplaceForArray($listNumber, $word) : "";
        if (is_numeric($number)) {
            $timeNumber = $number;
        }

        if (is_numeric($word)) {
            $timeNumber = $word;
        }

        if (in_array($word, ["минут", "minutes"])) {
            $timeType = 'minutes';
        }

        if (in_array($word, ["часов", "hours"])) {
            $timeType = 'hour';
        }

        if (strripos($word, ":") && strlen($word) >= 4) {
            $time = $word . (strripos($string, "pm") ? ' PM' : (strripos($string, "am") ? ' AM' : '')) ;
            return date('H:i:s', strtotime($time));
        }

        if (!empty($timeType) && is_numeric($timeNumber)) {
            return date('H:i:s', strtotime('-' . $timeNumber . ' ' . $timeType));
        }

    }
    return false;
}

function getDayDate($string)
{
    $listNumber = array(
        "час" => "1",
        "один" => "1",
        "два" => "2",
        "три" => "3",
        "четыре" => "4",
        "пять" => "5",
        "шесть" => "6",
        "семь" => "7",
        "восемь" => "8",
        "девять" => "9",
        "hour" => "1",
        "one" => "1",
        "two" => "2",
        "three" => "3",
        "four" => "4",
        "five" => "5",
        "six" => "6",
        "seven" => "7",
        "eight" => "8",
        "nine" => "9"
    );
    $listDay = array(
        "вчера" => date('d', strtotime("-1 days")),
        "сегодня" => date("d"),
        "yesterday" => date('d', strtotime("-1 days")),
        "today" => date("d")
    );

    $timeType = "";
    $timeNumber = "";
    $words = explode(" ", $string);

    foreach ($words as $key => $word) {

        $number = array_key_exists($word, $listNumber) ? strReplaceForArray($listNumber, $word) : "";
        if (is_numeric($number)) {
            $timeNumber = $number;
        }

        if (is_numeric($word) && strlen($word)<=2) {
            $timeNumber = $word;
        }

        if (in_array($word, ["минут", "minutes"])) {
            $timeType = 'minutes';
        }

        if (in_array($word, ["часов", "hours"])) {
            $timeType = 'hour';
        }

        if (in_array($word, ["назад", "ago"])) {
            if (!empty($timeNumber) && !empty($timeType)) {
                return date('d', strtotime('-' . $timeNumber . ' ' . $timeType));
            }
        }

        $isDay = array_key_exists($word, $listDay);
        if ($isDay) {
            $day = strReplaceForArray($listDay, $word);
            if (!empty($day)) {
                return $day;
            }
        }

        if (!empty($timeNumber) && $key+1 == count($words)) {
            return $timeNumber;
        }

    }
    return date("d");
}

function getGroups($db)
{
    $groups = $db->query("
        SELECT
        *
        FROM
        groups g
        WHERE 
            g.status_id = 4
    ");
    return $groups->fetchall(PDO::FETCH_ASSOC);
}

function parsePosts($db, $driver, $config, $limit=3)
{
    $groups = getGroups($db);
    if ($groups) {
        foreach ($groups as $group) {
            pageOpen($driver, 'https://vk.com/' . $group['group_id']);
            $posts = getElementBySelector($driver, $config->xpath["posts"]);
            if ($posts) {
                $count = 0;
                while (true) {
                    $elements = getElementBySelector(
                        $driver,
                        str_replace(
                            ["[COUNT]"],
                            [strval($count + 1)],
                            $config->xpath["postsItems"]
                        ),
                        true,
                        true
                    );

                    if ($elements) {
                        foreach ($elements as $element) {
                            $count++;

                            $postText = getElementBySelector($element, $config->xpath["postText"], false);

                            if ($postText) {
                                $post = (object)[];

                                $post->post_image = '';
                                $postImage = getElementBySelector(
                                    $element,
                                    str_replace(
                                        ["[COUNT]"],
                                        [strval($count)],
                                        $config->xpath["postImage"]
                                    ),
                                    false
                                );
                                if ($postImage) {
                                    $postImageUrl = $postImage->getAttribute('src');
                                    if (!empty($postImageUrl)) {
                                        $postImageName = downloadImage($postImageUrl);
                                        if (!empty($postImageName)) {
                                            $post->post_image = $postImageName;
                                        }
                                    }
                                    unset($postImageUrl);
                                    unset($postImage);
                                }

                                $post->post_date = '';
                                $postDate = getElementBySelector($element, $config->xpath["postDate"], false);
                                if ($postDate) {
                                    $post->post_date = $postDate->getText();
                                    logg('post_date | ' . $post->post_date);
                                    logg('post_date | ' . $post->post_date, false, "date.txt");
                                    if (!empty($post->post_date)) {
                                        $post->post_date = normalizePostDate($post->post_date);
                                        $post->post_date = getYearDate($post->post_date) . "-" . getMonthDate($post->post_date) . "-" . getDayDate($post->post_date) . " " . getTimeDate($post->post_date);
                                    }
                                    logg('post_date | ' . $post->post_date);
                                }

                                $post->group_id = $group['id'];
                                $post->post_id = $element->getAttribute('id');
                                $post->post_discription = normalizePostDiscription($postText ? $postText->getText() : "-");
                                $post->status_id = "1";
                                $post->post_updated = date("Y-m-d H:i:s");
                                $post->post_created = date("Y-m-d H:i:s");
                                insertPost($db, $post);
                            }

                            if ($count == $limit) {
                                break;
                            }
                        }

                        if ($count == $limit) {
                            break;
                        }
                        logg('scroll    |');
                        $driver->executeScript(
                            "window.scrollTo(0, document.body.scrollHeight);"
                        );

                        $driver->wait(10, 500)->until(function ($driver) use ($count) {
                            return $driver->executeScript(
                                'return document.getElementById("groups_list_search_cont").getElementsByClassName("groups_row").length > ' . $count
                            );
                        });
                        continue;
                    }
                    break;
                }
            }
        }
    }
    return false;

}

try {
    //$post_date = normalizePostDate("29 минут назад");
    //$post_date = normalizePostDate("вчера в 20:13");
    //$post_date = normalizePostDate("13 янв в 21:34");
    //$post_date = normalizePostDate("11 июл 2021");
    //$post_date = normalizePostDate("сегодня в 9:19");
    //$post_date = normalizePostDate("42 minutes ago");
    //$post_date = normalizePostDate("ten hours ago");

    //$post_date = getYearDate($post_date) . "-" . getMonthDate($post_date) . "-" . getDayDate($post_date) . " " . getTimeDate($post_date);
    //die($post_date);

    $driver      = initWebDriver();
    $dotenv      = initDotenv();
    $db          = initDb();
    $log         = logg("", true);

    $config = (object)array(
        'captcha'                  => array(
            'api_key'              => 'eb95b39db56e7dc082e8161312e9e08a'
        ),
        'vk'                       => array(
            'login'                => '+79164401342',
            'password'             => 'huj2ov4f'
        ),
        'vk_alt'                   => array(
            'login'                => '+79954451342',
            'password'             => 'Huj2ov4f'
        ),
        'xpath'                    => array(
            'login'                => '//*[@id="index_email"]',
            'loginEnter'           => '/html/body/div[10]/div/div/div[2]/div[2]/div[2]/div/div[1]/div[2]/div[1]/div[1]/form/button[1]',
            'loginToPassword'      => '/html/body/div[1]/div/div/div/div/div[2]/div/div/div/form/div[4]/div/button[2]',
            'captcha'              => array(
                                      '/html/body/div[1]/div/div/div/div[2]/div/div/form/img',
                                      '//*[@class="vkc__Captcha__container"]'
            ),
            'captchaImage'         => array(
                                      '/html/body/div[1]/div/div/div/div[2]/div/div/form/img',
                                      '//*[@class="vkc__Captcha__image"]'
            ),
            'captchaInput'         => '//*[@class="vkc__TextField__input"]',
            'captchaSend'          => '//*[@class="vkc__Captcha__button"]',
            'password'             => '/html/body/div[1]/div/div/div/div/div[2]/div/div/div/form/div[1]/div[3]/div[1]/div/input',
            'continue'             => '/html/body/div[1]/div/div/div/div/div[2]/div/div/div/form/div[2]/button',
            'recommends'           => '//*[@id="page_layout"]',
            'groupsInput'          => '//*[@id="groups_list_search"]',
            'groupsSearch'         => array(
                                      '/html/body/div[11]/div/div/div[2]/div[2]/div[2]/div/div/div[2]/div/div[1]/div[1]/div/div[1]/button',
                                      '//*[@class="ui_search_button_search"]',
                                      '/html/body/div[11]/div/div/div[2]/div[2]/div[2]/div/div/div[2]/div/div[2]/div[1]/div/div[1]/button'
            ),
            'groupsWrap'           => '//*[@id="groups_list_search_wrap"]',
            'groupsList'           => '/html/body/div[11]/div/div/div[2]/div[2]/div[2]/div/div/div[2]/div/div[3]/div[1]',
            'groupsItems'          => 'div.groups_row:nth-child(n+[COUNT])',
            'groupsCount'          => '//*[@id="groups_search_summary"]',
            'groupsCountAlt'       => 'span.page_block_header_count',
            'groupTitle'           => 'div.title a',
            'posts'                => '//*[@id="page_wall_posts"]',
            'postsItems'           => 'div.post:nth-child(n+[COUNT])',
            'postText'             => 'div.wall_post_text',
            'postImage'            => 'img.MediaGrid__imageOld',
            'postDate'             => 'span.rel_date'
        )
    );

    $signIn      = signIn($driver, $config);
    $queryGroups = parsePosts($db, $driver, $config);
    $driver      = closeWebDriver($driver);
    $db          = closeDb($db);
} catch (Exception $e) {
    $driver      = closeWebDriver($driver);
    $db          = closeDb($db);
}
