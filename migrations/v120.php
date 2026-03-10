<?php
namespace mundophpbb\profilecover\migrations;

class v120 extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return ['\\mundophpbb\\profilecover\\migrations\\v110'];
    }

    public function effectively_installed()
    {
        return isset($this->config['profilecover_header_height']);
    }

    public function update_data()
    {
        return [
            ['config.add', ['profilecover_header_height', 300]],
        ];
    }
}
