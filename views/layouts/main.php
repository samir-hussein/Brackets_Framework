<?php

use core\Application;
$this->title = "Welcome";
?>

<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=Application::$app->router->title?></title>
    <!-- Bootstrab 4 CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="/assets/css/uikit.min.css" />
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
    <!-- My CSS  -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/preloader.css">
    <!-- JQuery  -->
    <script src="/assets/js/jquery-3.5.1.min.js"></script>
</head>
<body style="direction: rtl;text-align: right;">

    <div class="preload">
        <div class="loader">Loading...</div>
    </div>

    <header>

    </header>

    <main>
        {{content}}
    </main>

    <footer>

    </footer>

    <!-- Bootstrab 4 JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <!-- UIkit JS -->
    <script src="/assets/js/uikit.min.js"></script>
    <script src="/assets/js/uikit-icons.min.js"></script>
    <!-- My script -->
    <script src="/assets/js/script.js"></script>
    <!-- preloader function -->
    <script>
        $(window).on("load",function(){
            $(".preload").fadeOut("slow");
        });
    </script>
</body>
</html>