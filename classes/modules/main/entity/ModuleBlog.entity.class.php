<?php

class PluginApi_ModuleMain_EntityModuleBlog extends PluginApi_ModuleMain_EntityModule
{
    protected function runList()
    {
        $aFilter = array(
            'exclude_type' => 'personal'
        );
        $aFilterFields = $this->makeFilterFields($this->getParamStr('fields'));
        if (!$aFilterFields) {
            throw new PluginApi_ModuleMain_ExceptionBadRequest('Need set filter fields');
        }
        $aResult = $this->Blog_GetBlogsByFilter($aFilter, array('blog_count_user' => 'desc'), $this->getPage(), $this->getPerPage());
        foreach ($aResult['collection'] as $i => $oBlog) {
            $aResult['collection'][$i] = $this->filterObject($oBlog, $aFilterFields, Config::Get('plugin.api.allow_fields.blog'));
        }
        return $aResult;
    }
}