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
    'PROFILE_COVER' => 'Profile cover',
    'PROFILE_COVER_EXPLAIN' => 'Upload a JPG, JPEG, PNG, GIF or WebP image to use as your profile cover.',
    'PROFILE_COVER_CONSTRAINTS' => 'Current limits: maximum width of %1$d px, maximum height of %2$d px, maximum file size of %3$d KB and visual header height of %4$d px.',
    'PROFILE_COVER_DELETE' => 'Remove current cover',
    'PROFILE_COVER_UPLOAD_ERROR' => 'The profile cover could not be uploaded.',
    'PROFILE_COVER_INVALID_TYPE' => 'Please upload a valid JPG, JPEG, PNG, GIF or WebP image.',
    'PROFILE_COVER_INVALID_IMAGE' => 'The uploaded file is not a valid image.',
    'PROFILE_COVER_FILE_TOO_LARGE' => 'The cover image exceeds the limit of %d KB.',
    'PROFILE_COVER_WIDTH_TOO_LARGE' => 'The image width exceeds the limit of %d px.',
    'PROFILE_COVER_HEIGHT_TOO_LARGE' => 'The image height exceeds the limit of %d px.',
    'PROFILE_COVER_UNSUPPORTED_SERVER_FORMAT' => 'The server does not support image processing for the %s format.',
    'PROFILE_COVER_PREVIEW' => 'Cover preview',
    'PROFILE_COVER_CHOOSE_FILE' => 'Choose file',
    'PROFILE_COVER_NO_FILE_SELECTED' => 'No file selected',
    'PROFILE_COVER_POSITION_HELP' => 'Drag or click on the preview to choose the focal point of the cover.',
]);
