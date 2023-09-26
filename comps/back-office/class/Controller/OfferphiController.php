<?php

namespace Controller;
use Model\Admin;

class OfferTestController extends AdminController
{
    function __construct($groupCode)
    {
        parent::__construct($groupCode);
    }

    // post
    function request($request) : void
    {
        $response = [];
        try {
            parent::setParam($request);
            $param = $this->getParam();
            switch ($request['purpose']) {
                case 'excelFileDown' :
                    parent::excelFileDown($param);
                    break;
                case 'updateClientData' :
                    $response = $this->updateClientData($param);
                    break;
                case 'updateFreeTicket' :
                    $response = $this->updateFreeTicket($param);
                    break;
                case 'registConsultant':
                    $response = $this->registConsultant($param);
                    break;
                case 'updateIsActiveClient' :
                    $response = $this->updateIsActiveClient($param);
                    break;
                case 'uploadOfferCompanyDb':
                    $response = $this->uploadOfferCompanyDb($param);
                    break;
                case 'get***Report':
                    parent::get***Report($param);
                    break;
                case 'registGoods':
                    $response = $this->registGoods($param);
                    break;
                case 'deleteGoods':
                    $response = $this->deleteGoods($param);
                    break;
                case 'couponRegist' :
                    $response = $this->couponRegist($param);
                    break;
                case 'refundPayment' :
                    $response = $this->refundPayment($param);
                    $this->sendRefundSms($response['data']);
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

    // get
    function search($page, $request): void
    {
        try {
            parent::setParam($request);
            $param = $this->getParam();

            if (isset($request['purpose'])) {
                $response = [];
                switch ($request['purpose']) {
                    case 'searchTicketData' :
                        $response = $this->searchTicketData($param);
                        break;
                    case 'menu':
                        $response = parent::setMenu();
                        break;
                    case 'registCoupon' :
                        $response = $this->registCouponList($param);
                        break;
                    case 'couponList' :
                        $response = $this->userCouponList($param);
                        break;
                    case 'searchTicketsData' :
                        $response = $this->searchTicketsData($param);
                        break;
                    case 'company' :
                        $response = $this->consultantList($param);
                        break;
                    case 'searchCompany' :
                    case 'searchCompanyIssue' :
                    case 'searchCompanyDate' :
                        $response = $this->searchCompany($param);
                        break;
                    case 'bioage' :
                        $response = $this->bioageList($param);
                        break;
                    case 'searchConsultantId' :
                        $response = $this->searchConsultantId($param);
                        break;
                    case 'goods' :
                        $response = $this->goodsList($param);
                        break;
                    case 'searchGoodsEdit':
                        $param['searchType'] = 'edit';
                        $response = $this->searchGoods($param);
                        break;
                    case 'searchGoodsDel':
                        $param['searchType'] = 'delete';
                        $response = $this->searchGoods($param);
                        break;
                    case 'payment' :
                        $response = $this->paymentList($param);
                        break;
                    case 'searchPayment' :
                        $response = $this->searchPayment($param);
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

    // 알림톡 전송 (ClientControlIdx)
    function sendRefundSms($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->sendRefundSms($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 쿠폰 사용 내역 조회::유저
    function userCouponList($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->userCouponList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사별 티켓 잔량 업데이트
    function updateFreeTicket($param) : array
    {
        $this->desc = 'updateFreeTicket';
        try {
            $admin = new Admin();
            return $admin->updateFreeTicket($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사 정보 업데이트
    function updateClientData($param) : array
    {
        $this->desc = 'updateClientData';
        try {
            $admin = new Admin();
            return $admin->updateClientData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사별 티켓 잔량 조회
    function searchTicketData($param) : array
    {
        try {
            $admin = new Admin();
            return $admin->searchTicketData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사관리::상담사 등록
    function registConsultant($param) : array
    {
        $this->desc = 'registConsultant';
        try {
            $admin = new Admin();
            return $admin->registConsultant($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사 활성화 업데이트
    function updateIsActiveClient($param) : array
    {
        $this->desc = 'updateIsActiveClient';
        try {
            $admin = new Admin();
            return $admin->updateIsActiveClient($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 결제내역 취소
    function refundPayment($param): array
    {
        try {
            $admin = new Admin();
            return $admin->refundPayment($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 결제내역 조회 (결제취소 modal)
    function searchPayment($param): array
    {
        try {
            $admin = new Admin();
            return $admin->searchPayment($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 결제내역 조회
    function paymentList($param): array
    {
        try {
            $admin = new Admin();
            return $admin->paymentList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈 정보 등록 또는 수정
    function registGoods($param): array
    {
        try {
            $admin = new Admin();
            return $admin->registGoods($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈 정보 조회
    function deleteGoods($param): array
    {
        try {
            $admin = new Admin();
            return $admin->deleteGoods($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈 정보 조회
    function searchGoods($param): array
    {
        try {
            $admin = new Admin();
            return $admin->searchGoods($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈 정보 (결제경로관리)
    function goodsList($param): array
    {
        try {
            $admin = new Admin();
            return $admin->goodsList($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 등록된 쿠폰 리스트
    function registCouponList($param) : array
    {
        $this->desc = 'registCouponList';
        try {
            $product = new Admin();
            return $product->registCouponList($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 쿠폰 등록
    function couponRegist($param): array
    {
        try {
            $admin = new Admin();
            return $admin->couponRegist($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사 조회
    function searchConsultantId($param): array
    {
        try {
            $admin = new Admin();
            return $admin->searchConsultantId($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용자 정보
    function consultantList($param): array
    {
        try {
            $admin = new Admin();
            return $admin->consultantList($param, $this->gIdx);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //특정 회사 조회(식별자)
    function searchTicketsData($param): array
    {
        try {
            $admin = new Admin();
            return $admin->searchTicketsData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //특정 사용자 조회(식별자)
    function searchCompany($param): array
    {
        try {
            $admin = new Admin();
            return $admin->searchCompany($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 사용처 대량등록
    function uploadOfferCompanyDb($param) : array
    {
        $this->desc = 'uploadOfferCompanyDb';
        $param['category'] = 'I';
        try {
            $admin = new Admin();
            return $admin->uploadOfferCompanyDb($param);
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

    //TODO:: 페이지 세팅 ->공통으로 올려도 무방한 듯 보이는데 확인할것
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