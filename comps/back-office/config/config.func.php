<?php
/**
 * 출력
 */
function printR($arr)
{
    echo '<xmp>';
    print_r($arr);
    echo '</xmp>';
}

/**
 * alert
 */
function alert($msg)
{
    echo "<script>alert('{$msg}');</script>";
}

function alertwithWindowClose(string $msg)
{
    alert($msg);
    echo "<script>window.close();</script>";
}

function _microtime()
{
    return array_sum(explode(' ', microtime()));
}

/**
 * ip 주소
 */
function getClientIp()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if (getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if (getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
    } else if (getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}

/**
 * xxxxxx 토큰생성함수
 */
function tokenCheck()
{
    global $apiUrl;

    $path = $apiUrl . "/api/open/auth";
    $body = array(
        "id" => "******",
        "password" => "******",
    );

    $post_data = json_encode($body);
    $url = $path;
    $header_data = array(
        'Content-Type: application/json; charset=utf-8'
    );

    //만료시간 체크
    if (isset($_SESSION['u2Token'])) {
        if (date("Y-m-d H:i:s") > $_SESSION['u2Time']) {
            unset($_SESSION['u2Token']);
            unset($_SESSION['u2Time']);
        }
    }

    //token 발급
    if (!isset($_SESSION['u2Token'])) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $header_data,
            CURLOPT_POSTFIELDS => $post_data
        ));

        $result = curl_exec($ch);
        $data = json_decode($result, true);
        if (empty($data['error'])) {
            $token = $data['token'];
        }
        curl_close($ch);

        $_SESSION['tmpData'] = $data;
        $_SESSION['ip'] = $data['claims']['data']['ip'];
        $_SESSION['no'] = $data['claims']['data']['no'];
        $u2Time = strtotime("+ " . 2 . " hours", time());
        $limitTimes = date("Y-m-d H:i:s", $u2Time);
        $_SESSION['u2Time'] = $limitTimes;
        $_SESSION['u2Token'] = $token;
    }

}

function add_hyphen($tel)
{
    $tel = preg_replace("/[^0-9]/", "", $tel);
    // 숫자 이외 제거
    if (substr($tel, 0, 2) == '02')
        return preg_replace("/([0-9]{2})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $tel);
    else if (strlen($tel) == '8' && (substr($tel, 0, 2) == '15' || substr($tel, 0, 2) == '16' || substr($tel, 0, 2) == '18'))        // 지능망 번호이면
        return preg_replace("/([0-9]{4})([0-9]{4})$/", "\\1-\\2", $tel);
    else
        return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $tel);
}

/**
 * 백오피스 Log 입력
 */
function insertLog(array $data, string $desc)
{
    $ipaddress = getClientIp();
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $editerId = $_SESSION['userId'] ?? '-';
    $actionData = json_encode($data, JSON_UNESCAPED_UNICODE);

    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();

    $logSql = "INSERT INTO test.BackOfficeLog (IpAddress, EditerId, ActionDesc, ActionData, Referer) 
                   VALUES (:ipAddress, :editerId, :actionDesc, :actionData, :referer)";

    $stmt = $conn->prepare($logSql);
    $stmt->bindValue(':ipAddress', $ipaddress);
    $stmt->bindValue(':editerId', $editerId);
    $stmt->bindValue(':actionDesc', $desc);
    $stmt->bindValue(':actionData', $actionData);
    $stmt->bindValue(':referer', $referer);
    $stmt->execute();
    $logIdx = $conn->lastInsertId() ?? 0;

    $pdo = null;
    $conn = null;

    return $logIdx;
}

function response($code, $msg, $data)
{
    $response = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ];

    echo json_encode($response, true);
}

function result($code, $msg, $data)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ];

    return $result;
}

function error($code, $msg, $request)
{
    $referer = $_SERVER['HTTP_REFERER'] ?? "";
    $ipAddress = getClientIp();

    if (is_array($request)) {
        $request = json_encode($request, true);
    }

    $CustomException = new \CustomException\CustomException($msg, $code);
    $response = $CustomException->RESTResFunc();

    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();
    $sql = "INSERT INTO *.ErrorLog (Code, Msg, Request, Referer, IpAddress) 
            VALUES ('{$response['code']}','{$response['msg']}','{$request}','{$referer}','{$ipAddress}')";

    $stmt = $conn->query($sql);

    response($code, $msg, $request);
}

/**
 * 변수 형식 체크
 */
function checkPattern(string $type, $value, $isString = true)
{
    $searchValue = trim($value);

    switch ($type) {
        case 'Name':
            $validation = preg_match('/^[가-힣]{1,20}/', $searchValue);
            break;
        case 'String':
            $validation = preg_match('/^[가-힣a-zA-Z\s0-9\-_]/i', $searchValue);
            break;
        case 'Number':
            $validation = preg_match('/^\d/i', $searchValue);
            break;
        case 'Gender':
            $validation = $searchValue === '남' || $searchValue === '여';
            break;
        case 'Email':
            $validation = preg_match('/^[_.\da-zA-Z@]/i', $searchValue);
            break;
        case 'Yn':
            $validation = $searchValue === 'Y' || $searchValue === 'N';
            break;
        case 'Date':
            $validation = preg_match('/[\d-]/', $searchValue);
            break;
        case 'yyyy-mm-dd':
            $validation = preg_match('/^(\d{4})?(-)?(0?[1-9]|1?[0-2])?(-)?(0?[1-9]|[1-2]?\d|3?[0-1])?$/', $searchValue);
            break;
        case 'Search':
            $validation = preg_match('/^[\d\w가-힣\_\@\-\,\(\)\s]/i', $searchValue);
            break;
        case 'Datetime':
            $validation = preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $searchValue);
            break;
        case 'Letter':
            $validation = preg_match('/^\w/', $searchValue);
            break;
        case 'Code':
            $validation = preg_match('/^[\w\_]/i', $searchValue);
            break;
        case 'ShortUrl':
            $validation = preg_match('/https:\/\/me2.do\/[a-zA-Z0-9]{8}/', $searchValue);
            break;
        case 'ReportType':
            $validation = $searchValue === "직접출력" || $searchValue === "이메일";
            break;
        case 'SendType':
            $validation = $searchValue === "API" || $searchValue === "수동";
            break;
        default:
            $validation = false;
            break;
    }

    $result = ($validation) ? $searchValue : NULL;
    $response = (!$isString) ? $validation : $result;

    return $response;
}

/**
 * Consultant 테이블 StatusCode 값 정의
 */
function consultStatusDef($val, $def = true)
{
    switch ($val) {
        case 'A':
        case '계약체결':
            $code = 'A';
            $codeDef = '계약체결';
            break;
        case 'B':
        case '종결':
            $code = 'B';
            $codeDef = '종결';
            break;
        case 'C':
        case '결번':
            $code = 'C';
            $codeDef = '결번';
            break;
        case 'D':
        case '상담거절':
            $code = 'D';
            $codeDef = '상담거절';
            break;
        case 'E':
        case '무응답':
            $code = 'E';
            $codeDef = '무응답';
            break;
        case 'F':
        case '중복':
            $code = 'F';
            $codeDef = '중복';
            break;
        case 'G':
        case '부재':
            $code = 'G';
            $codeDef = '부재';
            break;
        case 'H':
        case '병력':
            $code = 'H';
            $codeDef = '병력';
            break;
        case 'I':
        case '통화예약':
            $code = 'I';
            $codeDef = '통화예약';
            break;
        case 'J':
        case '상담완료':
            $code = 'J';
            $codeDef = '상담완료';
            break;
        case 'K':
        case '방문약속':
            $code = 'K';
            $codeDef = '방문약속';
            break;
        case 'L':
        case '계약대기':
            $code = 'L';
            $codeDef = '계약대기';
            break;
        case 'M':
        case '상담':
            $code = 'M';
            $codeDef = '상담';
            break;
        case 'N':
        case '거절':
            $code = 'N';
            $codeDef = '거절';
            break;
        case 'O':
        case '보완':
            $code = 'O';
            $codeDef = '보완';
            break;
        case 'P':
        case '인수불가':
            $code = 'P';
            $codeDef = '인수불가';
            break;
        case 'Q':
        case '신청오류':
            $code = 'Q';
            $codeDef = '신청오류';
            break;
        case 'Z':
        case '기타':
            $code = 'Z';
            $codeDef = '기타';
            break;
        default:
            $codeDef = '';
            $code = '';
            break;
    }

    if ($def) {
        return $codeDef;
    } else {
        return $code;
    }
}

function convertAging($birthDay, $targetDay)
{
    $birth_time = strtotime($birthDay);
    $target = date('Ymd', strtotime($targetDay));
    $birthday = date('Ymd', $birth_time);
    $age = floor(($target - $birthday) / 10000);
    return $age;
}

function reconvertAging($age)
{
    $minBirthDay = date('Ymd') - $age * 10000;
    return $minBirthDay;
}

function getInfisSearch(array $params, array $apiInfo)
{
    $curl = new CurlFactory();
    $response = $curl->curl('GET', $apiInfo['url'], $apiInfo['header'], $params);
    if ($response['code'] !== 200) {
        $infisApiSearchTemp = [
            'url' => 'http://infis.mrkim.co.kr/service/api/request-refine/search/refine',
            'header' => [
                'mrkim_access: eyJhbGciOiJSUzUxMiJ9.eyJpZCI6IjU1OGNkNmNkLTg1NDgtY2JkMi0yM2UxLTIyMjg0ODIxMmU2MyIsIm5hbWUiOiLqtIDrpqzsnpAiLCJ1c2VybmFtZSI6IklORklTIiwidGVhbUlkIjoiNTU4Y2Q2Y2QtODU0OC1jYmQyLTIzZTEtMjIyODQ4MjEyZTYzIiwiZW1haWwiOiIiLCJ0ZW1wb3JhcnlBZG1pbiI6IlkiLCJhdXRob3JpdGllcyI6IkFETUlOXHRVU0VSIiwiaWF0IjoxNjYzNTc4MjY4LCJleHAiOjE5NzM0Njc1NzV9.BAylqbaVtNf7nxQm1qyhMSJLD-_M5povq-tcyXslY3St6k9yhNChmDJXPvGGRZt6EPnnXjxSJiXdN-CzWn6PfN46l0oDNO-nHUuWwEOikqV8_nsU2kAhUaB36ofXk5UCT8TWL3ZYJ4yqAalGEW8FLpv3n2rto9QVvkthFbot5g-IbyU0SyTTbz7IrJldb7eZS9TQ8q_fN7tWtjPP1Ng4kad21oAjyOJtbJ0-AsTCdffXz39QhNZBYn2oohUWhtf-JwD1XzXJnTzC7CHn36xLp0tRwW1pP_XR3udpDlOXgMXvXOfyE-ZqFbt61c8CoWWPfv3stMP3UqcCLx13aoGAKg9LLs4C9OYtGHPAdD8dpSp4Z6YopxmkaYplDd23QVt-UpNeS-hQ7hfKQlyLqPqUlMKAzuTYCqwVkciQm2MBoSPyVDd9lFoaW8c4q9bYQzNefl1ywTaydMOyv1rkcf3ot0Emlzr8DE-jN13dzuX_P4LCetmuNmgFfdhIF4IQSiFx_j5OG33_duENlIue7niIK-FxmUb6lAIEX8ecschUOOMGm9KqA2AL1x8-7C-qkJ0XgjkHhhU6_EGB-MJR-N57rIBTc0pDiL4Oi5fUla9MeIRv7Y9NO1csv0XsIX-o5G6a9Q_ay0MotSVhNFZhG0EICj0646IjafANOKvdb7K_9lE',
                'Content-Type: application/json; charset=utf-8'
            ]
        ];
        $response = $curl->curl('GET', $infisApiSearchTemp['url'], $infisApiSearchTemp['header'], $params);
    }

    $result = $response['response'];
    $rs = json_decode($result, true);

    return $rs;
}

function view($template, $service, $vars = [])
{
    if (count($vars) > 0){
        foreach ($vars as $name => $value) {
            $$name = $value;
        }
    }
    include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/header.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/" . $service . "Navbar.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/resources/view/" . $template;
    include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/footer.php";
}

function paginate(array $params)
{

    $item_per_page = $params['rowNum'];
    $current_page = $params['page'];
    $total_records = $params['totalCnt'];
    $total_pages = ceil($total_records / $item_per_page);
    $page_url = $params['pageUrl'];

    $pagination = '';
    if ($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages) { //verify total pages and current page number
        $pagination .= '<ul class="pagination">';

        $right_links = $current_page + 3;
        $previous = $current_page - 3; //previous link
        $next = $current_page + 1; //next link
        $first_link = true; //boolean var to decide our first link

        if ($current_page > 1) {
            $previous_link = ($previous <= 0) ? 1 : $previous;
            $pagination .= '<li class="page-item first"><a class="page-link" href="' . $page_url . '?page=1&rowNum=' . $item_per_page . '" title="First">&laquo;</a></li>'; //first link
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '?page=' . $previous_link . '&rowNum=' . $item_per_page . '" title="Previous">Previous</a></li>'; //previous link
            for ($i = ($current_page - 2); $i < $current_page; $i++) { //Create left-hand side links
                if ($i > 0) {
                    $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '?page=' . $i . '&rowNum=' . $item_per_page . '">' . $i . '</a></li>';
                }
            }
            $first_link = false; //set first link to false
        }

        if ($first_link) { //if current active page is first link
            $pagination .= '<li class="page-item first active"><a class="page-link">' . $current_page . '</a></li>';
        } elseif ($current_page == $total_pages) { //if it's the last active link
            $pagination .= '<li class="page-item last active"><a class="page-link">' . $current_page . '</a></li>';
        } else { //regular current link
            $pagination .= '<li class="page-item active"><a class="page-link">' . $current_page . '</a></li>';
        }

        for ($i = $current_page + 1; $i < $right_links; $i++) { //create right-hand side links
            if ($i <= $total_pages) {
                $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '?page=' . $i . '&rowNum=' . $item_per_page . '">' . $i . '</a></li>';
            }
        }
        if ($current_page < $total_pages) {
            $next_link = ($i > $total_pages) ? $total_pages : $i;
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '?page=' . $next_link . '&rowNum=' . $item_per_page . '" >Next</a></li>'; //next link
            $pagination .= '<li class="page-item last"><a class="page-link" href="' . $page_url . '?page=' . $total_pages . '&rowNum=' . $item_per_page . '" title="Last">&raquo;</a></li>'; //last link
        }

        $pagination .= '</ul>';
    }
    return $pagination; //return pagination links
}