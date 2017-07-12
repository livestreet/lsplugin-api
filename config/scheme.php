<?php

/*
 * Описание настроек плагина для интерфейса редактирования
 */
$config['$config_scheme$'] = array(
    'per_page'           => array(
        /*
         * тип: integer, string, array, boolean, float
         */
        'type'        => 'integer',
        /*
         * отображаемое имя параметра, ключ языкового файла
         */
        'name'        => 'config.per_page.name',
        /*
         * отображаемое описание параметра, ключ языкового файла
         */
        'description' => 'config.per_page.description',
        /*
         * валидатор (не обязательно)
         */
        'validator'   => array(
            /*
             * тип валидатора, существующие типы валидаторов движка:
             * Boolean, Compare, Date, Email, Number, Regexp, Required, String, Tags, Type, Url, Array (специальный валидатор, см. документацию)
             */
            'type'   => 'Number',
            /*
             * параметры, которые будут переданы в валидатор
             */
            'params' => array(
                'min'         => 1,
                'max'         => 50,
                /*
                 * разрешить только целое число
                 */
                'integerOnly' => true,
                /*
                 * не допускать пустое значение
                 */
                'allowEmpty'  => false,
            ),
        ),
    ),
    'api_key'     => array(
        'type'        => 'string',
        'name'        => 'config.api_key.name',
        'description' => 'config.api_key.description',
        'validator'   => array(
            'type'   => 'String',
            'params' => array(
                'min'        => 1,
                'max'        => 50,
                'allowEmpty' => true,
            ),
        ),
    )
);


/**
 * Описание разделов для настроек
 * Каждый раздел группирует определенные параметры конфига
 */
$config['$config_sections$'] = array(
    /**
     * Настройки раздела
     */
    array(
        /**
         * Название раздела
         */
        'name'         => 'config_sections.main',
        /**
         * Список параметров для отображения в разделе
         */
        'allowed_keys' => array(
            'api_key',
            'per_page',
        ),
    ),
);

return $config;