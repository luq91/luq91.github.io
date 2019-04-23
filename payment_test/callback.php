<?php

###############################
#		Configuration
###############################

	$PIN = "OIsn5dRYKNZShl5FcWH7ugV7BlK565ys";	//16-32 characters, found in Settings -> Notifications
	$file = "callback.txt";	//save to this file

#	Choose writing method; keep only last request or store everything
	$write = "w+";		//override
//	$write = "a";		//accumulate

#	Disable or enable "Callback verification" section
//	$verify = 0;		//just save
	$verify = 1;		//verify data and save

#	Choose IP verification method
	$IPcheck = $_SERVER['REMOTE_ADDR'];						//the good one
//	$IPcheck = $_SERVER['HTTP_X_FORWARDED_FOR'];		//the mediocre one, when behind proxy

###############################
#		Callback verification
###############################

if($verify)
{

	if($_SERVER['REQUEST_METHOD'] != 'POST') //URLC always uses POST
		die($_SERVER['REQUEST_METHOD']." is incorrect request method");

	if($IPcheck != '195.150.9.37') //IP for URLC is always 195.150.9.37
		die("Unexpected IP: ".$IPcheck);

	if(strlen($_POST['signature']) != '64') //signature always has 64 characters
		die("Invalid POST content, API version is not set to dev?");

	$sign=
	$PIN.
	$_POST['id'].
	$_POST['operation_number'].
	$_POST['operation_type'].
	$_POST['operation_status'].
	$_POST['operation_amount'].
	$_POST['operation_currency'].
	$_POST['operation_withdrawal_amount'].
	$_POST['operation_commission_amount'].
	$_POST['is_completed'].
	$_POST['operation_original_amount'].
	$_POST['operation_original_currency'].
	$_POST['operation_datetime'].
	$_POST['operation_related_number'].
	$_POST['control'].
	$_POST['description'].
	$_POST['email'].
	$_POST['p_info'].
	$_POST['p_email'].
	$_POST['credit_card_issuer_identification_number'].
	$_POST['credit_card_masked_number'].
	$_POST['credit_card_brand_codename'].
	$_POST['credit_card_brand_code'].
	$_POST['credit_card_id'].
	$_POST['channel'].
	$_POST['channel_country'].
	$_POST['geoip_country'];
	$_POST['payer_bank_account_name'].
	$_POST['payer_bank_account'].
	$_POST['payer_transfer_title'];
	$signature = hash('sha256', $sign);

	if($signature != $_POST['signature']) //compare POST signature with calculated one
		die("Signature mismatch! Check PIN");

}

###############################
#		Save to file and return OK
###############################

#	Prepare content
if($write == "a")
	$data .= "\n\n\n";
$data .= "----- ".date("r")." ----- \n\n";
if(!$verify)
	{
	$data .= "Request method: ".$_SERVER['REQUEST_METHOD']."\n";
	$data .= "Remote IP: ".$_SERVER['REMOTE_ADDR']."\n";
	$data .= "Forwarded IP: ".$_SERVER['HTTP_X_FORWARDED_FOR']."\n\n";
	}
$data .= "Request headers: \n\n".print_r(getallheaders(), true)."\n";
$data .= "POST array content: \n\n".print_r($_REQUEST, true);

#	Ceate file if necessary, open it and save
	if(fopen("$file", "$write") == FALSE)
		fopen("$file", "x+") or die("File ". $file. " does not exist and cannot create it! No permission to write in this directory?");

	else
		{
		$foo = fopen("$file", "$write") or die("Cannot open file: '". $file. "' to write! Read-only?");
		flock($foo, 2);

		if(fwrite($foo, $data))
			echo "OK";
		else
			echo "Write error!";
		}

flock($foo, 3);
fclose($foo);

###############################

?>
