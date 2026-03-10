<?php
namespace mundophpbb\profilecover\acp;

class main_info
{
    public function module()
    {
        global $user;

        if (is_object($user))
        {
            $user->add_lang_ext('mundophpbb/profilecover', 'info_acp_profilecover');
        }

        return [
            'filename' => '\\mundophpbb\\profilecover\\acp\\main_module',
            'title' => 'ACP_PROFILECOVER_TITLE',
            'modes' => [
                'settings' => [
                    'title' => 'ACP_PROFILECOVER_SETTINGS',
                    'auth' => 'ext_mundophpbb/profilecover && acl_a_board',
                    'cat' => ['ACP_PROFILECOVER_TITLE'],
                ],
            ],
        ];
    }
}
