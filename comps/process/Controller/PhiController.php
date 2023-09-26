<?php

namespace Controller;
use Model\***;

// xxxxxx 관련 통신부
class TestController extends ServiceController
{
    function __construct() {
        parent::__construct();
    }

    // 진입 페이지 정의
    public function definedDestination(): void
    {
        try {
            $page = '';
            if ($this->hCode) {
                parent::setProductGroupIdx();
                $page = "product_group/{$this->productGroupIdx}.php";
            } else if ($this->eCode) {
                parent::setEventItem();
                $page = "event_page/{$this->eCode}.php";
            }
        } catch (\Exception $e) {
            switch ($e->getCode()) {
                case 204:
                    $page = 'close.html';
                    break;
                case 203:
                    $page = 'inspection.html';
                    break;
                default :
                    $page = 'error_500.html';
                    break;
            }
        } finally {
            parent::views($page);
        }
    }

    //프로세스 처리부
    public function requestProcess()
    {
        try {
            $param = parent::getParam();
            $response = [];
            switch ($param['process']) {
                // 회원조희/등록
                case 'findMembers' :
                    $response = parent::findMembers($param);
                    break;
                // 유저 상태 조회
                case 'checkUserStatus' :
                    $response = parent::checkUserStatus($param);
                    break;
                // 약관동의
                case 'agreement' :
                    $response = parent::agreement($param);
                    break;
                // 본인인증요청
                case 'tryAuth' :
                    parent::event($param);
                    $response = $this->tryAuth($param);
                    break;
                // 건강검진 스크래핑
                case 'nhis' :
                    $response = $this->requestNhisData($param);
                    break;
                // *** 결과 및 리포트 생성
                case '***' :
                    $response = $this->make***Report($param);
                    break;
                // 맞춤영양 Event
                case 'supplements':
                    $response = $this->recommendSupplements($param);
                    break;
                // MemberStatus 입력 및 갱신
                case 'MemberStatus' :
                    $response = parent::MemberStatus($param);
                    break;
                // 이벤트 업데이트
                case 'updateEvent' :
                    $response = parent::updateEvent($param);
                    break;
                // 회원 질환 및 맞춤영양 결과 조회
                case 'personalResult' :
                    $response = $this->getPersonalResult($param);
                    break;
                // 거래처 사용량 및 사용기간 체크
                case 'checkClientCustomerStatus' :
                    $response = parent::checkClientCustomerStatus($param);
                    break;
                // 거래처 사용량 갱신
                case 'updateIssueCnt' :
                    $response = parent::updateIssueCnt($param);
                    break;
                // *** 리포트 보기 또는 pdf 다운로드
                case '***Report' :
                    $response = $this->get***Report($param);
                    break;
                // 알림톡 전송
                case 'sendBizM' :
                    $response = parent::sendBizMMessage($param);
                    break;
                default :
                    break;
            }
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }
        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            parent::errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            parent::errorLog($e);
        } finally {
            echo parent::jsonResponse();
            exit;
        }
    }

    // *** 리포트 보기 또는 다운로드
    function get***Report($param): array
    {
        try {
            $*** = new Test();
            return $***->get***Report($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 질환 및 맞춤영양 결과 조회
    function getPersonalResult($param): array
    {
        try {
            $*** = new Test();
            return $***->getPersonalResult($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 맞춤영양 분석
    function recommendSupplements($param): array
    {
        try {
            $*** = new Test();
            return $***->recommendSupplements($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // *** 리포트 요청
    function make***Report($param): array
    {
        try {
            $*** = new Test();
            return $***->make***Report($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 건강검진 데이터 요청 :: xxxxxx 리포트 분리해서 내부에 끼워놨는데, 분리가 필요치 않은 경우 해당 함수 삭제하고 메서드 통합해주세요
    function requestNhisData($param): array
    {
        try {
            $*** = new Test();
            return $***->requestNhisData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 코드에프 간편인증
    function tryAuth($param): array
    {
        try {
            if (
                !isset(
                    $param['name'],
                    $param['phone'],
                    $param['birth1'],
                    $param['birth2'],
                    $param['tempIdx'],
                    $param['loginType'],
//                    $param['telecom'],
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다", "404");
            }

            //codef request body
            $params = [
                'organization' => "0002",
                'loginType' => "5",
                'loginTypeLevel' => $param['loginType'], //인증정보?
                'inquiryType' => "0",
                'searchStartYear' => "1970",
                'searchEndYear' => date('Y'),
                'type' => "1",
                'identity' => $param['birth1'] . $param['birth2'],//생년월일
                'id' => $param['tempIdx'],//key
                'userName' => $param['name'], // 이름
                'phoneNo' => $param['phone'], //전화번호
            ];
            if ($param['loginType'] === '5') {
                $params['telecom'] = $param['telecom'];
            }
            $*** = new Test();
            $rs = $***->tryAuth($params);
            if ($rs) {
                $status = [
                    'process' => 'C',
                    'statusCode' => $rs['code'],
                ];
                $***->MemberStatus($param, $status);
            }
            return $rs;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}