<?

namespace Controller;
use Model\Pay;

class PayController extends ServiceController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function definedDestination(): void
    {
        try {
            $page = '';
            if ($this->gCode) {
                parent::setProductGroupIdx();
                $deviceType = (deviceCheck() === 'pc') ? "P" : "M";
                $page = "pay_page/pc_{$this->productGroupIdx}.php";
                if ($deviceType === 'M') {
                    $page = "pay_page/mobile_{$this->productGroupIdx}.php";
                }
            }
        } catch (\Exception $e) {
            $page = 'error_500.html';
        } finally {
            parent::views($page);
        }
    }

    public function requestProcess()
    {
        try {
            $param = parent::getParam();
            $response = [];

            // 모바일 KCP 모듈 호출 이후 request 처리
            // pay.js > chk_pay() 처리와 동일
            if (!isset($param['process'])) {
                $param['process'] = $param['param_opt_1'];
            }
            if (isset($param['res_cd']) && $param['res_cd'] != '0000' && $param['process'] != 'complete') {
                $this->definedDestination();
                exit;
            }

            switch ($param['process']) {
                case 'registerPayOrder':
                    $response = $this->registerPayOrder($param);
                    break;
                case 'tryKcpAuth':
                    $response = $this->tryKcpAuth($param);
                    $bizMParam = $this->getBizMParam($response['data']);
                    if ($bizMParam) {
                        parent::sendClientBizM($bizMParam, 41);
                        parent::sendClientBizM($bizMParam, 42);
                    }
                    $page = "pay_page/success.php";
                    parent::views($page);
                    exit;
                case 'registerPayTrade':
                    $response = $this->registerPayTrade($param);
                    break;
                case 'complete' :
                    $bizMParam = $this->getBizMParam($param);
                    if ($bizMParam) {
                        parent::sendClientBizM($bizMParam, 41);
                        parent::sendClientBizM($bizMParam, 42);
                    }
                    $page = "pay_page/success.php";
                    parent::views($page);
                    exit;
            }
            if (count($response) > 0) {
                foreach ($response as $key => $val){
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

    /**
     * BizM 파라미터 만들기 ('KCP 결제' & '0원 결제' 두 가지 경우)
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function getBizMParam($param): array
    {
        try {
            $pay = new Pay();
            return $pay->getBizMParam($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 결제 등록(PC / MO)
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function registerPayOrder($param): array
    {
        try {
            $pay = new Pay();
            return $pay->registerPayOrder($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 결제 완료(PC / MO)
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function tryKcpAuth($param): array
    {
        try {
            $pay = new Pay();
            return $pay->tryKcpAuth($param);
        } catch (\Exception $e) {
            $this->alert($e->getMessage(), $param['Ret_URL']);
            exit;
        }
    }

    /**
     * 주문 데이터 생성
     * @param $param
     * @return array
     */
    public function registerPayTrade($param): array
    {
        try {
            $pay = new Pay();
            return $pay->registerPayTrade($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}