<?php

function FupdateAPPDATATYPO3($APP_UID,$new = 0){
    $_SESSION["PM_RUN_OUTSIDE_MAIN_APP"] = true;
    G::LoadClass("case");
    $caseInstance = new Cases ();
    $newFields = $caseInstance->loadCase ($APP_UID);
    $newFields['APP_DATA']['FLAGTYPO3'] = 'On';
    $newFields['APP_DATA']['FLAG_ACTIONTYPO3'] = 'actionCreateCase';

    if ($_REQUEST['redirect'])
        $newFields['APP_DATA']['FLAG_REDIRECT_PAGE'] = urldecode($_REQUEST['redirect']);
    
    if ($new == 1) {
        
        $newFields['APP_DATA']['NUM_DOSSIER'] = $newFields['APP_NUMBER'];
    }

    PMFSendVariables($APP_UID, $newFields['APP_DATA']);         
    $caseInstance->updateCase($APP_UID, $newFields);

}

function authentication($user, $pass) {
    global $RBAC;

    if (strpos($pass, 'md5:') === false) {
        $pass = 'md5:' . $pass;
    }

    $uid = $RBAC->VerifyLogin($user , $pass);

    if ($uid < 0) {
        throw new Exception('Wrong user or pass.');
    }

    return $uid;
}

function browser_detection($which_test, $test_excludes = '', $external_ua_string = '')
{
    G::script_time(); // set script timer to start timing

    static $a_full_assoc_data, $a_mobile_data, $a_moz_data, $a_webkit_data, $b_dom_browser, $b_repeat, $b_safe_browser, $browser_name, $browser_number, $browser_math_number, $browser_user_agent, $browser_working, $ie_version, $mobile_test, $moz_number, $moz_rv, $moz_rv_full, $moz_release_date, $moz_type, $os_number, $os_type, $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;

    // switch off the optimization for external ua string testing.
    if ( $external_ua_string ) {
        $b_repeat = false;
    }

    /*
    this makes the test only run once no matter how many times you call it since
    all the variables are filled on the first run through, it's only a matter of
    returning the the right ones
    */
    if ( !$b_repeat ) {
        //initialize all variables with default values to prevent error
        $a_browser_math_number = '';
        $a_full_assoc_data = '';
        $a_full_data = '';
        $a_mobile_data = '';
        $a_moz_data = '';
        $a_os_data = '';
        $a_unhandled_browser = '';
        $a_webkit_data = '';
        $b_dom_browser = false;
        $b_os_test = true;
        $b_mobile_test = true;
        $b_safe_browser = false;
        $b_success = false;// boolean for if browser found in main test
        $browser_math_number = '';
        $browser_temp = '';
        $browser_working = '';
        $browser_number = '';
        $ie_version = '';
        $mobile_test = '';
        $moz_release_date = '';
        $moz_rv = '';
        $moz_rv_full = '';
        $moz_type = '';
        $moz_number = '';
        $os_number = '';
        $os_type = '';
        $run_time = '';
        $true_ie_number = '';
        $ua_type = 'bot';// default to bot since you never know with bots
        $webkit_type = '';
        $webkit_type_number = '';

        // set the excludes if required
        if ( $test_excludes ) {
            switch ( $test_excludes ){
                case '1':
                    $b_os_test = false;
                    break;
                case '2':
                    $b_mobile_test = false;
                    break;
                case '3':
                    $b_os_test = false;
                    $b_mobile_test = false;
                    break;
                default:
                    die( 'Error: bad $test_excludes parameter 2 used: ' . $test_excludes );
                    break;
            }
        }

        /*
        make navigator user agent string lower case to make sure all versions get caught
        isset protects against blank user agent failure. tolower also lets the script use
        strstr instead of stristr, which drops overhead slightly.
        */
        if ( $external_ua_string ) {
            $browser_user_agent = strtolower( $external_ua_string );
        } elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
        } else {
            $browser_user_agent = '';
        }

        // known browsers, list will be updated routinely, check back now and then
        $a_browser_types = array(
            array( 'opera', true, 'op', 'bro' ),
            array( 'msie', true, 'ie', 'bro' ),
            // webkit before gecko because some webkit ua strings say: like gecko
            array( 'webkit', true, 'webkit', 'bro' ),
            // konq will be using webkit soon
            array( 'konqueror', true, 'konq', 'bro' ),
            // covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
            array( 'gecko', true, 'moz', 'bro' ),
            array( 'netpositive', false, 'netp', 'bbro' ),// beos browser
            array( 'lynx', false, 'lynx', 'bbro' ), // command line browser
            array( 'elinks ', false, 'elinks', 'bbro' ), // new version of links
            array( 'elinks', false, 'elinks', 'bbro' ), // alternate id for it
            array( 'links2', false, 'links2', 'bbro' ), // alternate links version
            array( 'links ', false, 'links', 'bbro' ), // old name for links
            array( 'links', false, 'links', 'bbro' ), // alternate id for it
            array( 'w3m', false, 'w3m', 'bbro' ), // open source browser, more features than lynx/links
            array( 'webtv', false, 'webtv', 'bbro' ),// junk ms webtv
            array( 'amaya', false, 'amaya', 'bbro' ),// w3c browser
            array( 'dillo', false, 'dillo', 'bbro' ),// linux browser, basic table support
            array( 'ibrowse', false, 'ibrowse', 'bbro' ),// amiga browser
            array( 'icab', false, 'icab', 'bro' ),// mac browser
            array( 'crazy browser', true, 'ie', 'bro' ),// uses ie rendering engine

            // search engine spider bots:
            array( 'bingbot', false, 'bing', 'bot' ),// bing
            array( 'exabot', false, 'exabot', 'bot' ),// exabot
            array( 'googlebot', false, 'google', 'bot' ),// google
            array( 'google web preview', false, 'googlewp', 'bot' ),// google preview
            array( 'mediapartners-google', false, 'adsense', 'bot' ),// google adsense
            array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' ),// old yahoo bot
            array( 'yahoo! slurp', false, 'yahoo', 'bot' ), // new yahoo bot
            array( 'yahoo-mm', false, 'yahoomm', 'bot' ), // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
            array( 'inktomi', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'slurp', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'fast-webcrawler', false, 'fast', 'bot' ),// Fast AllTheWeb
            array( 'msnbot', false, 'msn', 'bot' ),// msn search
            array( 'ask jeeves', false, 'ask', 'bot' ), //jeeves/teoma
            array( 'teoma', false, 'ask', 'bot' ),//jeeves teoma
            array( 'scooter', false, 'scooter', 'bot' ),// altavista
            array( 'openbot', false, 'openbot', 'bot' ),// openbot, from taiwan
            array( 'ia_archiver', false, 'ia_archiver', 'bot' ),// ia archiver
            array( 'zyborg', false, 'looksmart', 'bot' ),// looksmart
            array( 'almaden', false, 'ibm', 'bot' ),// ibm almaden web crawler
            array( 'baiduspider', false, 'baidu', 'bot' ),// Baiduspider asian search spider
            array( 'psbot', false, 'psbot', 'bot' ),// psbot image crawler
            array( 'gigabot', false, 'gigabot', 'bot' ),// gigabot crawler
            array( 'naverbot', false, 'naverbot', 'bot' ),// naverbot crawler, bad bot, block
            array( 'surveybot', false, 'surveybot', 'bot' ),//
            array( 'boitho.com-dc', false, 'boitho', 'bot' ),//norwegian search engine
            array( 'objectssearch', false, 'objectsearch', 'bot' ),// open source search engine
            array( 'answerbus', false, 'answerbus', 'bot' ),// http://www.answerbus.com/, web questions
            array( 'sohu-search', false, 'sohu', 'bot' ),// chinese media company, search component
            array( 'iltrovatore-setaccio', false, 'il-set', 'bot' ),

            // various http utility libaries
            array( 'w3c_validator', false, 'w3c', 'lib' ), // uses libperl, make first
            array( 'wdg_validator', false, 'wdg', 'lib' ), //
            array( 'libwww-perl', false, 'libwww-perl', 'lib' ),
            array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' ),
            array( 'python-urllib', false, 'python-urllib', 'lib' ),
            // download apps
            array( 'getright', false, 'getright', 'dow' ),
            array( 'wget', false, 'wget', 'dow' ),// open source downloader, obeys robots.txt
            // netscape 4 and earlier tests, put last so spiders don't get caught
            array( 'mozilla/4.', false, 'ns', 'bbro' ),
            array( 'mozilla/3.', false, 'ns', 'bbro' ),
            array( 'mozilla/2.', false, 'ns', 'bbro' )
        );

        //array( '', false ); // browser array template

        /*
        moz types array
        note the order, netscape6 must come before netscape, which  is how netscape 7 id's itself.
        rv comes last in case it is plain old mozilla. firefox/netscape/seamonkey need to be later
        Thanks to: http://www.zytrax.com/tech/web/firefox-history.html
        */
        $a_moz_types = array( 'bonecho', 'camino', 'epiphany', 'firebird', 'flock', 'galeon', 'iceape', 'icecat', 'k-meleon', 'minimo', 'multizilla', 'phoenix', 'songbird', 'swiftfox', 'seamonkey', 'shiretoko', 'iceweasel', 'firefox', 'minefield', 'netscape6', 'netscape', 'rv' );

        /*
        webkit types, this is going to expand over time as webkit browsers spread
        konqueror is probably going to move to webkit, so this is preparing for that
        It will now default to khtml. gtklauncher is the temp id for epiphany, might
        change. Defaults to applewebkit, and will all show the webkit number.
        */
        $a_webkit_types = array( 'arora', 'chrome', 'epiphany', 'gtklauncher', 'konqueror', 'midori', 'omniweb', 'safari', 'uzbl', 'applewebkit', 'webkit' );

        /*
        run through the browser_types array, break if you hit a match, if no match, assume old browser
        or non dom browser, assigns false value to $b_success.
        */
        $i_count = count( $a_browser_types );
        for ($i = 0; $i < $i_count; $i++) {
            //unpacks browser array, assigns to variables, need to not assign til found in string
            $browser_temp = $a_browser_types[$i][0];// text string to id browser from array

            if ( strstr( $browser_user_agent, $browser_temp ) ) {
                /*
                it defaults to true, will become false below if needed
                this keeps it easier to keep track of what is safe, only
                explicit false assignment will make it false.
                */
                $b_safe_browser = true;
                $browser_name = $browser_temp;// text string to id browser from array

                // assign values based on match of user agent string
                $b_dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
                $browser_working = $a_browser_types[$i][2];// working name for browser
                $ua_type = $a_browser_types[$i][3];// sets whether bot or browser

                switch ( $browser_working ) {
                    // this is modified quite a bit, now will return proper netscape version number
                    // check your implementation to make sure it works
                    case 'ns':
                        $b_safe_browser = false;
                        $browser_number = G::get_item_version( $browser_user_agent, 'mozilla' );
                        break;
                    case 'moz':
                        /*
                        note: The 'rv' test is not absolute since the rv number is very different on
                        different versions, for example Galean doesn't use the same rv version as Mozilla,
                        neither do later Netscapes, like 7.x. For more on this, read the full mozilla
                        numbering conventions here: http://www.mozilla.org/releases/cvstags.html
                        */
                        // this will return alpha and beta version numbers, if present
                        $moz_rv_full = G::get_item_version( $browser_user_agent, 'rv' );
                        // this slices them back off for math comparisons
                        $moz_rv = substr( $moz_rv_full, 0, 3 );

                        // this is to pull out specific mozilla versions, firebird, netscape etc..
                        $j_count = count( $a_moz_types );
                        for ($j = 0; $j < $j_count; $j++) {
                            if ( strstr( $browser_user_agent, $a_moz_types[$j] ) ) {
                                $moz_type = $a_moz_types[$j];
                                $moz_number = G::get_item_version( $browser_user_agent, $moz_type );
                                break;
                            }
                        }
                        /*
                        this is necesary to protect against false id'ed moz'es and new moz'es.
                        this corrects for galeon, or any other moz browser without an rv number
                        */
                        if ( !$moz_rv ) {
                            // you can use this if you are running php >= 4.2
                            if ( function_exists( 'floatval' ) ) {
                                $moz_rv = floatval( $moz_number );
                            } else {
                                $moz_rv = substr( $moz_number, 0, 3 );
                            }
                            $moz_rv_full = $moz_number;
                        }
                        // this corrects the version name in case it went to the default 'rv' for the test
                        if ( $moz_type == 'rv' ) {
                            $moz_type = 'mozilla';
                        }

                        //the moz version will be taken from the rv number, see notes above for rv problems
                        $browser_number = $moz_rv;
                        // gets the actual release date, necessary if you need to do functionality tests
                        G::get_set_count( 'set', 0 );
                        $moz_release_date = G::get_item_version( $browser_user_agent, 'gecko/' );
                        /*
                        Test for mozilla 0.9.x / netscape 6.x
                        test your javascript/CSS to see if it works in these mozilla releases, if it
                        does, just default it to: $b_safe_browser = true;
                        */
                        if ( ( $moz_release_date < 20020400 ) || ( $moz_rv < 1 ) ) {
                            $b_safe_browser = false;
                        }
                        break;
                    case 'ie':
                        /*
                        note we're adding in the trident/ search to return only first instance in case
                        of msie 8, and we're triggering the  break last condition in the test, as well
                        as the test for a second search string, trident/
                        */
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name, true, 'trident/' );
                        // construct the proper real number if it's in compat mode and msie 8.0/9.0
                        if ( strstr( $browser_number, '7.' ) && strstr( $browser_user_agent, 'trident/5' ) ) {
                            // note that 7.0 becomes 9 when adding 1, but if it's 7.1 it will be 9.1
                            $true_ie_number = $browser_number + 2;
                        } elseif ( strstr( $browser_number, '7.' ) && strstr( $browser_user_agent, 'trident/4' ) ) {
                            // note that 7.0 becomes 8 when adding 1, but if it's 7.1 it will be 8.1
                            $true_ie_number = $browser_number + 1;
                        }
                        // the 9 series is finally standards compatible, html 5 etc, so worth a new id
                        if ( $browser_number >= 9 ) {
                            $ie_version = 'ie9x';
                        } elseif ( $browser_number >= 7 ) {
                            $ie_version = 'ie7x';
                        } elseif ( strstr( $browser_user_agent, 'mac') ) {
                            $ie_version = 'ieMac';
                        } elseif ( $browser_number >= 5 ) {
                            $ie_version = 'ie5x';
                        } elseif ( ( $browser_number > 3 ) && ( $browser_number < 5 ) ) {
                            $b_dom_browser = false;
                            $ie_version = 'ie4';
                            // this depends on what you're using the script for, make sure this fits your needs
                            $b_safe_browser = true;
                        } else {
                            $ie_version = 'old';
                            $b_dom_browser = false;
                            $b_safe_browser = false;
                        }
                        break;
                    case 'op':
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        // opera is leaving version at 9.80 (or xx) for 10.x - see this for explanation
                        // http://dev.opera.com/articles/view/opera-ua-string-changes/
                        if ( strstr( $browser_number, '9.' ) && strstr( $browser_user_agent, 'version/' ) ) {
                            G::get_set_count( 'set', 0 );
                            $browser_number = G::get_item_version( $browser_user_agent, 'version/' );
                        }

                        if ( $browser_number < 5 ) {
                            $b_safe_browser = false;
                        }
                        break;
                    case 'webkit':
                        // note that this is the Webkit version number
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        // this is to pull out specific webkit versions, safari, google-chrome etc..
                        $j_count = count( $a_webkit_types );
                        for ($j = 0; $j < $j_count; $j++) {
                            if (strstr( $browser_user_agent, $a_webkit_types[$j])) {
                                $webkit_type = $a_webkit_types[$j];
                                if ( $webkit_type == 'omniweb' ) {
                                    G::get_set_count( 'set', 2 );
                                }
                                $webkit_type_number = G::get_item_version( $browser_user_agent, $webkit_type );
                                // epiphany hack
                                if ( $a_webkit_types[$j] == 'gtklauncher' ) {
                                    $browser_name = 'epiphany';
                                } else {
                                    $browser_name = $a_webkit_types[$j];
                                }
                                break;
                            }
                        }
                        break;
                    default:
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        break;
                }
                // the browser was id'ed
                $b_success = true;
                break;
            }
        }

        //assigns defaults if the browser was not found in the loop test
        if ( !$b_success ) {
            /*
            this will return the first part of the browser string if the above id's failed
            usually the first part of the browser string has the navigator useragent name/version in it.
            This will usually correctly id the browser and the browser number if it didn't get
            caught by the above routine.
            If you want a '' to do a if browser == '' type test, just comment out all lines below
            except for the last line, and uncomment the last line. If you want undefined values,
            the browser_name is '', you can always test for that
            */
            // delete this part if you want an unknown browser returned
            $browser_name = substr( $browser_user_agent, 0, strcspn( $browser_user_agent , '();') );
            // this extracts just the browser name from the string, if something usable was found
            if ( $browser_name && preg_match( '/[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*/', $browser_name, $a_unhandled_browser ) ) {
                $browser_name = $a_unhandled_browser[0];
                if ( $browser_name == 'blackberry' ) {
                    G::get_set_count( 'set', 0 );
                }
                $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
            } else {
                $browser_name = 'NA';
                $browser_number = 'NA';
            }
        }
        // get os data, mac os x test requires browser/version information, this is a change from older scripts
        if ($b_os_test) {
            $a_os_data = G::get_os_data( $browser_user_agent, $browser_working, $browser_number );
            $os_type = $a_os_data[0];// os name, abbreviated
            $os_number = $a_os_data[1];// os number or version if available
        }
        /*
        this ends the run through once if clause, set the boolean
        to true so the function won't retest everything
        */
        $b_repeat = true;
        if ($browser_number && preg_match( '/[0-9]*\.*[0-9]*/', $browser_number, $a_browser_math_number ) ) {
            $browser_math_number = $a_browser_math_number[0];
        }
        if ( $b_mobile_test ) {
            $mobile_test = G::check_is_mobile( $browser_user_agent );
            if ( $mobile_test ) {
                $a_mobile_data = G::get_mobile_data( $browser_user_agent );
                $ua_type = 'mobile';
            }
        }
    }

    switch ($which_test) {
        case 'math_number':
            $which_test = 'browser_math_number';
            break;
        case 'number':
            $which_test = 'browser_number';
            break;
        case 'browser':
            $which_test = 'browser_working';
            break;
        case 'moz_version':
            $which_test = 'moz_data';
            break;
        case 'true_msie_version':
            $which_test = 'true_ie_number';
            break;
        case 'type':
            $which_test = 'ua_type';
            break;
        case 'webkit_version':
            $which_test = 'webkit_data';
            break;
    }
    /*
    assemble these first so they can be included in full return data, using static variables
    Note that there's no need to keep repacking these every time the script is called
    */
    if (!$a_moz_data) {
        $a_moz_data = array( $moz_type, $moz_number, $moz_rv, $moz_rv_full, $moz_release_date );
    }
    if (!$a_webkit_data) {
        $a_webkit_data = array( $webkit_type, $webkit_type_number, $browser_number );
    }
    $run_time = G::script_time();

    if ( !$a_full_assoc_data ) {
        $a_full_assoc_data = array(
            'browser_working' => $browser_working,
            'browser_number' => $browser_number,
            'ie_version' => $ie_version,
            'dom' => $b_dom_browser,
            'safe' => $b_safe_browser,
            'os' => $os_type,
            'os_number' => $os_number,
            'browser_name' => $browser_name,
            'ua_type' => $ua_type,
            'browser_math_number' => $browser_math_number,
            'moz_data' => $a_moz_data,
            'webkit_data' => $a_webkit_data,
            'mobile_test' => $mobile_test,
            'mobile_data' => $a_mobile_data,
            'true_ie_number' => $true_ie_number,
            'run_time' => $run_time
        );
    }

    // return parameters, either full data arrays, or by associative array index key
    switch ($which_test) {
        // returns all relevant browser information in an array with standard numberic indexes
        case 'full':
            $a_full_data = array(
                $browser_working,
                $browser_number,
                $ie_version,
                $b_dom_browser,
                $b_safe_browser,
                $os_type,
                $os_number,
                $browser_name,
                $ua_type,
                $browser_math_number,
                $a_moz_data,
                $a_webkit_data,
                $mobile_test,
                $a_mobile_data,
                $true_ie_number,
                $run_time
            );
            return $a_full_data;
            break;
        case 'full_assoc':
            return $a_full_assoc_data;
            break;
        default:
            # check to see if the data is available, otherwise it's user typo of unsupported option
            if (isset( $a_full_assoc_data[$which_test])) {
                return $a_full_assoc_data[$which_test];
            } else {
                die( "You passed the browser detector an unsupported option for parameter 1: " . $which_test );
            }
            break;
    }
}

$result = new stdclass();
try {
    // Validating request data
    if (!isset($_REQUEST['a'])) {
        throw new Exception('The required parameter "a" is empty.');
    }

    $browser = browser_detection('full_assoc');
    switch($browser['browser_working'])
    {
        case 'moz':
            $browserVersion = explode('.',$browser['browser_number']);
            if($browserVersion[0] < 13)
            {
                echo "Vous disposez d'une version:".$browser['browser_number']." . Ce site est optimis&#233; pour Firefox 13";
                die;
            }
        break;
        
        case 'webkit':   //chrome, safari, opera 
        
            if($browser['browser_name'] == 'safari')
            {
                echo "Ce site ne supporte pas le navigateur Safari, Ce site est optimis&#233; pour Firefox 13, Internet Explorer 8 et Chrome 18";
                die;
            }
            else
            {
                $browserVersion = explode ('.',$browser['webkit_data'][1]);
                
                if($browserVersion[0] < 18)
                {
                    echo "Vous disposez d'une version: ".$browser['webkit_data']." . Ce site est optimis&#233; pour Chrome 18";
                    die;
                }
            }
        break;
        
        case 'ie':
            
            if($browser['browser_name'] == 'msie')
            {
                $browserVersion = explode('.',$browser['browser_number']);
                
                if($browserVersion[0] < 8)
                {
                    echo "Vous disposez d'une version: ".$browser['browser_number']." . Ce site est optimis&#233; pour Internet Explorer 8";
                    die;
                }
            }   
        break;
        
        default:
                echo "Le navigateur que vous essayez d'entrer est inconnue essayer un autre navigateur ";
                die;
        break;
    }

    // Authentication
    $_SESSION['USER_LOGGED'] = authentication($_REQUEST['u'], $_REQUEST['p']);
    $_SESSION['USR_USERNAME'] = $_REQUEST['u'];
    switch ($_REQUEST['a']) {
        case 'webEntry':
            // Redirect to web entry
            if (!isset($_REQUEST['task'])) {
                throw new Exception('The required parameter "task" is empty.');
            }
            G::header('Location: ../../'.urldecode($_REQUEST['task']));
            die();
            break;
        case 'mesdemandes':        
            // Redirect to inbox
            //G::header('Location: ../../convergenceList/inboxDinamic.php?table=95654345151237be9ca3ab2040266744&filter=demande');
            if (!isset($_REQUEST['task'])) {
                G::header('Location: ../../convergenceList/inboxDinamic.php?idInbox=DEMANDES');
            }
            else
                G::header('Location: ../../convergenceList/inboxDinamic.php?idInbox='.$_REQUEST['task']);
            die();
            break;
        case 'inbox':
            // Redirect to inbox

            G::header('Location: ../../cases/casesListExtJs');
            die();
            break;
        case 'main_init':
            // Redirect to inbox
            G::header('Location: ../../cases/main_init');
            die();
            break;
        case 'main':
            // Redirect to inbox
            G::header('Location: ../../cases/main');
            die();
            break;
        case 'editProfile':
            G::header('Location: ../../fieldcontrol/my_profile'); 
            die();
            break;
        case 'editPassword':
            G::header('Location: ../../fieldcontrol/my_profile.php?type=onlyPassword'); 
            die();
            break;
        case 'start':
            // Validating request data
            if (!isset($_REQUEST['task'])) {
                throw new Exception('The required parameter "task" is empty.');
            }
            // Start a case

            # Get the last draft case
            G::loadClass ( 'pmFunctions' );            
            G::LoadClass('case');
            $caseInstance = new Cases();            
            
            $sqlGetProcess = 'SELECT PRO_UID FROM TASK WHERE TAS_UID="'.$_REQUEST['task'].'"';
            $resultGetProcess = executeQuery($sqlGetProcess);
            
            $sqlDraft = "SELECT MAX(APP_NUMBER), APP_UID FROM APPLICATION 
                         WHERE APP_STATUS = 'DRAFT' AND PRO_UID='".$resultGetProcess[1]['PRO_UID']."' AND APP_CUR_USER= '".$_SESSION['USER_LOGGED']."'  GROUP BY APP_NUMBER ";
            $resultDraft = executeQuery($sqlDraft);
            if(sizeof($resultDraft)){                
                $sqlprocessTask = "SELECT * FROM APP_DELEGATION WHERE APP_UID= '".$resultDraft[1]['APP_UID']."' AND DEL_INDEX= 1 ";
                $resultprocessTask = executeQuery($sqlprocessTask);
                if(sizeof($resultprocessTask)){
                    $_SESSION['APPLICATION'] = $resultDraft[1]['APP_UID'];
                    $_SESSION['INDEX'] = 1;
                    $_SESSION['PROCESS'] = $resultprocessTask[1]['PRO_UID'];
                    $_SESSION['TASK'] = $resultprocessTask[1]['TAS_UID'];
                    $_SESSION['STEP_POSITION'] = 0;
                    FupdateAPPDATATYPO3($_SESSION['APPLICATION']); // typo3
                    // Execute events
                    require_once 'classes/model/Event.php';
                    $eventInstance = new Event();
                    $eventInstance->createAppEvents($resultprocessTask[1]['PRO_UID'], $resultDraft[1]['APP_UID'], '1', $resultprocessTask[1]['TAS_UID']);

                    // Redirect to cases steps
                    $nextStep = $caseInstance->getNextStep($resultprocessTask[1]['PRO_UID'], $resultDraft[1]['APP_UID'], '1', 0);
                    G::header('Location: ../../cases/' . $nextStep['PAGE']);                    
                }                
            }
            # End Get the last draft case
            else{

                $data = $caseInstance->startCase($_REQUEST['task'], $_SESSION['USER_LOGGED']);
                $_SESSION['APPLICATION'] = $data['APPLICATION'];
                $_SESSION['INDEX'] = $data['INDEX'];
                $_SESSION['PROCESS'] = $data['PROCESS'];
                $_SESSION['TASK'] = $_REQUEST['task'];
                $_SESSION['STEP_POSITION'] = 0;

                // Execute events
                require_once 'classes/model/Event.php';
                $eventInstance = new Event();
                $eventInstance->createAppEvents($_SESSION['PROCESS'], $_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['TASK']);

                FupdateAPPDATATYPO3($_SESSION['APPLICATION'],1); // typo3
                
                // Redirect to cases steps
                $nextStep = $caseInstance->getNextStep($_SESSION['PROCESS'], $_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['STEP_POSITION']);
                G::header('Location: ../../cases/' . $nextStep['PAGE']);
            }                
            die();
            break;
        default:
            throw new Exception('Unknow action ("' . $_REQUEST['a'] . '").');
            break;
    }
} catch(Exception $error) {
    $result->status = 'error';
    $result->message = $error->getMessage();
}

$messageError = "<p style='margin-bottom: 0cm'>« ce compte n’existe pas , veuillez vous inscrire » ?</p>";
die($messageError);
