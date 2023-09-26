<?php

namespace Controller;

use Model\Codef;

class CodefController extends BaseController
{
    public function __construct()
    {
    }

    /***
     * CodeF 간편인증 요청
     * @return void
     */
    public function sendEasyAuth()
    {
        $data = [
            'LoginType' => 0,
            'Name' => "",
            'Birth' => "",
            'Phone' => "",
            'TempIdx' => ""
        ];
        $this->isTestCheck();
        try {
            //Check POST Data
            if (!isset($_POST['UsersIdx'])) {
                if (!isset($_POST['loginTypeLevel'], $_POST['name'], $_POST['birth'], $_POST['phone'], $_POST['tempIdx'])) {
                    throw new \Exception("필수 파라미터가 존재하지않습니다.", "9999");
                }
                if(!$_POST['tempIdx']){
                    throw new \Exception("필수 파라미터가 존재하지않습니다.", "9999");
                }
                $data['Name'] = $this->checkPattern('kor', $_POST['name'], true);
                $birth = $this->checkBirth($_POST['birth']);
                $data['Birth'] = $birth['Birth1'] . $birth['Birth2'];
                $data['Phone'] = $this->checkPattern('Number', str_replace("-", "", $_POST['phone']), true);

                if($data['Birth'] == ""){
                    throw new \Exception("생년월일 정보 확인이 필요합니다. Data : " . $_POST['birth'], "0009");
                }
            } else {
                if (!isset($_POST['loginTypeLevel'], $_POST['tempIdx'])) {
                    throw new \Exception("필수 파라미터가 존재하지않습니다.", "9999");
                }
                if(!$_POST['tempIdx']){
                    throw new \Exception("필수 파라미터가 존재하지않습니다.", "9999");
                }
                $data['UsersIdx'] = $_POST['UsersIdx'];
            }

            $data['LoginType'] = $this->checkPattern('Number', $_POST['loginTypeLevel'], true);
            $data['TempIdx'] = $this->checkPattern('Number', $_POST['tempIdx'], true);
            $data['telecom'] = (isset($_POST['telecom'])) ? $this->checkPattern('Number', $_POST['telecom'], true) : "";

            //Codef 토큰 발급 및 간편인증 요청
            $codef = new Codef();
            $resultData = $codef->sendEasyAuthModel($data);
            $accessResult = $resultData['result'] ?? $resultData;

            switch ($accessResult['code']) {
                case "CF-03002":
                    $this->result("0000", "success", $resultData, "간편인증 요청 성공");
                    break;
                case "CF-12835":
                    throw new \Exception("본인인증 처리에 실패했습니다. 입력 정보 확인 후 시도하시길 바랍니다.", "0009");
                case "CF-12871":
                    throw new \Exception("앱이 설치되지 않았거나, 인증서를 발급받지 않으셨습니다. 앱 설치 및 인증서 발급(재발급) 후 시도하시길 바랍니다.", "0009");
                case "CF-12870":
                    throw new \Exception("앱 설치 또는 인증서 발급 여부를 확인하거나, 사용자 정보(이름, 전화번호, 주민등록번호)를 확인 후 거래하시기 바랍니다.", "0009");
                default:
                    throw new \Exception("Code: " . $accessResult['code'] . " Msg: " . $accessResult['message'], "0099");
            }
        } catch (\Exception $exception) {
            header('HTTP/1.1 500 Internal Server Booboo');
            header('Content-Type: application/json; charset=UTF-8');
            ErrorInsert(450, $exception->getMessage(), json_encode($data));
            $this->result(strval($exception->getCode()), $exception->getMessage(), $data, "");
        }
        exit;
    }


    /***
     * 건강** 정보 요청(간편인증 선행 완료 후)
     * @return array
     */
    public function getNHISUserData()
    {
        $this->isTestCheck();
        try {
            $tempIdx = $_POST['tempIdx'] ?? 0;
            if (!$_POST['tempIdx']) {
                throw new \Exception("필수 파라미터가 존재하지않습니다.", "9999");
            }
            if (!isset($_POST['transactionId'], $_POST['jobIndex'], $_POST['threadIndex'],
                $_POST['jti'], $_POST['twoWayTimestamp'])) {
                throw new \Exception("인증 데이터를 찾을 수 없습니다.", "0010");
            }
            $birth = $this->checkBirth($_POST['birth']);
            $phone = str_replace("-", "", $_POST['phone']);
            $params = [
                'UsersIdx' => ($_POST['UsersIdx']) ?? "",
                'name' => ($_POST['name']) ?? "",
                'birth1' => ($birth['Birth1']) ?? "",
                'birth2' => ($birth['Birth2']) ?? "",
                'phone' => ($phone) ?? "",
                'tempIdx' => $tempIdx,
                'transactionId' => ($_POST['transactionId']) ?? "",
                "jobIndex" => ($_POST['jobIndex']) ?? "",
                "threadIndex" => ($_POST['threadIndex']) ?? "",
                "jti" => ($_POST['jti']) ?? "",
                "twoWayTimestamp" => ($_POST['twoWayTimestamp']) ?? "",
                "clientCustomerIdx" => ($_POST['clientCustomerIdx']) ?? "",
                "gender" => ($_POST['sex']) ?? "",
                "email" => ($_POST['email']) ?? "",
                "allAgreeYn" => ($_POST['allAgreeYn']) ?? "",
                "agreeDate" => ($_POST['agreeDate']) ?? "",
            ];
            $codef = new Codef();
            $result = $codef->getNHISDataModel($params);
            if (!$result['result']) {
                throw new \Exception($result['message'], $result['code']);
            }
        } catch (\Exception $exception) {
            header('HTTP/1.1 500 Internal Server Booboo');
            header('Content-Type: application/json; charset=UTF-8');
            $result = [
                'result' => false,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        } finally {
            echo json_encode($result);
        }
        exit;
    }

}