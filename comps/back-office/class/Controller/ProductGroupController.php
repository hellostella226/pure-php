<?

namespace Controller;

use Base;
use Model\Product;
use Model\ProductGroup;

class ProductGroupController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        if (isset($_GET['api']) && $_GET['api'] === 'Y') {
            $pagination = [
                'pageUrl' => "/abc/productGroup?api=Y",
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
                && in_array($_GET['searchColumn'], ['productGroupCode', 'productGroupName'])
            ) {
                $search['column'] = $_GET['searchColumn'];
                $search['value'] = htmlspecialchars($_GET['searchValue']);
            }

            $params = [
                'pagination' => $pagination,
                'search' => $search
            ];

            $productGroup = new ProductGroup();
            $result = $productGroup->selectProductGroupList($params);
            $pagination['totalCnt'] = $productGroup->getProductGroupCnt($params);
            $productCategory = $productGroup->getProductCategory();

            $vars = [
                'controller' => 'productGroup',
                'pagination' => $pagination,
                'search' => $search,
                'list' => $result,
                'category' => $productCategory,
            ];

            response(201, "데이터 조회 성공", $vars);

        } else {
            view("productGroup.php", "***", ['controller' => 'productGroup']);
        }

    }

    public function insert()
    {
        if (empty($_POST['data'])) {
            error(441, "요청 데이터 누락 오류", []);
            exit;
        }

        $data = json_decode($_POST['data'], true);
        $params = array_filter($data);

        if (empty($params['productGroupName'])) {
            error(441, "필수 데이터 누락 오류", $params);
            exit;
        }

        if (empty($params['productList'])) {
            error(441, "할당 상품 누락 오류", $params);
            exit;
        }

        $productGroup = new ProductGroup();
        $productGroupIdx = $productGroup->insertProductGroup($params);

        response(202, "상품그룹 등록 성공", ['productGroupIdx' => $productGroupIdx]);
    }

    public function update(int $productGroupIdx)
    {
        if (!$productGroupIdx || !is_numeric($productGroupIdx)){
            error(441, "요청 데이터 누락 오류", [$productGroupIdx]);
            exit;
        }

        $params = [];
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $response = file_get_contents('php://input');
            $params = ($response) ? json_decode($response, true) : [];
        }
        if (empty($params['productGroupName'])){
            error(441, "필수 데이터 누락 오류", ['productGroupIdx' => $productGroupIdx, 'params' => $response]);
        }

        $productGroup = new ProductGroup();
        $result = $productGroup->updateProductGroup($productGroupIdx, $params);
        if (!$result['result']){
            error(500, "서버 오류 (PDO)", ['productGroupIdx' => $productGroupIdx, 'params' => $response]);
            exit;
        }

        response(203, "상품그룹명 수정 성공", ['productGroupIdx' => $productGroupIdx]);
    }

    public function delete(int $productGroupIdx)
    {
        if (!$productGroupIdx || !is_numeric($productGroupIdx)){
            error(441, "요청 데이터 누락 오류", ['productGroupIdx' => $productGroupIdx]);
            exit;
        }

        $productGroup = new ProductGroup();
        $result = $productGroup->deleteProductGroup($productGroupIdx);
        if (!$result['result']){
            error(500, "서버 오류 (PDO)", ['productGroupIdx' => $productGroupIdx]);
            exit;
        }

        response(204, "상품 및 하위 항목 삭제 성공", ['productGroupIdx' => $productGroupIdx]);
    }

}