
<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset = "utf-8">
    <meta http-equiv = "X-UA-Compatible" content = "IE = edge">
    <meta name = "viewport" content = "width = device-width, initial-scale = 1">
    <title><?php echo $title; ?></title>

    <!-- Bootstrap core CSS -->
    <link href = "/assets/css/bootstrap.min.css" rel = "stylesheet">
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js">
    </script>
    <script src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
<nav class = "navbar navbar-inverse navbar-fixed-top">
    <div class = "container">
        <div class = "navbar-header">

            <button type = "button" class = "navbar-toggle collapsed"
                    datatoggle = "collapse" data-target = "#navbar"
                    aria-expanded = "false" ariacontrols = "navbar">
                <span class=  "sr-only">Toggle navigation</span>
                <span class = "icon-bar"></span>
                <span class = "icon-bar"></span>
                <span class = "icon-bar"></span>
            </button>
            <a class = "navbar-brand" href = "#">Events</a>
        </div>
        <div id = "navbar" class = "collapse navbar-collapse">
            <ul class = "nav navbar-nav">
                <li class = "<?php echo ($active=='about' ? 'active' :'') ?>"><a href = "/">About</a></li>
                <li class = "<?php echo ($active=='catalog' ? 'active':'') ?>"><a href = "/catalog">List</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class = "container">
    <div class = "starter-template" style = "padding: 50px 0 0 0;">
        <h1><?php echo $title; ?></h1>

        <?php echo $content; ?>

    </div>

</div><!-- /.container -->
</body>
</html>

