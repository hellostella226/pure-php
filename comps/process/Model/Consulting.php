<?
namespace Model;

class Consulting extends Base
{
    public ?object $conn = null;

    function __construct()
    {
        parent::__construct();
        $this->conn = (new PDOFactory)->PDOCreate();
    }

    /**
     * **상담 요일/시간 입력
     * @param $param
     * @return array
     */
    function reserveConsult($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $status = [
                'process' => 'E',
                'statusCode' => '20009',
            ];
            //상태정보 갱신
            $this->MemberStatus($param, $status);

            // Check POST Data
            if (!isset($param['UsersIdx'], $param['orderIdx'], $param['appointmentDay'], $param['appointmentHour'])) {
                throw new \Exception("필수 파라미터가 존재하지 않습니다.", '20009');
            }

            // Check Data
            $appointmentDay = parent::checkDayofWeek($param['appointmentDay']);
            $appointmentHour = parent::checkHour($param['appointmentHour']);
            if (!$appointmentDay || !$appointmentHour) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", '20009');
            }

            $sql = "INSERT INTO abc.Consultant (
                        UsersIdx, OrderIdx, AppointmentHour, AppointmentDay)
                    VALUES (
                        :UsersIdx, :orderIdx, :appointmentHour, :appointmentDay)
                    ON DUPLICATE KEY UPDATE
                        AppointmentHour = VALUE(AppointmentHour),
                        AppointmentDay = VALUE(AppointmentDay),
                        ModDatetime = NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':appointmentHour', $appointmentHour, $this->conn::PARAM_INT);
            $stmt->bindValue(':appointmentDay', $appointmentDay, $this->conn::PARAM_INT);
            $stmt->execute();

            $status = [
                'process' => 'E',
                'statusCode' => '20000',
            ];
            //상태정보 갱신
            $this->MemberStatus($param, $status);

            $this->desc = "reserveConsult";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * **상담 날짜 또는 시간 입력
     * @param $param
     * @return array
     */
    function reserveConsultDetail($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            // Check POST Data
            if (!isset($param['UsersIdx'], $param['orderIdx'])) {
                throw new \Exception("필수 파라미터가 존재하지 않습니다.", '404');
            }

            $appointmentDate = $param['appointmentDate'] ?? '';
            $appointmentHour = parent::checkHour($param['appointmentHour']);
            // Check Data
            if (!$appointmentDate && !$appointmentHour) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", '404');
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "UPDATE abc.Consultant SET ";
            if ($appointmentDate) {
                $sql .= " AppointmentDate = '{$appointmentDate}' ";
            }
            if ($appointmentHour) {
                if ($appointmentDate) {
                    $sql .= " , ";
                }
                $sql .= " AppointmentHour = {$appointmentHour} ";
            }
            $sql .= " WHERE (UsersIdx, OrderIdx) = (:UsersIdx, :orderIdx)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->desc = "reserveConsultDetail";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

}