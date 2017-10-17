<?php

class WISlider {
    function wis_activate() {
        $date = '['. date('Y-m-d H:m:s') . ']';
        error_log($date . " -> Плагин активирован\r\n", 3, dirname(__FILE__) . '/wp-idea-errors.log');
        global $wpdb;
        //call global object - wpdb
        $sql = "CREATE TABLE IF NOT EXISTS `ids_slider` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `image_id` varchar(255) NOT NULL,
            `description` varchar(255) NOT NULL,
            `link` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);
    }

    function wis_deactivate() {
        $date = '['. date('Y-m-d H:m:s') . ']';
        error_log($date . " -> Плагин деактивирован\r\n", 3, dirname(__FILE__).'/wp-idea-errors.log');
    }
}


class SliderAdmin extends WISlider {
    function wis_admin_menu() {
        add_menu_page(
            'IDEA Slider',
            'IDEA Slider',
            'manage_options',
            'wis_edit_pages',
            array(&$this, 'wis_slider_view'),
            'dashicons-images-alt2'
        );
        add_action('admin_enqueue_scripts', array(&$this, 'wis_admin_scripts'));
    }

    function wis_admin_scripts($hook) {
        if ($hook != 'toplevel_page_wis_edit_pages') return;
        wp_enqueue_style('wis-style', plugins_url('css/slider.css', __FILE__));
        wp_enqueue_script('wis-script', plugins_url('js/slider.js', __FILE__), array('jquery'));
    }

    public function wis_slider_view() {
        if(!current_user_can('update_core')) {
            echo '<p style="text-align: center; margin-top: 300px">Нет прав для просмотра страницы</p>';
            return;
        }
        global $wpdb;
        $sql = 'SELECT * FROM `ids_slider`';
        $result = $wpdb->get_results($sql);

        $html = '<div class="wrap">';
        $html .= '<h2>IDEA SLIDER - Настройки</h2>';
        if (!count($result)) {
            $html .= '<p>В слайдере нет картинок</p>';
        }
        $html .= '<form id="wis_form" enctype="multipart/form-data" action="" method="POST">';
        $html .= '<label class="wis_label" for="photo">Фото в формате JPG, PNG</label>';
        $html .= '<input type="file" id="photo" name="photo"><br><br>';
        $html .= '<button id="wis_load_photo" type="submit">Загрузить фото</button>';
        $html .= '</form>';



        $html .= '<div class="wis_slider_list"><ul>';
        foreach ($result as $item) {
            $itemArr = wp_get_attachment_image_src($item->image_id, 'medium');
            $html .= '<li data-id="'.$item->id.'"><div class="wis_img" style="background-image:url(' . $itemArr[0]  . ')"></div>
            <textarea class="wis_img_desc" cols="30" rows="5" style="resize:none" placeholder="Описание (необязательно)">'. $item->descripion . '</textarea>
            <input type="text" placeholder="Ссылка на страницу (необязательно)">
            </li>';
        }
        $html .='</ul></div>';
        $html .= '<div class="loader-wrapper"><span>Фото загружается</span><div class="cssload-container"><div class="cssload-loading"><i></i><i></i><i></i><i></i></div></div></div>';
        $html .= '</div>';

        echo $html;
    }

    static function load_slider_image() {
        global $wpdb;
        $file = null;
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        if (!empty($_FILES['photo'])) {
            $_FILES['photo']['name'] = preg_replace('/[\x{0410}-\x{042F}]+.*[\x{0410}-\x{042F}]+/iu', '', sanitize_file_name($_FILES['photo']['name']));
        }

        $attachment_id = media_handle_upload( 'photo', 0 );

        if ( !is_wp_error( $attachment_id )) {
            $itemArr = wp_get_attachment_image_src($attachment_id, 'medium');

            $ajax_html = '<li data-id="'.$attachment_id.'"><div class="wis_img" style="background-image:url(' . $itemArr[0]  . ')"></div>
                           <textarea class="wis_img_desc" cols="30" rows="5" style="resize:none" placeholder="Описание (необязательно)"></textarea>
                           <input placeholder="Ссылка на страницу (необязательно)"></li>';

            if ($wpdb->query($wpdb->prepare(
                "INSERT INTO ids_slider(image_id) VALUES (%s)", $attachment_id
            ))) {
                wp_send_json(array(
                    'result' => 'ok',
                    'html' => $ajax_html
                ));
            }
        }

        wp_send_json_error(array(
            'error' => 'Не удалось загрузить фото',
        ), 500);
    }
}