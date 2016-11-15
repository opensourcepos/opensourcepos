<?php

function load_stats()
{
    $CI =& get_instance();
    $line = $CI->lang->line('common_you_are_using_ospos');

    if(count($CI->session->userdata('session_sha1')) == 0)
    {
        $footer_tags = file_get_contents(APPPATH . 'views/partial/footer.php');
        $d = preg_replace('/\$Id:\s.*?\s\$/', '$Id$', $footer_tags);
        $session_sha1 = sha1("blob " .strlen( $d ). "\0" . $d);
        $CI->session->set_userdata('session_sha1', substr($session_sha1, 0, 7));

        preg_match('/\$Id:\s(.*?)\s\$/', $footer_tags, $matches);
        $needle = 'Open Source Point Of Sale';

        if(!stristr($line, $needle) || $session_sha1 != $matches[1])
        {
            $CI->load->library('tracking_lib');

            $roguer = $CI->Appconfig->get('company') . ' | ' .  $CI->Appconfig->get('address') . ' | ' . $CI->Appconfig->get('email') . ' | ' . $CI->Appconfig->get('website') . ' | ' . $CI->config->item('base_url');
            $CI->tracking_lib->track_page('rogue/roguer', 'roguer', $roguer);
            $CI->tracking_lib->track_page('rogue/footer', 'rogue footer', $footer_tags);

			$login_footer = '';

			if($handle = @fopen(APPPATH . 'views/login.php', 'r'))
			{
				while(!feof($handle))
				{
					$buffer = fgets($handle);
					if(strpos($buffer, $needle) !== FALSE)
					{
						$login_footer = '';
					}
					elseif(strpos($buffer, 'form_close') !== FALSE)
					{
						$login_footer = 'Footer: ';
					}
					elseif($login_footer != '')
					{
						$login_footer .= $buffer;
					}
				}

				fclose($handle);
			}

            if($login_footer != '')
            {
                $CI->tracking_lib->track_page('rogue/login', 'rogue login', $login_footer);
            }
        }
    }
}

?>