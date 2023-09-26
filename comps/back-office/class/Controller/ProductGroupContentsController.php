<?

namespace Controller;

use Base;
use Model\ProductGroup;

class ProductGroupContentsController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        $response = [];
        $productGroup = new ProductGroup();

        if (isset($_GET['categoryIdx']) && is_numeric($_GET['categoryIdx'])) {
            $categoryIdx = (int)$_GET['categoryIdx'];

            $response = $productGroup->getProductList($categoryIdx);
            if (empty($response)) {
                response(451, '조회되는 상품이 없습니다.', ['categoryIdx' => $categoryIdx]);
                exit;
            }
        }

        if (isset($_GET['productGroupIdx']) && is_numeric($_GET['productGroupIdx'])) {
            $productGroupIdx = (int)$_GET['productGroupIdx'];

            $response = $productGroup->getClientCustomerList($productGroupIdx);
            if (empty($response)) {
                response(451, '소속된 거래처 병원이 없습니다.', []);
                exit;
            }
        }

        response(201, 'success', $response);
    }
}