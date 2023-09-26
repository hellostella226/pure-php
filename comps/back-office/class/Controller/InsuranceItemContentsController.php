<?

namespace Controller;

use Base;
use Model\InsuranceItem;

class InsuranceItemContentsController extends Base
{
    public function __construct()
    {
    }

    public function select()
    {
        $response = [];
        $insuranceItem = new InsuranceItem();

        if (isset($_GET['insuranceIdx']) && is_numeric($_GET['insuranceIdx'])) {
            $insuranceIdx = (int)$_GET['insuranceIdx'];

            $response = $insuranceItem->getInsuranceItem($insuranceIdx);
            if (empty($response)) {
                response(451, '조회되는 **정보가 없습니다.', ['insuranceIdx' => $insuranceIdx]);
                exit;
            }
        }

        response(201, 'success', $response);
    }
}