<?php
if ( ! class_exists( 'WP_Site_Health' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
}

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

function curl_get_contents($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

	$return = curl_exec($ch);
    curl_close($ch);
	return $return;
}

function dia_hol_set_rest_api($endpoint, $callback)
{
    register_rest_route(
        'diahol/v1',
        $endpoint,
        array(
            array(
                'methods' => 'GET',
                'callback' => $callback,
                'permission_callback' => function (WP_REST_Request $request) {
                    $https = $_SERVER['HTTPS'];
                    if ($https == 'off' || $https == null) {
                        return false;
                    }
                    $header = $request->get_header('Authentication');
                    $code = str_replace('BASIC ', '', $header);
                    $data = explode('|', $code);
                    $username = $data[0];
                    $password = $data[1];

                    $user = get_user_by('login', $username);
                    if (!$user) {
                        $user = get_user_by('email', $username);
                    }

                    if ($user && wp_check_password($password, $user->data->user_pass, $user->ID)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            ), array(
                'methods' => 'POST',
                'callback' => $callback,
                'permission_callback' => function (WP_REST_Request $request) {
                    $https = $_SERVER['HTTPS'];
                    if ($https == 'off' || $https == null) {
                        return false;
                    }
                    $header = $request->get_header('Authentication');
                    $code = str_replace('BASIC ', '', $header);
                    $data = explode('|', $code);
                    $username = $data[0];
                    $password = $data[1];

                    $user = get_user_by('login', $username);
                    if (!$user) {
                        $user = get_user_by('email', $username);
                    }

                    if ($user && wp_check_password($password, $user->data->user_pass, $user->ID)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            )
        )
    );
}

add_shortcode('loving_angel_application', 'loving_angel_application_2022');
function loving_angel_application_2022() {
    
    $username = 'DIAMOND PainBuster Website';
    $partner_code = 'MY5H0398';

    $copy_link = 'https://diamond.holdings/diamond-love-ambassador-application/';
	
    $whatsapp_link = "https://api.whatsapp.com/send?text=Congratulations! You have been invited to become a DIAMOND Love Ambassador! Please click on the link below to complete your application: " . str_replace('&', '%26', str_replace('=', '%3D', $copy_link));

    $html = '<script>
            function copyLink() {
                const temp = jQuery(\'<input id="text-to-copy" style="position: absolute">\');
                jQuery("body").append(temp);
                temp.val("' . $copy_link . '").select();
                document.execCommand("copy");
                temp.remove();
                alert("Link copied to clipboard.");
            }
        </script>';

    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 0px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Love Ambassador Application</div><div style="text-align: left; font-size: 14px; margin: 0px 0px 30px 0px;"><a href="#" onclick="copyLink()" style="color: #ac1a2f;"><u>Copy this link</u></a> <span style="color: black !important;">or</span> <a href="'.$whatsapp_link.'" target="_blank" style="color: #ac1a2f;"><u>Share to WhatsApp</u></a></div>';
    
	$html .= "[gravityform id=2 title=false description=false field_values='refname=".$username."&refcode=".$partner_code."']";

    return do_shortcode($html);
}

add_action('gform_after_submission_2', 'insert_angel');
function insert_angel($entry) {
    global $wpdb;

    $name = $entry[1];
    $nric = $entry[2];
    $email = $entry[3];
    $contact = $entry[4];
    $addr1 = $entry[5];
    $addr2 = $entry[6];
    $city = $entry[7];
    $state = $entry[8];
    $postcode = $entry[9];
    $partner_code = $entry[15];
    $sponsor = $entry[13];

    $insert = $wpdb->query("INSERT INTO `ct_loving_angel`(`Name`, `NRIC`, `Email`, `Contact`, `Address_1`, `Address_2`, `City`, `State`, `Postcode`, `Partner_Code`, `Referral_Code`, `Status`, `Created_Date`) VALUES ('{$name}','{$nric}','{$email}','{$contact}','{$addr1}','{$addr2}','{$city}','{$state}','{$postcode}','{$partner_code}','{$sponsor}','Active',NOW())");

    $exists = email_exists( $email );
	
	if ($exists) {
        $user = get_user_by( 'email', $email );

		if (!get_user_meta($user->ID, 'dAngel_Code', true)) {
			add_user_meta($user->ID, 'dAngel_Code', $partner_code);
		}

		$user->add_role( 'diamond_angel' );

	} else {
		$userdata = array(
			'user_login' => $email,
			'user_email' => $email,
			'user_pass'  => $contact,
			'first_name' => $name,
			'role' => 'diamond_angel'
		);

		$user_id = wp_insert_user( $userdata );

		add_user_meta($user_id, 'dAngel_Code', $partner_code);
	}
}

add_action('gform_after_submission_4', 'test_sync');
function test_sync($entry) {
	global $wpdb;

	$test = $entry["1"];
	
	//$request = 'http://210.187.100.195/ArisstoMY/OnlineForm?sp_name=sp_test_sync&param_count=1&param1='.$test;
    $request = 'http://210.187.100.195/WebServices_UAT/GetData?query=200&param_count=1&param1=PBtest1';
	//$request = str_replace( ' ', '%20', $request );
    echo $request;

	//curl_get_contents($request);
	$wpdb->query("INSERT INTO `ct_integration` (`form_id`,`unique_no`,`call_Value`) VALUES (".$entry["form_id"].",'".$test."','".$request."')");

}

add_shortcode( 'voucher_list', 'voucher_list' );
function voucher_list() {
	global $wpdb;
	
	$user = wp_get_current_user();
	$email = $user->user_email;
	
	$url = "http://210.187.100.195/WebServices/GetData?query=86&param_count=1&param1=tbk006009@gmail.com";
	//$url = "https://catfact.ninja/fact";
	$ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
    curl_close($ch);
	
	$decode = json_decode($data, true);
	
	$table = '';

	print_r ( $decode );
	
	echo '<h2 style="font-weight: bold;color:#ab162a;">Voucher Listing</h2>';

	//echo $data['fact'];
	//echo $data['length'];
	
	$table = '<div class="table-1">
	<table width="100%">
	<thead>
	<tr>
	<td style="background-color: #ab162b; color:white" align="center">No</td>
	<td style="background-color: #ab162b; color:white" align="center">Partner Code</td>
	<td style="background-color: #ab162b; color:white" align="center">Ref No</td>
	<td style="background-color: #ab162b; color:white" align="center">Remark</td>
	<td style="background-color: #ab162b; color:white" align="center">Voucher No</td>
	<td style="background-color: #ab162b; color:white" align="center">Voucher Amount</td>
	</tr>
	</thead>
	<tbody>';
	
	/*foreach( $decode as $voc ) {
		$table .= '<tr>
		<td align="center">'.$voc->fact.'</td>
		<td align="center">'.$voc->PartnerCode.'</td>
		<td align="center">'.$voc->ReferenceNo.'</td>
		<td align="center">'.$voc->Remarks.'</td>
		<td align="center">'.$voc->VoucherNo.'</td>
		<td align="center">'.$voc->VoucherAmount.'</td>
		</tr>';
	}*/
	
	$table .= '</tbody></table></div><br>';
	echo $table;
}

add_action('rest_api_init', dia_hol_set_rest_api('gztest', 'gztest'));
function gztest()
{
    // echo dirname(dirname(dirname(__FILE__)));
    var_dump($_POST);
    echo "\n\n";
    $data = stripslashes($_POST['data']);
    var_dump(is_string($data));
    var_dump($data);
    echo "\n\n";
    $payload = json_decode($data, true);
    var_dump(is_array($payload));
    var_dump(is_string($payload));
    var_dump($payload);
    $email = $payload['Email'];
    var_dump($email);
    
}

add_action('rest_api_init', dia_hol_set_rest_api('dabemail', 'arissto_angel_boss_email_2022'));
function arissto_angel_boss_email_2022()
{
    global $wpdb;
    
    define('MAILGUN_URL', 'https://api.mailgun.net/v3/diamond.holdings');
    define('MAILGUN_KEY', 'key-d8e3498eaf621b508540a795f7cd19da');
    
    
    
    if (!empty($_POST)) {
        $data = stripslashes($_POST['data']);
        $payload = json_decode($data, true);
        $email = $payload['Email'];
        
        $template = file_get_contents(dirname(dirname(dirname(__FILE__)))  . '/email/diamond_love_ambassador.html');
        
        $param['username'] = $payload['Email'];
        $param['password'] = $payload['Contact'];
    
        foreach ($param as $k => $v) {
            $template = str_replace('{'.$k.'}', $v, $template);
        }
        
        $mailfromname = 'DIAMOND';
        $mailfrom = 'donotreply@diamond.holdings';
        $to = $email;
        $subject = 'Your DIAMOND Angel Boss Login Details';
    
        $html = $template;
    
        $array_data = array(
            'from' => $mailfromname . '<' . $mailfrom . '>',
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            
        );
        $session = curl_init(MAILGUN_URL . '/messages');
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, 'api:' . MAILGUN_KEY);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($session);
        curl_close($session);
        $email_result = json_decode($response, true);
        
        $query = "INSERT INTO `ct_loving_angel_email_arissto` (`recipient_email`, `payload`,`email_result`, `date_created`) VALUES ('$email', '".$data."', '".$response."', NOW())";
        $result = $wpdb->query($query);
    }
    
    return (isset($result) && $result !== false);
}

add_action('rest_api_init', dia_hol_set_rest_api('requestemail', 'diamond_holdings_send_email'));
function diamond_holdings_send_email() {
    /*
    * Url: https://diamond.holdings/wp-json/diahol/v1/requestemail
    *
    * Curl method: POST only
    *
    * Required parameters:
    * $_POST = array(
    *   "from" => "DIAMOND", // Default sender name
    *   "fromname" => "donotreply@diamond.holdings", // Default sender email
    *   "to" => "", // Required
    *   "cc" => "", // Optional
    *   "bcc" => "", // Optional
    *   "subject" => "", // Required
    *   "html" => "", // Required
    *   "reference" => "", // Optional. Describe what the email is for, for future reference or checking
    * );
    *
    */
    global $wpdb;
    
    define('MAILGUN_URL', 'https://api.mailgun.net/v3/diamond.holdings');
    define('MAILGUN_KEY', 'key-d8e3498eaf621b508540a795f7cd19da');
    
    if (!empty($_POST) && isset($_POST['to']) && !empty($_POST['to']) && isset($_POST['subject']) && !empty($_POST['subject']) && isset($_POST['html']) && !empty($_POST['html'])) {
        
        $mailfromname = (isset($_POST['fromname']) && !empty($_POST['fromname'])) ? $_POST['fromname'] : 'DIAMOND';
        $mailfrom = (isset($_POST['from']) && !empty($_POST['from'])) ? $_POST['from'] : 'donotreply@diamond.holdings';
        $to = $_POST['to'];
        $subject = $_POST['subject'];
        $html = $_POST['html'];
        $reference = (isset($_POST['reference']) && !empty($_POST['reference'])) ? $_POST['reference'] : "";
    
        $array_data = array(
            'from' => $mailfromname . '<' . $mailfrom . '>',
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
        );
        
        if (isset($_POST['cc']) && !empty($_POST['cc'])) {
            $array_data['cc'] = $_POST['cc'];
        }
        
        if (isset($_POST['bcc']) && !empty($_POST['bcc'])) {
            $array_data['bcc'] = $_POST['bcc'];
        }
        
        $session = curl_init(MAILGUN_URL . '/messages');
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, 'api:' . MAILGUN_KEY);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($session);
        curl_close($session);
        $email_result = json_decode($response, true);
        
        $query = "INSERT INTO `ct_email_log`(`recipient`, `payload`, `reference`, `date_created`, `mailgun_id`, `date_updated`) VALUES ('".$to."', '".json_encode($array_data)."', '".$reference."', NOW(), '".(($response)?$response:"-1")."', NOW())";
        $result = $wpdb->query($query);
    }
    
    return (isset($result) && $result !== false);
}

add_filter( 'gform_pre_render_5', 'workshop_form' );
function workshop_form($form) {
?>
	<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery("#input_5_27").attr("readonly", "readonly");
        jQuery("#input_5_39").attr("readonly", "readonly");
        jQuery("#input_5_40").attr("readonly", "readonly");
    
		jQuery("#input_5_27").css({"background-color": "#ececec"});
        jQuery("#input_5_39").css({"background-color": "#ececec"});
        jQuery("#input_5_40").css({"background-color": "#ececec"});
	});
	</script>
<?php
	return $form;
}

add_action( 'gform_pre_submission_5', 'verify_date' );
function verify_date($form) {
	global $wpdb;

	$date = rgpost( 'input_27' );
	$today = date('Y-m-d');
    $date2 = str_replace("/","-",$date);

	if ((strtotime($date2) > strtotime($today)) || (strtotime($date2) < strtotime($today))) {
		$expired = 'Yes';
	} else {
		$expired = 'No';
	}
	$_POST['input_38'] = $expired;
}

add_shortcode('workshop', 'workshop');
function workshop ($atts) {
    global $wpdb;

	$phone = $_REQUEST['phone'];
    $status = $_REQUEST['status'];

    $details = $wpdb->get_results("Select Name,Email,Contact,Date,Referral_Code from `ct_pre_register` Where Contact = '".$phone."'");
	foreach ( $details as $val ) {
		$name = $val->Name;
		$email = $val->Email;
		$contact = $val->Contact;
		$date = $val->Date;
		$refcode = $val->Referral_Code;
	}

    $sc_form = '[gravityform id=5 title=false description=false ajax=false field_values="date='.$date.'&name='.$name.'&email='.$email.'&phone='.$contact.'&refcode='.$refcode.'&status='.$status.'"]';

	return do_shortcode($sc_form);
}

/*add_filter( 'gform_pre_render_6', 'pre_workshop_form' );
function pre_workshop_form($form) {
?>
	<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery("#input_6_136").attr("readonly", "readonly");
        jQuery("#input_6_137").attr("readonly", "readonly");
        jQuery("#input_6_140").attr("readonly", "readonly");
    
		jQuery("#input_6_136").css({"background-color": "#ececec"});
        jQuery("#input_6_137").css({"background-color": "#ececec"});
        jQuery("#input_6_140").css({"background-color": "#ececec"});
	});
	</script>
<?php
	return $form;
}*/

add_action('gform_after_submission_6', 'pre_workshop_ins');
function pre_workshop_ins($entry) {
	global $wpdb;

	$name = $entry["7"];
    $email = $entry["13"];
    $phone = $entry["12"];
    $date = $entry["141"];
    $code = $entry["16"];
    $refphone = $entry["136"];
    $refcode = $entry["140"];
	
	$wpdb->query("INSERT INTO `ct_pre_register` (`Name`, `Email`, `Contact`, `Date`, `Partner_Code`, `Referral_Phone`, `Referral_Code`) VALUES ('".$name."','".$email."','".$phone."','".$date."','".$code."','".$refphone."','".$refcode."')");
}

add_action( 'gform_pre_submission_7', 'verify_search' );
function verify_search($form) {
	global $wpdb;

	$phone = rgpost( 'input_1' );
	
    $details = $wpdb->get_results("Select Contact,Date from `ct_pre_register` Where Contact = '".$phone."'");
	foreach ( $details as $val ) {
		$contact = $val->Contact;
		$date = $val->Date;
	}

	if ($contact) {
		$verify = 'Yes';
	} else {
		$verify = 'No';
	}
	$_POST['input_2'] = $verify;
    $_POST['input_3'] = $date;
}

add_shortcode( 'test_api', 'test_api' );
function test_api() {
    //$request = wp_remote_get( 'http://api.github.com/users/wordpress' );
	//var_dump($request);
	//$url = "http://system.nepdiamond.com/nepws/htmlcall.asmx/OnlineAffiliateApprove?Environment=PRD&memberno=&distno=89898988&fullname=test&nric=&email=&contactno=&referral=&addr1=&addr2=&city=&state=&postcode=";
    $url = "http://210.187.100.195/WebServices_UAT/GetData?query=200&param_count=1&param1=PBtest1";
	
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
    curl_close($ch);
	
	//$decode = json_decode($data, true);

	//print_r ( $decode );
    echo $data;
}

add_shortcode('kf_test', 'kf_test');
function kf_test ($atts) {
	$url = "http://dev.nepdiamond.com/WebServices_UAT/GetData?query=200&param_count=1&param1=PBtest1";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	echo $data;
	curl_close($ch);
}

add_shortcode( 'gz_test', 'gz_test' );
function gz_test() {
    global $wpdb;
    
    $filepath = dirname(__FILE__)."/Diamond Workshop Pre Registration - Sheet1.csv";
    
    echo "<pre>";
    echo "filepath:";
    print_r($filepath);
    echo "<br>";
    echo "file_exist:";
    var_dump(file_exists($filepath));
    echo "<br>";
    
    $file = fopen($filepath,"r");
    
    $data = array();
    while(! feof($file)) {
      $data[] = fgetcsv($file);
    }
    unset($data[0]);
    // print_r($data[1]);
    fclose($file);
    
    $match_count = 0;
    $nomatch_count = 0;
    
    echo "<br><br>Starting loop...<br>";
    foreach ($data as $k => $v) {
        $name = $v[1];
        $email = $v[2]; 
        $contact = $v[3];
        $contact = str_replace(' ', '', $contact);
        $contact = str_replace('-', '', $contact);
        if ($contact[0] != '0') $contact = "0".$contact;
        
        $referral_contact = $v[4];
        $referral_contact = str_replace(' ', '', $referral_contact);
        $referral_contact = str_replace('-', '', $referral_contact);
        if ($referral_contact[0] != '0') $referral_contact = "0".$referral_contact;
        
        $search = $wpdb->get_row("SELECT * FROM `ct_pre_register` WHERE `Name` LIKE '%$name%' OR `Email` = '$email' OR `Contact` = '$contact'", ARRAY_A);
        if ($search) {
            // echo "Match<br>";
            // $match_count++;
        } else {
            echo "#".$k." No Match Found<br>";
            print_r($v[1]);
            echo "<br>";
            $nomatch_count++;
            // $wpdb->query("INSERT INTO `ct_pre_register` (`Name`, `Email`, `Contact`, `Date`, `Partner_Code`, `Referral_Phone`, `Referral_Code`, `Created_Date`) VALUE ('$name', '$email', '$contact', '" . $v[7] . "', '" . $v[5] . "', '$referral_contact', '" . $v[6] . "', NOW())");
        }
    }
    echo "<br><br>";
    echo "Match count: ".$match_count;
    echo "No match count: ".$nomatch_count;
    // $result = $wpdb->get_results("SELECT * FROM `ct_pre_register`", ARRAY_A);
    // echo "<br>Result:";
    // print_r($result[0]);
    
    echo "</pre>"; 
}

add_shortcode('create_event', 'do_create_event');
function do_create_event ($atts) {
	
	$sc_form = '<div style="background-color: #f26c24; padding: 8px; color: #ffffff; text-align: center; margin-bottom: 50px; font-size: 18px;">Create New Event</div><br>[gravityform id=14 title=false description=false]';
	return do_shortcode($sc_form);
	
}

add_action( 'gform_after_submission_14', 'submit_evt_form' );
function submit_evt_form($entry) {
	global $wpdb;
	$current_user = wp_get_current_user();
	$user = $current_user->user_login;
	$name = $entry["1"];
	$date = $entry["2"];

	$start = $entry["3"];
	$h_start = explode(":",$start);
	$m_start = explode(" ",$h_start[1]);
	
	$limit = $entry["6"];
	$location = $entry["7"];
	$online = $entry["8"];
	
	$s_hour = $h_start[0];
	if($m_start[1] == "pm" && $s_hour != 12) {
		$s_hour += 12;
	} else if($m_start[1] == "am" && $s_hour == 12) {
		$s_hour = 0;
	}
	
	$s_min = $m_start[0];
	$start_time = $s_hour.":".$s_min.":00";
	
	$end = $entry["4"];
	$h_end = explode(":",$end);
	$m_end = explode(" ",$h_end[1]);
	
	$e_hour = $h_end[0];
	if($m_end[1] == "pm" && $e_hour != 12) {
		$e_hour += 12;
	} else if($m_end[1] == "am" && $e_hour == 12) {
		$e_hour = 0;
	}
	
	$e_min = $m_end[0];
	$end_time = $e_hour.":".$e_min.":00";
	
	$code = $entry["5"];
	$url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chld=L|0&chl=".$code;
	$list = "https://diamond.holdings/attendance-list/?code=".$code;
	$update = "https://diamond.holdings/update-event-status/?code=".$code;
    $banner = $entry["11"];

    $sponsor_event = $entry["12"];

    if ($sponsor_event == "Yes") {
        $sponsor = get_user_meta($current_user->ID, 'Partner_Code', true);
    }

    $fnb = (isset($entry["13"]) && !empty($entry["13"]))?$entry["13"]:"";
    $vege = (isset($entry["14"]) && !empty($entry["14"]))?$entry["14"]:"";
	
	$wpdb -> query ("INSERT INTO `ct_event_list`(`Code`, `Name`, `Date`, `Start_Time`, `End_Time`, `QR_URL`, `List_URL`,`Update_URL`,`Banner_URL`,`Sponsor_Code`,`Created_By`,`Event_limit`,`Event_Total_Limit`,`Event_limit_after`,`Event_location`, `Event_online`, `FnB_Provided`, `Vege_Friendly`) VALUES ('".$code."','".$name."','".$date."','".$start_time."','".$end_time."','".$url."','".$list."','".$update."','".$banner."','".$sponsor."','".$user."','".$limit."','".$limit."','".$limit."','".$location."','".$online."','".$fnb."','".$vege."')");

}

add_shortcode('eventPreRegistration','eventPreRegistration');
function eventPreRegistration( $atts ){
    global $wpdb;
	$code = $_REQUEST['ref'];
	$leader = $_REQUEST['leader'];

    $banner = $wpdb->get_var("SELECT Banner_URL FROM ct_event_list WHERE Code = '$code'");
    $g_form = "";
    if ($banner) $g_form .= '<img alt="" class="img-responsive ls-is-cached lazyloaded" src="'.$banner.'"  width="100%">';
	
	$g_form .= "[gravityform id=15 title=false description=false field_values=\"eventcode=$code&leader=$leader\"]";
	return do_shortcode( $g_form );
}

add_action('gform_after_submission_15', 'event_preregister_15');
function event_preregister_15($entry) {
	global $wpdb;

	$name = $entry["7"];
	$email = $entry["13"];
	$phone = $entry["12"];
	$CAcode = $entry["16"];
	$eventcode = $entry["133"];
	$leader = $entry["135"];
	$referral_name = $entry["136"];
	
	$limit = $wpdb -> get_var("SELECT `Event_limit` FROM `ct_event_list` WHERE Code = '$eventcode'");
	
	$limit_deduct = $limit - 1;
	
	$wpdb->query("UPDATE ct_event_list SET Event_limit = '$limit_deduct' WHERE Code = '$eventcode'");

	$wpdb->query("INSERT INTO `ct_pre_event`(`Name`, `Email`, `Phone`, `Partner_Code`, `Event_Code`, `Leader`, `Referral_Name`) VALUES ('$name', '$email', '$phone', '$CAcode', '$eventcode', '$leader', '$referral_name')");
}

add_shortcode('event_applicants', 'event_applicants');
function event_applicants($atts) {
    global $wpdb;
    $code = $_REQUEST['code'];
    $date = $_REQUEST['date'];
    $leader = $_REQUEST['leader'];

    $banner = $wpdb->get_var("SELECT Banner_URL FROM ct_event_list WHERE Code = '$code'");
    $sc_form = "";
    if ($banner) $sc_form .= '<img alt="" class="img-responsive ls-is-cached lazyloaded" src="'.$banner.'"  width="100%">';

    if (!empty($code)) {
        $sc_form .= "[gravityform id=16 title=false description=false ajax=false field_values=\"code=$code&date=$date&leader=$leader\"]";
        return do_shortcode($sc_form);
    } else {
        return '<h3>Please get your event code</h3>';
    }
}

add_action('gform_after_submission_16', 'event_applicants_16');
function event_applicants_16($entry) {
    global $wpdb;

    $name = $entry["7"];
    $email = $entry["13"];
    $phone = $entry["12"];
    $CAcode = $entry["16"];
    $Eventcode = $entry["131"];
    $Eventdate = $entry["129"];
    $leader = $entry["132"];
    $referral_name = $entry["133"];

    $limit = $wpdb->get_var("SELECT `Event_limit_after` FROM `ct_event_list` WHERE Code = '$Eventcode'");

    $limit_deduct = $limit - 1;

    $wpdb->query("UPDATE ct_event_list SET Event_limit_after = '$limit_deduct' WHERE Code = '$Eventcode'");

    $wpdb->query("INSERT INTO `ct_post_event`(`Name`, `Email`, `Phone`, `Partner_Code`, `Event_Code`, `Event_Date`, `Leader`, `Referral_Name`) VALUES ('$name', '$email', '$phone', '$CAcode', '$Eventcode', '$Eventdate', '$leader', '$referral_name')");
}

add_shortcode('event_list', 'show_event');
function show_event ($atts) {
	$sc_form = '[wpdatatable id=1]';
	return do_shortcode($sc_form);
}

add_shortcode('pre_event_attendance', 'pre_event_attendance');
function pre_event_attendance ($atts) {
    $code = $_REQUEST['ref'];
    $leader = $_REQUEST['leader'];
    
	$sc_form = "[wpdatatable id=2 var1='$code' var2='$leader']";
	return do_shortcode($sc_form);
}

add_shortcode('post_event_attendance', 'post_event_attendance');
function post_event_attendance($atts) {
    $code = $_REQUEST['ref'];
    $leader = $_REQUEST['leader'];
    global $wpdb;

    $sc_form = "[wpdatatable id=3 var1='$code' var2='$leader']";
    return do_shortcode($sc_form);
}

add_shortcode('event_status', 'event_update');
function event_update ($atts) {
	global $wpdb;

	$code = $_REQUEST['code'];
	$current_user = wp_get_current_user();
	$user = $current_user->user_login;
	
	if(!empty($code)) {
		$result = $wpdb->get_var("SELECT status FROM ct_event_list WHERE Code ='".$code."'");
		if($result == 0) {
			$wpdb -> query("UPDATE ct_event_list SET Status = 1, Modified_Date = CURRENT_TIMESTAMP, Modified_By = '".$user."' WHERE Code = '".$code."'");
		} else {
			$wpdb -> query("UPDATE ct_event_list SET`Status` = 0, Modified_Date = CURRENT_TIMESTAMP, Modified_By = '".$user."' WHERE Code = '".$code."'");
		}
		return '<strong>Status Update Successful.</strong><br/><br/><strong><u><a href="https://diamond.holdings/event-list/">Return event list</a></u></strong>';
	} else {
		return "Invalid event!";
	}
}

add_shortcode('diamond_referral_application', 'diamond_referral_application');
function diamond_referral_application() {
    global $wpdb;

    $refcode = $_REQUEST['refcode'];

    $copy_link = esc_url(add_query_arg('refcode', $_REQUEST['refcode'], 'https://diamond.holdings/diamond-referral-registration/'));
    
    $whatsapp_link = "https://api.whatsapp.com/send?text=Thank you for joining DIAMOND Referral " . str_replace('&', '%26', str_replace('=', '%3D', $copy_link));

    $html = '<script>
            console.log("ipartner_code: '.$ip_code.'");
                function copyLink() {
                    const temp = jQuery(\'<input id="text-to-copy" style="position: absolute">\');
                    jQuery("body").append(temp);
                    temp.val("' . $copy_link . '").select();
                    document.execCommand("copy");
                    temp.remove();
                    alert("Link copied to clipboard.");
                }
            </script>';

    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 0px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Referral Registration Form</div>
            <div style="text-align: left; font-size: 14px; margin: 0px 0px 30px 0px;"><a href="#" onclick="copyLink()" style="color: #ac1a2f;"><u>Copy this link</u></a> <span style="color: black !important;">or</span> <a href="' . $whatsapp_link . '" target="_blank" style="color: #ac1a2f;"><u>Share to WhatsApp</u></a></div>';
    $html .= "[gravityform id=11 title=false description=false field_values='refcode=" . $refcode . "']";

    return do_shortcode($html);
}

add_action('gform_after_submission_11', 'referral_ins');
function referral_ins($entry) {
	global $wpdb;

	$name = $entry["7"];
    $email = $entry["143"];
    $phone = $entry["12"];
    $code = $entry["142"];
    $refcode = $entry["144"];
	
	$wpdb->query("INSERT INTO `ct_referral` (`Name`, `Email`, `Contact`, `Partner_Code`, `Referral_Code`) VALUES ('".$name."','".$email."','".$phone."','".$code."','".$refcode."')");
}

add_shortcode('diamond_attendance', 'diamond_attendance');
function diamond_attendance() {
    global $wpdb;

    $refcode = $_REQUEST['refcode'];
    $reference_no = "";
    
    if (!empty($refcode)) {
        $query = "SELECT * FROM ct_event_special WHERE Partner_Code = '" . $refcode . "' AND Active = 'Y'";
        $res = $wpdb->get_row($query, ARRAY_A);
        
        
        if ($res) {
            $number = $wpdb->get_row("SELECT * FROM ct_numbers WHERE Type = '" . $res['Type'] . "' AND Active = 'Y'", ARRAY_A);
            
            if ($number) {
                $padding = $number['Length'] - strlen($number['Prefix']) - strlen($number['Suffix']);
                $vc = $number['Prefix'] . str_pad($number['Number'], $padding, '0', STR_PAD_LEFT) . $number['Suffix'];
                
                $reference_no = '&reference_no='.$vc;
            }
        }
    }

    $copy_link = esc_url(add_query_arg('refcode', $_REQUEST['refcode'], 'https://diamond.holdings/diamond-party-attendance/'));
    
    $whatsapp_link = "https://api.whatsapp.com/send?text=Thank you for joining DIAMOND Referral " . str_replace('&', '%26', str_replace('=', '%3D', $copy_link));

    /*$html = '<script>
            console.log("ipartner_code: '.$ip_code.'");
                function copyLink() {
                    const temp = jQuery(\'<input id="text-to-copy" style="position: absolute">\');
                    jQuery("body").append(temp);
                    temp.val("' . $copy_link . '").select();
                    document.execCommand("copy");
                    temp.remove();
                    alert("Link copied to clipboard.");
                }
            </script>';

    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 0px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Party Attendance Form</div><div style="text-align: left; font-size: 14px; margin: 0px 0px 30px 0px;"><a href="#" onclick="copyLink()" style="color: #ac1a2f;"><u>Copy this link</u></a> <span style="color: black !important;">or</span> <a href="' . $whatsapp_link . '" target="_blank" style="color: #ac1a2f;"><u>Share to WhatsApp</u></a></div>';*/
    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 0px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Party Attendance Form</div>';
    
    $html .= "[gravityform id=12 title=false description=false field_values='refcode=" . $refcode . $reference_no . "']";

    return do_shortcode($html);
}

add_shortcode('diamond_party_thank_you', 'diamond_party_thank_you');
function diamond_party_thank_you() {
    global $wpdb;
    
    $ref_code = $_REQUEST['ref'];
    $email = $_REQUEST['email'];
    $contact = $_REQUEST['contact'];
    $reference_no = $_REQUEST['reference_no'];
    
    $html = '<h2 style="text-align: center;margin-top: 50px;">Successfully Attendance Take</h2>
    <p style="text-align: center;">Thank You and have a nice day.</p>';
    
    if (isset($reference_no) && !empty($reference_no)) {
        $query = "SELECT * FROM ct_event_special_log WHERE Reference_No = '" . $reference_no . "'";
        $check_avail = $wpdb->get_row($query, ARRAY_A);
        
        $query = "SELECT * FROM ct_event_special WHERE Partner_Code = '" . $ref_code . "' AND Active = 'Y'";
        $res = $wpdb->get_row($query, ARRAY_A);
        
        if ($check_avail) {
            $number = $wpdb->get_row("SELECT * FROM ct_numbers WHERE Type = '" . $res['Type'] . "' AND Active = 'Y'", ARRAY_A);
            $padding = $number['Length'] - strlen($number['Prefix']) - strlen($number['Suffix']);
            $reference_no = $number['Prefix'] . str_pad($number['Number'], $padding, '0', STR_PAD_LEFT) . $number['Suffix'];
            
        }
        
        $wpdb->query("INSERT INTO `ct_event_special_log`(`Partner_Code`, `Email`, `Contact`, `Reference_No`, `Created_Date`) VALUES ('" . $ref_code . "', '" . $email . "', '" . $contact . "', '" . $reference_no . "', NOW())");
        $wpdb->query("UPDATE ct_numbers SET number = (number + 1) WHERE Type = '" . $res['Type'] . "'");
        
        $html .= '<br><h4 style="text-align: center; margin-top:-20px;">Here is your Reference No.:' . $reference_no . '</h4>';
    }
    
    return do_shortcode($html);
}

add_shortcode('diamond_recruitment_thank_you', 'diamond_recruitment_thank_you');
function diamond_recruitment_thank_you() {
    global $wpdb;
    
    $ref_code = $_REQUEST['ref'];
    $email = $_REQUEST['email'];
    $contact = $_REQUEST['contact'];
    $type = $_REQUEST['type'];
    
    $html = '<h2 style="text-align: center;margin-top: 50px;">Successfully Attendance Take</h2>
    <p style="text-align: center;">Thank You and have a nice day.</p>';
    
    if (!empty($ref_code)) {
        $query = "SELECT * FROM ct_event_special WHERE Partner_Code = '" . $ref_code . "' AND Active = 'Y'";
        $res = $wpdb->get_row($query, ARRAY_A);
        
        if ($res) {
            $number = $wpdb->get_row("SELECT * FROM ct_numbers WHERE Type = '" . ((isset($type) && !empty($type)) ? $type : $res['Type']) . "' AND Active = 'Y'", ARRAY_A);
            
            if ($number) {
                $padding = $number['Length'] - strlen($number['Prefix']) - strlen($number['Suffix']);
                $vc = $number['Prefix'] . str_pad($number['Number'], $padding, '0', STR_PAD_LEFT) . $number['Suffix'];
                $html .= '<br><p style="text-align: center;">Here is your Reference No.:' . $vc . '</p>';
                
                $wpdb->query("INSERT INTO `ct_event_special_log`(`Partner_Code`, `Email`, `Contact`, `Reference_No`, `Created_Date`) VALUES ('" . $ref_code . "', '" . $email . "', '" . $contact . "', '" . $vc . "', NOW())");
                $wpdb->query("UPDATE ct_numbers SET number = (number + 1) WHERE Type = '" . ((isset($type) && !empty($type)) ? $type : $res['Type']) . "'");
            }
        }
    }
    
    return do_shortcode($html);
}

add_shortcode('diamond_recruitment', 'diamond_recruitment');
function diamond_recruitment() {
    global $wpdb;

    $refcode = $_REQUEST['refcode'];

    $copy_link = esc_url(add_query_arg('refcode', $_REQUEST['refcode'], 'https://diamond.holdings/diamond-recruitment-party-attendance/'));
    
    $whatsapp_link = "https://api.whatsapp.com/send?text=Thank you for joining DIAMOND Referral " . str_replace('&', '%26', str_replace('=', '%3D', $copy_link));

    /*$html = '<script>
            console.log("ipartner_code: '.$ip_code.'");
                function copyLink() {
                    const temp = jQuery(\'<input id="text-to-copy" style="position: absolute">\');
                    jQuery("body").append(temp);
                    temp.val("' . $copy_link . '").select();
                    document.execCommand("copy");
                    temp.remove();
                    alert("Link copied to clipboard.");
                }
            </script>';

    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 30px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Recruitment Party Attendance</div><div style="text-align: left; font-size: 14px; margin: 0px 0px 30px 0px;"><a href="#" onclick="copyLink()" style="color: #ac1a2f;"><u>Copy this link</u></a> <span style="color: black !important;">or</span> <a href="' . $whatsapp_link . '" target="_blank" style="color: #ac1a2f;"><u>Share to WhatsApp</u></a></div>';*/
    $html .= '<div style="background-color: #FF6A05; margin: 30px 0px 30px 0px; padding: 1em; color: white;text-align: center;font-size: 24px;font-weight: 400;">DIAMOND Recruitment Party Attendance</div>';
    
    $html .= "[gravityform id=9 title=false description=false field_values='refcode=" . $refcode . "']";

    return do_shortcode($html);
}

add_shortcode('event_special_partner_list', 'show_event_special_partner_list');
function show_event_special_partner_list() {
    global $wpdb;
    $html = "";
    $user = wp_get_current_user();
    
    if (isset($_REQUEST['action'])) {
        if ($_REQUEST['action'] == 'delete' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $args = array(
                "Active" => "N"
            );
            $where = array(
                "id" => $_REQUEST['id']
            );
            
            $res = $wpdb->update('ct_event_special', $args, $where);
            
            if ($res) {
                $html .= '<script>alert("Deleted successfully!");</script>';
            } else {
                $html .= '<script>alert("Failed to delete");</script>';
            }
        } 
    } 
    
    if (in_array("administrator", $user->roles)) {
        $html .= "[wpdatatable id=4]";
        $html .= "<br><button type=\"button\" onclick=\"window.location.href = https://diamond.holdings/add-event-special-partner/\">Add New</button>";
        
    } else {
        header("location: https://diamond.holdings/");
        die();
    }
    
    return do_shortcode($html);
}

add_shortcode('add_event_special', 'show_add_event_special');
function show_add_event_special() {
    $html = "";
    $user = wp_get_current_user();
    
    if (in_array("administrator", $user->roles)) {
        $html = "<h4>New Event Special Partner</h4>";
        $html .= "[gravityform id=19 title=false description=false]";
    } else {
        header("location: https://diamond.holdings/");
        die();
    }
    
    return do_shortcode($html);
}

add_filter( 'gform_pre_render_19', 'pre_render_form_19' );
function pre_render_form_19($form) {
    global $wpdb;
    $options = array();
    $values = array();
    
    $types = $wpdb->get_results("SELECT * FROM `ct_numbers` WHERE Active = 'Y'", ARRAY_A);
    
    if ($types) {
        foreach ($types as $k => $v) {
            $padding = $v['Length'] - strlen($v['Prefix']) - strlen($v['Suffix']);
            $option = $v['Prefix'] . str_pad("", $padding, 'X', STR_PAD_LEFT) . $v['Suffix'];
            
            $options[$k] = $v['Type'] . " - [" . $option . "]";
            $values[$k] = $v['Type'];
        }
    } else {
        $options = array("No reference code found");
        $values = array(""); 
    }
?>
<script type="text/javascript">
    var options = ['<?php echo implode("', '", $options); ?>'];
    var values = ['<?php echo implode("', '", $values); ?>'];

    jQuery (document).ready(function () {
        jQuery("#input_19_10").empty();
        jQuery.each(options, function(idx, elm) {
            jQuery("#input_19_10").append('<option value=\"' + values[idx] + '\">' + elm + '</option>');
        });
    });
</script>
<?php
    return $form;
}

add_action('gform_after_submission_19', 'update_form_19');
function update_form_19($entry) {
    global $wpdb;
    
    $partner_code = $entry['1'];
    $new = $entry['6'];
    $type = $entry['4'];
    $reference_code = $entry['10'];
    $prefix = $entry['7'];
    $suffix = $entry['8'];
    $length = $entry['9'];
    $remarks = $entry['3'];
    
    if (isset($new) && !empty($new)) {
        if ($new == 'Yes') {
            
            $args_number = array(
                "Type" => $type,
                "Prefix" => $prefix,
                "Number" => 1,
                "Suffix" => $suffix,
                "Length" => $length,
                "Active" => "Y",
                "Created_Date" => date("Y-m-d H:i:s")
            );
            
            $wpdb->insert('ct_numbers', $args_number);
            
            $args = array(
                "Partner_Code" => $partner_code, 
                "Type" => $type,
                "Remarks" => $remarks,
                "Active" => "Y",
                "Created_Date" => date('Y-m-d H:i:s')   
            );
            
            $wpdb->insert('ct_event_special', $args);
            
            
        } else {
            $args = array(
                "Partner_Code" => $partner_code, 
                "Type" => $reference_code,
                "Remarks" => $remarks,
                "Active" => "Y",
                "Created_Date" => date('Y-m-d H:i:s')   
            );
            
            $wpdb->insert('ct_event_special', $args);
        }
    }
}

add_action( 'gform_after_submission_20', 'add_event_location_form' );
function add_event_location_form($entry) {
    global $wpdb;
    
    $location = $entry['1'];
    $date = $entry['6'];
    $min_pax = $entry['4'];
    $max_pax = $entry['5'];
    $recruitment = $entry['7.1'];
    $customer = $entry['7.2'];
    
    $args = array(
        "Location" => $location, 
        "Date" => date('Y-m-d', strtotime($date)), 
        "Min_Pax" => $min_pax, 
        "Max_Pax" => $max_pax, 
        "Recruitment" => ($recruitment == "Y")?"Y":"N", 
        "Customer" => ($customer == "Y")?"Y":"N", 
        "Active" => "Y", 
        "Created_Date" => date('Y-m-d H:i:s')
    );
    
    $wpdb->insert('ct_event_location', $args); 
}

add_filter( 'gform_pre_render_9', 'pre_render_form_9' );
function pre_render_form_9($form) {
    global $wpdb;
    $options = array();
    
    $location = $wpdb->get_results("SELECT * FROM `ct_event_location` WHERE Active = 'Y' AND Recruitment = 'Y'", ARRAY_A);
    
    if ($location) {
        foreach ($location as $k => $v) {
            $options[$k] = date('d/m', strtotime($v['Date'])) . " " . $v['Location'];
        }
    
    } else {
        $options = array("No location found at the moment");
    }
?>
<script type="text/javascript">
    var options = ['<?php echo implode("', '", $options); ?>'];

    jQuery (document).ready(function () {
        jQuery("#input_9_146").empty();
        jQuery.each(options, function(idx, elm) {
            jQuery("#input_9_146").append('<option value=\"' + elm + '\">' + elm + '</option>');
        });
        jQuery("#input_9_146").append('<option value=\"others\">Others</option>');
    });
</script>
<?php
    return $form;
}

add_filter( 'gform_pre_render_12', 'pre_render_form_12' );
function pre_render_form_12($form) {
    global $wpdb;
    $options = array();
    
    $location = $wpdb->get_results("SELECT * FROM `ct_event_location` WHERE Active = 'Y' AND Customer = 'Y'", ARRAY_A);
    
    if ($location) {
        foreach ($location as $k => $v) {
            $options[$k] = date('d/m', strtotime($v['Date'])) . " " . $v['Location'];
        }
    
    } else {
        $options = array("No location found at the moment");
    }
?>
<script type="text/javascript">
    var options = ['<?php echo implode("', '", $options); ?>'];

    jQuery (document).ready(function () {
        jQuery("#input_12_145").empty();
        jQuery.each(options, function(idx, elm) {
            jQuery("#input_12_145").append('<option value=\"' + elm + '\">' + elm + '</option>');
        });
        jQuery("#input_12_145").append('<option value=\"others\">Others</option>');
    });
</script>
<?php
    return $form;
}

add_shortcode('event_location_list', 'event_location_list');
function event_location_list() {
    global $wpdb;
    
    $action = $_REQUEST['action'];
    $lid = $_REQUEST['id'];
    $msg = false;
    $alert = $_REQUEST['m'];
    
    if (isset($action) && !empty($action) && isset($lid) && !empty($lid)) {
        switch ($action) {
            case "recruitment_change":
                $res = $wpdb->get_var("SELECT Recruitment FROM ct_event_location WHERE Active = 'Y' AND id = ".$lid);
                if ($res) {
                    if ($res == 'Y') {
                        $args = array("Recruitment" => "N");
                        $where = array("id" => $lid);
                        $update = $wpdb->update('ct_event_location', $args, $where);
                    } else {
                        
                        $args = array("Recruitment" => "Y");
                        $where = array("id" => $lid);
                        $update = $wpdb->update('ct_event_location', $args, $where);
                    }
                    
                    $msg = ($update) ? "Updated successfully" : "Failed to update" ;
                }
                break;
            case "customer_change":
                $res = $wpdb->get_var("SELECT Customer FROM ct_event_location WHERE Active = 'Y' AND id = ".$lid);
                if ($res) {
                    if ($res == 'Y') {
                        $args = array("Customer" => "N");
                        $where = array("id" => $lid);
                        $update = $wpdb->update('ct_event_location', $args, $where);
                    } else {
                        
                        $args = array("Customer" => "Y");
                        $where = array("id" => $lid);
                        $update = $wpdb->update('ct_event_location', $args, $where);
                    }
                    
                    $msg = ($update) ? "Updated successfully" : "Failed to update" ;
                }
                break;
            case "del":
                $args = array("Active" => "N");
                $where = array("id" => $lid);
                $update = $wpdb->update('ct_event_location', $args, $where);
                $msg = ($update) ? "Updated successfully" : "Failed to update" ;
                break;
            default:
                break;
        }
        
        if ($msg) {
            ?>
            <script>window.location.href = "https://diamond.holdings/event-location-list/?m=<?php echo $msg; ?>"</script>
            <?php
        }
    }
    
    if ($alert) {
        ?>
        <script>alert('<?php echo $alert; ?>');</script>
        <?php
    }
    
    $html = "[wpdatatable id=5]";
    $html .= "<br> <button onclick=\"window.location.href='https://diamond.holdings/add-event-location-page'\">Add New</button>";
    return do_shortcode($html);
}

add_shortcode('diamond_event_rundown', 'diamond_event_rundown');
function diamond_event_rundown() {
    global $wpdb;
    
    $code = $_REQUEST['leader'];
    $location = $_REQUEST['loc'];

    $name = $wpdb->get_var("SELECT Name FROM ct_event_leader WHERE Code = '".$code."'");

    if ($location == "KL") {
        $html = "<img src='https://diamond.holdings/wp-content/uploads/2022/12/kl-kayingjie-2023.jpg'><br><br>";
    }
    if ($location == "Muar") {
        $html = "<img src='https://diamond.holdings/wp-content/uploads/2022/12/muar-kayingjie-2023.jpg'><br><br>";
    }

    $html .= "[gravityform id=21 title=false description=false field_values='leader=".$code."&loc=".$location."&leadername=".$name."']";

    return do_shortcode($html);
}

add_filter( 'gform_pre_render_21', 'event_form' );
function event_form($form) {
?>
	<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery("#input_21_140").attr("readonly", "readonly");
        jQuery("#input_21_148").attr("readonly", "readonly");
    
		jQuery("#input_21_140").css({"background-color": "#ececec"});
        jQuery("#input_21_148").css({"background-color": "#ececec"});

	});
	</script>
<?php
	return $form;
}

add_action( 'gform_pre_submission_21', 'verify_name_phone' );
function verify_name_phone($form) {
	global $wpdb;

	$name = rgpost( 'input_7' );
	$phone = rgpost( 'input_12' );
	
	$namephone = $name . $phone;

	$_POST['input_146'] = $namephone;
}

add_action('gform_after_submission_21', 'ins_event_rundown');
function ins_event_rundown($entry) {
	global $wpdb;

	$name = $entry["7"];
	$email = $entry["13"];
	$phone = $entry["12"];
	$partnercode = $entry["16"];
	$refphone = $entry["136"];
	$refcode = $entry["140"];
	$refname = $entry["148"];
	$location = $entry["144"];

	$wpdb->query("INSERT INTO `ct_event_rundown`(`FullName`, `Email`, `PhoneNo`, `Partner_Code`, `Location`, `Referral_Phone`, `Referral_Code`, `Referral_Name`) VALUES ('".$name."', '".$email."', '".$phone."', '".$partnercode."', '".$location."', '".$refphone."', '".$refcode."', '".$refname."')");
}

add_shortcode( 'event_rundown_list', 'event_rundown_list' );
function event_rundown_list() {
	$leader = $_REQUEST['leader'];
    $location = $_REQUEST['loc'];

	return do_shortcode( '[wpdatatable id=7 var1='.$leader.' var2='.$location.']' );
}