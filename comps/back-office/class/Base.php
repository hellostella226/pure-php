<?php
class Base
{
    private $secretKey = '********';
    private $secretIv = '********';
    private $jusoUrl = "https://business.juso.go.kr/addrlink/addrLinkado";
    private $jusoKey = "********";

    public function __construct()
    {

    }

    public function Encrypt($str, $secretKey, $secretIv)
    {
        $useKey = ($secretKey !== '') ? $secretKey : $this->secretKey;
        $useIv = ($secretIv !== '') ? $secretIv : $this->secretIv;

        $key = hash('sha256', $useKey);
        $iv = substr(hash('sha256', $useIv), 0, 32);

        return @str_replace("=", "", base64_encode(
                openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv))
        );
    }


    public function Decrypt($str, $secretKey, $secretIv)
    {
        $useKey = ($secretKey !== "") ? $secretKey : $this->secretKey;
        $useIv = ($secretIv !== "") ? $secretIv : $this->secretIv;

        $key = hash('sha256', $useKey);
        $iv = substr(hash('sha256', $useIv), 0, 32);

        return @openssl_decrypt(
            base64_decode($str), "AES-256-CBC", $key, 0, $iv
        );
    }

    public function findAddress(string $keyword)
    {
        $keywordEncode = urlencode($keyword);
        $url = $this->jusoUrl . "?confmKey=" . $this->jusoKey . "&currentPage=1&countPerPage=1&hstryYn=Y&resultType=json&firstSort=road&keyword=" . $keywordEncode;

        return file_get_contents($url);
    }
}