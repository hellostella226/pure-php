<?

namespace Controller;

use Model\Admin;

class TestController extends AdminController
{
    function __construct($groupCode)
    {
        parent::__construct($groupCode);
    }

    // get 요청
    function search($page, $request): void
    {
        try {
            parent::setParam($request);
            $param = $this->getParam();

            if (isset($request['purpose'])) {
                $response = [];
                switch ($request['purpose']) {
                    case 'menu':
                        $response = parent::setMenu();
                        break;
                    //product
                    case 'item' :
                        $response = $this->productList($param);
                        break;
                    case 'group' :
                        $response = $this->productGroupList($param);
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
                    //company
                    case 'company' :
                        $response = $this->companyList($param);
                        break;
                    case 'searchCompany' :
                        $response = $this->searchCompany($param);
                        break;
                    //Members
                    case 'Members' :
                        $response = $this->MembersList($param);
                        break;
                    //disease
                    case 'disease' :
                        $response = $this->diseaseList($param);
                        break;
                    //genetic
                    case 'genetic' :
                        $response = $this->geneticList($param);
                        break;
                    // agreementFail
                    case 'agreementFail' :
                        $response = $this->agreementFailList($param);
                        break;
                    // telephone
                    case 'telephone' :
                        $response = $this->telephoneList($param);
                        break;
                    // sms
                    case 'sms' :
                        $response = $this->smsList($param);
                        break;
                    case 'searchSms' :
                        $response = $this->searchSms($param);
                        break;
                    // consulting
                    case 'consulting':
                        $response = $this->consultingResultList($param);
                        break;
                    case 'searchConsulting':
                        $response = $this->searchConsultingResult($param);
                        break;
                    // insuranceItem
                    case 'insuranceItem' :
                        $response = $this->insuranceItemList($param);
                        break;
                    case 'insurance':
                        $response = $this->insuranceList($param);
                        break;
                    case 'searchInsurance' :
                        $response = $this->searchInsurance($param);
                        break;
                    case 'insureib':
                        $response = $this->insureIbList($param);
                        break;
                    case 'findAllocateUser':
                        $response = $this->findAllocateUser($param);
                        break;
                    case 'ibAllocationData':
                        $response = parent::ibAllocationData($param);
                        break;
                    case 'searchIbUserData':
                        $response = parent::searchIbUserData($param);
                        break;
                    case 'allDown' :
                        parent::allDown($param);
                        break;
                    default :
                        break;
                }
                if (count($response) > 0) {
                    foreach ($response as $key => $val) {
                        $this->$key = $val;
                    }
                }
                echo parent::jsonResponse();
            } else {
                $this->setPage($page, $param);
            }
        } catch (\PDOException $PDOException) {
            parent::errorLog($PDOException);
            $this->code = "500";
            $this->msg = "Internal Server Error";
            echo parent::jsonResponse();
        } catch (\Exception $e) {
            parent::errorLog($e);
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            echo parent::jsonResponse();
        }
    }

    // POST 요청 처리부
    function request($request): void
    {
        $response = [];
        try {
            parent::setParam($request);
            $param = $this->getParam();
            switch ($request['purpose']) {
                // product
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
                // Members
                case 'updateMembers':
                    $response = $this->updateMembers($param);
                    break;
                // hospital
                case 'registCompany' :
                    $response = $this->registCompany($param);
                    break;
                case 'qrDown' :
                    parent::qrDown($param);
                    break;
                case 'uploadCompanyDb':
                    $response = $this->uploadCompanyDb($param);
                    break;
                // disease
                case 'get***Report':
                    parent::get***Report($param);
                    break;
                // genetic
                case 'geneticAgreement' :
                    $this->getGeneticAgreement($param);
                    break;
                // telephone
                case 'updateConsultingData':
                    $response = $this->updateConsultingData($param);
                    break;
                // sms
                case 'sendSms':
                    $response = $this->sendSms($param);
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
                case 'insuranceUpdate' :
                    $response = $this->insuranceUpdate($param);
                    break;
                case 'uploadDbAllocation' :
                    $response = parent::uploadDbAllocation($param);
                    break;
                case 'getIbReport':
                    parent::getIbReport($param);
                    break;
                default :
                    break;
            }
            if (count($response) > 0) {
                foreach ($response as $key => $val) {
                    $this->$key = $val;
                }
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

    // **IB 할당 유저 조회
    function findAllocateUser($param) : array
    {
        $this->desc = 'findAllocateUser';
        try {
            $admin = new Admin();
            return $admin->findAllocateUserforTest($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ** IB 관리
    function insureIbList($param) : array
    {
        $this->desc = 'insureIbListfor***';
        try {
            $product = new Admin();
            return $product->insureIbListforTest($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 엑셀 업로드
    function uploadConsultingResult($param): array
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
    function consultingResultDown($param): void
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
    function searchConsultingResult($param): array
    {
        $this->desc = 'searchConsultingResult';
        try {
            $admin = new Admin();
            return $admin->searchConsultingResult($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 조회
    function consultingResultList($param): array
    {
        $this->desc = 'consultingResultList';
        try {
            $admin = new Admin();
            return $admin->consultingResultList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **사 또는 **상품 대량등록
    function uploadInsuranceItem($param): array
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
    function deleteInsuranceItem($param): array
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
    function updateInsuranceItem($param): array
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
    function insuranceItemList($param): array
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
    function sendSms($param): array
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
    function searchSms($param): array
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
    function smsList($param): array
    {
        $this->desc = 'smsList';
        try {
            $admin = new Admin();
            return $admin->smsList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담예약 업데이트
    function updateConsultingData($param): array
    {
        $this->desc = 'updateConsultingData';
        try {
            $admin = new Admin();
            return $admin->updateConsultingData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 전화상담 조회
    function telephoneList($param): array
    {
        $this->desc = 'telephoneList';
        try {
            $admin = new Admin();
            return $admin->telephoneList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // xxx검사 신청서 발송오류 조회
    function agreementFailList($param): array
    {
        $this->desc = 'agreementFailList';
        try {
            $admin = new Admin();
            return $admin->agreementFailList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // xxx검사 신청서 다운로드
    function getGeneticAgreement($param): void
    {
        $this->desc = 'getGeneticAgreement';
        try {
            $admin = new Admin();
            $admin->getGeneticAgreement($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // xxx검사신청 조회
    function geneticList($param): array
    {
        $this->desc = 'geneticList';
        try {
            $admin = new Admin();
            return $admin->geneticList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 질환검사 조회
    function diseaseList($param): array
    {
        $this->desc = 'diseaseList';
        try {
            $admin = new Admin();
            return $admin->diseaseList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용처 대량등록
    function uploadCompanyDb($param): array
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
    function registCompany($param): array
    {
        $this->desc = 'registCompany';
        try {
            $admin = new Admin();
            return $admin->registCompany($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //특정 사용처 조회(식별자)
    function searchCompany($param): array
    {
        $this->desc = 'searchCompany';
        try {
            $admin = new Admin();
            return $admin->searchCompany($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용처 정보
    function companyList($param): array
    {
        $this->desc = 'companyList';
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
    function itemGroupInsert($param): array
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
    function updateGroupName($param): array
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
    function createProduct($param): array
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
    function searchProductGroupName($param): array
    {
        $this->desc = 'searchProductGroupName';
        try {
            $admin = new Admin();
            return $admin->searchProductGroupName($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 조회
    function searchProductItem($param): array
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
    function justGroupList($param): array
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
    function catalogList($param): array
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
    function MembersList($param): array
    {
        $this->desc = 'MembersList';
        try {
            $admin = new Admin();
            return $admin->MembersList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    function updateMembers($param): array
    {
        $this->desc = 'updateMembers';
        try {
            $admin = new Admin();
            return $admin->updateMembers($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품 리스트 조회
    public function productList($param): array
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
    function productGroupList($param): array
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

    function searchInsurance($parma): array
    {
        $this->desc = 'searchInsurance';
        try {
            $admin = new Admin();
            return $admin->searchInsurance($parma);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //페이지 세팅(GET)
    public function setPage($page, $param): void
    {
        try {
            $isPage = false;
            foreach ($this->navi[$this->productGroupCode] as $item) {
                if ($item['id'] === $page) {
                    $isPage = true;
                    if (isset($item['sub'], $param['sub'])) {
                        $page = $page . '/' . $param['sub'];
                    }
                    break;
                }
            }
            if (!$isPage) {
                throw new \Exception("유효한 경로가 아닙니다. 개발팀에 문의하세요.");
            }
        } catch (\Exception $e) {
            $this->alert($e->getMessage(), '');
            $page = 'error_500.html';
        } finally {
            parent::views($page);
        }
    }
}