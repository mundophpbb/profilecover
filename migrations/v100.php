<?php
namespace mundophpbb\profilecover\migrations;

class v100 extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return ['\\phpbb\\db\\migration\\data\\v330\\v330'];
    }

    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_profile_cover');
    }

    public function update_schema()
    {
        return [
            'add_columns' => [
                $this->table_prefix . 'users' => [
                    'user_profile_cover' => ['VCHAR:255', ''],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_columns' => [
                $this->table_prefix . 'users' => [
                    'user_profile_cover',
                ],
            ],
        ];
    }
}
