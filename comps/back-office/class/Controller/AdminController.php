<?

namespace Controller;

use Model\Admin;

class AdminController
{
    public int $headCode = 0;
    public array $data = [];
    public string $code = "0";
    public array $param = [];
    public string $msg = '';
    public string $desc = '';
    public string $productGroupCode = '';
    public string $gIdx = '';
    public array $menu = [];
    public array $navi = [
        '***' => [
            ['id' => 'product', 'name' => '상품관리',
                'sub' => [
                    'item' => ['id' => 'item', 'name' => '품목관리'],
                    'group' => ['id' => 'group', 'name' => '그룹관리'],
                ],
            ],
            ['id' => 'company', 'name' => '병원정보'],
            ['id' => 'Members', 'name' => '회원정보'],
            ['id' => 'disease', 'name' => '질환검사'],
            ['id' => 'genetic', 'name' => 'xxx검사신청'],
            ['id' => 'agreementFail', 'name' => 'xxx미발송'],
            ['id' => 'telephone', 'name' => '전화상담'],
            ['id' => 'sms', 'name' => '알림톡'],
            ['id' => 'insureib', 'name' => '**IB관리'],
            ['id' => 'consulting', 'name' => '**상담결과'],
            ['id' => 'insurance', 'name' => '**거래처'],
            ['id' => 'insuranceItem', 'name' => '**상품관리'],
        ],
        'bioage' => [
            ['id' => 'product', 'name' => '상품관리',
                'sub' => [
                    'item' => ['id' => 'item', 'name' => '품목관리'],
                    'group' => ['id' => 'group', 'name' => '그룹관리'],
                ],
            ],
            ['id' => 'Members', 'name' => '회원정보'],
            ['id' => '***', 'name' => '테스트정보'],
            ['id' => 'sms', 'name' => '알림톡'],
        ],
        'pharmacy' => [
            ['id' => 'product', 'name' => '상품관리',
                'sub' => [
                    'item' => ['id' => 'item', 'name' => '품목관리'],
                    'group' => ['id' => 'group', 'name' => '그룹관리'],
                ],
            ],
            ['id' => 'company', 'name' => '사용처정보'],
            ['id' => 'Members', 'name' => '회원정보'],
            ['id' => 'bioage', 'name' => '질환검사'],
            ['id' => 'consulting', 'name' => '**상담예약'],
            ['id' => 'survey', 'name' => '설문응답'],
            ['id' => 'supplement', 'name' => '맞춤영양'],
            ['id' => 'summaryResult', 'name' => '요약검사결과'],
            ['id' => 'sms', 'name' => '알림톡'],
            ['id' => 'insure', 'name' => '**관리',
                'sub' => [
                    'insureib' => ['id' => 'insureib', 'name' => '**IB할당'],
                    'history' => ['id' => 'history', 'name' => '**할당이력'],
                    'consultingResult' => ['id' => 'consultingResult', 'name' => '**상담결과'],
                    'insurance' => ['id' => 'insurance', 'name' => '**거래처등록'],
                    'insuranceItem' => ['id' => 'insuranceItem', 'name' => '**상품관리'],
                ],
            ],
        ],
        'offer***' => [
            ['id' => 'goods', 'name' => '결제경로 관리'],
            ['id' => 'payment', 'name' => '결제내역관리'],
            ['id' => 'coupon', 'name' => '쿠폰 관리',
                'sub' => [
                    'registCoupon' => ['id' => 'registCoupon', 'name' => '생성관리'],
                    'couponList' => ['id' => 'couponList', 'name' => '사용관리'],
                ],
            ],
            ['id' => 'company', 'name' => '상담사 관리'],
            ['id' => 'bioage', 'name' => '검사자 조회'],
        ],
    ];

    public function __construct($groupCode)
    {
        $this->setHeadCode($_SERVER['HTTP_HOST']);
        $this->setGroupInfo($groupCode);
    }

    // **IB관리 할당 엑셀 업로드
    function uploadDbAllocation($param) : array
    {
        $this->desc = 'uploadDbAllocation';
        try {
            $admin = new Admin();
            return $admin->uploadDbAllocation($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 일괄다운
    function allDown($param) : void
    {
        $this->desc = 'allDown';
        try {
            $admin = new Admin();
            $admin->allDown($param);
        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo "<script>alert('" . $this->msg . "');</script>";
            echo "<script>window.close();</script>";
            exit;
        }
    }

    // **IB관리 기간별 데이터 조회
    function ibAllocationData($param) : array
    {
        $this->desc = 'ibAllocationData';
        try {
            $admin = new Admin();
            return $admin->ibAllocationData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **IB관리 더보기 옵션 조회
    function searchIbUserData($param) : array
    {
        $this->desc = 'searchIbUserData';
        try {
            $admin = new Admin();
            return $admin->searchIbUserData($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // IB 다운
    function getIbReport($param): void
    {
        $this->desc = 'getIbReport';
        try {
            $admin = new Admin();
            $admin->getIbReport($param);
        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo "<script>alert('" . $this->msg . "');</script>";
            echo "<script>window.close();</script>";
            exit;
        }
    }

    // *** 다운
    function get***Report($param): void
    {
        $this->desc = 'get***Report';
        try {
            $admin = new Admin();
            $admin->get***Report($param);
        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo "<script>alert('" . $this->msg . "');</script>";
            echo "<script>window.close();</script>";
            exit;
        }
    }

    // 엑셀 다운로드 데이터 조회
    function excelFileDown($param): void
    {
        try {
            $admin = new Admin();
            $admin->excelFileDown($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //menu 세팅
    function setMenu(): array
    {
        try {
            $this->code = "200";
            $this->data = $this->menu;
            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹 정보 세팅
    function setGroupInfo($groupCode): void
    {
        try {
            $admin = new Admin();
            $response = $admin->setGroupInfo($groupCode)['data'];

            $this->productGroupCode = $response['ProductGroupCode'];
            $this->gIdx = $response['ProductGroupIdx'];
            $this->menu['navi'] = $this->navi;
            $this->menu['productGroupCode'] = $this->productGroupCode ?? '';
            $this->menu['productGroup'] = $response['ProductGroup'] ?? [];

        } catch (\Exception $e) {
            echo 'Critical Error';
        }
    }

    // 헤드코드 세팅
    function setHeadCode($httpHost)
    {
        if (strpos($httpHost, "api") !== false) {
            $this->headCode = 10;
        } elseif (strpos($httpHost, "ds") !== false) {
            $this->headCode = 21;
        } elseif (strpos($httpHost, "admin") !== false) {
            $this->headCode = 20;
        } elseif (strpos($httpHost, "mall") !== false) {
            $this->headCode = 41;
        } else {
            $this->headCode = 31;
        }
    }

    // json 응답
    public function jsonResponse(): string
    {
        $res = ['code' => $this->headCode . $this->code, 'data' => $this->data, 'message' => $this->msg, 'desc' => $this->desc];
        return json_encode($res, true);
    }

    // REQUEST 파라미터 세팅
    function setParam($request)
    {
        foreach ($request as $key => $val) {
            $this->param[$key] = $val;
        }
        $this->param['gIdx'] = $this->gIdx;
    }

    // 파라미터 조회
    function getParam(): array
    {
        return $this->param;
    }

    // 리턴
    function response(): array
    {
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg, 'desc' => $this->desc];
    }

    // 페이지 정의
    function views($views)
    {
        include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/header.php";
        include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/navbar.php"; //공통 네비로 변경
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/b***-*abc/resources/view/' . $this->productGroupCode . '/' . $views . '.php';
        if (file_exists($filename)) {
            include $filename;
        }
        include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/inc/footer.php";
    }

    // 링크 이동
    function link($views)
    {
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/process/View/' . $views;
        if (file_exists($filename)) {
            echo "<script type='text/javascript'>location.href='http://ld.g******com/process/View/error_500.html';</script>";
            exit;
        }
    }

    // 얼럿
    function alert($msg, $redirect)
    {
        echo "<script type='text/javascript'>alert('{$msg}');</script>";
        if($redirect < 0){
            echo "<script type='text/javascript'>window.close()</script>";
        }
        exit;
    }

    // 오류 입력
    function errorLog($e)
    {
        $admin = new Admin();
        $admin->errorLog($e->getMessage(), $e->getCode(), $this->data);
    }

    //qr다운로드
    function qrDown($param): void
    {
        $this->desc = 'qrDown';
        try {
            $admin = new Admin();
            $admin->qrDown($param);
        } catch (\Exception $e) {
            $this->alert($e->getMessage(), -1);
            exit;
        }
    }
}