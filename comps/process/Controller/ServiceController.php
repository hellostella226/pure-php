<?

namespace Controller;
use Exception;
use Model\Base;

class ServiceController extends \CustomException
{
    public int $headCode = 0;
    public $code = "400";
    public array $data = [];
    public string $msg = "";
    public string $desc = "";
    public array $param = [];
    // 하드코딩이 불가피한 메인 분류값, 해당 분류값에 따라 페이지 타입을 정함
    public array $parentsProducts = [
        1 => 'gene',
        7 => 'survey',
        12 => '***',
        13 => 'consulting',
        29 => 'pay',
    ];
    // url파라미터 : 병원코드
    public string $hCode = '';
    public string $eCode = '';
    public string $gCode = '';
    public string $couponCode = '';
    public string $ClientControlIdx = '';
    public string $productGroupIdx = '';
    public int $eventItemIdx = 0;

    function __construct()
    {
        parent::__construct($this->message, $this->code);
        $this->hCode = $_REQUEST['hCode'] ?? "";
        $this->eCode = $_REQUEST['eCode'] ?? "";
        $this->gCode = $_REQUEST['gCode'] ?? "";
        $this->setHeadCode($_SERVER['HTTP_HOST']);
        //response code 앞 세팅
        //요청 파라미터 세팅
        $this->setParam($_REQUEST);
    }

    // 오류 입력
    function errorLog($e)
    {
        $base = new Base();
        $base->errorLog($e->getMessage(), $e->getCode(), $this->data);
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

    // 거래처 사용량 갱신
    function updateIssueCnt($param): array
    {
        try {
            $base = new Base();
            return $base->updateIssueCnt($param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 거래처 사용량 및 사용기간 체크
    function checkClientCustomerStatus($param): array
    {
        try {
            $base = new Base();
            return $base->checkClientCustomerStatus($param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 알림톡 전송
    function sendBizMMessage($param): array
    {
        try {
            $base = new Base();
            return $base->sendBizMMessage($param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 알림톡 전송 (ClientControl)
    function sendClientBizM($param, $processStep): array
    {
        try {
            $base = new Base();
            return $base->sendClientBizM($param, $processStep);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 회원 상태 갱신
    function MemberStatus($param): array
    {
        try {
            $base = new Base();
            return $base->MemberStatus($param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 상품별 동의여부 저장
    function agreement($param): array
    {
        try {
            $base = new Base();
            return $base->agreement($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 회원 상태 조회
    function checkUserStatus($param): array
    {
        try {
            $base = new Base();
            return $base->checkUserStatus($param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 이벤트 데이터 저장
    function event($param): array
    {
        try {
            $base = new Base();
            return $base->event($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 이벤트 데이터 업데이트
    function updateEvent($param): array
    {
        try {
            $base = new Base();
            return $base->updateEvent($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 회원 찾기 : 조회-Y->response, N->regist->response
    public function findMembers($param): array
    {
        try {
            $base = new Base();
            return $base->findMembers($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹식별자 정의
    function setProductGroupIdx(): void
    {
        try {
            if (!$this->hCode && !$this->gCode) {
                throw new \Exception("유효한 경로가 아닙니다.", "404");
            }

            $type = "";
            $code = "";
            $base = new Base();
            if ($this->hCode) {
                $type = "hCode";
                $code = $this->hCode;
            }
            if ($this->gCode) {
                $type = "gCode";
                $code = $this->gCode;
            }
            $this->productGroupIdx = $base->getProductGroupIdx($code, $type);
            if (!$this->productGroupIdx) {
                throw new \Exception("올바른 경로가 아닙니다.", "404");
            }

            $serviceStatus = $base->checkProductGroupStatus($this->productGroupIdx);

            //ServiceStatus값이 3(운영 종료)인 경우 운영 종료 안내페이지로 이동하도록 수정
            //ServiceStatus값이 2인 경우 서비스 점검중 페이지로 이동
           if ($serviceStatus === 3) {
                throw new \Exception("검사 서비스 운영이 종료되었습니다.", "204");
           } else if ($serviceStatus === 2) {
               throw new \Exception("서비스 점검 중 입니다.", "203");
           }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 검사요약결과 페이지 정의
    function setEventItem(): void
    {
        try {
            if (!$this->eCode) {
                throw new \Exception("유효한 경로가 아닙니다.", "404");
            }
            $base = new Base();
            $this->eventItemIdx = $base->getEventItem($this->eCode)['EventItemManageIdx'];
            if (!$this->eventItemIdx) {
                throw new \Exception("올바른 경로가 아닙니다.", "404");
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // gCode 및 couponCode
    public function getGoodsInfo()
    {
        try {
            if (!$this->gCode) {
                throw new \Exception("유효한 경로가 아닙니다.", "404");
            }
            $base = new Base();
            $response = $base->getGoodsInfo($this->gCode, $this->param);
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }

        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo $this->jsonResponse();
            exit;
        }
    }

    // 거래처코드 별 상품 리스트 조회
    public function getProductItemList(): array
    {
        try {
            if (!$this->hCode) {
                throw new Exception("유효한 경로가 아닙니다.", "404");
            }
            $base = new Base();
            $response = $base->getProductItemList($this->hCode);
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }

        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo $this->jsonResponse();
            exit;
        }
    }

    // OrderIdx 조회
    function getOrderInfo(): array
    {
        try {
            if (!$this->eCode || !isset($this->param['orderIdx'], $this->param['UsersIdx'])) {
                throw new Exception("유효한 경로가 아닙니다.", "404");
            }
            $base = new Base();
            $response = $base->getOrderInfo($this->eCode, $this->param);
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }

        } catch (\PDOException $PDOException) {
            $this->code = "500";
            $this->msg = "Internal Server Error";
            $this->errorLog($PDOException);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
            $this->errorLog($e);
        } finally {
            echo $this->jsonResponse();
            exit;
        }
    }

    // json 응답
    public function jsonResponse(): string
    {
        $res = [
            'code' => $this->headCode . $this->code,
            'data' => $this->data,
            'message' => $this->msg,
            'desc' => $this->desc
        ];
        return json_encode($res, true/*JSON_UNESCAPED_UNICODE*/);
    }

    // REQUEST 파라미터 세팅
    function setParam($request)
    {
        $reg = $request['reg'] ?? '';
        if ($reg) {
            $requestDecrypt = explode('/', Decrypt($reg));
            $UsersIdx = $requestDecrypt[0];
            $orderIdx = $requestDecrypt[1];
            if (!is_numeric($UsersIdx) || !is_numeric($orderIdx)) {
                alert('잘못된 접근입니다.');
                exit;
            }
            $this->param['UsersIdx'] = $UsersIdx;
            $this->param['orderIdx'] = $orderIdx;
        }
        foreach ($request as $key => $val) {
            $this->param[$key] = $val;
        }
    }

    // 파라미터 조회
    function getParam(): array
    {
        return $this->param;
    }

    /**
     * @date 2023-05-09
     * @brief View 페이지 할당
     * @author hellostellaa
     */
    function views($views = ''): void
    {
        $filename = $_SERVER['DOCUMENT_ROOT'] . "/process/View/{$views}";
        if ($views && file_exists($filename) !== false) {
            if (strpos($views, 'mobile')) {
                header("Progma:no-cache");
                header("Cache-Control: no-store, no-cache ,must-revalidate");
            }
            require_once $filename;
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/process/View/error_500.html';
        }
    }

    function link($views)
    {
        $filename = $_SERVER['DOCUMENT_ROOT'] . "/process/View/{$views}";
        if (file_exists($filename)) {
            echo "<script type='text/javascript'>location.href='http://ld.***.com/process/View/error_500.html';</script>";
            exit;
        }
    }

    /**
     * @date 2023-05-09
     * @brief 얼럿 후 종료
     * @param msg: 얼럿메시지, redirect: 리디렉션 경로 (옵션)
     * @author hellostellaa
     */
    function alert($msg, $redirect = '')
    {
        echo "<script type='text/javascript'>alert('{$msg}');</script>";
        if ($redirect) {
            echo "<script type='text/javascript'>location.href='{$redirect}';</script>";
        }
        exit;
    }
}