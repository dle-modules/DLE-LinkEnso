<?php

/*
=============================================================================
 Файл: linkenso.php (backend) версия 2.3
-----------------------------------------------------------------------------
 Автор: Фомин Александр Алексеевич, mail@mithrandir.ru
-----------------------------------------------------------------------------
 Помощь: ПафНутиЙ, pafnuty10@gmail.com, http://pafnuty.name
-----------------------------------------------------------------------------
 Сайт поддержки: http://alaev.info/blog/post/3982
-----------------------------------------------------------------------------
 Назначение: генератор кода для вставки модуля в шаблон fullstory.tpl
=============================================================================
*/

    // Антихакер
    if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
            die( "Hacking attempt!" );
    }

    echoheader('LinkEnso PRO', 'Модуль кольцевой перелинковки');
        echo '

'.($config['version_id'] >= 10.2 ? '<style>.uniform, div.selector {min-width: 250px;}</style>' : '<style>
@import url("engine/skins/application.css");

.box {
margin:10px;
}
.uniform {
position: relative;
padding-left: 5px;
overflow: hidden;
min-width: 250px;
font-size: 12px;
-webkit-border-radius: 0;
-moz-border-radius: 0;
-ms-border-radius: 0;
-o-border-radius: 0;
border-radius: 0;
background: whitesmoke;
background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgi…pZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JhZCkiIC8+PC9zdmc+IA==");
background-size: 100%;
background-image: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(0%, #ffffff), color-stop(100%, #f5f5f5));
background-image: -webkit-linear-gradient(top, #ffffff, #f5f5f5);
background-image: -moz-linear-gradient(top, #ffffff, #f5f5f5);
background-image: -o-linear-gradient(top, #ffffff, #f5f5f5);
background-image: linear-gradient(top, #ffffff, #f5f5f5);
-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
border: 1px solid #ccc;
font-size: 12px;
height: 28px;
line-height: 28px;
color: #666;
}
</style>').'

<div class="box">

	<div class="box-header">
		<div class="title">Генератор кода для вставки модуля</div>
		<ul class="box-toolbar">
			<li class="toolbar-link">
			<a target="_blank" href="http://alaev.info/blog/post/3982?from=LinkEnsoPro">LinkEnso PRO v.2.3 © 2014 Блог АлаичЪ\'а - разработка и поддержка модуля</a>
			</li>
		</ul>
	</div>

	<div class="box-content">
	<table class="table table-normal">
	<tbody>
		<tr>
		<td class="col-xs-6"><h5>Шаблон блока:</h5><span class="note large">Название шаблона, через который будет выведен блок с ссылками (не обязательно).<br />Расширение .tpl указывать не нужно!</span></td>
		<td class="col-xs-6 settingstd"><input class="uniform" type="text" name="linkenso_template" id="linkenso_template" placeholder="linkenso/linkenso" /></td>
		</tr><tr>
		<td class="col-xs-6"><h5>Количество ссылок:</h5><span class="note large">Общее количество ссылок, выводимых модулем.</span></td>
		<td class="col-xs-6 settingstd"><input class="uniform" type="text" name="linkenso_links" id="linkenso_links" value="3" /></td>
		</tr><tr>
		<td class="col-xs-6"><h5>Какие новости показывать:</h5><span class="note large"><strong>Предыдущие публикации</strong> - в ссылках будут выводиться предыдущие новости.<br /><strong>Свежие публикации</strong> - в ссылках будут выводиться свежие новости.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_date" id="linkenso_date">
			<option value="old">Предыдущие новости</option>
			<option value="new">Свежие новости</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Закольцевать ссылки:</h5><span class="note large"><strong>Да</strong> - ссылки будут закольцованы, т.е. в блоке свежих статей  в самых свежих новостях будут отображены самые старые новости.<br /><strong>Нет</strong> - ссылки не будут закольцованы, т.е., если не будет найдено свежих или предыдущих ссылок, то модуль ничего не выведет.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_ring" id="linkenso_ring">
			<option value="yes">да</option>
			<option value="no">нет</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Сканировать категории:</h5><span class="note large"><strong>Все категории</strong> - в модуле будут выводиться ссылки на новости из всех категорий.<br /><strong>Текущая категория</strong> - в модуле будут выводиться ссылки на новости из той же категории, что и текущая.<br /><strong>Глобальная категория</strong> - в модуле будут выводиться ссылки на новости из самой корневой категории для текущей новости.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_scan" id="linkenso_scan">
			<option value="all_cat">все категории</option>
			<option value="same_cat">текущая категория</option>
			<option value="global_cat">глобальная категория</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Заголовко ссылки (анкор):</h5><span class="note large"><strong>Название новости</strong> - в ссылках будут выведены заголовки новостей.<br /><strong>Title новости</strong> - в ссылках будут выведены title новостей.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_anchor" id="linkenso_anchor">
			<option value="name">Название новости</option>
			<option value="title">Title новости</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Атрибут title ссылок:</h5><span class="note large"><strong>Title новости</strong> - в title будут выведены title новостей.<br /><strong>Название новости</strong> - в title будут выведены заголовки новостей.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_title" id="linkenso_title">
			<option value="title">title новости</option>
			<option value="name">название новости</option>
			<option value="empty">оставить пустым</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Изображение:</h5><span class="note large">Принцип вывода изображения из новости. При выборе изображения из дополнительного поля, необходимо ввести название нужного дополнительного поля вместо "xfield" в коде для вставки.</span></td>
		<td class="col-xs-6 settingstd">
			<select class="uniform" name="linkenso_image" id="linkenso_image">
			<option value="full_story">1-е изображение полной новости</option>
			<option value="short_story">1-е изображение краткой новости</option>
			<option value="xfield">значение дополнительного поля</option>
			</select>
		</td>
		</tr><tr>
		<td class="col-xs-6"><h5>Обрезка текста:</h5><span class="note large">Количество символов, выводимое в блоке текста.</span></td>
		<td class="col-xs-6 settingstd"><input class="uniform" type="text" name="linkenso_limit" id="linkenso_limit" /></td>
		</tr><tr>
		<td class="col-xs-6"><h5>Код для вставки в <strong>fullstory.tpl</strong></h5><span class="note large"></span></td>
		<td class="col-xs-6 settingstd">
			<textarea type="text" style="width:100%;height:100px;" name="linkenso_code" id="linkenso_code">{include file=\'engine/modules/linkenso.php?post_id={news-id}\'}</textarea>
		</td>
		</tr>
                                <script type="text/javascript">
                                    var linkenso_options = [
                                         "template",
                                         "links",
                                         "date",
                                         "ring",
                                         "scan",
                                         "anchor",
                                         "title",
                                         "image",
                                         "limit"
                                    ];

                                    document.getElementById("linkenso_image").onchange = function(){
                                        switch(document.getElementById("linkenso_image").value)
                                        {
                                            case \'short_story\':
                                                document.getElementById("linkenso_image").type = \'hidden\';
                                                document.getElementById("linkenso_image").value = \'short_story\';
                                                break;

                                            case \'full_story\':
                                                document.getElementById("linkenso_image").type = \'hidden\';
                                                document.getElementById("linkenso_image").value = \'full_story\';
                                                break;

                                            case \'xfield\':
                                                document.getElementById("linkenso_image").value = \'\';
                                                document.getElementById("linkenso_image").type = \'text\';
                                                break;

                                            default:
                                                break;
                                        }

                                        recalculate_code();
                                    };

                                    for(i = 0; i < linkenso_options.length; i = i+1)
                                    {
                                        document.getElementById("linkenso_" + linkenso_options[i]).onchange = function(){
                                            recalculate_code();
                                        };
                                    }

                                    function recalculate_code()
                                    {

                                        document.getElementById("linkenso_code").value = "{include file=\'engine/modules/linkenso.php?post_id={news-id}";

                                        for(var i = 0; i < linkenso_options.length; i = i+1)
                                        {
                                            if(document.getElementById("linkenso_" + linkenso_options[i]).value)
                                            {
                                                document.getElementById("linkenso_code").value = document.getElementById("linkenso_code").value + "&" + linkenso_options[i] + "=" + document.getElementById("linkenso_" + linkenso_options[i]).value;
                                            }
                                        }

                                        document.getElementById("linkenso_code").value = document.getElementById("linkenso_code").value + "\'}";
                                    }
                                </script>
	</tbody>
	</table>
	</div>
</div>
        ';

        // Отображение подвала админского интерфейса
        echofooter();

?>