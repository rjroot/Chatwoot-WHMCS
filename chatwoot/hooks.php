<?php

/***************************************************************************
// *                                                                       *
// * Chatwoot WHMCS Addon (v1.2.1).                                        *
// * This addon modules enables you integrate Chatwoot with your WHMCS     *
//   and leverage its powerful features.                                   *
// * Tested on WHMCS Version: 7.9.2 (7.9.2-release.1).                     *
// * For assistance on how to use and setup Chatwoot, visit                *
//   https://www.chatwoot.com/docs/channels/website                        *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Contributed by: WevrLabs Hosting                                      *
// * Email: hello@wevrlabs.net                                             *
// * Website: https://wevrlabs.net                                         *
// *                                                                       *
// *************************************************************************/

if(!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function hook_chatwoot_footer_output($vars) {
    
    $chatwoot_jscode = Capsule::table('tbladdonmodules')->where('module', 'chatwoot')->where('setting', 'chatwoot_jscode')->value('value');
    $chatwoot_position = Capsule::table('tbladdonmodules')->where('module', 'chatwoot')->where('setting', 'chatwoot_position')->value('value');
    $chatwoot_setlabel = Capsule::table('tbladdonmodules')->where('module', 'chatwoot')->where('setting', 'chatwoot_setlabel')->value('value');
    $chatwoot_setlabelloggedin = Capsule::table('tbladdonmodules')->where('module', 'chatwoot')->where('setting', 'chatwoot_setlabelloggedin')->value('value');
    $isenabled =  Capsule::table('tbladdonmodules')->select('value')-> WHERE('module', '=' , 'chatwoot')->WHERE('setting' , '=', 'chatwoot_enable')->WHERE('value' , 'on')->count();   
	
	// Disable or Enable Chatwoot
	if (empty($isenabled)) {
        return;
    }
    
    if(!$chatwoot_jscode) {
        return;
    }
	
    
    // Fetch labels
    $client = Menu::context('client');
    if (!is_null($client)){
        $chatwoot_label = $chatwoot_setlabelloggedin;
        } else {
        $chatwoot_label = $chatwoot_setlabel;
    }

    // Get client ID
    if ($vars['clientsdetails']['id']) {
        $varsID = $vars['clientsdetails']['id'];
    }
	if (!is_null($client)){
			$clientid = hash_hmac("sha256", $varsID, "S0m3r@nd0m5tring");
	} else {
        	$clientid = uniqid('v-', true);
    }

    // Get client email
    if ($vars['clientsdetails']['email']) {
        $clientemail = $vars['clientsdetails']['email'];
    }

    // Get First and Last name
    if ($vars['clientsdetails']['firstname']) {
        $clientname = $vars['clientsdetails']['firstname'] . " " . $vars['clientsdetails']['lastname'];
    }

    // Fetch client avatar if any
    $rating = (isset($params['rating']) ? $params['rating'] : 'G');
    $default = (isset($params['default']) ? $params['default'] : 'mp');
    $size = (isset($params['size']) ? $params['size'] : '150'); 
    $gravatarurl = "https://www.gravatar.com/avatar/".md5($clientemail) . "?r=".$rating . "&d=".$default . "&s=".$size; 

    
    if (!is_null($client)){
		$chatwoot_output = "$chatwoot_jscode
			<script>
				window.addEventListener('chatwoot:ready', function () {
					window.\$chatwoot.setUser('$clientid', {
						email: '$clientemail',
						name: '$clientname',
						avatar_url: '$gravatarurl',
					});

					window.\$chatwoot.setLabel('$chatwoot_label')
					window.\$chatwoot.removeLabel('$chatwoot_setlabel')

					window.chatwootSettings = {
						position: '$chatwoot_position',
						locale: '$chatwoot_lang',
					}
				});
			</script>
			";
		} 
		else {
		$chatwoot_output = "$chatwoot_jscode
			<script>
				window.addEventListener('chatwoot:ready', function () {
					window.\$chatwoot.setLabel('$chatwoot_label')

					window.chatwootSettings = {
						position: '$chatwoot_position',
						locale: '$chatwoot_lang',
					};
				});
			</script>
			";
		}
     
	
	// Now print JS code 
	echo $chatwoot_output;
}


function hook_chatwoot_logout_footer_output($vars) {
    $chatwoot_logoutJS = "<script>
                            document.addEventListener('readystatechange', event => {
                                window.\$chatwoot.reset()
                            });
                          </script>
                          ";
     echo $chatwoot_logoutJS;
}   

add_hook('ClientAreaFooterOutput', 1, 'hook_chatwoot_footer_output');
add_hook('ClientLogout', 1, 'hook_chatwoot_logout_footer_output');
