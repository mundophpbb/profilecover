<?php
namespace mundophpbb\profilecover\migrations;

class v110 extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return ['\\mundophpbb\\profilecover\\migrations\\v100'];
    }

    public function effectively_installed()
    {
        return isset($this->config['profilecover_max_width'])
            && isset($this->config['profilecover_max_height'])
            && isset($this->config['profilecover_max_filesize']);
    }

    public function update_data()
    {
        return [
            ['config.add', ['profilecover_max_width', 1600]],
            ['config.add', ['profilecover_max_height', 500]],
            ['config.add', ['profilecover_max_filesize', 512]],

            ['module.add', [
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_PROFILECOVER_TITLE',
            ]],
            ['module.add', [
                'acp',
                'ACP_PROFILECOVER_TITLE',
                [
                    'module_basename' => '\\mundophpbb\\profilecover\\acp\\main_module',
                    'modes' => ['settings'],
                ],
            ]],
        ];
    }
}
