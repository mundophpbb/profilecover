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
    'PROFILE_COVER' => 'Capa do perfil',
    'PROFILE_COVER_EXPLAIN' => 'Envie uma imagem JPG, JPEG, PNG, GIF ou WebP para ser usada como capa do seu perfil.',
    'PROFILE_COVER_CONSTRAINTS' => 'Limites atuais: largura máxima de %1$d px, altura máxima de %2$d px, tamanho máximo de %3$d KB e altura visual do header de %4$d px.',
    'PROFILE_COVER_DELETE' => 'Remover capa atual',
    'PROFILE_COVER_UPLOAD_ERROR' => 'Não foi possível enviar a capa do perfil.',
    'PROFILE_COVER_INVALID_TYPE' => 'Envie uma imagem válida nos formatos JPG, JPEG, PNG, GIF ou WebP.',
    'PROFILE_COVER_INVALID_IMAGE' => 'O arquivo enviado não é uma imagem válida.',
    'PROFILE_COVER_FILE_TOO_LARGE' => 'A imagem da capa excede o limite de %d KB.',
    'PROFILE_COVER_WIDTH_TOO_LARGE' => 'A largura da imagem excede o limite de %d px.',
    'PROFILE_COVER_HEIGHT_TOO_LARGE' => 'A altura da imagem excede o limite de %d px.',
    'PROFILE_COVER_UNSUPPORTED_SERVER_FORMAT' => 'O servidor não possui suporte de processamento para o formato %s.',
    'PROFILE_COVER_PREVIEW' => 'Pré-visualização da capa',
    'PROFILE_COVER_CHOOSE_FILE' => 'Escolher arquivo',
    'PROFILE_COVER_NO_FILE_SELECTED' => 'Nenhum arquivo selecionado',
    'PROFILE_COVER_POSITION_HELP' => 'Arraste ou clique na pré-visualização para escolher o ponto focal da capa.',
]);
