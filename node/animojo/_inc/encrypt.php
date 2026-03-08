<?php
function encrypt_srt($str, $key)
{
    $cipher = "AES-256-CBC";
    $iv = str_repeat("\0", openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($str, $cipher, $key, 0, $iv);
    return base64_encode($encrypted);
}
function decrypt_srt($enc_str, $key)
{
    $cipher = "AES-256-CBC";
    $iv = str_repeat("\0", openssl_cipher_iv_length($cipher));
    $cipherText = base64_decode($enc_str);
    return openssl_decrypt($cipherText, $cipher, $key, 0, $iv);
}