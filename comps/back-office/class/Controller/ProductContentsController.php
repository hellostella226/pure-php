<?

namespace Controller;

use Base;
use Model\Product;

class ProductContentsController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        $response = [];
        if (!isset($_GET['productIdx']) || !is_numeric($_GET['productIdx'])) {
            response(451, '요청 데이터 누락 오류', []);
            exit;
        }
        $productIdx = (int)$_GET['productIdx'];

        $product = new Product();
        $response['productInfo'] = $product->getProduct($productIdx);
        $response['productCatalog'] = $product->getProductCatalog($productIdx);

        if (empty($response)) {
            response(451, '필수 데이터 오류', ['productIdx' => $productIdx]);
            exit;
        }
        response(201, 'success', $response);
    }
}