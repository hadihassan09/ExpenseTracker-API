<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;
use Carbon\Carbon;


$dotenv = new DotEnv(__DIR__);
$dotenv->load();

// PHP has no base64UrlEncode function, so let's define one that
// does some magic by replacing + with -, / with _ and = with ''.
// This way we can pass the string within URLs without
// any URL encoding.
function base64UrlEncode($text)
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($text)
    );
}

function createJWT($data){
    $secret = getenv('SECRET');
// Create the token header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);
// Create the token payload
    $payload=json_encode($data);
// Encode Header
    $base64UrlHeader = base64UrlEncode($header);
// Encode Payload
    $base64UrlPayload = base64UrlEncode($payload);
// Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
// Encode Signature to Base64Url String
    $base64UrlSignature = base64UrlEncode($signature);
// Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    return $jwt;
}

function verifyJWT($jwt){
    error_reporting(0);
// get the local secret key
    $secret = getenv('SECRET');
// split the token
    $tokenParts = explode('.', $jwt);
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];

// check the expiration time - note this will cause an error if there is no 'exp' claim in the token
    $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
    $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);
// build a signature based on the header and payload using the secret
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);
// verify it matches the signature provided in the token
    $signatureValid = ($base64UrlSignature === $signatureProvided);

    if($signatureValid && !$tokenExpired) {
        return json_decode($payload);
    }else{
        return false;
    }
}

