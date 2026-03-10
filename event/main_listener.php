<?php
namespace mundophpbb\profilecover\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    const COVER_DIR = 'images/profile_covers';
    const THUMB_SUFFIX = '_thumb';
    const DEFAULT_MAX_WIDTH = 1600;
    const DEFAULT_MAX_HEIGHT = 500;
    const DEFAULT_HEADER_HEIGHT = 300;
    const DEFAULT_MAX_FILESIZE_KB = 512;
    const DEFAULT_FOCAL_POS = 50;
    const THUMB_WIDTH = 320;
    const THUMB_HEIGHT = 120;
    const ASSET_VERSION = '1.3.0';

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\request\request_interface */
    protected $request;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\user */
    protected $user;

    /** @var string */
    protected $root_path;

    public function __construct(
        \phpbb\config\config $config,
        \phpbb\request\request_interface $request,
        \phpbb\template\template $template,
        \phpbb\user $user,
        $root_path
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->template = $template;
        $this->user = $user;
        $this->root_path = $root_path;
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'load_language_on_setup',
            'core.ucp_profile_modify_profile_info' => 'assign_ucp_template_vars',
            'core.ucp_profile_validate_profile_info' => 'validate_cover_upload',
            'core.ucp_profile_info_modify_sql_ary' => 'handle_cover_upload',
            'core.memberlist_view_profile' => 'assign_profile_template_vars',
        ];
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = isset($event['lang_set_ext']) && is_array($event['lang_set_ext']) ? $event['lang_set_ext'] : [];
        $lang_set_ext[] = [
            'ext_name' => 'mundophpbb/profilecover',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function assign_ucp_template_vars($event)
    {
        $this->ensure_language_loaded();
        $this->ensure_cover_directory();
        $this->assign_asset_template_vars();

        $cover = !empty($this->user->data['user_profile_cover']) ? $this->user->data['user_profile_cover'] : '';
        $pos_x = $this->get_user_cover_pos_x($this->user->data);
        $pos_y = $this->get_user_cover_pos_y($this->user->data);

        $this->template->assign_var('S_FORM_ENCTYPE', ' enctype="multipart/form-data"');
        $this->assign_current_cover_to_template($cover, $pos_x, $pos_y);
        $this->assign_constraint_template_vars();
    }

    public function validate_cover_upload($event)
    {
        $this->ensure_language_loaded();

        if (!$this->request->is_set_post('submit'))
        {
            return;
        }

        $upload = $this->get_uploaded_file();
        if (!$upload['has_file'])
        {
            return;
        }

        $error = $event['error'];

        if ($upload['error'] !== UPLOAD_ERR_OK)
        {
            if ($upload['error'] !== UPLOAD_ERR_NO_FILE)
            {
                $error[] = $this->user->lang('PROFILE_COVER_UPLOAD_ERROR');
            }

            $event['error'] = $error;
            return;
        }

        $extension = $this->get_extension($upload['name']);
        if (!$this->is_allowed_extension($extension))
        {
            $error[] = $this->user->lang('PROFILE_COVER_INVALID_TYPE');
            $event['error'] = $error;
            return;
        }

        if (!$this->can_process_extension($extension))
        {
            $error[] = $this->user->lang('PROFILE_COVER_UNSUPPORTED_SERVER_FORMAT', strtoupper($extension));
            $event['error'] = $error;
            return;
        }

        $image_info = @getimagesize($upload['tmp_name']);
        if ($image_info === false || !$this->is_valid_uploaded_image($upload['tmp_name']))
        {
            $error[] = $this->user->lang('PROFILE_COVER_INVALID_IMAGE');
            $event['error'] = $error;
            return;
        }

        if ($upload['size'] > $this->get_max_file_size_bytes())
        {
            $error[] = $this->user->lang('PROFILE_COVER_FILE_TOO_LARGE', $this->get_max_filesize_kb());
        }

        $img_width = (int) $image_info[0];
        $img_height = (int) $image_info[1];

        if ($img_width > $this->get_max_width())
        {
            $error[] = $this->user->lang('PROFILE_COVER_WIDTH_TOO_LARGE', $this->get_max_width());
        }

        if ($img_height > $this->get_max_height())
        {
            $error[] = $this->user->lang('PROFILE_COVER_HEIGHT_TOO_LARGE', $this->get_max_height());
        }

        $event['error'] = $error;
    }

    public function handle_cover_upload($event)
    {
        $this->ensure_language_loaded();
        $this->ensure_cover_directory();

        if (!$this->request->is_set_post('submit'))
        {
            return;
        }

        $current_cover = !empty($this->user->data['user_profile_cover']) ? $this->user->data['user_profile_cover'] : '';
        $current_pos_x = $this->get_user_cover_pos_x($this->user->data);
        $current_pos_y = $this->get_user_cover_pos_y($this->user->data);
        $new_pos_x = $this->get_requested_cover_pos('profile_cover_pos_x');
        $new_pos_y = $this->get_requested_cover_pos('profile_cover_pos_y');
        $delete_requested = (bool) $this->request->variable('delete_profile_cover', 0);
        $upload = $this->get_uploaded_file();

        if ($upload['has_file'] && $upload['error'] === UPLOAD_ERR_OK)
        {
            $extension = $this->get_extension($upload['name']);

            if (!$this->is_allowed_extension($extension) || !$this->can_process_extension($extension) || !$this->is_valid_uploaded_image($upload['tmp_name']))
            {
                return;
            }

            $image_info = @getimagesize($upload['tmp_name']);
            if ($image_info === false)
            {
                return;
            }

            if ($upload['size'] > $this->get_max_file_size_bytes())
            {
                return;
            }

            if ((int) $image_info[0] > $this->get_max_width() || (int) $image_info[1] > $this->get_max_height())
            {
                return;
            }

            $saved_filename = $this->process_and_store_cover($upload['tmp_name'], $extension, $new_pos_x, $new_pos_y);
            if (!$saved_filename)
            {
                return;
            }

            if ($current_cover)
            {
                $this->delete_cover_file($current_cover);
                $this->delete_cover_thumbnail($current_cover);
            }

            $sql_ary = $event['sql_ary'];
            $sql_ary['user_profile_cover'] = $saved_filename;
            $sql_ary['user_profile_cover_pos_x'] = $new_pos_x;
            $sql_ary['user_profile_cover_pos_y'] = $new_pos_y;
            $event['sql_ary'] = $sql_ary;
            return;
        }

        if ($delete_requested && $current_cover)
        {
            $this->delete_cover_file($current_cover);
            $this->delete_cover_thumbnail($current_cover);

            $sql_ary = $event['sql_ary'];
            $sql_ary['user_profile_cover'] = '';
            $sql_ary['user_profile_cover_pos_x'] = self::DEFAULT_FOCAL_POS;
            $sql_ary['user_profile_cover_pos_y'] = self::DEFAULT_FOCAL_POS;
            $event['sql_ary'] = $sql_ary;
            return;
        }

        if ($current_cover && ($new_pos_x !== $current_pos_x || $new_pos_y !== $current_pos_y))
        {
            $absolute_path = $this->get_cover_directory_absolute() . '/' . basename($current_cover);
            if (is_file($absolute_path))
            {
                $this->regenerate_thumbnail_from_existing_cover($current_cover, $new_pos_x, $new_pos_y);
            }

            $sql_ary = $event['sql_ary'];
            $sql_ary['user_profile_cover_pos_x'] = $new_pos_x;
            $sql_ary['user_profile_cover_pos_y'] = $new_pos_y;
            $event['sql_ary'] = $sql_ary;
        }
    }

    public function assign_profile_template_vars($event)
    {
        $this->ensure_language_loaded();
        $this->assign_asset_template_vars();

        $member = $event['member'];
        $cover = !empty($member['user_profile_cover']) ? $member['user_profile_cover'] : '';
        $pos_x = $this->get_user_cover_pos_x($member);
        $pos_y = $this->get_user_cover_pos_y($member);

        $this->assign_current_cover_to_template($cover, $pos_x, $pos_y);
        $this->template->assign_var('PROFILE_COVER_HEADER_HEIGHT', $this->get_header_height());
    }

    protected function assign_current_cover_to_template($filename, $pos_x, $pos_y)
    {
        $filename = basename((string) $filename);
        $absolute = $this->get_cover_directory_absolute() . '/' . $filename;
        $thumb_absolute = $this->get_cover_thumbnail_absolute($filename);
        $exists = ($filename !== '' && is_file($absolute));
        $thumb_exists = ($filename !== '' && is_file($thumb_absolute));

        $this->template->assign_vars([
            'S_PROFILE_COVER_EXISTS' => $exists,
            'PROFILE_COVER_SRC' => $exists ? $this->build_cover_url($filename) : '',
            'PROFILE_COVER_THUMB_SRC' => ($exists && $thumb_exists) ? $this->build_cover_thumb_url($filename) : '',
            'PROFILE_COVER_POS_X' => $this->clamp_cover_pos($pos_x),
            'PROFILE_COVER_POS_Y' => $this->clamp_cover_pos($pos_y),
            'PROFILE_COVER_HEADER_HEIGHT' => $this->get_header_height(),
        ]);
    }

    protected function ensure_language_loaded()
    {
        $this->user->add_lang_ext('mundophpbb/profilecover', 'common');
    }

    protected function assign_asset_template_vars()
    {
        $this->template->assign_vars([
            'PROFILECOVER_THEME_PATH' => $this->root_path . 'ext/mundophpbb/profilecover/styles/all/theme',
            'PROFILECOVER_TEMPLATE_PATH' => $this->root_path . 'ext/mundophpbb/profilecover/styles/all/template',
            'PROFILECOVER_ASSET_VERSION' => self::ASSET_VERSION,
        ]);
    }

    protected function assign_constraint_template_vars()
    {
        $this->template->assign_vars([
            'PROFILE_COVER_MAX_WIDTH' => $this->get_max_width(),
            'PROFILE_COVER_MAX_HEIGHT' => $this->get_max_height(),
            'PROFILE_COVER_MAX_FILESIZE_KB' => $this->get_max_filesize_kb(),
            'PROFILE_COVER_HEADER_HEIGHT' => $this->get_header_height(),
            'PROFILE_COVER_CONSTRAINTS' => $this->user->lang(
                'PROFILE_COVER_CONSTRAINTS',
                $this->get_max_width(),
                $this->get_max_height(),
                $this->get_max_filesize_kb(),
                $this->get_header_height()
            ),
            'PROFILE_COVER_POSITION_HELP' => $this->user->lang('PROFILE_COVER_POSITION_HELP'),
        ]);
    }

    protected function get_uploaded_file()
    {
        $file = $this->request->file('profile_cover');

        if (!is_array($file))
        {
            $file = [];
        }

        $name = isset($file['name']) ? (string) $file['name'] : '';
        $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        $has_file = ($name !== '' || $error !== UPLOAD_ERR_NO_FILE);

        return [
            'has_file' => $has_file,
            'name' => $name,
            'tmp_name' => $tmp_name,
            'error' => $error,
            'size' => $size,
        ];
    }

    protected function is_allowed_extension($extension)
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    protected function can_process_extension($extension)
    {
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                return function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');

            case 'png':
                return function_exists('imagecreatefrompng') && function_exists('imagepng');

            case 'gif':
                return function_exists('imagecreatefromgif') && function_exists('imagegif');

            case 'webp':
                return function_exists('imagecreatefromwebp') && function_exists('imagewebp');
        }

        return false;
    }

    protected function is_valid_uploaded_image($tmp_name)
    {
        return $tmp_name !== '' && is_uploaded_file($tmp_name) && @getimagesize($tmp_name) !== false;
    }

    protected function get_extension($filename)
    {
        return strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
    }

    protected function ensure_cover_directory()
    {
        $directory = $this->get_cover_directory_absolute();

        if (!is_dir($directory))
        {
            @mkdir($directory, 0755, true);
        }

        $index_file = $directory . '/index.htm';
        if (is_dir($directory) && !file_exists($index_file))
        {
            @file_put_contents($index_file, '');
        }
    }

    protected function delete_cover_file($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '')
        {
            return;
        }

        $path = $this->get_cover_directory_absolute() . '/' . $filename;
        if (file_exists($path))
        {
            @unlink($path);
        }
    }

    protected function delete_cover_thumbnail($filename)
    {
        $path = $this->get_cover_thumbnail_absolute($filename);
        if ($path && file_exists($path))
        {
            @unlink($path);
        }
    }

    protected function build_cover_url($filename)
    {
        return $this->root_path . self::COVER_DIR . '/' . rawurlencode(basename((string) $filename));
    }

    protected function build_cover_thumb_url($filename)
    {
        return $this->root_path . self::COVER_DIR . '/' . rawurlencode($this->get_thumb_filename($filename));
    }

    protected function get_cover_directory_absolute()
    {
        return rtrim($this->root_path, '/') . '/' . self::COVER_DIR;
    }

    protected function get_cover_thumbnail_absolute($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '')
        {
            return '';
        }

        return $this->get_cover_directory_absolute() . '/' . $this->get_thumb_filename($filename);
    }

    protected function get_thumb_filename($filename)
    {
        $filename = basename((string) $filename);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        return $basename . self::THUMB_SUFFIX . '.' . $extension;
    }

    protected function get_max_width()
    {
        return $this->get_config_int('profilecover_max_width', self::DEFAULT_MAX_WIDTH);
    }

    protected function get_max_height()
    {
        return $this->get_config_int('profilecover_max_height', self::DEFAULT_MAX_HEIGHT);
    }

    protected function get_header_height()
    {
        $value = $this->get_config_int('profilecover_header_height', self::DEFAULT_HEADER_HEIGHT);
        return min($value, $this->get_max_height());
    }

    protected function get_max_filesize_kb()
    {
        return $this->get_config_int('profilecover_max_filesize', self::DEFAULT_MAX_FILESIZE_KB);
    }

    protected function get_max_file_size_bytes()
    {
        return $this->get_max_filesize_kb() * 1024;
    }

    protected function get_config_int($name, $default)
    {
        $value = isset($this->config[$name]) ? (int) $this->config[$name] : (int) $default;
        return ($value > 0) ? $value : (int) $default;
    }

    protected function get_requested_cover_pos($key)
    {
        return $this->clamp_cover_pos((int) $this->request->variable($key, self::DEFAULT_FOCAL_POS));
    }

    protected function get_user_cover_pos_x($row)
    {
        return $this->clamp_cover_pos(isset($row['user_profile_cover_pos_x']) ? (int) $row['user_profile_cover_pos_x'] : self::DEFAULT_FOCAL_POS);
    }

    protected function get_user_cover_pos_y($row)
    {
        return $this->clamp_cover_pos(isset($row['user_profile_cover_pos_y']) ? (int) $row['user_profile_cover_pos_y'] : self::DEFAULT_FOCAL_POS);
    }

    protected function clamp_cover_pos($value)
    {
        $value = (int) $value;
        if ($value < 0)
        {
            return 0;
        }
        if ($value > 100)
        {
            return 100;
        }
        return $value;
    }

    protected function generate_token()
    {
        try
        {
            return bin2hex(random_bytes(8));
        }
        catch (\Exception $e)
        {
            return md5(uniqid((string) mt_rand(), true));
        }
    }

    protected function process_and_store_cover($tmp_name, $extension, $pos_x, $pos_y)
    {
        $source = $this->create_image_resource($tmp_name, $extension);
        if (!$source)
        {
            return false;
        }

        $image_info = @getimagesize($tmp_name);
        if ($image_info === false)
        {
            imagedestroy($source);
            return false;
        }

        $source_width = (int) $image_info[0];
        $source_height = (int) $image_info[1];
        if ($source_width < 1 || $source_height < 1)
        {
            imagedestroy($source);
            return false;
        }

        $filename = sprintf('cover_%d_%s.%s', (int) $this->user->data['user_id'], $this->generate_token(), $extension);
        $main_path = $this->get_cover_directory_absolute() . '/' . $filename;
        $thumb_path = $this->get_cover_thumbnail_absolute($filename);

        $saved_main = $this->save_image_resource($source, $main_path, $extension);
        if (!$saved_main)
        {
            imagedestroy($source);
            return false;
        }

        $saved_thumb = $this->save_thumbnail_from_source($source, $thumb_path, $extension, $source_width, $source_height, $pos_x, $pos_y);
        imagedestroy($source);

        if (!$saved_thumb && file_exists($thumb_path))
        {
            @unlink($thumb_path);
        }

        @chmod($main_path, 0644);
        if (file_exists($thumb_path))
        {
            @chmod($thumb_path, 0644);
        }

        return $filename;
    }

    protected function regenerate_thumbnail_from_existing_cover($filename, $pos_x, $pos_y)
    {
        $filename = basename((string) $filename);
        if ($filename === '')
        {
            return false;
        }

        $path = $this->get_cover_directory_absolute() . '/' . $filename;
        if (!is_file($path))
        {
            return false;
        }

        $extension = $this->get_extension($filename);
        $source = $this->create_image_resource($path, $extension);
        if (!$source)
        {
            return false;
        }

        $image_info = @getimagesize($path);
        if ($image_info === false)
        {
            imagedestroy($source);
            return false;
        }

        $thumb_path = $this->get_cover_thumbnail_absolute($filename);
        $saved = $this->save_thumbnail_from_source($source, $thumb_path, $extension, (int) $image_info[0], (int) $image_info[1], $pos_x, $pos_y);
        imagedestroy($source);

        if ($saved && file_exists($thumb_path))
        {
            @chmod($thumb_path, 0644);
        }

        return $saved;
    }

    protected function create_image_resource($path, $extension)
    {
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($path);

            case 'png':
                return @imagecreatefrompng($path);

            case 'gif':
                return @imagecreatefromgif($path);

            case 'webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
        }

        return false;
    }

    protected function prepare_destination_canvas($resource, $extension)
    {
        if (in_array($extension, ['png', 'gif', 'webp'], true))
        {
            imagealphablending($resource, false);
            imagesavealpha($resource, true);
            $transparent = imagecolorallocatealpha($resource, 0, 0, 0, 127);
            imagefilledrectangle($resource, 0, 0, imagesx($resource), imagesy($resource), $transparent);
        }
        else
        {
            $background = imagecolorallocate($resource, 255, 255, 255);
            imagefilledrectangle($resource, 0, 0, imagesx($resource), imagesy($resource), $background);
        }
    }

    protected function save_thumbnail_from_source($source, $thumb_path, $extension, $source_width, $source_height, $pos_x, $pos_y)
    {
        $target_ratio = self::THUMB_WIDTH / self::THUMB_HEIGHT;
        $source_ratio = $source_width / max(1, $source_height);

        if ($source_ratio > $target_ratio)
        {
            $crop_height = $source_height;
            $crop_width = (int) round($crop_height * $target_ratio);
        }
        else
        {
            $crop_width = $source_width;
            $crop_height = (int) round($crop_width / $target_ratio);
        }

        $max_x = max(0, $source_width - $crop_width);
        $max_y = max(0, $source_height - $crop_height);
        $src_x = (int) round($max_x * ($this->clamp_cover_pos($pos_x) / 100));
        $src_y = (int) round($max_y * ($this->clamp_cover_pos($pos_y) / 100));

        $thumb = imagecreatetruecolor(self::THUMB_WIDTH, self::THUMB_HEIGHT);
        if (!$thumb)
        {
            return false;
        }

        $this->prepare_destination_canvas($thumb, $extension);

        $saved = false;
        if (imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, self::THUMB_WIDTH, self::THUMB_HEIGHT, $crop_width, $crop_height))
        {
            $saved = $this->save_image_resource($thumb, $thumb_path, $extension);
        }

        imagedestroy($thumb);
        return $saved;
    }

    protected function save_image_resource($resource, $path, $extension)
    {
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                return @imagejpeg($resource, $path, 90);

            case 'png':
                return @imagepng($resource, $path, 6);

            case 'gif':
                return @imagegif($resource, $path);

            case 'webp':
                return function_exists('imagewebp') ? @imagewebp($resource, $path, 90) : false;
        }

        return false;
    }
}
