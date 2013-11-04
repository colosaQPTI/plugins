<?php
$IP = "172.17.20.29";
$workSpace = "CheqLivreApp";
$skin="neoclassic";
$language="fr";
$service_url = "http://".$IP."/sys".$workSpace."/".$language."/".$skin."/convergenceList/services/api.php/forgotpasswdpm";
$curl = curl_init($service_url);
$curl_post_data = array(
        'username' => 'admin',
        'currentpasswd' => 'sample',
        'newpasswd' => 'admin'        
);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
$curl_response = curl_exec($curl);
var_export($curl_response);
curl_close($curl);