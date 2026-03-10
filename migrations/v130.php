<?php
namespace mundophpbb\profilecover\migrations;

class v130 extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return ['\\mundophpbb\\profilecover\\migrations\\v120'];
    }

    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_profile_cover_pos_x')
            && $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_profile_cover_pos_y');
    }

    public function update_schema()
    {
        return [
            'add_columns' => [
                $this->table_prefix . 'users' => [
                    'user_profile_cover_pos_x' => ['UINT:3', 50],
                    'user_profile_cover_pos_y' => ['UINT:3', 50],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_columns' => [
                $this->table_prefix . 'users' => [
                    'user_profile_cover_pos_x',
                    'user_profile_cover_pos_y',
                ],
            ],
        ];
    }
}
