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
    'blog'  => array(
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
    'topic' => array(
        'topic_id',
        'topic_title',
        'topic_slug',
        'topic_tags',
        'topic_date_publish',
        'topic_publish',
        'topic_publish_index',
        'topic_rating',
        'topic_count_vote',
        'topic_count_vote_up',
        'topic_count_vote_down',
        'topic_count_read',
        'topic_count_comment',
        'topic_count_favourite',
        'topic_text',
        'topic_text_short',
        'topic_cut_text',
        'topic_forbid_comment',
        'topic_extra'   => '#serialize',
        'preview_image' => '#topic.preview(800x300crop)', // обработка превью. topic - модуль, preview - название обработчика, в скобках параметры
        'user'          => 'user',
    ),
    'user'  => array(
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