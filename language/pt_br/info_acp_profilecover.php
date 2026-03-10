<?php
if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

$lang = array_merge($lang, [
    'ACP_PROFILECOVER_TITLE' => 'Capa de perfil',
    'ACP_PROFILECOVER_SETTINGS' => 'Configurações',
]);
