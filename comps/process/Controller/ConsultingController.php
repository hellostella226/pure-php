<?

namespace Controller;

use Model\Consulting;

class ConsultingController extends ServiceController
{
    function __construct()
    {
        parent::__construct();
    }

    // 프로세스 처리부
    public function requestProcess()
    {
        try {
            $param = parent::getParam();
            $response = [];

            switch ($param['process']) {
                // 약관동의
                case 'agreement':
                    $response = parent::agreement($param);
                    break;
                // 상담 요일/시간 선택
                case 'consult':
                    $response = $this->reserveConsult($param);
                    break;
                // MemberStatus 입력 및 갱신
                case 'MemberStatus':
                    $response = parent::MemberStatus($param);
                    break;
                // 상담사 설명 또는 나중에 신청 선택
                case 'updateEvent' :
                    $response = parent::updateEvent($param);
                    break;
                // 상담 날짜/시간 선택
                case 'consultDetail' :
                    $response = $this->reserveConsultDetail($param);
                    parent::updateEvent($param);
                    break;
                // 알림톡 전송
                case 'sendBizM' :
                    $response = parent::sendBizMMessage($param);
                    break;
                default:
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

    /**
     * **상담 요일/시간 입력
     */
    function reserveConsult($param): array
    {
        try {
            $consulting = new Consulting();
            return $consulting->reserveConsult($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * **상담 날짜 또는 시간 입력
     */
    function reserveConsultDetail($param): array
    {
        try {
            $consulting = new Consulting();
            return $consulting->reserveConsultDetail($param);

        } catch (\Exception $e) {
            throw $e;
        }
    }

}