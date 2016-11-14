<?php


//STEP 1

// -- notify payfast
header( 'HTTP/1.0 200 OK' );
flush();

// -- store payfast data
define( 'SANDBOX_MODE', true );
$pfHost = SANDBOX_MODE ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
// Posted variables from ITN
$pfData = $_POST;
 
// Strip any slashes in data
foreach( $pfData as $key => $val )
{
    $pfData[$key] = stripslashes( $val );
}

var_dump($pfData);

//STEP 2

// --security check 1
// $pfData includes of ALL the fields posted through from PayFast, this includes the empty strings
foreach( $pfData as $key => $val )
{
    if( $key != 'signature' )
    {
        $pfParamString .= $key .'='. urlencode( $val ) .'&';
    }
}
 
// Remove the last '&' from the parameter string
$pfParamString = substr( $pfParamString, 0, -1 );
$pfTempParamString = $pfParamString;
// If a passphrase has been set in the PayFast Settings, then it needs to be included in the signature string.
$passPhrase = 'GUMBA'; //You need to get this from a constant or stored in you website database
/// !!!!!!!!!!!!!! If you testing your integration in the sandbox, the passPhrase needs to be empty !!!!!!!!!!!!
if( !empty( $passPhrase ) && !SANDBOX_MODE )
{
    $pfTempParamString .= '&passphrase='.urlencode( $passPhrase );
}
$signature = md5( $pfTempParamString );
 
if($signature!=$pfData['signature'])
{
    die('Invalid Signature');
}

// --security check 2
// Variable initialization
$validHosts = array(
    'www.payfast.co.za',
    'sandbox.payfast.co.za',
    'w1w.payfast.co.za',
    'w2w.payfast.co.za',
);
 
$validIps = array();
 
foreach( $validHosts as $pfHostname )
{
    $ips = gethostbynamel( $pfHostname );
 
    if( $ips !== false )
    {
        $validIps = array_merge( $validIps, $ips );
    }
}
 
// Remove duplicates
$validIps = array_unique( $validIps );
 
if( !in_array( $_SERVER['REMOTE_ADDR'], $validIps ) )
{
    die('Source IP not Valid');
}


// --security check 3
//$cartTotal = xxxx; //This amount needs to be sourced from your application
//if( abs( floatval( $cartTotal ) - floatval( $pfData['amount_gross'] ) ) > 0.01 )
//{
    //die('Amounts Mismatch');
//}

// --security check 4
if( in_array( 'curl', get_loaded_extensions() ) )
{
    // Variable initialization
    $url = 'https://'. $pfHost .'/eng/query/validate';
 
    // Create default cURL object
    $ch = curl_init();
 
    // Set cURL options - Use curl_setopt for freater PHP compatibility
    // Base settings
    curl_setopt( $ch, CURLOPT_USERAGENT, PF_USER_AGENT );  // Set user agent
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );      // Return output as string rather than outputting it
    curl_setopt( $ch, CURLOPT_HEADER, false );             // Don't include header in output
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
 
    // Standard settings
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
    curl_setopt( $ch, CURLOPT_TIMEOUT, PF_TIMEOUT );
    if( !empty( $pfProxy ) )
    {
        curl_setopt( $ch, CURLOPT_PROXY, $proxy );
    }      
    // Execute CURL
    $response = curl_exec( $ch );
    curl_close( $ch );
}
else
{
    $header = '';
    $res = '';
    $headerDone = false;
 
    // Construct Header
    $header = "POST /eng/query/validate HTTP/1.0\r\n";
    $header .= "Host: ". $pfHost ."\r\n";
    $header .= "User-Agent: ". PF_USER_AGENT ."\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen( $pfParamString ) . "\r\n\r\n";
 
    // Connect to server
    $socket = fsockopen( 'ssl://'. $pfHost, 443, $errno, $errstr, PF_TIMEOUT );
 
    // Send command to server
    fputs( $socket, $header . $pfParamString );
 
    // Read the response from the server
    while( !feof( $socket ) )
    {
        $line = fgets( $socket, 1024 );
 
        // Check if we are finished reading the header yet
        if( strcmp( $line, "\r\n" ) == 0 )
        {
            // read the header
            $headerDone = true;
        }
        // If header has been processed
        else if( $headerDone )
        {
            // Read the main response
            $response .= $line;
        }
    }
}
$lines = explode( "\r\n", $response );
$verifyResult = trim( $lines[0] );
 
if( strcasecmp( $verifyResult, 'VALID' ) != 0 )
{
    die('Data not valid');
}

//STEP 3
$pfPaymentId = $pfData['pf_payment_id'];
//query your database and compare in order to ensure you have not processed this payment allready


//STEP 4
switch( $pfData['payment_status'] )
{
    case 'COMPLETE':
    // If complete, update your application, email the buyer and process the transaction as paid                    
    break;
    case 'FAILED':                    
    // There was an error, update your application and contact a member of PayFast's support team for further assistance
    break;
    case 'PENDING':
    // The transaction is pending, please contact a member of PayFast's support team for further assistance
    break;
    default:
    // If unknown status, do nothing (safest course of action)
    break;
}

