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
    'ACP_PROFILECOVER_SETTINGS_EXPLAIN' => 'Set the global limits used during validation, automatic crop and display of the profile cover.',
    'ACP_PROFILECOVER_MAX_WIDTH' => 'Maximum image width',
    'ACP_PROFILECOVER_MAX_WIDTH_EXPLAIN' => 'Value in pixels. Images wider than this value will be rejected.',
    'ACP_PROFILECOVER_MAX_HEIGHT' => 'Maximum image height',
    'ACP_PROFILECOVER_MAX_HEIGHT_EXPLAIN' => 'Value in pixels. Images taller than this value will be rejected.',
    'ACP_PROFILECOVER_HEADER_HEIGHT' => 'Visual header height',
    'ACP_PROFILECOVER_HEADER_HEIGHT_EXPLAIN' => 'Value in pixels used on the public profile and as the target height for the automatic crop. It must be less than or equal to the maximum height.',
    'ACP_PROFILECOVER_MAX_FILESIZE' => 'Maximum file size',
    'ACP_PROFILECOVER_MAX_FILESIZE_EXPLAIN' => 'Value in KB. Larger files will be rejected.',
    'ACP_PROFILECOVER_SAVED' => 'The profile cover settings have been saved successfully.',
    'ACP_PROFILECOVER_INVALID_POSITIVE' => 'All values must be integers greater than zero.',
    'ACP_PROFILECOVER_HEADER_HEIGHT_TOO_LARGE' => 'The visual header height cannot be greater than the maximum image height.',
]);
