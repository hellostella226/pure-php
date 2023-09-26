<?php

namespace Model;

use Controller\BaseController;

class BaseModel
{
    public function curl($method, $url, $header, $body)
    {

        $curl = curl_init();

        if ($method == 'POST') {
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $header,
            ]);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        }

        if ($method == 'GET') {
            $body = !$body ? $body : http_build_query($body, '', '&');
            $url = $url . '?' . $body;

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        }

        $return = [
            'response' => $response,
            'code' => $http_code
        ];

        return $return;
    }

    public function checkPattern(string $type, $param, bool $isString = false)
    {
        $result = null;

        $value = trim($param);
        $pattern = $this->getPattern($type);
        $isValid = preg_match($pattern, $value);

        if ($isValid) {
            $result = ($isString) ? $value : $isValid;
        }

        return $result;
    }

    public function checkGender($param)
    {
        $juminLastNum = (int)$this->checkPattern('number', $param, true);

        if ($juminLastNum % 2 === 0) {
            // 짝수 - 여성
            $gender = 2;
        } else {
            // 홀수 - 남성
            $gender = 1;
        }

        return $gender;
    }

    public function getPattern(string $type): string
    {
        $type = strtolower($type);

        $pattern = '';
        switch ($type) {
            case 'string':
                $pattern = '/[a-zA-Z가-힣]/i';
                break;
            case 'kor':
                $pattern = '/[가-힣]/i';
                break;
            case 'number':
                $pattern = '/[0-9]/i';
                break;
            case 'birth':
                $pattern = '/[0-9]{6}/';
                break;
            case 'fullBirth':
                $pattern = '/[0-9]{8}/';
                break;
            case 'email':
                $pattern = '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i';
                break;
            case 'date':
                $pattern = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
                break;
        }

        return $pattern;
    }

    function ErrorInsert($conn, $code, $msg, $request)
    {
        $requestData = $request ? addslashes(htmlspecialchars($request)) : "";
        $referer = $_SERVER['HTTP_REFERER'] ?? "";
        $ipAddress = getClientIp();

        $CustomException = new \CustomException($msg, $code);
        $response = $CustomException->RESTResFunc();

        $sql = "INSERT INTO *.ErrorLog (
                    Code, Msg, Request, Referer, IpAddress) 
                VALUES (
                    :Code, :Msg, :RequestData, :Referer, :IpAddress)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':Code', $response['code']);
        $stmt->bindValue(':Msg', $response['msg']);
        $stmt->bindValue(':RequestData', $requestData);
        $stmt->bindValue(':Referer', $referer);
        $stmt->bindValue(':IpAddress', $ipAddress);
        $result = $stmt->execute();

        return $result;
    }
}