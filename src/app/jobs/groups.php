<?php

require_once dirname(__DIR__, 2) . "/vendor/autoload.php";

function getElementBySelector(
    $driver,
    $selector,
    $wait = true,
    $many = false,
    $secondWait = 10
) {
    $selectors = !is_array($selector) ? [$selector] : $selector;

    $goto = 0;
    begin:
    if ($goto == 3) {
        return false;
    }
    $goto++;

    foreach ($selectors as $selector) {
        try {
            //logg("init      | " . $selector);
            if ($wait) {
                $driver
                    ->wait(5, $secondWait * 1000)
                    ->until(
                        Facebook\WebDriver\WebDriverExpectedCondition::presenceOfElementLocated(
                            preg_match("/^\//", $selector)
                                ? Facebook\WebDriver\WebDriverBy::xpath(
                                $selector
                            )
                                : Facebook\WebDriver\WebDriverBy::cssSelector(
                                $selector
                            )
                        )
                    );
            }
            $element = $many
                ? $driver->findElements(
                    preg_match("/^\//", $selector)
                        ? Facebook\WebDriver\WebDriverBy::xpath($selector)
                        : Facebook\WebDriver\WebDriverBy::cssSelector($selector)
                )
                : $driver->findElement(
                    preg_match("/^\//", $selector)
                        ? Facebook\WebDriver\WebDriverBy::xpath($selector)
                        : Facebook\WebDriver\WebDriverBy::cssSelector($selector)
                );
            if ($element) {
                //logg("success   | " . $selector);
                return $element;
            }
            //logg("rejected  | " . $selector);
            continue;
        } catch (Facebook\WebDriver\Exception\TimeoutException $e) {
            //logg("timeout   | " . $selector);
            continue;
        } catch (Exception $e) {
            //echo $e->getMessage();
            //logg("exception | " . $selector);
            continue;
        }
    }

    sleep(5);
    goto begin;
}

function initWebDriver()
{

    $caps = Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
    $caps->setBrowserName("chrome");
    $caps->setVersion("100.0");
    $caps->setCapability("enableVNC", true);

    $options = new Facebook\WebDriver\Chrome\ChromeOptions();
    $options->addArguments(
        [
            "--disable-gpu",
            "--disable-dev-shm-usage"
        ]
    );

    $caps->setCapability(Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $options);

    $driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
        "http://selenoid:4444/wd/hub",
        $caps
    );

    $driver
        ->manage()
        ->window()
        ->setSize(new Facebook\WebDriver\WebDriverDimension(1920, 1080));
    return $driver;
}

function closeWebDriver($driver)
{
    $driver->quit();
    return null;
}

function initDotenv()
{
    $dotenv = Dotenv\Dotenv::create(dirname(__DIR__, 2));
    $dotenv->load();
    return $dotenv;
}

function initDb()
{
    try {
        $db = new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_NAME"),
            getenv("DB_USER"),
            getenv("DB_PASSWORD")
        );
        $db->query("SET NAMES utf8");
        return $db;
    } catch (PDOException $error) {
        die("MySQL don`t connect");
    }
}

function closeDb($db)
{
    return null;
}

function logg($message, $remove = false)
{
    $filename = realpath(dirname(__FILE__)) . "/.." . "/tmp/log.txt";
    if ($remove && file_exists($filename)) {
        unlink($filename);
    }
    $data = $message . PHP_EOL;
    $file = fopen($filename, "a");
    fwrite($file, $data);
    fclose($file);
}

function recognizeImage($image, $config)
{
    try {
        $ac = new AntiCaptcha\AntiCaptcha(
            AntiCaptcha\AntiCaptcha::SERVICE_ANTICAPTCHA,
            [
                "api_key" => $config->captcha["api_key"],
                "debug" => false,
            ]
        );
        return $ac->recognizeImage(
            file_get_contents($image),
            null,
            ["phrase" => 0, "numeric" => 0],
            "en"
        );
    } catch (AntiCaptcha\AntiCaptchaException $exception) {
        return false;
    }
}

function pageOpen($driver, $url)
{
    $driver->get($url);
}

function captcha($driver, $config)
{
    sleep(1);
    $captcha = getElementBySelector($driver, $config->xpath["captcha"]);
    if ($captcha) {
        $filename = removeFileTmp(randHash() . ".png");
        $captchaImage = getElementBySelector(
            $driver,
            $config->xpath["captchaImage"]
        );
        $captchaImage->takeElementScreenshot($filename);
        $recognizeText = recognizeImage($filename, $config);
        if (!empty($recognizeText)) {
            $loginCaptchaInput = click($driver, $config->xpath["captchaInput"]);
            $loginCaptchaInput = write(
                $driver,
                $config->xpath["captchaInput"],
                $recognizeText
            );
            $loginCaptchaSend = click($driver, $config->xpath["captchaSend"]);
            return $recognizeText;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function window($driver)
{
    $driver->executeScript(
        "document.getElementById('box_layer_wrap').click();"
    );
}

function click($driver, $xpath)
{
    $btn = getElementBySelector($driver, $xpath);
    if ($btn) {
        $btn->click();
    } else {
        $driver->executeScript(
            "document.evaluate('" .
            $xpath .
            "', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click();"
        );
    }
    sleep(1);
    return $btn;
}

function write($driver, $xpath, $text)
{
    $input = getElementBySelector($driver, $xpath);
    if ($input) {
        $input->sendKeys($text);
    }
    sleep(1);
    return $input;
}

function fileTmpPath($filename)
{
    return realpath(dirname(__FILE__)) . "/.." . "/tmp/" . $filename;
}

function removeFileTmp($filename)
{
    $tmp = fileTmpPath($filename);
    if (file_exists($tmp)) {
        unlink($tmp);
    }
    return $tmp;
}

function signIn($driver, $config)
{
    pageOpen($driver, "https://vk.com/");
    $login = write($driver, $config->xpath["login"], $config->vk_alt["login"]);
    $enter = click($driver, $config->xpath["loginEnter"]);
    $loginToPassword = getElementBySelector(
        $driver,
        $config->xpath["loginToPassword"]
    );
    $loginToPassword ?: captcha($driver, $config);
    $loginToPassword = click($driver, $config->xpath["loginToPassword"]);
    $password = getElementBySelector($driver, $config->xpath["password"]);
    $password ?: captcha($driver, $config);
    $password = write(
        $driver,
        $config->xpath["password"],
        $config->vk_alt["password"]
    );
    $continue = click($driver, $config->xpath["continue"]);
    $recommends = getElementBySelector($driver, $config->xpath["recommends"]);
    if ($recommends) {
        return true;
    } else {
        $driver->executeScript("location.reload()");
        sleep(1);
        $recommends = getElementBySelector(
            $driver,
            $config->xpath["recommends"]
        );
        if ($recommends) {
            return true;
        }
    }
    return false;
}

function insertGroup($db, $group)
{
    echo $group->group_title . " | " . $group->group_id . "\n";
    $sql =
        '
    INSERT INTO groups (
        `id`, `group_id`, `group_title`, `source_id`, `status_id`, `group_updated`, `group_created`
    ) VALUES (
        NULL,
        "' . $group->group_id      . '",
        "' . $group->group_title   . '",
        '  . $group->source_id     . ',
        '  . $group->status_id     . ',
        "' . $group->group_updated . '",
        "' . $group->group_created . '"
    ) ON DUPLICATE KEY UPDATE 
        `group_title` = VALUES (`group_title`),
        `group_updated` = VALUES (`group_updated`);
    ';
    $db->query($sql);
    $id_group = $db->lastInsertId();

    $sql = '
    INSERT INTO groups_keywords (
        `group_id`, `keyword_id`
    ) VALUES (
        ' . $id_group    . ',
        ' . $group->keyword_id  . '
    );
    ';
    $db->query($sql);

}

function initConfig()
{
    return (object) json_decode(
        file_get_contents(realpath(dirname(__FILE__)) . "/config.json"),
        true
    );
}

function normalizeGroupId($string) {
    $string = strtok($string, "?");
    $string = str_replace('\\', '', $string);
    $string = str_replace('/', '', $string);
    return $string;
}

function getKeywords($db)
{
    $rows = $db->query("
        SELECT
        *
        FROM
        keywords w
        WHERE
        w.parsed IS NULL
    ");
    return $rows->fetchall(PDO::FETCH_ASSOC);
}

function updateKeyword($db, $keyword)
{
    $sql = '
    UPDATE keywords SET 
        parsed = "' . $keyword->parsed . '"
    WHERE 
        id = ' . $keyword->id . '
    ;';
    $db->query($sql);
}

function parseGroups($db, $driver, $config, $limit)
{
    begin:
    pageOpen($driver, "https://vk.com/groups");
    window($driver);
    $keywords = getKeywords($db);
    if (count($keywords) == 0) {
        echo "wait keyword ..." . "\n";
        sleep(45);
        goto begin;
    }

    if ($keywords) {
        foreach ($keywords as $keyword) {
            $keyword = (object)$keyword;

            write($driver, $config->xpath["groupsInput"], $keyword->title);
            click($driver, $config->xpath["groupsSearch"]);
            $groupsCount = getElementBySelector($driver, $config->xpath["groupsCount"]);
            $total = intval(preg_replace("/\D/", "", $groupsCount->getText()));

            if ($total == 0) {
                continue;
            }

            updateKeyword($db, (object)array(
                "id" => $keyword->id,
                "parsed" => "0001-01-01 01:00:00"
            ));

            $count = 0;
            while (true) {
                $elements = getElementBySelector(
                    $driver,
                    str_replace(
                        ["[COUNT]"],
                        [strval($count + 1)],
                        $config->xpath["groupsItems"]
                    ),
                    true,
                    true
                );

                if ($elements) {
                    $count = $count + count($elements);

                    if ($count == $total || $count == $limit) {
                        updateKeyword($db, (object)array(
                            "id" => $keyword->id,
                            "parsed" => date("Y-m-d H:i:s")
                        ));
                        goto begin;
                    }

                    foreach ($elements as $element) {
                        $group = (object)[];
                        $groupTitle = getElementBySelector(
                            $element,
                            $config->xpath["groupTitle"],
                            false
                        );
                        if ($groupTitle) {
                            $group->group_id = normalizeGroupId($groupTitle->getAttribute("href"));
                            $group->group_title = $groupTitle->getText();
                            $group->source_id = '1';
                            $group->status_id = '1';
                            $group->group_updated = date("Y-m-d H:i:s");
                            $group->group_created = date("Y-m-d H:i:s");
                            $group->keyword_id = $keyword->id;
                            insertGroup($db, $group);
                        }
                    }

                    $driver->executeScript(
                        "window.scrollTo(0, document.body.scrollHeight);"
                    );
                    $driver->wait(10, 500)->until(function ($driver) use ($count) {
                        return $driver->executeScript(
                            'return document.getElementById("groups_list_search_cont").getElementsByClassName("groups_row").length > ' .
                            strval($count)
                        );
                    });
                    continue;
                }
                break;
            }
            goto begin;
        }
    }
}

try {
    $driver      = initWebDriver();
    $dotenv      = initDotenv();
    $db          = initDb();
    $log         = logg("", true);
    $config      = initConfig();
    $signIn      = signIn($driver, $config);
    $queryGroups = parseGroups($db, $driver, $config, 1000);
    $driver      = closeWebDriver($driver);
    $db          = closeDb($db);
} catch (Exception $e) {
    $driver      = closeWebDriver($driver);
    $db          = closeDb($db);
}