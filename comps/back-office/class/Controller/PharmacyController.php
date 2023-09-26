<?php

namespace Controller;
use Model\Admin;

class PharmacyController extends AdminController
{
    function __construct($groupCode)
    {
        parent::__construct($groupCode);
    }

    // GET 요청 처리부
    function search($page, $request) : void
    {
        try {
            parent::setParam($request);
            $param = $this->getParam();

            if(isset($request['purpose'])) {
                $response = [];
                switch ($request['purpose']) {
                    case 'menu':
                        $response = parent::setMenu();
                        break;
                    case 'group':
                        $response = $this->productGroupList($param);
                        break;
                    case 'Members' :
                        $response = $this->MembersList($param);
                        break;
                    case 'item' :
                        $response = $this->productList($param);
                        break;
                    case 'groupList' :
                        $response = $this->justGroupList($param);
                        break;
                    case 'catalogList' :
                        $response = $this->catalogList($param);
                        break;
                    case 'searchProductItem' :
                        $response = $this->searchProductItem($param);
                        break;
                    case 'searchProductGroupName' :
                        $response = $this->searchProductGroupName($param);
                        break;
                    case 'company' :
                        $response = $this->companyList($param);
                        break;
                    case 'searchCompany' :
                        $response = $this->searchCompany($param);
                        break;
                    case 'bioage' :
                        $response = $this->bioageList($param);
                        break;
                    case 'consulting':
                        $response = $this->consultingList($param);
                        break;
                    case 'survey':
                        $response = $this->surveyList($param);
                        break;
                    case 'supplement':
                        $response = $this->supplementList($param);
                        break;
                    case 'summaryResult':
                        $response = $this->summaryList($param);
                        break;
                    case 'history':
                        $response = $this->insureIbHistory($param);
                        break;
                    case 'insureib':
                        $response = $this->insureIbList($param);
                        break;
                    case 'consultingResult':
                        $response = $this->consultingResList($param);
                        break;
                    case 'searchConsulting':
                        $response = $this->searchConsultingResult($param);
                        break;
                    case 'insurance':
                        $response = $this->insuranceList($param);
                        break;
                    case 'searchInsurance' :
                        $response = $this->searchInsurance($param);
                        break;
                    case 'findAllocateUser':
                        $response = $this->findAllocateUser($param);
                        break;
                    case 'insuranceItem' :
                        $response = $this->insuranceItemList($param);
                        break;
                    case 'sms' :
                        $response = $this->smsList($param);
                        break;
                    case 'searchSms' :
                        $response = $this->searchSms($param);
                        break;
                    case 'allDown' :
                        parent::allDown($param);
                        break;
                    case 'searchIbUserData':
                        $response = parent::searchIbUserData($param);
                        break;
                    case 'ibAllocationData':
                        $response = parent::ibAllocationData($param);
                        break;
                    default :
                        break;
                }
                foreach ($response as $key => $val) {
                    $this->$key = $val;
                }
                echo parent::jsonResponse();
            } else {
                $this->setPage($page, $param);
            }
        } catch (\PDOException $PDOException) {
//            $this->code = "500";
//            $this->msg = "Internal Server Error";
            $this->code = $PDOException->getCode();
            $this->msg = $PDOException->getMessage();
            echo parent::jsonResponse();
//            parent::errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
//            parent::errorLog($e);
            echo parent::jsonResponse();
        }
    }

    // **할당이력
    function insureIbHistory($param) : array
    {
        $this->desc = 'insureIbHistory';
        try {
            $admin = new Admin();
            return $admin->insureIbHistory($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 엑셀 업로드
    function uploadConsultingResult($param) : array
    {
        $this->desc = 'uploadConsultingResult';
        try {
            $admin = new Admin();
            return $admin->uploadConsultingResult($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 엑셀 다운로드
    function consultingResultDown($param) : void
    {
        $this->desc = 'consultingResultDown';
        try {
            $admin = new Admin();
            $admin->consultingResultDown($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 infis 통신
    function searchConsultingResult($param) : array
    {
        $this->desc = 'searchConsultingResult';
        try {
            $admin = new Admin();
            return $admin->searchConsultingResult($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과
    function consultingResList($param) : array
    {
        $this->desc = 'consultingResList';
        try {
            $admin = new Admin();
            return $admin->consultingResList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ** IB 관리
    function insureIbList($param) : array
    {
        $this->desc = 'insureIbList';
        try {
            $product = new Admin();
            return $product->insureIbList($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 요약 검사 결과
    function summaryList($param) : array
    {
        $this->desc = 'summaryList';
        try {
            $product = new Admin();
            return $product->summaryList($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 맞춤 영양 리스트
    function supplementList($param) : array
    {
        $this->desc = 'supplementList';
        try {
            $admin = new Admin();
            return $admin->supplementList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 설문 응답 리스트
    function surveyList($param) : array
    {
        $this->desc = 'surveyList';
        try {
            $admin = new Admin();
            return $admin->surveyList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 질환검사 리스트
    function bioageList($param) : array
    {
        $this->desc = 'bioageList';
        try {
            $admin = new Admin();
            return $admin->bioageList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //특정 사용처 조회(식별자)
    function searchCompany($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->searchCompany($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // POST 요청 처리부
    function request($request) : void
    {
        $response = [];
        try {
            parent::setParam($request);
            $param = $this->getParam();
            switch ($request['purpose']) {
                case 'product' :
                    $response = $this->createProduct($param);
                    break;
                case 'disableProduct' :
                    $response = $this->disableProduct($param);
                    break;
                case 'groupName' :
                    $response = $this->updateGroupName($param);
                    break;
                case 'itemGroupInsert' :
                    $response = $this->itemGroupInsert($param);
                    break;
                case 'disableItemGroup' :
                    $response = $this->disableItemGroup($param);
                    break;
                case 'registCompany' :
                    $response = $this->registCompany($param);
                    break;
                case 'qrDown' :
                    parent::qrDown($param);
                    break;
                case 'uploadCompanyDb':
                    $response = $this->uploadCompanyDb($param);
                    break;
                case 'updateMembers':
                    $response = $this->updateMembers($param);
                    break;
                case 'get***Report':
                    parent::get***Report($param);
                    break;
                case 'getIbReport':
                    parent::getIbReport($param);
                    break;
                case 'updateConsultingData':
                    $response = $this->updateConsultingData($param);
                    break;
                case 'updateSurveyData':
                    $response = $this->updateSurveyData($param);
                    break;
                case 'insuranceUpdate' :
                    $response = $this->insuranceUpdate($param);
                    break;
                case 'uploadDbAllocation' :
                    $response = parent::uploadDbAllocation($param);
                    break;
                // consulting
                case 'consultingDown':
                    $this->consultingResultDown($param);
                    break;
                case 'uploadConsulting':
                    $response = $this->uploadConsultingResult($param);
                    break;
                // insuranceItem
                case 'updateInsuranceItem':
                    $response = $this->updateInsuranceItem($param);
                    break;
                case 'deleteInsuranceItem':
                    $response = $this->deleteInsuranceItem($param);
                    break;
                case 'uploadInsuranceItem':
                    $response = $this->uploadInsuranceItem($param);
                    break;
                // sms
                case 'sendSms':
                    $response = $this->sendSms($param);
                    break;
                default :
                    break;
            }
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }
        } catch (\PDOException $PDOException) {
//            $this->code = "500";
//            $this->msg = "Internal Server Error";
            $this->code = $PDOException->getCode();
            $this->msg = $PDOException->getMessage();
//            parent::errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
//            parent::errorLog($e);
        } finally {
            echo parent::jsonResponse();
            exit;
        }
    }

    // **IB 할당 유저 조회
    function findAllocateUser($param) : array
    {
        $this->desc = 'findAllocateUser';
        try {
            $admin = new Admin();
            return $admin->findAllocateUser($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담예약 업데이트
    function updateConsultingData($param) : array
    {
        $this->desc = 'updateConsultingData';
        try {
            $admin = new Admin();
            return $admin->updateConsultingData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    // 설문응답 업데이트
    function updateSurveyData($param) : array
    {
        $this->desc = 'updateSurveyData';
        try {
            $admin = new Admin();
            return $admin->updateSurveyData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담예약 리스트 조회
    function consultingList($param) : array
    {
        $this->desc = 'consultingList';
        try {
            $admin= new Admin();
            return $admin->consultingList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 회원정보 정보 수정
    function updateMembers($param) : array
    {
        $this->desc = 'updateMembers';
        try {
            $admin = new Admin();
            return $admin->updateMembers($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용처 대량등록
    function uploadCompanyDb($param) : array
    {
        $this->desc = 'uploadCompanyDb';
        try {
            $admin = new Admin();
            return $admin->uploadCompanyDb($param);
        } catch (\Exception $e) {
           throw $e;
        }
    }

    // 사용처 등록:수정
    function registCompany($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->registCompany($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용처 정보
    function companyList($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->companyList($param, $this->gIdx);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹 비활성화
    function disableItemGroup($param): array
    {
        $this->desc = 'disableItemGroup';
        try {
            $admin = new Admin();
            return $admin->disableItemGroup($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹 등록
    function itemGroupInsert($param) : array
    {
        $this->desc = 'itemGroupInsert';
        try {
            $admin = new Admin();
            return $admin->itemGroupInsert($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹명 수정
    function updateGroupName($param) : array
    {
        $this->desc = 'updateGroupName';
        try {
            $product = new Admin();
            return $product->updateGroupName($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 삭제
    function disableProduct($param): array
    {
        $this->desc = 'removeProduct';
        try {
            $admin = new Admin();
            return $admin->disableProduct($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 등록
    function createProduct($param) : array
    {
        $this->desc = 'createProduct';
        try {
            $admin = new Admin();
            return $admin->createProduct($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 그룹명 조회
    function searchProductGroupName($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->searchProductGroupName($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 조회
    function searchProductItem($param) : array
    {
        $this->desc = 'searchProductItem';
        try {
            $admin = new Admin();
            return $admin->searchProductItem($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 그룹 조회
    function justGroupList($param) : array
    {
        $this->desc = 'justGroupList';
        try {
            $admin = new Admin();
            return $admin->justGroupList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 카탈로그 조회
    function catalogList($param) : array
    {
        $this->desc = 'catalogList';
        try {
            $admin = new Admin();
            return $admin->catalogList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 회원정보 조회
    function MembersList($param) : array
    {
        $this->desc = 'MembersList';
        try {
            $admin = new Admin();
            return $admin->MembersList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 리스트 조회
    public function productList($param) : array
    {
        $this->desc = 'productList';
        try {
            $admin = new Admin();
            return $admin->productList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 그룹조회
    function productGroupList($param) : array
    {
        $this->desc = 'productGroupList';
        try {
            $admin = new Admin();
            return $admin->productGroupList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //** 거래처 리스트 조회
    function insuranceList($param): array
    {
        $this->desc = 'insuranceList';
        try {
            $admin = new Admin();
            return $admin->insuranceList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //** 거래처 등록 및 수정
    function insuranceUpdate($param): array
    {
        $this->desc = 'insuranceUpdate';
        try {
            $admin = new Admin();
            return $admin->insuranceUpdate($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    function searchInsurance($parma) : array
    {
        $this->desc = 'searchInsurance';
        try {
            $admin = new Admin();
            return $admin->searchInsurance($parma);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **사 또는 **상품 대량등록
    function uploadInsuranceItem($param) : array
    {
        $this->desc = 'uploadInsuranceItem';
        try {
            $admin = new Admin();
            return $admin->uploadInsuranceItem($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **사 또는 **상품 비활성화
    function deleteInsuranceItem($param) : array
    {
        $this->desc = 'deleteInsuranceItem';
        try {
            $admin = new Admin();
            return $admin->deleteInsuranceItem($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **사 또는 **상품 수정
    function updateInsuranceItem($param) : array
    {
        $this->desc = 'updateInsuranceItem';
        try {
            $admin = new Admin();
            return $admin->updateInsuranceItem($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상품 조회
    function insuranceItemList($param) : array
    {
        $this->desc = 'insuranceItemList';
        try {
            $admin = new Admin();
            return $admin->insuranceItemList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //알림톡 일괄 전송
    function sendSms($param) : array
    {
        $this->desc = "sendSms";
        try {
            $admin = new Admin();
            return $admin->sendSms($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //특정 회원 알림톡 전송일 조회
    function searchSms($param) : array
    {
        $this->desc = "searchSms";
        try {
            $admin = new Admin();
            return $admin->searchSms($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 알림톡 조회
    function smsList($param) : array
    {
        $this->desc = 'smsList';
        try {
            $admin = new Admin();
            return $admin->smsList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 페이지 세팅 (GET)
    public function setPage($page, $param): void
    {
        try {
            $isPage = false;
            foreach ($this->navi[$this->productGroupCode] as $item) {
                if($item['id'] === $page) {
                    $isPage = true;
                    if(isset($item['sub'], $param['sub'])) {
                        $page = $page.'/'.$param['sub'];
                    }
                    break;
                }
            }
            if(!$isPage) {
                throw new \Exception("유효한 경로가 아닙니다. 개발팀에 문의하세요.");
            }
        } catch (\Exception $e) {
            $this->alert($e->getMessage(),'');
            $page = 'error_500.html';
        } finally {
            parent::views($page);
        }
    }
}