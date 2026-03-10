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
    'ACP_PROFILECOVER_SETTINGS_EXPLAIN' => 'Defina os limites globais usados na validação, no recorte automático e na exibição da capa do perfil.',
    'ACP_PROFILECOVER_MAX_WIDTH' => 'Largura máxima da imagem',
    'ACP_PROFILECOVER_MAX_WIDTH_EXPLAIN' => 'Valor em pixels. Imagens com largura maior que este valor serão rejeitadas.',
    'ACP_PROFILECOVER_MAX_HEIGHT' => 'Altura máxima da imagem',
    'ACP_PROFILECOVER_MAX_HEIGHT_EXPLAIN' => 'Valor em pixels. Imagens com altura maior que este valor serão rejeitadas.',
    'ACP_PROFILECOVER_HEADER_HEIGHT' => 'Altura visual do header',
    'ACP_PROFILECOVER_HEADER_HEIGHT_EXPLAIN' => 'Valor em pixels usado no perfil público e como altura alvo do recorte automático. Deve ser menor ou igual à altura máxima.',
    'ACP_PROFILECOVER_MAX_FILESIZE' => 'Tamanho máximo do arquivo',
    'ACP_PROFILECOVER_MAX_FILESIZE_EXPLAIN' => 'Valor em KB. Arquivos maiores serão rejeitados.',
    'ACP_PROFILECOVER_SAVED' => 'As configurações da capa de perfil foram salvas com sucesso.',
    'ACP_PROFILECOVER_INVALID_POSITIVE' => 'Todos os valores devem ser números inteiros maiores que zero.',
    'ACP_PROFILECOVER_HEADER_HEIGHT_TOO_LARGE' => 'A altura visual do header não pode ser maior que a altura máxima da imagem.',
]);
