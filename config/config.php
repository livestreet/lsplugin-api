<?php
/**
 * Таблица БД
 */
$config['$root$']['db']['table']['api_main_session'] = '___db.table.prefix___api_session';
/**
 * Роутинг
 */
$config['$root$']['router']['page']['api'] = 'PluginApi_ActionMain';
/**
 * Количество статей на одну страницу
 */
$config['per_page'] = 10;
/**
 * Список разрешенных полей для возврата через API
 */
$config['allow_fields'] = array(
    'blog' => array(
        'blog_id',
        'blog_title',
        'blog_description',
        'blog_type',
        'blog_rating',
        'blog_count_user',
        'blog_count_topic',
        'blog_avatar' => '#avatar', // преобразует поле в полный URL до файла
        'owner'       => 'user',
    ),
    'user' => array(
        'user_id',
        'user_login',
        'user_date_register',
        'user_rating',
        'user_activate',
        'user_profile_name',
        'user_profile_sex',
        'user_profile_country',
        'user_profile_avatar' => '#avatar',
        'user_profile_foto'   => '#avatar',
    ),
);
/**
 * API ключ, если пустой, то обращаться к API может любой
 */
$config['api_key'] = '12345';

return $config;