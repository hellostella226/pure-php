<?

namespace Controller;

use Base;
use SpreadsheetFactory;
use Model\InsuranceItem;

class InsuranceItemController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        if (isset($_GET['api']) && $_GET['api'] === 'Y') {
            $pagination = [
                'pageUrl' => "/abc/insuranceItem?api=Y",
                'page' => 1,
                'rowNum' => 1,
            ];
            if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                $pagination['page'] = (int)$_GET['page'];
            }
            if (isset($_GET['rowNum']) && in_array($_GET['rowNum'], [50, 100, 200, 300, 500, 1000, 1500, 2000, 2500, 3000])) {
                $pagination['rowNum'] = (int)$_GET['rowNum'];
            }
            $pagination['startNo'] = (($pagination['page'] - 1) * $pagination['rowNum']);

            $search = [
                'column' => "",
                'value' => "",
            ];
            if (
                isset($_GET['searchColumn'], $_GET['searchValue'])
                && in_array($_GET['searchColumn'], ['ibCompany', 'insuranceIdx', 'insuranceCode', 'insuranceName', 'itemCode', 'itemName'])
            ) {
                $search['column'] = $_GET['searchColumn'];
                $search['value'] = htmlspecialchars($_GET['searchValue']);
            }

            $params = [
                'pagination' => $pagination,
                'search' => $search
            ];

            $insuranceItem = new InsuranceItem();
            $result = $insuranceItem->selectInsuranceItemList($params);
            $pagination['totalCnt'] = $insuranceItem->getInsuranceItemCnt($params);
            $ibCompanyList = $insuranceItem->getServiceCompanyList();

            $vars = [
                'controller' => 'insuranceItem',
                'pagination' => $pagination,
                'search' => $search,
                'list' => $result,
                'ibCompany' => $ibCompanyList,
            ];

            response(201, "데이터 조회 성공", $vars);

        } else {
            view("insuranceItem.php", "***", ['controller' => 'insuranceItem']);
        }

    }

    public function insert()
    {
        if (empty($_FILES)) {
            error(441, "업로드 파일 첨부 오류", []);
            exit;
        }

        if ($_FILES['registerInsuranceList']['tmp_name'] === '') {
            error(441, "업로드 파일 누락 오류", []);
            exit;
        }

        if (!in_array($_FILES['registerInsuranceList']['type'], [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.ms-excel'
        ])) {
            error(442, '파일 형식이 올바르지 않습니다.', []);
            exit;
        }

        if ($_FILES['registerInsuranceList']['size'] > 500000) {
            error(442, "업로드 파일크기 오류", []);
            exit;
        }

        if (!isset($_POST['registerType'], $_POST['ibCompanyIdx'])) {
            error(441, "필수 데이터 누락 오류", []);
            exit;
        }

        if (!in_array($_POST['registerType'], ['insurance', 'item'])) {
            error(441, "필수 데이터 오류", []);
            exit;
        }

        if ($_POST['registerType'] === 'insurance' && !is_numeric($_POST['ibCompanyIdx'])){
            error(441, "필수 데이터 오류", []);
            exit;
        }

        $registerType = $_POST['registerType'];
        $ibCompanyIdx = (int)$_POST['ibCompanyIdx'];

        $serverFilename = $_FILES['registerInsuranceList']['tmp_name'];
        $pcFilename = $_FILES['registerInsuranceList']['name'];

        $spreadsheet = new SpreadsheetFactory();
        $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
        if ($result['code'] !== 200) {
            error(442, $result['msg'], $result['data']);
            exit;
        }

        $spreadData = $result['data'];
        $column = array_shift($spreadData);
        if (count($spreadData) === 0) {
            error(441, "업로드 파일 empty 오류", []);
            exit;
        }

        $diff = [];
        if ($registerType === 'insurance') {
            $insuranceColumn = ['**사 코드', '**사명'];
            $diff = array_diff($insuranceColumn, $column);
        } elseif ($registerType === 'item') {
            $itemColumn = ['**사 식별코드', '상품코드', '상품명'];
            $diff = array_diff($itemColumn, $column);
        }
        if (count($diff) !== 0) {
            error(442, "엑셀 형식 오류", []);
            exit;
        }

        $response = [];
        $insuranceItem = new InsuranceItem();
        if ($registerType === 'insurance') {
            $response = $insuranceItem->insertInsurance($ibCompanyIdx, $spreadData);
        } elseif ($registerType === 'item') {
            $response = $insuranceItem->insertInsuranceItem($spreadData);
        }

        response(202, "** 등록", $response);
    }

    public function update(int $insuranceIdx)
    {

        if (!$insuranceIdx || !is_numeric($insuranceIdx)) {
            error(1404, "요청 데이터 누락 오류", [$insuranceIdx]);
            exit;
        }

        $params = [];
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $response = file_get_contents('php://input');
            $params = ($response) ? json_decode($response, true) : [];
        }

        if (empty($params)) {
            error(441, "필수 데이터 누락 오류", ['insuranceIdx' => $insuranceIdx, 'params' => $response]);
        }

        if (!is_numeric($params['insuranceIdx']) || !is_numeric($params['itemIdx'])) {
            error(442, "필수 데이터 형식 오류", ['insuranceIdx' => $insuranceIdx, 'params' => $response]);
        }

        if (
            ($params['insuranceIdx'] && (!$params['insuranceCode'] || !$params['insuranceName']))
            || ($params['itemIdx'] && (!$params['itemCode'] || !$params['itemName']))
        ) {
            error(441, "필수 데이터 누락 오류", ['insuranceIdx' => $insuranceIdx, 'params' => $response]);
        }

        $insuranceItem = new InsuranceItem();
        $result = $insuranceItem->updateInsuranceItem($insuranceIdx, $params);

        if (!$result['result']) {
            if (isset($result['detail'])) {
                error($result['detail']['code'], $result['detail']['msg'], ['insuranceIdx' => $insuranceIdx, 'params' => $response]);
                exit;
            }
            error(500, "서버 오류 (PDO)", ['insuranceIdx' => $insuranceIdx, 'params' => $response]);
            exit;
        }

        response(203, "**사 및 **상품 수정 성공", ['insuranceIdx' => $result['insuranceIdx']]);
    }

    public function delete(int $insuranceIdx)
    {
        if (!$insuranceIdx || !is_numeric($insuranceIdx)) {
            error(441, "요청 데이터 누락 오류", []);
            exit;
        }

        $insuranceItem = new InsuranceItem();
        $result = $insuranceItem->deleteInsuranceItem($insuranceIdx);

        if (!$result['result']) {
            if (isset($result['detail'])) {
                error($result['detail']['code'], $result['detail']['msg'], ['insuranceIdx' => $insuranceIdx]);
                exit;
            }
            error(500, "서버 오류 (PDO)", [$insuranceIdx]);
            exit;
        }

        response(1201, "**사 또는 **상품 삭제 성공", ['insuranceIdx' => $insuranceIdx]);
    }

}