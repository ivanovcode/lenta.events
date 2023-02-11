// pageMain($driver);
// login($driver, '9164401342', 'Huj2ov4f');
// print("<pre>".print_r($results,true)."</pre>"); die();
// editMessage($id_message, 'test');
// fix take-element-screenshot https://github.com/sapzape/php-webdriver/tree/take-element-screenshot
$options = new Facebook\WebDriver\Chrome\ChromeOptions();
$options->addArguments(
[
//"no-sandbox",
//"disable-infobars",
//"disable-gpu",
//'headless",
"--disable-gpu",
"--disable-dev-shm-usage",
//"--session-timeout 600"
]
);