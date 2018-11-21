<?php

/*
=============================================================================
 Файл: linkenso.php (frontend) версия 2.3
-----------------------------------------------------------------------------
 Автор: Фомин Александр Алексеевич, mail@mithrandir.ru
-----------------------------------------------------------------------------
 Помощь: ПафНутиЙ, pafnuty10@gmail.com, http://pafnuty.name
-----------------------------------------------------------------------------
 Сайт поддержки: http://alaev.info/blog/post/3982
-----------------------------------------------------------------------------
 Назначение: кольцевая перелинковка новостей на сайте
=============================================================================
*/

/**
 * ChangeLog:
 * v.2.2.3 — 07.04.2014
 * - Совместимость с новым форматом файла настроек в версии DLE 10.2 и выше.
 *
 * 
 * v.2.2.2 — 18.03.2014
 * - Исправлена ошибка с неверным отображением заголовка статьи (было потеряно поле metatitle в запросе).
 *
 * 
 * v.2.2.1 — 06.03.2014
 * - Исправлена ошибка в DLE 10.0 (возможно и в 10.1), когда "неизвестно откуда" в тексте новости появляется ALT картинки. Виной всему создаваемый скрытый span для отображения описания при просмотре увеличенной картинки. Не знаю зачем это разработчики сделали через php 
 *
 * 
 * v.2.2.0 — 03.02.2014
 * - Исправлена ошибка "закольковки" последней добавленной новости.
 * - Добавлено 4 новых тега:
 * 		{link-category} - Выводит сылки на все категории, через запятую, к которым принадлежит новость.
 *		{category} - Выводит название категории, к которой принадлежит новость.
 *		{category-icon} - Выводит все иконки категорий, к которым относится новость (если новость принадлежит к 5ти категориям - будет выведено 5 иконок). В папку linkenso текущего шаблона сайта необходимо положить катинку с именем noicon.png
 *		{category-url} - Выводит полный URL на категорию, которой принадлежит данная новость.
 *
 * 
 * v.2.1.1 — 27.11.2013
 * - Исправлена ошибка с формированием ЧПУ в версиях DLE <9.6
 * - Мелкие исправления в админке (неверная подсказка и версия модуля)
 *
 * 
 * v.2.1 — 02.11.2013
 * - Полный отказ от DLE_API - теперь модуль работает намного быстрее и потребляет гораздо меньше ресурсов. Ну и вполне возможно, что с мемкешем работать будет как надо т.к. реализаия кеша сделана по "фен-шую".
 * - Отдельная папка (по умолчанию) для шаблонов модуля для удобства (не будет рабоатть на сатрых версиях dle).
 * - Возможность использовать разные шаблоны для разных блоков.
 * - Отказ от шаблона-обёртки, теперь всё, что должно быть снаружи шаблона элемента (одной новости) должно указываться вокруг строки подключения.
 * - Исправлен показ содержимого полной новости, теперь тег {full-story} выводит полную новость, а не короткую.
 * - Если при показе картинки это окажется спойлер или смайл - будет взята следующая.
 * - Добавлен тег {link-url} - выводит чистый URL на новость.
 * - Добавлен блок [not_show_image] - выводит текст, если картинки в посте нет.
 */

// Антихакер
if (!defined('DATALIFEENGINE')) {
	die("Hacking attempt!");
}

/*
 * Класс для вывода рубрик сайта
 */
if (!class_exists('LinkEnso')) {
	class LinkEnso {
		/*
		 * Конструктор класса LinkEnso 
		 * @param $linkEnsoConfig - массив с конфигурацией модуля
		 */
		public function __construct($linkEnsoConfig) {
			global $db, $config, $category, $tpl, $cat_info;
			$this->dle_config = $config;
			$this->db = $db;
			$this->tpl = $tpl;
			$this->cat_info = $cat_info;

			// Задаем конфигуратор класса
			$this->config = $linkEnsoConfig;
		}


		/*
		 * Главный метод класса LinkEnso
		 */
		public function run() {
			// Пробуем подгрузить содержимое модуля из кэша
			$output = false;
			if ($this->dle_config['allow_cache'] && $this->dle_config['allow_cache'] != "no") {
				$output = dle_cache('linkenso_', md5(implode('_', $this->config)) . $this->dle_config['skin']);
			}

			// Если значение кэша для данной конфигурации получено, выводим содержимое кэша
			if ($output !== false) {
				$this->showOutput($output);
				return;
			}

			// Если в кэше ничего не найдено, генерируем модуль заново
			$wheres = array();

			// Получаем информацию о том, в каких категориях лежит данный пост
			$post = $this->db->super_query("SELECT category FROM " . PREFIX . "_post WHERE id = '" . $this->config['postId'] . "'");

			// Исправляем тупой косяк DLE API - такой метод ОБЯЗАН возвращать пустой массив в случае, если ничего не найдено
			if (!empty($post) && !empty($post['category'])) $postCategories = array();

			$postCategories = explode(',', $post['category']);

			// Получаем список категорий для выборки в зависимости от параметра scan
			$categoriesArray = array();
			switch ($this->config['scan']) {
				// Если нужно сканировать только текущую категорию
				case 'same_cat':
					// Каждую категорию текущего поста и все ее подкатегории добавляем в общий массив
					foreach ($postCategories as $postCategory) {
						$postCategory = intval($postCategory);
						$categoriesArray[] = $postCategory;
						$categoriesArray = array_merge($categoriesArray, $this->getSubcategoriesArray($postCategory));
					}
					break;

				// Если нужно сканировать все подкатегории самой "верхней категории"
				case 'global_cat':
					// Для каждой из категорий текущего поста находим корневую категорию и все её подкатегории
					foreach ($postCategories as $postCategory) {
						$postCategory = intval($postCategory);
						$globalCategoryId = $this->getGlobalCategory($postCategory);
						$categoriesArray[] = $globalCategoryId;
						$categoriesArray = array_merge($categoriesArray, $this->getSubcategoriesArray($globalCategoryId));
					}
					break;

				default:
					break;
			}

			// Условие на список категорий
			if (count($categoriesArray) > 0) {
				switch ($this->dle_config['allow_multi_category']) {
					// Если включена поддержка мультикатегорий
					case '1':
						$categoryWheres = array();
						foreach ($categoriesArray as $categoryId) {
							$categoryWheres[] = 'category regexp "[[:<:]](' . str_replace(',', '|', $categoryId) . ')[[:>:]]"';
						}
						$wheres[] = '(' . implode(' OR ', $categoryWheres) . ')';
						break;

					// Если поддержки мультикатегорий нет
					default:
						$wheres[] = 'category IN (' . implode(',', $categoriesArray) . ')';
						break;
				}
			}

			// В зависимости от параметра date определяем старые нам посты нужны или новые
			switch ($this->config['date']) {
				case 'new':
					$dateWhere = 'id > ' . $this->config['postId'];
					break;

				default:
					$dateWhere = 'id < ' . $this->config['postId'];
					break;
			}

			// Условие для отображения только постов, прошедших модерацию
			$wheres[] = 'approve = 1';

			// Условие для отображения только тех постов, дата публикации которых уже наступила
			$wheres[] = 'date < "' . date("Y-m-d H:i:s") . '"';

			// Условие для фильтрации текущего id
			$wheres[] = 'id != ' . $this->config['postId'];

			// Складываем условия
			$where = implode(' AND ', $wheres);

			// Направление сортировки зависит от того, свежие мы смотрим или старые (для свежих - ASC, для старых - DESC)
			$ordering = $this->config['date'] == 'new' ? 'ASC' : 'DESC';

			// Поля из БД, которые выводятся запросом (используются в двух местаx, поэтому объединены в одну переменную на случай если надо будет дополнить чем то)
			$fields = 'id, date, short_story, full_story, xfields, title, metatitle, category, alt_name, approve';
			$fields .= ($this->dle_config['version_id'] < 9.6) ? ', flag' : '' ; // для старых dle выбираем поле flag

			// Первый этап - получение предыдущих постов
			$posts = $this->db->super_query("SELECT " . $fields . " FROM " . PREFIX . "_post WHERE " . $where . " AND " . $dateWhere . " ORDER BY id " . $ordering . " LIMIT 0, " . $this->config['links'], true);

			// Исправляем тупой косяк DLE API - такой метод ОБЯЗАН возвращать пустой массив в случае, если ничего не найдено
			if (empty($posts)) $posts = array();

			// Второй этап - если в нужном направлении постов не хватило и параметр ring установлен как 1, ищем посты с другой стороны
			if (count($posts) < $this->config['links'] && $this->config['ring'] == 'yes') {
				// Создаём список id постов, чтобы отфильтровать их
				$posts_id_array = array();
				foreach ($posts as $post) {
					$posts_id_array[] = $post['id'];
				}

				// Условие для фильтрации уже отобранных новостей
				if (!empty($posts_id_array)) {
					$wheres[] = 'id NOT IN(' . implode(',', $posts_id_array) . ')';
				}

				// Складываем условия
				$where = implode(' AND ', $wheres);

				// Получаем доп. посты из новых
				$morePosts = $this->db->super_query("SELECT " . $fields . " FROM " . PREFIX . "_post WHERE " . $where . " ORDER BY id " . $ordering . " LIMIT 0, " . ($this->config['links'] - count($posts)), true);

				// Исправляем тупой косяк DLE API - такой метод ОБЯЗАН возвращать пустой массив в случае, если ничего не найдено
				if (empty($morePosts)) $morePosts = array();

				$posts = array_merge_recursive($posts, $morePosts);
			}

			// Формируем список ссылок
			$linksOutput = '';
			foreach ($posts as $post) {
				// Формируем ссылки на категории и иконки категорий
				$my_cat = array();
				$my_cat_icon = array();
				$my_cat_link = array();
				$cat_list = explode(',', $post['category']);
				foreach($cat_list as $element) {
					if(isset($this->cat_info[$element])) {
						$my_cat[] = $this->cat_info[$element]['name'];
						if ($this->cat_info[$element]['icon'])
							$my_cat_icon[] = '<img class="category-icon" src="'.$this->cat_info[$element]['icon'].'" alt="'.$this->cat_info[$element]['name'].'" />';
						else
							$my_cat_icon[] = '<img class="category-icon" src="{THEME}/linkenso/noicon.png" alt="'.$this->cat_info[$element]['name'].'" />';
						if($this->dle_config['allow_alt_url'] == 'yes') 
							$my_cat_link[] = '<a href="'.$this->dle_config['http_home_url'].get_url($element).'/">'.$this->cat_info[$element]['name'].'</a>';
						else 
							$my_cat_link[] = '<a href="'.$PHP_SELF.'?do=cat&category='.$this->cat_info[$element]['alt_name'].'">'.$this->cat_info[$element]['name'].'</a>';
					}
				}
				$categoryUrl = ($post['category']) ? $this->dle_config['http_home_url'] . get_url(intval($post['category'])) . '/' : '/' ;


				// Убираем слэши
				$post['short_story'] = stripslashes($post['short_story']);
				$post['full_story'] = stripslashes($post['full_story']);

				// Вывод изображения
				$image = '';
				switch ($this->config['image']) {
					// Первое изображение из краткого описания
					case 'short_story':
						$image = $this->getContentImage($post['short_story'], 0);
						break;

					// Первое изображение из полного описания
					case 'full_story':
						$image = $this->getContentImage($post['full_story'], 0);
						break;

					// По умолчанию - название дополнительного поля
					default:
						$xfields = xfieldsdataload($post['xfields']);
						if (!empty($xfields) && !empty($xfields[$this->config['image']])) {
							$image = $xfields[$this->config['image']];
						}
						break;
				}

				$linksOutput .= $this->applyTemplate($this->config['template'], array(
					'{link}'         => '<a ' . ($this->config['title'] != 'empty' ? 'title="' . ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) . '"' : '') . ' href="' . ($this->getPostUrl($post)) . '">' . ($this->config['anchor'] == 'title' ? stripslashes($post['metatitle']) : stripslashes($post['title'])) . '</a>',
					'{anchor}'       => $this->config['anchor'] == 'title' ? stripslashes($post['metatitle']) : stripslashes($post['title']),
					'{title}'        => $this->config['title'] != 'empty' ? ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) : '',
					'{short-story}'  => $this->crobContent($post['short_story'], $this->config['limit']),
					'{full-story}'   => $this->crobContent($post['full_story'], $this->config['limit']),
					'{image}'        => $image,
					'{link-url}'     => $this->getPostUrl($post),
					'{link-category}'=> implode(', ', $my_cat_link),
					'{category}'	 => implode(', ', $my_cat),
					'{category-icon}'=> implode('', $my_cat_icon),
					'{category-url}' => $categoryUrl,
				), array(
					"'\[link\\](.*?)\[/link\]'si"                     => '<a ' . ($this->config['title'] != 'empty' ? 'title="' . ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) . '"' : '') . ' href="' . ($this->getPostUrl($post)) . '">' . "\\1" . '</a>',
					"'\[show_image\\](.*?)\[/show_image\]'si"         => !empty($image) ? "\\1" : '',
					"'\[not_show_image\\](.*?)\[/not_show_image\]'si" => empty($image) ? "\\1" : '',
				));
			}
			// 
			$output = $linksOutput;

			// Если разрешено кэширование, сохраняем в кэш по данной конфигурации
			if ($this->dle_config['allow_cache'] && $this->dle_config['allow_cache'] != "no") {
				create_cache('linkenso_', $output, md5(implode('_', $this->config)) . $this->dle_config['skin']);
			}

			// Выводим содержимое модуля
			$this->showOutput($output);
		}


		/*
		 * Метод рекурсивно возвращает массив всех подкатегорий определенной категории
		 * @param $categoryId - идентификатор исходной категории
		 * @return array - массив со списком подкатегорий
		 */
		public function getSubcategoriesArray($categoryId) {
			// Проверка $categoryId
			$categoryId = intval($categoryId);

			// Массив со списком подкатегорий
			$subcategoriesArray = array();

			// Получаем список подкатегорий
			$subcategories = $this->db->super_query("SELECT id FROM " . PREFIX . "_category WHERE parentid = " . $categoryId, true);

			if (empty($subcategories)) $subcategories = array();

			foreach ($subcategories as $subcategory) {
				// Добавляем в массив текущую подкатегорию
				$subcategoriesArray[] = intval($subcategory['id']);

				// Добавляем в массив все ее подкатегории
				$subcategoriesArray = array_merge($subcategoriesArray, $this->getSubcategoriesArray($subcategory['id']));
			}

			// Возвращаем массив подкатегорий
			return $subcategoriesArray;
		}


		/*
		 * Метод возвращает массив всех подкатегорий определенной категории
		 * @param $categoryId - идентификатор исходной категории
		 * @return int - идентификатор самой "верхней" категории этой новости
		 */
		public function getGlobalCategory($categoryId) {
			// Проверка $categoryId
			$categoryId = intval($categoryId);

			// Подхватываем глобальный массив с информацией о категориях
			// Ползем по массиву категорий вверх, чтобы получить самую корневую категорию
			while ($this->cat_info[$categoryId]['parentid'] > 0) {
				$categoryId = intval($this->cat_info[$categoryId]['parentid']);
			}

			// Возвращаем самую корневую категорию
			return $categoryId;
		}


		/*
		 * @param $post - массив с информацией о статье
		 * @return string URL для категории
		 */
		public function getPostUrl($post) {
			
			if ($this->dle_config['allow_alt_url'] && $this->dle_config['allow_alt_url'] != "no") {
				if (
					($this->dle_config['version_id'] < 9.6 && $post['flag'] && $this->dle_config['seo_type'])
					||
					($this->dle_config['version_id'] >= 9.6 && ($this->dle_config['seo_type'] == 1 || $this->dle_config['seo_type'] == 2))
				) {
					if (intval($post['category']) && $this->dle_config['seo_type'] == 2) {
						$url = $this->dle_config['http_home_url'] . get_url(intval($post['category'])) . '/' . $post['id'] . '-' . $post['alt_name'] . '.html';
					}
					else {
						$url = $this->dle_config['http_home_url'] . $post['id'] . '-' . $post['alt_name'] . '.html';
					}
				}
				else {
					$url = $this->dle_config['http_home_url'] . date("Y/m/d/", strtotime($post['date'])) . $post['alt_name'] . '.html';
				}
			}
			else {
				$url = $this->dle_config['http_home_url'] . 'index.php?newsid=' . $post['id'];
			}

			return $url;
		}


		/*
		 * Метод обрезает строку $content на длину $length, удаляя все html-теги и возвращает её
		 * 
		 * @param $content - исходная строка
		 * @param $length - количество симолов, на которое нужно обрезать строку
		 * 
		 * @return string - результат обрезки
		 */
		public function crobContent($content = '', $length = 0) {
			$content = preg_replace( "'\<span class=\"highslide-caption\">(.*?)\</span>'si", "", "$content" );
			$content = str_replace("</p><p>", " ", $content);
			$content = strip_tags($content, "<br />");
			$content = trim(str_replace("<br>", " ", str_replace("<br />", " ", str_replace("\n", " ", str_replace("\r", "", $content)))));

			if ($length && dle_strlen($content, $config['charset']) > $length) {
				$content = dle_substr($content, 0, $length, $this->dle_config['charset']);
				if (($temp_dmax = dle_strrpos($content, ' ', $this->dle_config['charset']))) {
					$content = dle_substr($content, 0, $temp_dmax, $this->dle_config['charset']);
				}
			}

			return $content;
		}


		/*
		 * Метод возвращает $index по счету изображение из строки $content
		 * 
		 * @param $content - строка с контентом для поиска изображения
		 * @param $index - порядковый номер возвращаемого изображения начиная с 0
		 */

		public function getContentImage($content, $imageIndex = 0) {
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $content, $media);
			$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

			foreach ($data as $index => $url) {
				if ($index == $imageIndex) {
					$info = pathinfo($url);
					if (isset($info['extension'])) {
						$info['extension'] = strtolower($info['extension']);
						if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) {
							if (substr_count($url, 'data/emoticons') == 0 && substr_count($url, 'dleimages') == 0) return $url;
						}
					}
					$imageIndex++;
				}
			}

			return false;
		}


		/*
		 * Метод подхватывает tpl-шаблон, заменяет в нём теги и возвращает отформатированную строку
		 * @param $template - название шаблона, который нужно применить
		 * @param $vars - ассоциативный массив с данными для замены переменных в шаблоне
		 * @param $vars - ассоциативный массив с данными для замены блоков в шаблоне
		 *
		 * @return string tpl-шаблон, заполненный данными из массива $data
		 */
		public function applyTemplate($template, $vars = array(), $blocks = array()) {
			// Подключаем файл шаблона $template.tpl, заполняем его

			$this->tpl = new dle_template();
			$this->tpl->dir = TEMPLATE_DIR;

			$this->tpl->load_template($template . '.tpl');

			// Заполняем шаблон переменными
			$this->tpl->set('', $vars);

			// Заполняем шаблон блоками
			foreach ($blocks as $block => $value) {
				$this->tpl->set_block($block, $value);
			}

			// Компилируем шаблон (что бы это не означало ;))
			$this->tpl->compile($template);

			// Выводим результат
			return $this->tpl->result[$template];
		}


		/*
		 * Метод выводит содержимое модуля в браузер
		 * @param $output - строка для вывода
		 */
		public function showOutput($output) {
			echo $output;
		}
	}
}
/*---End Of LinkEnso Class---*/


// Подхватываем конфигурацию модуля
$linkEnsoConfig = array(
	'postId'   => !empty($post_id) ? $post_id : false,
	'links'    => !empty($links) ? $links : 3,
	'date'     => !empty($date) ? $date : 'old',
	'ring'     => !empty($ring) ? $ring : 'yes',
	'scan'     => !empty($scan) ? $scan : 'all_cat',
	'anchor'   => !empty($anchor) ? $anchor : 'name',
	'title'    => !empty($title) ? $title : 'title',
	'limit'    => !empty($limit) ? $limit : 0,
	'image'    => !empty($image) ? $image : 'full_story',
	'template' => !empty($template) ? $template : 'linkenso/linkenso'
);

// Создаем экземпляр класса для перелинковки и запускаем его главный метод
$linkEnso = new LinkEnso($linkEnsoConfig);
$linkEnso->run();

?>