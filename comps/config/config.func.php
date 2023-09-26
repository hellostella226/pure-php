<?php

/**
 * xxxxxx 토큰생성함수
 */
function tokenCheck()
{
    global $apiUrl;

    $path = $apiUrl . "/api/open/auth";
    $body = array(
        "id" => "****",
        "password" => "********",
    );

    $post_data = json_encode($body);
    $url = $path;
    $header_data = array(
        'Content-Type: application/json; charset=utf-8'
    );

    //만료시간 체크
    if (isset($_SESSION['**Token'])) {
        if (date("Y-m-d H:i:s") > $_SESSION['**Time']) {
            unset($_SESSION['**Token']);
            unset($_SESSION['**Time']);
        }
    }

    //token 발급
    if (!isset($_SESSION['**Token'])) {
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
            $age = $data['claims']['data']['age'];
        }
        curl_close($ch);

        $_SESSION['tmpData'] = $data;
        $_SESSION['ip'] = $data['ip'];
        $_SESSION['no'] = $data['no'];
        $nowDatetime = time();
        $addHour = (int)$age / 3600000;
        $**Time = strtotime("+ " . $addHour . " hours", $nowDatetime);
        $limitTimes = date("Y-m-d H:i:s", $**Time);
        $_SESSION['**Time'] = $limitTimes;
        $_SESSION['**Token'] = $token;
    }

}

function printR($arr)
{
    echo '<xmp>';
    print_r($arr);
    echo '</xmp>';
}

function alert($msg)
{
    echo "<script>alert('{$msg}');</script>";
}

function scriptLocation($path, $type = "replace")
{
    $locationValue = array(
        'href' => "location.href = '$path';",
        'replace' => "location.replace('$path');",
        'back' => "history.back();",
        'go' => "history.go($path);",
        'reload' => "location.reload();",
    );

    echo "<script>" . $locationValue[$type] . "</script>";
}

function getLabgeMembersData($gcRegNo, $gcRegDate){
    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();

    $sql = "SELECT MM.UsersIdx, MM.MembersIdx, GCM.GCRegNo, GCM.GCRegDate, M.Name, M.Phone, M.Birth1, M.Birth2, M.Gender 
            FROM abc.Genom AS GCM 
            JOIN abc.Users as MM ON GCM.UsersIdx = MM.UsersIdx 
            JOIN abc.Members AS M ON MM.MembersIdx = M.MembersIdx 
            WHERE GCM.GCRegNo = :GCRegNo AND GCM.GCRegDate = :GCRegDate";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':GCRegNo', $gcRegNo, PDO::PARAM_INT);
    $stmt->bindValue(':GCRegDate', $gcRegDate);
    $stmt->execute();
    $result = $stmt->fetch();

    $pdo = null;
    return $result;
}

function getMembersData($UsersIdx)
{
    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();

    $sql = "SELECT M.*,MM.* FROM abc.Members AS M 
            JOIN abc.Users AS MM ON M.MembersIdx = MM.MembersIdx
            WHERE MM.UsersIdx = :UsersIdx AND MM.IsOut IS FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':UsersIdx', $UsersIdx, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();

    $pdo = null;
    return $result;
}

function get***Data($UsersIdx, $reportType)
{
    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();

    $sql = "select * FROM abc.Report WHERE UsersIdx = :UsersIdx AND reportType = :ReportType ORDER BY ExamDate DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':UsersIdx', $UsersIdx, PDO::PARAM_INT);
    $stmt->bindValue(':ReportType', $reportType, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();

    $pdo = null;
    return $result;
}

function getMemberStatus($UsersIdx)
{
    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();

    $sql = "select Process,StatusCode FROM abc.MemberStatus WHERE UsersIdx = :UsersIdx LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':UsersIdx', $UsersIdx, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();

    $pdo = null;
    return $result;
}

function Encrypt($str, $secret_key = '********', $secret_iv = '********')
{
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 32);
    return @str_replace("=", "", base64_encode(
            openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv))
    );
}

function Decrypt($str, $secret_key = '********', $secret_iv = '********')
{
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 32);
    return @openssl_decrypt(
        base64_decode($str), "AES-256-CBC", $key, 0, $iv
    );
}

function ErrorInsert($code, $msg, $request)
{
    $referer = $_SERVER['HTTP_REFERER'];
    $ipAddress = getClientIp();

    $CustomException = new CustomException($msg, $code);
    $response = $CustomException->RESTResFunc();

    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();
    $sql = "INSERT INTO *.ErrorLog (Code, Msg, Request, Referer, IpAddress) 
            VALUES (:Code, :Msg, :Request, :Referer, :IpAddress)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':Code', $response['code']);
    $stmt->bindValue(':Msg', $response['msg']);
    $stmt->bindValue(':Request', $request);
    $stmt->bindValue(':Referer', $referer);
    $stmt->bindValue(':IpAddress', $ipAddress);
    $stmt->execute();

    $pdo = null;
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
 * @date 2022-07-26
 * @brief client ip check
 * @param null
 * @return ip adress
 * @author hellostellaa
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
 * @date 2022-07-15
 * @brief device check
 * @param none
 * @return device type
 * @author hellostellaa
 */
function deviceCheck()
{
    $mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";
    if (preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])) {
        return 'mo';
    } else {
        return 'pc';
    }
}

function osCheck()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
        return 'ios';
    } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false) {
        return 'android';
    } else {
        return 'ios';
    }
}

function kakaoWebViewCheck()
{
    $kakao_agent = "/(KAKAO)/";

    if (preg_match($kakao_agent, $_SERVER['HTTP_USER_AGENT'])) {
        return osCheck();
    } else {
        return true;
    }
}

function sigsToken()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sgisakostat.go.kr/OpenAPI3/auth/authentication.json?consumer_key=********&consumer_secret=********',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array('consumer_key' => '********', 'consumer_secret' => '********'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}

function getAddressList($accessKey, $cd = '')
{

    $curl = curl_init();
    $url = 'https://sgisakostat.go.kr/OpenAPI3/addr/stage.json?accessToken=' . $accessKey;
    if ($cd != '') {
        $url .= "&cd=" . $cd;
    }
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}