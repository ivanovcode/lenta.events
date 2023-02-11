<?php

function randHash($len=32)
{
    return substr(md5(openssl_random_pseudo_bytes(20)),-$len);
}

function getFileNameExtension($filename)
{
    return end(explode('.', $filename));
}

function getFileNameFromUrl($url)
{
    preg_match('/(?:.+\/)(.+\.(png|jpg|jepg))[?#]?.*$/', $url, $matches);
    if(isset($matches[1]) && $matches[1]) {
        return $matches[1];
    }
    return false;
}

function downloadImage($url)
{
    $fileextension = getFileNameExtension(
        getFileNameFromUrl($url)
    );

    if($fileextension) {
        $filename = randHash() . '.' . $fileextension;
        if(!empty($filename) && $filename) {
            try {
                file_put_contents(realpath(dirname(__FILE__)) . "/.." . "/images/" . $filename, file_get_contents($url));
                return $filename;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    return false;
}

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
    if ($wait) {
        sleep(5);
        goto begin;
    }
    return false;
}

function initWebDriver()
{

    $caps = Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
    $caps->setBrowserName("chrome");
    $caps->setVersion("100.0");
    $caps->setCapability("enableVNC", true);
    /*$caps->setCapability("selenoid:options",
        [
            "enableVNC" => true
        ]
    );*/

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

    /*$driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
        "http://selenoid:4444/wd/hub",
        [
            "browserName" => "chrome",
            "browserVersion" => "100.0",
            "selenoid:options" => ["enableVNC" => true],
        ]
    );*/
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

function logg($message, $remove = false, $file = "log.txt")
{
    $filename = realpath(dirname(__FILE__)) . "/.." . "/tmp/" . $file;
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


function clickByElement($element)
{
    $element->click();
    sleep(1);
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

function initConfig()
{
    return (object) json_decode(
        file_get_contents(realpath(dirname(__FILE__)) . "/config.json"),
        true
    );
}

function signIn($driver, $config)
{
    pageOpen($driver, "https://vk.com/");
    $login = write($driver, $config->xpath["login"], $config->vk["login"]);
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
        $config->vk["password"]
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
    sleep(1);
    return true;
}