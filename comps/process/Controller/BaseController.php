<?php

namespace Controller;

class BaseController
{
    public function __construct()
    {
        try {
            $apachHeader = apache_request_headers();
            foreach ($apachHeader as $key => $item) {
                $key = strtolower(str_replace('-', '', $key));
                $$key = $item;
            }

            $this->authorization = ($authorization) ?? "";
            $this->contentType = ($contenttype) ?? "";

            if ($_SERVER['REQUEST_METHOD'] === "POST" || $_SERVER['REQUEST_METHOD'] === "PUT") {
                $result = file_get_contents('php://input');
                $response = ($result) ? json_decode($result, true) : [];
                $this->data = $response;
            }

        } catch (\Exception $e) {
            $this->error(503, "Server Error");
            exit;
        }
    }

    public function error($code = 503, $msg = 'Server Error', $data = [])
    {
        //에러로그 추가
        if (empty($data)) {
            $data = $_REQUEST ?? '';
        }
        $request = !$data ? json_encode($data) : '';

        ErrorInsert($code, $msg, $request);

        $this->result($code, $msg, [], 'error');
    }

    public function result($code, $msg, $data = [], $desc)
    {
        $result = [
            'code' => (gettype($code) === "integer") ? $code : strval($code),
            'message' => $msg,
            'data' => $data,
            'desc' => $desc
        ];
        echo json_encode($result);
    }

    public function response($code, $msg, $data = [], $desc)
    {
        $response = [
            'code' => strval($code),
            'message' => $msg,
            'data' => $data,
            'desc' => $desc
        ];
        return $response;
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
    public function checkBirth($data){
        $birth = [
            'Birth1' => "",
            'Birth2' => ""
        ];

        $fullBirth = $this->checkPattern('birth', $data, true);
        if (!$fullBirth) {
            return $birth;
        }

        //Split Birth to Birth1 & Birth2
        $jumin = substr($fullBirth, 0, 2);
        $juminYear = (date('y') >= $jumin) ? "20" : "19";
        $birth1 = $juminYear . $jumin;
        $birth2 = substr($fullBirth, 2, 4);
        //Check OverAge (기준생년 1957)
        $validYear = 1957;
        if ($birth1 <= $validYear) {
            return $birth;
        }
        $birth['Birth1'] = $birth1;
        $birth['Birth2'] = $birth2;

        return $birth;
    }

    public function isTestCheck()
    {
        if (isset($_POST['isTest'])) {
            if ($_POST['isTest'] === '1') {
                echo json_encode(['code' => '0000', 'msg' => 'success', 'data' => [
                    'data' => [
                        'jonIndex' => 1, 'jti' => 1, 'threadIndex' => 1, 'twoWayTimestamp' => 1
                    ],
                    'result' => [
                        'transactionId' => 1
                    ]
                ]
                ]);
                exit;
            }
        }
    }
}