<?

namespace CustomException;

use Exception;

class CustomException extends Exception
{
    public $code;
    public $message;
    private $service;

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        if (strpos($_SERVER['HTTP_HOST'], "api") !== false){
            $this->service = 10;
        } elseif (strpos($_SERVER['HTTP_HOST'], "ds") !== false){
            $this->service = 21;
        } elseif (strpos($_SERVER['HTTP_HOST'], "admin") !== false){
            $this->service = 20;
        } elseif (strpos($_SERVER['HTTP_HOST'], "mall") !== false){
            $this->service = 41;
        } else {
            $this->service = 31;
        }

        parent::__construct($message, $this->service . $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function RESTResFunc()
    {
        switch (substr($this->code,2)) {
            case 200 :
                $desc = "Success";
                break;
            case 201 :
                $desc = "Created";
                break;
            case 202 :
                $desc = "Accepted";
                break;
            case 204 :
                $desc = "No Content";
                break;
            case 400 :
                $desc = "Bad Request";
                break;
            case 401 :
                $desc = "Unauthorized";
                break;
            case 403 :
                $desc = "Forbidden";
                break;
            case 404 :
                $desc = "Not Found";
                break;
            case 405 :
                $desc = "Method Not Allowed";
                break;
            case 409 :
                $desc = "Conflict";
                break;
            case 500 :
                $desc = "Internal Server Error";
                break;
            case 501 :
                $desc = "Not Implemented";
                break;
            case 502 :
                $desc = "Bad Gateway";
                break;
            case 503 :
                $desc = "Service Unavailable";
                break;
            case 504 :
                $desc = "Gateway Timeout";
                break;
            default :
                $desc = "Server Error";
                break;
        }

        $response = [
            'code' => $this->code,
            'msg' => $this->message,
            'desc' => $desc
        ];

        return $response;
    }
}