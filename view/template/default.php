<?php
/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= TITLE_PREFIX . $_content->title . TITLE_SUFFIX ?></title>


        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= $_content->meta ?>

        <link rel="shortcut icon" type="image/x-icon" href="<?= HOME ?>/favicon.ico" />

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">


        <link rel="stylesheet" href="<?= HOME ?>/css/style.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/home.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/event-date-select.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/event-list.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/location-list.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/gallery.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/filter.css">
        <link rel="stylesheet" href="<?= HOME ?>/css/admin.css">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <?= $_content->css ?>



    </head>
    <body class="<?= $_content->bodyClass ?>">

       


        <header id="header">
            <div class="container">


                <?= $_content->contentTop ?>

            </div>

        </header>



        <div id="main">

            <?= $_content->contentMain ?>


        </div><!-- #main -->


        <footer id="footer">
            <div id="footer-inner" class="wrap container">

               
               

            </div>
        </footer>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->

        <script type="text/javascript" src="<?= HOME ?>/core/jqueryUI/js/jquery-ui-1.10.4.custom.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

       



       

        <script type="text/javascript" src="<?= HOME ?>/js/afterload.js"></script>
      
      
        <?= $_content->script ?>

    </body>


</html>


