<?php

class PluginApi_Update_CreateTable extends ModulePluginManager_EntityUpdate
{
    /**
     * Выполняется при обновлении версии
     */
    public function up()
    {
        if (!$this->isTableExists('prefix_api_session')) {
            /**
             * При активации выполняем SQL дамп
             */
            $this->exportSQL(Plugin::GetPath(__CLASS__) . '/update/2.0/dump.sql');
        }
    }

    /**
     * Выполняется при откате версии
     */
    public function down()
    {
        $this->exportSQLQuery('DROP TABLE prefix_api_session;');
    }
}