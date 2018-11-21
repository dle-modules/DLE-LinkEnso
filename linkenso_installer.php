<!DOCTYPE HTML>
<html>
    <head>
        <title>Установка модуля LinkEnso</title>
        <link rel="stylesheet" type="text/css" href="http://store.alaev.info/style.css" />
        <style type="text/css">
            #header {width: 100%; text-align: center;}
            .module_image {float: left; margin: 0 15px 15px 0;}
            .box-cnt{width: 100%; overflow: hidden;}
        </style>
    </head>

    <body>
        <div class="wrap">
            <div id="header">
                <h1>LinkEnso</h1>
            </div>
            <div class="box">
                <div class="box-t">&nbsp;</div>
                <div class="box-c">
                    <div class="box-cnt">
                        <?php

                            $output = module_installer();
                            echo $output;

                        ?>
                    </div>
                </div>
                <div class="box-b">&nbsp;</div>
            </div>
        </div>
    </body>
</html>

<?php

    function module_installer()
    {
        // Стандартный текст
        $output = '<h2>Добро пожаловать в установщик модуля LinkEnso!</h2>';
        $output .= '<img class="module_image" src="/engine/skins/images/linkenso.png" />';
        $output .= '<p><strong>Внимание!</strong> После установки модуля <strong>обязательно</strong> удалите файл <strong>linkenso_installer.php</strong> с Вашего сервера!</p>';

        // Если через $_POST передаётся параметр linkenso_install, производим инсталляцию, согласно параметрам
        if(!empty($_POST['linkenso_install']))
        {
            // Подключаем config
            include_once ('engine/data/config.php');

            // Подключаем DLE API
            include ('engine/api/api.class.php');

            // Устанавливаем модуль в админку
            $dle_api->install_admin_module('linkenso', 'LinkEnso - модуль для организации кольцевой прелинковки', 'Модуль позволяет создать на сайте кольцевую перелинковку новостей с учетом их тематики', 'linkenso.png');

            // Вывод
            $output .= '<p>';
            $output .= 'Модуль успешно установлен! Спасибо за Ваш выбор! Приятной работы!';
            $output .= '</p>';
        }

        // Если через $_POST ничего не передаётся, выводим форму для установки модуля
        else
        {
            // Вывод
            $output .= '<p>';
            $output .= '<form method="POST" action="linkenso_installer.php">';
            $output .= '<input type="hidden" name="linkenso_install" value="1" />';
            $output .= '<input type="submit" value="Установить модуль" />';
            $output .= '</form>';
            $output .= '</p>';
        }
        
        $output .= '<p>';
        $output .= '<a href="http://alaev.info/blog/post/3982?from=LinkEnsoPro">разработка и поддержка модуля</a>';
        $output .= '</p>';

        // Функция возвращает то, что должно быть выведено
        return $output;
    }

?>