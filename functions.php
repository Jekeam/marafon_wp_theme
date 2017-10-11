<?php
//Загрузка/отключение скриптов/стилей
function add_styles_scripts(){
	//Jquery
    wp_deregister_script('jquery-core');
    wp_register_script('jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
    wp_enqueue_script('jquery');
    //Отключить стили плагина toc+
    wp_deregister_style('toc-screen');
}
add_action( 'wp_enqueue_scripts', 'add_styles_scripts' );

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

add_action('do_feed', 'morkovin_fb_disable_feed', 1);
add_action('do_feed_rdf', 'morkovin_fb_disable_feed', 1);
add_action('do_feed_rss', 'morkovin_fb_disable_feed', 1);
add_action('do_feed_rss2', 'morkovin_fb_disable_feed', 1);
add_action('do_feed_atom', 'morkovin_fb_disable_feed', 1);

function morkovin_fb_disable_feed() {
    wp_die( __('No feed available,please visit our <a href="'. get_bloginfo('url') .'">homepage</a>!') );
}
add_filter( 'xmlrpc_methods', 'morkovin_sar_block_xmlrpc_attacks' );
function morkovin_sar_block_xmlrpc_attacks( $methods ) {
   unset( $methods['pingback.ping'] );
   unset( $methods['pingback.extensions.getPingbacks'] );
   return $methods;
}
add_filter( 'wp_headers', 'morkovin_sar_remove_x_pingback_header' );
function morkovin_sar_remove_x_pingback_header( $headers ) {
   unset( $headers['X-Pingback'] );
   return $headers;
}

include('class.Kama_Make_Thumb.php');
include('morkovin_recent_posts.php');

add_theme_support('menus');
add_theme_support('post-thumbnails');
add_theme_support('title-tag');

register_nav_menu('nav_main', 'Главное меню');
register_nav_menu('nav_add', 'Дополнительное выпадающее');
register_nav_menu('nav_sidebar', 'Меню в сайдбаре');
register_nav_menu('nav_m', 'Меню для мобильной версии');
register_nav_menu('nav_footer', 'Меню в подвале');

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Верх сайдбара',
		'id' => "sidebar-1",
		'description' => '',
		'before_widget' => '<div class="section section_widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Низ сайдбара',
		'id' => "sidebar-2",
		'description' => '',
		'before_widget' => '<div class="section section_widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));

// удаляем ссылку с активного пункта меню
function no_link_current_page($menu) {
	return preg_replace('%((current_page_item|current-menu-item)[^<]+)[^>]+>([^<]+)</a>%', '$1<span>$3</span>', $menu);
}
add_filter('wp_nav_menu', 'no_link_current_page');

//Удалить атрибут rel
function my_remove_rel_attr($content) {
    return preg_replace('/\s+rel="attachment wp-att-[0-9]+"/i', '', $content);
}
add_filter('the_content', 'my_remove_rel_attr');

function new_excerpt_more($more) {
	global $post;
	return '';
}
add_filter('excerpt_more', 'new_excerpt_more');

//Количество просмотров записи
function get_post_views($postID) {
	$count_key = 'post_views_count';
	$count = get_post_meta($postID, $count_key, true);
	if ($count == '') {
		delete_post_meta($postID, $count_key);
		add_post_meta($postID, $count_key, '0');
		return '0';
	}
	return $count;
}

function set_post_views($postID) {
	$count_key = 'post_views_count';
	$count = get_post_meta($postID, $count_key, true);
	if ($count == '') {
		$count = 0;
		delete_post_meta($postID, $count_key);
		add_post_meta($postID, $count_key, '0');
	} else {
		$count++;
		update_post_meta($postID, $count_key, $count);
	}
}

add_filter('the_content', 'set_post_views_in_single');

function set_post_views_in_single($content) {
	if ( is_single() ) {
		set_post_views( get_the_ID() );
	}
	
	return $content;
}

//Хлебные крошки
function get_parent_of_subcategory($cat_id = false) {
    if($cat_id){
        $cat = get_category($cat_id);   
    } else {
        $cat = get_category( get_query_var('cat'),false );
    }
    
    $cat_parent_id = $cat->parent;

    if($cat_parent_id)
    {
        $cat_name = get_cat_name($cat_parent_id);
        $cat_url = get_category_link($cat_parent_id);

        return "<a href=\"$cat_url\" itemprop=\"item\"><span itemprop=\"name\">$cat_name</span></a>";
    }
    else
    {
        $cat_name = "";

        return FALSE;
    }
}

//Комментарии
function mytheme_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
<li id="comment-<?php comment_ID() ?>">
	<div id="div-comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<div class="gravatar"><?php echo get_avatar($comment, $size='50', $default=''); ?></div>
		<div class="comment_content">
			<div class="cauthor">
				<span class="author_name"><?php printf(__('<span class="fn">%s</span>'), get_comment_author_link()) ?></span>
				<span class="comment_date"> | <?php comment_date('j.m.Y H:i');?> <?php edit_comment_link(__('(Edit)'),'  ','') ?></span>
			</div>

			<div class="ctext">
				<?php if ($comment->comment_approved == '0') : ?>
					<p><em><?php _e('Your comment is awaiting moderation.') ?></em></p>
				<?php endif; ?>
				<?php comment_text() ?>

				<?php if (/*comments_open() AND */(get_option('thread_comments') == 1) AND ($depth != $args['max_depth'])) { ?>
				<div class="reply">
					<?php comment_reply_link(array_merge( $args, array('add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
				</div>
				<?php } ?>
			</div><!-- .ctext -->
		</div><!-- .comment_content -->
	</div>
<?php
}

function src_simple_recent_comments($src_count=7, $src_length=60) {
	global $wpdb;
	$sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_date_gmt, comment_date, comment_approved, comment_type,
		SUBSTRING(comment_content,1,$src_length) AS com_excerpt
		FROM $wpdb->comments
		LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
		WHERE comment_approved = '1' AND comment_type = '' AND post_password = ''
		ORDER BY comment_date_gmt DESC
		LIMIT $src_count";
	$comments = $wpdb->get_results($sql);
	foreach ($comments as $comment) {
		// $date = apply_filters('the_time', mysql2date("j F, H:i", $comment->comment_date));
?>
		<li>
			<p><?php echo strip_tags($comment->com_excerpt) ?>...</p>
			от: <?php echo $comment->comment_author ?> <a href="<?php echo get_permalink($comment->ID) ?>#comment-<?php echo $comment->comment_ID ?>"><?php echo $comment->post_title ?></a>
		</li>
<?php
	}
}

function delete_rel($link) {
    return str_replace('rel="category tag"', "", $link);
}
add_filter('the_category', 'delete_rel');

// удаляем лишние отступы у изображений с подписью
add_filter( 'img_caption_shortcode', 'my_img_caption_shortcode', 10, 3 );
function my_img_caption_shortcode( $empty, $attr, $content ){
	$attr = shortcode_atts( array(
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => ''
	), $attr );
	if ( 1 > (int) $attr['width'] || empty( $attr['caption'] ) ) { return ''; }
	if ( $attr['id'] ) { $attr['id'] = 'id="' . esc_attr( $attr['id'] ) . '" '; }
	return '<div ' . $attr['id']
	. 'class="wp-caption ' . esc_attr( $attr['align'] ) . '" '
	. 'style="max-width: ' . ( (int) $attr['width'] ) . 'px;">'
	. do_shortcode( $content )
	. '<div class="wp-caption-text">' . $attr['caption'] . '</div>'
	. '</div>';
}

if (is_admin()) {
	// колонка "ID" для таксономий (рубрик, меток и т.д.) в админке
	foreach (get_taxonomies() as $taxonomy) {
		add_action("manage_edit-${taxonomy}_columns",          'tax_add_col');
		add_filter("manage_edit-${taxonomy}_sortable_columns", 'tax_add_col');
		add_filter("manage_${taxonomy}_custom_column",         'tax_show_id', 10, 3);
	}
	add_action('admin_print_styles-edit-tags.php', 'tax_id_style');
	function tax_add_col($columns) {return $columns + array ('tax_id' => 'ID');}
	function tax_show_id($v, $name, $id) {return 'tax_id' === $name ? $id : $v;}
	function tax_id_style() {print '<style>#tax_id{width:4em}</style>';}

	// колонка "ID" для постов и страниц в админке
	add_filter('manage_posts_columns', 'posts_add_col', 5);
	add_action('manage_posts_custom_column', 'posts_show_id', 5, 2);
	add_filter('manage_pages_columns', 'posts_add_col', 5);
	add_action('manage_pages_custom_column', 'posts_show_id', 5, 2);
	add_action('admin_print_styles-edit.php', 'posts_id_style');
	function posts_add_col($defaults) {$defaults['wps_post_id'] = __('ID'); return $defaults;}
	function posts_show_id($column_name, $id) {if ($column_name === 'wps_post_id') echo $id;}
	function posts_id_style() {print '<style>#wps_post_id{width:4em}</style>';}
}

// отключаем стили YARPP
add_action('wp_print_styles','lm_dequeue_header_styles');
function lm_dequeue_header_styles() { wp_dequeue_style('yarppWidgetCss'); }
add_action('wp_footer','lm_dequeue_footer_styles');
function lm_dequeue_footer_styles() { wp_dequeue_style('yarppRelatedCss'); }

function genesis(){};

//noindex для toc+
add_filter( 'the_content', 'morkovin_noindex_toc', 1000);
function morkovin_noindex_toc($content){
     return preg_replace('/(<div id="toc_container"[^>]+>[^\n]+)/', '<!--noindex-->$1<!--/noindex-->', $content);
}

//настройки темы
add_action('customize_register', function($customizer){
    
    //общие настройки ------------------------------------------------------------------------
    $customizer->add_section(
        'section_general',
        array(
            'title' => 'Общие настройки',
            'description' => 'Опции',
            'priority' => 35,
        )
    );

    //Описание главной страницы
    $customizer->add_setting(
        'page_home'
    );
    $customizer->add_control(
        'page_home',
        array(
            'type' => 'dropdown-pages',
            'label' => 'Выберите страницу для главной',
            'section' => 'section_general',
        )
    );

    //Текс хлебной крошки
    $customizer->add_setting(
        'bread_crumbs'
    );
    $customizer->add_control(
        'bread_crumbs',
        array(
            'label' => 'Текст первой хлебной крошки',
            'section' => 'section_general',
            'type' => 'text',
        )
    );

    //Отключение сайдбара
    $customizer->add_setting(
        'remove_sidebar'
    );
    $customizer->add_control(
        'remove_sidebar',
        array(
            'type' => 'select',
            'label' => 'Отключить сайдбар главной',
            'section' => 'section_general',
            'choices' => array(
                '0' => 'Нет',
                '1' => 'Да',
            ),
        )
    );

    //Вывод даты
    $customizer->add_setting(
        'show_date'
    );
    $customizer->add_control(
        'show_date',
        array(
            'type' => 'select',
            'label' => 'Отображать дату',
            'section' => 'section_general',
            'choices' => array(
                '1' => 'Да',
                '0' => 'Нет',
            ),
        )
    );

    //Отображение количества комментариев
    $customizer->add_setting(
        'show_comments_number'
    );
    $customizer->add_control(
        'show_comments_number',
        array(
            'type' => 'select',
            'label' => 'Отображать кол-во коментов',
            'section' => 'section_general',
            'choices' => array(
                '1' => 'Да',
                '0' => 'Нет',
            ),
        )
    );

    //Отключить нижние хлебные крошки в публикации
    $customizer->add_setting(
        'disable_two_breadcrubs'
    );
    $customizer->add_control(
        'disable_two_breadcrubs',
        array(
            'type' => 'select',
            'label' => 'Отключить нижние хлебные крошки (на single)',
            'section' => 'section_general',
            'choices' => array(
                '0' => 'Нет',
                '1' => 'Да',
            ),
        )
    );

    //Липкое верхнее меню
    $customizer->add_setting(
        'sticky_top_menu'
    );
    $customizer->add_control(
        'sticky_top_menu',
        array(
            'type' => 'select',
            'label' => 'Липкое верхнее меню',
            'section' => 'section_general',
            'choices' => array(
                '0' => 'Нет',
                '1' => 'Да',
            ),
        )
    );

    //Форма комментирования без запроса e-mail и сайта
    $customizer->add_setting(
        'remove_email_site_comment_form'
    );
    $customizer->add_control(
        'remove_email_site_comment_form',
        array(
            'type' => 'select',
            'label' => 'Убрать из формы комментирования поля «email» и «сайт»',
            'section' => 'section_general',
            'choices' => array(
                '0' => 'Нет',
                '1' => 'Да',
            ),
        )
    );

    //Цитата
    $customizer->add_setting(
        'excerpt_or_content'
    );
    $customizer->add_control(
        'excerpt_or_content',
        array(
            'type' => 'select',
            'label' => 'Что выводить в анонсе поста',
            'section' => 'section_general',
            'choices' => array(
                '1' => 'Цитата',
                '2' => 'Описание из плагина Yoast SEO',
            ),
        )
    );

    //Количество выводимых постов на главной
    $customizer->add_setting(
        'posts_per_home'
    );
    $customizer->add_control(
        'posts_per_home',
        array(
            'label' => 'Количество постов на главной (по умолчанию 6)',
            'section' => 'section_general',
            'type' => 'text',
        )
    );

    //Поиск
    $customizer->add_setting(
        'selection_search_site'
    );
    $customizer->add_control(
        'selection_search_site',
        array(
            'type' => 'select',
            'label' => 'Поиск по сайту',
            'section' => 'section_general',
            'choices' => array(
                '1' => 'Wordpress (по умолчанию)',
                '2' => 'Яндекс поиск',
            ),
        )
    );

    //Социальные профили ------------------------------------------------------------------------
    $customizer->add_section(
        'section_social',
        array(
            'title' => 'Ссылки на cоц. сети',
            'description' => 'Ссылки на соц. сети',
            'priority' => 35,
        )
    );

    //ок
    $customizer->add_setting(
        'link_ok'
    );
    $customizer->add_control(
        'link_ok',
        array(
            'label' => 'Однокласники',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //yt
    $customizer->add_setting(
        'link_yt'
    );
    $customizer->add_control(
        'link_yt',
        array(
            'label' => 'YouTube',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //fb
    $customizer->add_setting(
        'link_fb'
    );
    $customizer->add_control(
        'link_fb',
        array(
            'label' => 'Facebook',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //gp
    $customizer->add_setting(
        'link_gp'
    );
    $customizer->add_control(
        'link_gp',
        array(
            'label' => 'GP',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //tw
    $customizer->add_setting(
        'link_tw'
    );
    $customizer->add_control(
        'link_tw',
        array(
            'label' => 'Twitter',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //in
    $customizer->add_setting(
        'link_in'
    );
    $customizer->add_control(
        'link_in',
        array(
            'label' => 'Instagram',
            'section' => 'section_social',
            'type' => 'text',
        )
    );
    //vk
    $customizer->add_setting(
        'link_vk'
    );
    $customizer->add_control(
        'link_vk',
        array(
            'label' => 'Вконтакте',
            'section' => 'section_social',
            'type' => 'text',
        )
    );

    // Стили ------------------------------------------------------------------------
    $customizer->add_section(
        'section_styles',
        array(
            'title' => 'Стили темы',
            'description' => 'Стили',
            'priority' => 35,
        )
    );

    //Цвет 1
    $customizer->add_setting(
        'section_color_1',
        array(
            'default' => '#6969b3',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_1',
            array(
                'label' => 'Цвет 1',
                'section' => 'section_styles',
                'settings' => 'section_color_1',
            )
        )
    );

    //Цвет 2
    $customizer->add_setting(
        'section_color_2',
        array(
            'default' => '#5a5aa1',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_2',
            array(
                'label' => 'Цвет 2',
                'section' => 'section_styles',
                'settings' => 'section_color_2',
            )
        )
    );

    //Цвет 3
    $customizer->add_setting(
        'section_color_3',
        array(
            'default' => '#5a5aa1',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_3',
            array(
                'label' => 'Цвет 3',
                'section' => 'section_styles',
                'settings' => 'section_color_3',
            )
        )
    );

    //Цвет ссылок в контенте
    $customizer->add_setting(
        'section_color_4',
        array(
            'default' => '#1e73be',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_4',
            array(
                'label' => 'Цвет ссылок в контенте',
                'section' => 'section_styles',
                'settings' => 'section_color_4',
            )
        )
    );
    //Цвет ссылок в контенте при наведении
    $customizer->add_setting(
        'section_color_5',
        array(
            'default' => '#e74949',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_5',
            array(
                'label' => 'Цвет ссылок в контенте при наведении',
                'section' => 'section_styles',
                'settings' => 'section_color_5',
            )
        )
    );

    //Цвет названия анонсов и подложки рубрики анонса при наведении
    $customizer->add_setting(
        'section_color_6',
        array(
            'default' => '#6969b3',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $customizer->add_control(
        new WP_Customize_Color_Control(
            $customizer,
            'section_color_6',
            array(
                'label' => 'Цвет названия анонсов и подложки рубрики анонса при наведении',
                'section' => 'section_styles',
                'settings' => 'section_color_6',
            )
        )
    );

    //Логотип в шапке
    $customizer->add_setting('logo_upload');
    $customizer->add_control(
        new WP_Customize_Image_Control(
            $customizer,
            'logo_upload',
            array(
                'label' => 'Логотип в шапке)',
                'section' => 'section_styles',
                'settings' => 'logo_upload'
            )
        )
    );

    //Логотип в подвале
    $customizer->add_setting('logo_footer_upload');
    $customizer->add_control(
        new WP_Customize_Image_Control(
            $customizer,
            'logo_footer_upload',
            array(
                'label' => 'Логотип в подвале',
                'section' => 'section_styles',
                'settings' => 'logo_footer_upload'
            )
        )
    );
});

//Опции темы
//Лого
$logo_upload = get_theme_mod('logo_upload', get_bloginfo('template_url').'/images/logo.png');  //по умолчанию стандартный логотип
$logo_footer_upload = get_theme_mod('logo_footer_upload', false);  //по умолчанию стандартный логотип

//Страница текст которой выводится в описании на главной
$homepage = get_theme_mod('page_home', false); //по умолчанию не выводим

//Хлебные крошки
$bread_crumbs_home = get_theme_mod('bread_crumbs', 'Главная'); //по умолчанию "главная"
if (!$bread_crumbs_home) $bread_crumbs_home = 'Главная'; 

//Отчелюение сайдбара на главной
$disable_sidebar_homepage = get_theme_mod('remove_sidebar', 0);

//Вывод даты
$show_date = get_theme_mod('show_date', 1);

//Отображение количества комментариев
$show_comments_number = get_theme_mod('show_comments_number', 1);

//Отключить хлебные крошки под записью
$disable_two_breadcrubs = get_theme_mod('disable_two_breadcrubs', 0);

//Липкое верхнее меню
$sticky_top_menu = get_theme_mod('sticky_top_menu', 0);

//Удаление полей email и сайт из формы комментирования
$remove_email_site_comment_form = get_theme_mod('remove_email_site_comment_form', 0);

//Что выводим в анонсе поста
$excerpt_or_content = get_theme_mod('excerpt_or_content', 1); //по умолчанию цитата

//Поиск по сайту
$selection_search_site = get_theme_mod('selection_search_site', 1); //по умолчанию поиск от вордпресс

//Количество постов на главной
$posts_per_home = get_theme_mod('posts_per_home', 6); //по умолчанию 6

//Соц сети
$social_ok = get_theme_mod('link_ok', '');
$social_yt = get_theme_mod('link_yt', '');
$social_fb = get_theme_mod('link_fb', '');
$social_gp = get_theme_mod('link_gp', '');
$social_tw = get_theme_mod('link_tw', '');
$social_in = get_theme_mod('link_in', '');
$social_vk = get_theme_mod('link_vk', '');

//Всплывающая подсказка
add_action( 'admin_enqueue_scripts', 'jdm_tut_pointer_header' );
function jdm_tut_pointer_header() {
    $enqueue = false;

    $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

    if ( ! in_array( 'jdm_tut_pointer', $dismissed ) ) {
        $enqueue = true;
        add_action( 'admin_print_footer_scripts', 'jdm_tut_pointer_footer' );
    }

    if ( $enqueue ) {
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }
}

function jdm_tut_pointer_footer() {
    $pointer_content = '<h3>Доро пожаловать в тему для марафонцев!</h3>';
    $pointer_content .= '<p>Для настроки темы воспользуйтесь пунктом меню внешний вид - настроить.</p>';
    $pointer_content .= '<p>Тема разработана командой Андрея Морковина.</p>';
    $pointer_content .= '<p><a href="https://docs.google.com/document/d/1u8qLmBg4Dj-8g5uKJHwID6eNYrkJGkisX1_gF7mM40o">Инструкция по работе с темой.</a></p>';
?>
<script type="text/javascript">// <![CDATA[
jQuery(document).ready(function($) {
    $('#menu-appearance').pointer({
        content: '<?php echo $pointer_content; ?>',
        position: {
            edge: 'left',
            align: 'center'
        },
        close: function() {
            $.post( ajaxurl, {
                pointer: 'jdm_tut_pointer',
                action: 'dismiss-wp-pointer'
            });
        }
    }).pointer('open');
});
// ]]></script>
<?php
}

add_filter('widget_text', 'do_shortcode');

if ( function_exists('register_sidebar') && $disable_sidebar_homepage )
    register_sidebar(array(
        'name' => 'Справа от популярных на главной',
        'id' => "popular-home",
        'description' => '',
        'before_widget' => '<div class="section section_widget section_popular-home %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="title">',
        'after_title' => '</div>',
    ));

add_filter( 'wp_postratings_schema_itemtype', 'wp_postratings_schema_itemtype' );  
function wp_postratings_schema_itemtype( $itemtype ) {  
    return '';  
}

function sample_admin_notice__success() {
    ?>
    <div class="notice">
        <p>Тема разработана командой Андрея Морковина специально для слушателей курсов в рамках проекта Puzat.ru. <a href="https://docs.google.com/document/d/1u8qLmBg4Dj-8g5uKJHwID6eNYrkJGkisX1_gF7mM40o/edit">Инструкция по работе с темой</a>.</p>
    </div>
    <?php
}
add_action( 'admin_notices', 'sample_admin_notice__success' ); 

function morkovin_change_yoast_description($str) { 
    $term_id = '';

    if ( is_category() ) {
        $term_id = get_query_var('cat');
    }
    if ( is_tag() ) {
        $term_id = get_query_var('tag_id');
    }

    if ( $term_id != '' ) {
        if ($str) {
            return $str;
        }
        
        $morkovin_description = false;
        $term = get_term($term_id);

        $title_posts = array();

        if ( $term->taxonomy == 'category' ) {
            $loop = new WP_Query('cat='.$term->term_id.'&posts_per_page=2');
        }
        if ( $term->taxonomy == 'post_tag' ) {
            $loop = new WP_Query('tag_id='.$term->term_id.'&posts_per_page=2');
        }

        if ( $loop->have_posts() ) {
            while ( $loop->have_posts() ) { $loop->the_post();
                $title_posts[] = get_the_title();
            }
            $morkovin_description = implode(". ", $title_posts);
            $morkovin_description = $morkovin_description.'.';
        } elseif ( $term->description ) {
            $morkovin_description = wp_trim_words($term->description);
        }
        
        wp_reset_query();

        return $morkovin_description;
    }

    return $str;
}; 
add_filter( 'wpseo_metadesc', 'morkovin_change_yoast_description', 10, 1 ); 