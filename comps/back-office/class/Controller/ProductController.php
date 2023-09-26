<?

namespace Controller;

use Base;
use Model\Product;

class ProductController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        if (isset($_GET['api']) && $_GET['api'] === 'Y') {
            $pagination = [
                'pageUrl' => "/abc/product?api=Y",
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
                && in_array($_GET['searchColumn'], ['productCode', 'categoryName', 'productName'])
            ) {
                $search['column'] = $_GET['searchColumn'];
                $search['value'] = htmlspecialchars($_GET['searchValue']);
            }

            $params = [
                'pagination' => $pagination,
                'search' => $search
            ];

            $product = new Product();
            $result = $product->selectProductList($params);
            $pagination['totalCnt'] = $product->getProductCnt($params);
            $productCategory = $product->getProductCategory();

            $vars = [
                'controller' => 'product',
                'pagination' => $pagination,
                'search' => $search,
                'list' => $result,
                'category' => $productCategory,
            ];

            response(201, "데이터 조회 성공", $vars);

        } else {
            view("product.php", "***", ['controller' => 'product']);
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

        if (!isset($params['categoryIdx'], $params['productName'])) {
            error(441, "필수 데이터 누락 오류", $params);
            exit;
        }

        $product = new Product();
        $productIdx = $product->insertProduct($params);

        response(202, "상품 등록 성공", ['productIdx' => $productIdx]);
    }

    public function update(int $productIdx)
    {
        if (!$productIdx || !is_numeric($productIdx)){
            error(441, "요청 데이터 누락 오류", [$productIdx]);
            exit;
        }

        $params = [];
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] === "PUT") {
            $response = file_get_contents('php://input');
            $params = ($response) ? json_decode($response, true) : [];
        }
        if (empty($params)){
            error(441, "필수 데이터 누락 오류", ['productIdx' => $productIdx, 'params' => $response]);
        }

        $product = new Product();
        $result = $product->updateProduct($productIdx, $params);

        if (!$result['result']){
            if (isset($result['detail'])){
                error($result['detail']['code'], $result['detail']['msg'], ['productIdx' => $productIdx, 'params' => $response]);
                exit;
            }
            error(500, "서버 오류 (PDO)", ['productIdx' => $productIdx, 'params' => $response]);
            exit;
        }

        response(203, "상품 및 하위 항목 수정 성공", ['productIdx' => $productIdx]);
    }

    public function delete(int $productIdx)
    {
        if (!$productIdx || !is_numeric($productIdx)){
            error(441, "요청 데이터 누락 오류", []);
            exit;
        }

        $product = new Product();
        $result = $product->deleteProduct($productIdx);

        if (!$result['result']){
            error(500, "서버 오류 (PDO)", [$productIdx]);
            exit;
        }

        response(204, "상품 및 하위 항목 삭제 성공", ['productIdx' => $productIdx]);
    }

}