<?php
namespace mundophpbb\profilecover\acp;

class main_module
{
    public $u_action;
    public $tpl_name = 'acp_profilecover';
    public $page_title = 'ACP_PROFILECOVER_SETTINGS';

    public function main($id, $mode)
    {
        global $phpbb_container;

        /** @var \phpbb\language\language $language */
        $language = $phpbb_container->get('language');
        /** @var \phpbb\request\request $request */
        $request = $phpbb_container->get('request');
        /** @var \phpbb\config\config $config */
        $config = $phpbb_container->get('config');
        /** @var \phpbb\template\template $template */
        $template = $phpbb_container->get('template');

        $language->add_lang('acp_profilecover', 'mundophpbb/profilecover');

        add_form_key('mundophpbb_profilecover_acp');

        $this->page_title = 'ACP_PROFILECOVER_SETTINGS';

        if ($request->is_set_post('submit'))
        {
            if (!check_form_key('mundophpbb_profilecover_acp'))
            {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $max_width = max(0, (int) $request->variable('profilecover_max_width', 1600));
            $max_height = max(0, (int) $request->variable('profilecover_max_height', 500));
            $max_filesize = max(0, (int) $request->variable('profilecover_max_filesize', 512));
            $header_height = max(0, (int) $request->variable('profilecover_header_height', 300));

            $errors = [];

            if ($max_width < 1 || $max_height < 1 || $max_filesize < 1 || $header_height < 1)
            {
                $errors[] = $language->lang('ACP_PROFILECOVER_INVALID_POSITIVE');
            }

            if ($header_height > $max_height)
            {
                $errors[] = $language->lang('ACP_PROFILECOVER_HEADER_HEIGHT_TOO_LARGE');
            }

            if (empty($errors))
            {
                $config->set('profilecover_max_width', $max_width);
                $config->set('profilecover_max_height', $max_height);
                $config->set('profilecover_max_filesize', $max_filesize);
                $config->set('profilecover_header_height', $header_height);

                trigger_error($language->lang('ACP_PROFILECOVER_SAVED') . adm_back_link($this->u_action));
            }

            $template->assign_vars([
                'S_ERROR' => true,
                'ERROR_MSG' => implode('<br>', $errors),
            ]);
        }

        $template->assign_vars([
            'U_ACTION' => $this->u_action,
            'PROFILECOVER_MAX_WIDTH' => isset($config['profilecover_max_width']) ? (int) $config['profilecover_max_width'] : 1600,
            'PROFILECOVER_MAX_HEIGHT' => isset($config['profilecover_max_height']) ? (int) $config['profilecover_max_height'] : 500,
            'PROFILECOVER_MAX_FILESIZE' => isset($config['profilecover_max_filesize']) ? (int) $config['profilecover_max_filesize'] : 512,
            'PROFILECOVER_HEADER_HEIGHT' => isset($config['profilecover_header_height']) ? (int) $config['profilecover_header_height'] : 300,
        ]);
    }
}
