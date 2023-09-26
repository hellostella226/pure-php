<?

namespace Controller;
use Model\Survey;

class SurveyController extends ServiceController
{
    function __construct() {
        parent::__construct();
    }

    /**
     * 프로세스 처리부
     */
    public function requestProcess()
    {
        try {
            $param = parent::getParam();
            $response = [];

            switch ($param['process']) {
                case 'survey':
                    $response = $this->insertSurveyEvent($param);
                    break;
                // MemberStatus 입력 및 갱신
                case 'MemberStatus':
                    $response = parent::MemberStatus($param);
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

    function insertSurveyEvent($param): array
    {
        try {
            $survey = new Survey();
            return $survey->insertSurveyEvent($param);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}