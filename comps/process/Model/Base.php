<?

namespace Model;

class Base
{
    public $code = "200";
    public array $data = [];
    public string $msg = "";
    public string $desc = "";
    public ?object $conn = null;
    public string $bizMUrl = "https://***-abizmsg.kr";
    public string $bizMApiId = "***";
    public string $bizMApiKey = "";
    private string $testSendApiKey = "***";
    private string $bizMEarlyQApiKey = "***";
    private string $bizMCouponApiKey = "***";
    private string $bizMBioAgeApiKey = "***";
    public string $naverShortUrl = "https://opena***.com/v1/util/shorturl";
    private string $naverId = "***";
    private string $naverSecret = "***";
    public string $payOrder = "P";
    public string $siteCd = "AJ***"; // 사이트코드 지정 완료
    public string $siteName = "***";

    public array $pattern = [
        'all' => '/^[가-힣a-zA-Z0-9\_]+$/',
        'code' => '/^[a-zA-Z0-9\_]+$/',
        'kor' => '/^[가-힣\s]+$/',
        'eng' => '/^[a-zA-Z\s]+$/',
        'num' => '/^[0-9]+$/',
        'email' => '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i',
    ];

    function __construct() {
        if (isDev) {
            $this->bizMUrl = "https://dev-***-abizmsg.kr:1443";
            $this->bizMApiKey = "***";
            $this->bizMApiId = "***";
            $this->payOrder = "T";
            $this->siteCd = "T0***";
            $this->siteName = "***";
        }
    }

    function response(): array
    {
        return ['code'=> $this->code, 'data'=> $this->data, 'msg'=> $this->msg, 'desc'=> $this->desc];
    }

    function createShortUrl($fullUrl)
    {
        try {
            $url = $this->naverShortUrl;
            $header = [
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                "X-Naver-Client-Id: {$this->naverId}",
                "X-Naver-Client-Secret: {$this->naverSecret}"
            ];
            $param = [
                'url' => $fullUrl
            ];
            $response = $this->curl("GET", $url, $header, $param);
            if ($response['code'] !== 200) {
                throw new \Exception("Naver Short Url 통신 실패", "400");
            }
            $result = json_decode($response['response'], true);

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // getPayItemInfo
    function getGoodsInfo(string $gCode, array $param = []): array
    {
        try {
            if (!$gCode) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        GM.ItemsIdx, GM.ServiceControlIdx, GM.ProductGroupIdx, 
                        GM.GoodsName, GM.SalesPrice 
                    FROM abc.Items AS GM 
                    JOIN abc.ProductGroup AS PG 
                      ON GM.ProductGroupIdx = PG.ProductGroupIdx 
                     AND PG.IsUse = b'1'
                    JOIN abc.ServiceControl AS SCM 
                      ON GM.ServiceControlIdx = SCM.ServiceControlIdx 
                   WHERE GM.ItemsIdx = :ItemsIdx 
                     AND GM.IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $gCode, $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$row) {
                throw new \Exception("해당 결제상품을 찾을 수 없습니다.", "404");
            }

            $productGroupIdx = $row['ProductGroupIdx'];
            $ServiceControlIdx = $row['ServiceControlIdx'];

            $payGoodsInfo = [
                'ItemsIdx' => $row['ItemsIdx'],
                'ServiceControlIdx' => $ServiceControlIdx,
                'productGroupIdx' => $productGroupIdx,
                'goodsName' => $row['GoodsName'],
                'salesPrice' => $row['SalesPrice'],
            ];

            $couponInfo = [];
            //쿠폰 데이터가 존재하는 경우
            if (isset($param['cpn'])) {
                $nowDate = date('Y-m-d');
                $sql = "SELECT 
                            icm.CouponIdx, cm.TicketsIdx, cm.CouponCode, cm.CouponType, 
                            cm.DiscountMethod, cm.DiscountAmount, cm.DiscountRate, cm.CouponName,
                            cm.ProductGroupIdx, cm.ServiceControlIdx, cm.ClientControlIdx, 
                            cm.CouponStatus
                        FROM abc.IssuedTickets AS icm
                        JOIN abc.Tickets AS cm 
                          ON cm.TicketsIdx = icm.TicketsIdx
                       WHERE icm.CouponCode = :couponCode 
                         AND cm.IsUse = b'1' 
                         AND cm.UseStartDate <= '{$nowDate}' 
                         AND cm.UseEndDate >= '{$nowDate}' 
                         AND cm.ProductGroupIdx = {$productGroupIdx}";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':couponCode', $param['cpn']);
                $stmt->execute();
                $row = $stmt->fetch($this->conn::FETCH_ASSOC);
                if ($row) {
                    //쿠폰 대상 체크
                    $couponUse = false;
                    $ccIdx = $param['ccIdx'] ?? NULL;
                    if (
                        $row['ClientControlIdx']
                        && $row['ClientControlIdx'] === $ccIdx
                    ) {
                        $couponUse = true;
                    }
                    if (
                        $row['ProductGroupIdx']
                        && $row['ProductGroupIdx'] === $productGroupIdx
                    ) {
                        $couponUse = true;
                    }
                    if (
                        $row['ServiceControlIdx']
                        && $row['ServiceControlIdx'] === $ServiceControlIdx
                    ) {
                        $couponUse = true;
                    }

                    if ($couponUse) {
                        if ($row['DiscountMethod'] === '1') {
                            $discount = (1 - ($row['DiscountRate'] / 100));
                        } else if ($row['DiscountMethod'] === '2') {
                            $discount = $row['DiscountAmount'];
                        }
                    }
                    $couponInfo = [
                        'couponIdx' => $row['CouponIdx'],
                        'TicketsIdx' => $row['TicketsIdx'],
                        'couponCode' => $row['CouponCode'],
                        'couponType' => $row['CouponType'],
                        'discountMethod' => $row['DiscountMethod'],
                        'discountAmount' => $row['DiscountAmount'],
                        'discountRate' => $row['DiscountRate'],
                        'discount' => $discount ?? 1,
                        'couponName' => $row['CouponName'],
                        'productGroupIdx' => $row['ProductGroupIdx'],
                        'ServiceControlIdx' => $row['ServiceControlIdx'],
                        'ClientControlIdx' => $row['ClientControlIdx'],
                        'couponStatus' => $row['CouponStatus'],
                    ];
                }
            }

            $clientInfo = [];
            if (isset($param['ccIdx'])) {
                $sql = "SELECT ClientControlIdx, ClientCustomerName, CCTel, CCManager, CCGroup
                          FROM abc.ClientControl
                         WHERE ClientControlIdx = :ClientControlIdx 
                           AND ProductGroupIdx = {$productGroupIdx}
                           AND ServiceControlIdx = {$ServiceControlIdx} 
                           AND IsUse = b'1'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ClientControlIdx', $param['ccIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch($this->conn::FETCH_ASSOC);
                if (!$row) {
                    throw new \Exception("재결제 대상자를 찾을수 없습니다.", "404");
                }
                $clientInfo = [
                    'ClientControlIdx' => $row['ClientControlIdx'],
                    'clientCustomerName' => $row['ClientCustomerName'],
                    'cCGroup' => $row['CCGroup'],
                    'cCManager' => $row['CCManager'],
                    'cCTel' => $row['CCTel'],
                ];
            }

            $deviceType = (deviceCheck() === 'pc') ? "P" : "M";
            list($microtime, $timestamp) = explode(' ', microtime());
            $time = $timestamp . substr($microtime, 2, 3);
            $this->data = [
                'deviceType' => $deviceType,
                'siteCd' => $this->siteCd,
                'siteName' => $this->siteName,
                'payOrder' => $this->payOrder . $deviceType . $gCode . $time . rand(10, 99),
                'payGoodsInfo' => $payGoodsInfo,
                'couponInfo' => $couponInfo,
                'clientInfo' => $clientInfo,
                'gCode' => $gCode,
                'ccIdx' => $param['ccIdx'] ?? 0,
                'cpn' => $param['cpn'] ?? '',
            ];

            $this->desc = "getGoodsInfo";
            $this->code = "200";
            $this->msg = "success";

            $this->conn = null;

            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상품그룹식별자 조회
    function getProductGroupIdx($code, $type): string
    {
        try {
            if (!preg_match($this->pattern['code'], $code)) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            if ($type === 'hCode') {
                $sql = "SELECT ProductGroupIdx
                        FROM abc.ClientControl
                        WHERE ClientCustomerCode = :hCode
                          AND IsUse = b'1'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':hCode', $code);
                $stmt->execute();
                $productGroupIdx = $stmt->fetch()['ProductGroupIdx'] ?? 0;
            }
            if ($type === 'gCode') {
                $sql = "SELECT ProductGroupIdx
                          FROM abc.Items
                         WHERE ItemsIdx = :gCode
                           AND IsUse = b'1'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':gCode', $code);
                $stmt->execute();
                $productGroupIdx = $stmt->fetch()['ProductGroupIdx'] ?? 0;
            }

            $this->conn = null;

            return (int)$productGroupIdx;
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상품그룹 서비스상태 조회
    function checkProductGroupStatus($productGroupIdx): int
    {
        try {
            if (!preg_match($this->pattern['num'], $productGroupIdx)) {
                throw new \Exception("유효한 경로가 아닙니다.", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "SELECT ServiceStatus
                    FROM abc.ProductGroup
                    WHERE ProductGroupIdx = :ProductGroupIdx
                      AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ProductGroupIdx', $productGroupIdx);
            $stmt->execute();
            $serviceStatus = (int)$stmt->fetch()['ServiceStatus'] ?? 0;

            $this->conn = null;

            return $serviceStatus;
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // ItemCategory 조회
    function getEventItem($eCode, $param = []): array
    {
        try {
            if (!preg_match($this->pattern['code'], $eCode)) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $eventItemInfo = [];

            // ItemCategory 조회
            $sql = "SELECT EventItemManageIdx, ItemCategory
                    FROM abc.EventItemManage
                    WHERE ItemCategory = :eCode";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':eCode', $eCode);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC);
            $eventItemInfo['EventItemManageIdx'] = (int)$row['EventItemManageIdx'] ?? 0;
            $eventItemInfo['ItemCategory'] = $row['ItemCategory'] ?? 0;

            // 주문 eventProcess 조회
            $orderEventInfo = [];
            if ($param) {
                $sql = "SELECT EventIdx, EventProcess FROM abc.Event
                        WHERE UsersIdx = :UsersIdx
                          AND OrderIdx = :orderIdx 
                          AND EventItemManageIdx = :eventItemManageIdx
                        ORDER BY EventIdx DESC
                        LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':eventItemManageIdx', $eventItemInfo['EventItemManageIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $orderEventInfo = $stmt->fetch($this->conn::FETCH_ASSOC);
            }
            $eventItemInfo['EventIdx'] = $orderEventInfo['EventIdx'] ?? 0;
            $eventItemInfo['EventProcess'] = $orderEventInfo['EventProcess'] ?? 'a';

            $this->conn = null;

            return $eventItemInfo;
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 병원코드에 해당하는 상품 정보 리스트 조회
    function getProductItemList($clientCustomerCode): array
    {
        try {
            if (!preg_match($this->pattern['code'], $clientCustomerCode)) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        p.ParentProductIdx, p.Gender, pg.ActivePoint, pg.CompletePoint,
                        ccm.ClientControlIdx, ccm.ParentClientCustomerIdx, ccm.ResponseType, ccm.SpecimenType, 
                        ccm.Category, ccm.CCManager, ccm.ServiceControlIdx,
                        pgm.ProductIdx, pgm.Sort, pgm.ProductGroupIdx
                    FROM abc.ClientControl AS ccm
                    JOIN abc.ProductGroup AS pg ON pg.ProductGroupIdx = ccm.ProductGroupIdx
                    JOIN abc.ProductGroupManage AS pgm ON pgm.ProductGroupIdx = pg.ProductGroupIdx
                    JOIN abc.Product AS p ON p.ProductIdx = pgm.ProductIdx
                    WHERE ccm.ClientCustomerCode = :ClientCustomerCode AND ccm.Depth = 2 AND ccm.IsActive = b'1'
                      AND pg.IsUse = b'1' AND p.IsUse = b'1'
                    ORDER BY pgm.Sort ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientCustomerCode', $clientCustomerCode);
            $stmt->execute();

            $activePoint = "";
            $completePoint = "";
            $ClientControlIdx = "";
            $ccManager = "";

            $productGroupIdx = 0;
            $specimenType = "";
            $ServiceControlIdx = 0;
            //$parentClientCustomerIdx = "";
            $products = [];
            $parentsProduct = [];
            while ($row = $stmt->fetch()) {
                $activePoint = (int)$row['ActivePoint'];
                $completePoint = (int)$row['CompletePoint'];
                $ClientControlIdx = (int)$row['ClientControlIdx'];
                $ccManager = $row['CCManager'];
                //$parentClientCustomerIdx = (int)$row['ParentClientCustomerIdx'];
                $productGroupIdx = (int)$row['ProductGroupIdx'];
                $specimenType = $row['SpecimenType'];
                $products[(int)$row['ParentProductIdx']][] = [
                    'ParentProductIdx' => (int)$row['ParentProductIdx'],
                    'ProductIdx' => (int)$row['ProductIdx'],
                    'Gender' => (int)$row['Gender'],
                    'ResponseType' => (int)$row['ResponseType'],
                    'Sort' => (int)$row['Sort']
                ];
                $parentsProduct[] = (int)$row['ParentProductIdx'];
                $ServiceControlIdx = (int)$row['ServiceControlIdx'];
            }


            $sql = "SELECT COUNT(SaleGoodsIdx) AS cnt FROM abc.IssuedSaleGoods
                    WHERE ClientControlIdx = :ClientControlIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $ClientControlIdx);
            $stmt->execute();
            $issueCnt = $stmt->fetch()['cnt'] ?? 0;

            $sql = "SELECT COUNT(SaleGoodsIdx) AS cnt FROM abc.ExpiredSaleGoods
                    WHERE ClientControlIdx = :ClientControlIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $ClientControlIdx);
            $stmt->execute();
            $issueUse = $stmt->fetch()['cnt'] ?? 0;

            $sql = "SELECT SaleGoodsIdx FROM abc.SaleGoods 
                    WHERE TicketType = 1  AND ClientControlIdx = :ClientControlIdx ORDER BY SaleGoodsIdx DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $ClientControlIdx);
            $stmt->execute();
            $SaleGoods = $stmt->fetch()['SaleGoodsIdx'] ?? 0;

            $ItemsIdx = 0;
            $couponCode = "";
            if ($SaleGoods) {
                $sql = "SELECT ItemsIdx, CouponCode FROM p.PayssItem 
                      WHERE ClientControlIdx = :ClientControlIdx AND RelatedPayOrderIdx IS NULL
                      AND OrderStatus = 1 ORDER BY PayOrderIdx DESC ";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ClientControlIdx', $ClientControlIdx);
                $stmt->execute();
                $payData = $stmt->fetch();
                $ItemsIdx = ($payData['ItemsIdx']) ?? 0;
                $couponCode = ($payData['CouponCode']) ?? '';
            }


            $this->data = [
                'ActivePoint' => $activePoint,
                'CompletePoint' => $completePoint,
                'ClientControlIdx' => $ClientControlIdx,
                'CCManager' => $ccManager,
                'ProductGroupIdx' => $productGroupIdx,
                'SpecimenType' => $specimenType,
                'ParentsProduct' => $parentsProduct,
                'Products' => $products,
                'IssueCnt' => (int)$issueCnt,
                'IssueUse' => (int)$issueUse,
            ];

            if ($ItemsIdx) {
                $this->data['ItemsIdx'] = (int)$ItemsIdx;
                $this->data['CouponCode'] = $couponCode;
            }

            $this->desc = "getProductItemList";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 검사요약페이지 최초 진입 시 orderIdx를 이용하여 회원정보 및 상태 조회
    function getOrderInfo($eCode, $param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        o.UsersIdx, o.PaysIdx, 
                        p.ParentProductIdx, p.Gender, pg.ActivePoint, pg.CompletePoint,
                        ccm.ClientControlIdx, ccm.ParentClientCustomerIdx, ccm.ResponseType, ccm.SpecimenType,
                        ccm.ClientCustomerName, ccm.State, ccm.City, ccm.FullCity, ccm.AddressDetail, ccm.CCTel, ccm.Category,
                        pgm.ProductIdx, pgm.Sort, pgm.ProductGroupIdx, cccm.CCMainTel
                    FROM o.Pays o
                    JOIN abc.Users mm ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.ClientControl ccm ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN abc.ProductGroup pg ON pg.ProductGroupIdx = o.ProductGroupIdx
                    JOIN abc.ProductGroupManage AS pgm ON pgm.ProductGroupIdx = pg.ProductGroupIdx
                    JOIN abc.Product AS p ON p.ProductIdx = pgm.ProductIdx
                    LEFT JOIN abc.ClientCustomerContractManage as cccm ON cccm.ClientControlIdx = ccm.ClientControlIdx
                    WHERE o.UsersIdx = :UsersIdx
                      AND o.PaysIdx = :orderIdx
                      AND o.IsActive = b'1'
                      AND mm.IsOut <> b'1'
                      AND pg.IsUse = b'1'
                      AND p.IsUse = b'1'
                    ORDER BY pgm.Sort ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];
            if (count($rows) === 0) {
                throw new \Exception("주문정보를 찾을 수 없습니다.", "404");
            }
            $UsersIdx = "";
            $activePoint = "";
            $completePoint = "";
            $ClientControlIdx = "";
            $clientCustomerName = "";
            $clientCustomerTel = "";
            $clientCustomerMainTel = "";
            $clientCustomerAddress = [
                'state' => "",
                'city' => "",
                'fullCity' => "",
                'addressDetail' => "",
            ];
            $category = "";
            $productGroupIdx = 0;
            $specimenType = "";
            //$parentClientCustomerIdx = "";
            $products = [];
            $parentsProduct = [];
            foreach ($rows as $row) {
                $UsersIdx = (int)$row['UsersIdx'];
                $orderIdx = (int)$row['OrderIdx'];
                $activePoint = (int)$row['ActivePoint'];
                $completePoint = (int)$row['CompletePoint'];
                $ClientControlIdx = (int)$row['ClientControlIdx'];
                $clientCustomerName = $row['ClientCustomerName'];
                $clientCustomerTel = $row['CCTel'];
                $clientCustomerMainTel = $row['CCMainTel'] ? $this->transposeTel($row['CCMainTel']) : '';
                $clientCustomerAddress['state'] = $row['State'];
                $clientCustomerAddress['city'] = $row['City'];
                $clientCustomerAddress['fullCity'] = $row['FullCity'];
                $clientCustomerAddress['addressDetail'] = $row['AddressDetail'];
                $category = $row['Category'];
                //$parentClientCustomerIdx = $row['ParentClientCustomerIdx'];
                $productGroupIdx = (int)$row['ProductGroupIdx'];
                $specimenType = $row['SpecimenType'];
                $products[(int)$row['ParentProductIdx']][] = [
                    'ParentProductIdx' => (int)$row['ParentProductIdx'],
                    'ProductIdx' => (int)$row['ProductIdx'],
                    'Gender' => (int)$row['Gender'],
                    'ResponseType' => (int)$row['ResponseType'],
                    'Sort' => (int)$row['Sort']
                ];
                $parentsProduct[] = (int)$row['ParentProductIdx'];
            }

            // personal_link인 경우, ConsultantType = N 으로 업데이트
            if ($eCode === 'personal_link') {
                $sql = "SELECT ConsultantType FROM abc.Consultant 
                        WHERE (UsersIdx, OrderIdx) = (:UsersIdx, :orderIdx)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':UsersIdx', $UsersIdx, $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $orderIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $consultantType = $stmt->fetch()['ConsultantType'] ?? '';
                if (!$consultantType) {
                    $sql = "INSERT INTO abc.Consultant (UsersIdx, OrderIdx, ConsultantType)
                            VALUES (:UsersIdx, :orderIdx, 'N')
                            ON DUPLICATE KEY UPDATE
                                ConsultantType = 'N'";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':UsersIdx', $UsersIdx, $this->conn::PARAM_INT);
                    $stmt->bindValue(':orderIdx', $orderIdx, $this->conn::PARAM_INT);
                    $stmt->execute();
                }
            }

            // event 데이터 포함시키기
            $eventInfo = $this->getEventItem($eCode, $param);
            if (!$eventInfo['EventIdx']) {
                $param = [
                    'UsersIdx' => $UsersIdx,
                    'orderIdx' => $orderIdx,
                    'eventItemManageIdx' => $eventInfo['EventItemManageIdx'],
                    'itemCategory' => $eventInfo['ItemCategory'],
                    'eventProcess' => 'a',
                    'dataContent' => '',
                ];
                $rs = $this->event($param);
                $eventInfo['EventIdx'] = $rs['data']['EventIdx'];
                $eventInfo['EventProcess'] = 'a';
            }

            $clientCustomerAddress = array_filter($clientCustomerAddress);
            $address = implode(" ", $clientCustomerAddress);
            $this->data = [
                'UsersIdx' => $UsersIdx,
                'OrderIdx' => $orderIdx,
                'ActivePoint' => $activePoint,
                'CompletePoint' => $completePoint,
                'ClientControlIdx' => $ClientControlIdx,
                'ClientCustomerName' => $clientCustomerName,
                'ClientCustomerTel' => $clientCustomerTel,
                'ClientCustomerMainTel' => $clientCustomerMainTel,
                'ClientCustomerAddress' => $address,
                'ClientCustomerCategory' => $category,
                'ProductGroupIdx' => $productGroupIdx,
                'SpecimenType' => $specimenType,
                //'ParentClientCustomerIdx' => $parentClientCustomerIdx,
                'ParentsProduct' => $parentsProduct,
                'Products' => $products,
                'EventItemManageIdx' => $eventInfo['EventItemManageIdx'],
                'ItemCategory' => $eventInfo['ItemCategory'],
                'EventIdx' => $eventInfo['EventIdx'],
                'EventProcess' => $eventInfo['EventProcess'],
            ];

            $this->desc = "getOrderInfo";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 전화번호 변환
    function transposeTel($tel) {
        $transposeTel = '';
        if ($tel !== '') {
            // 지역번호, 길이에 따른 전화번호 가공
            if (substr($tel, 0, 2) === '02') {
                $offset = 2;
            } else {
                $offset = 3;
            }
            if (mb_strlen(substr($tel, $offset)) < 8) {
                $tel = substr_replace($tel, '-', $offset + 3, 0);
            } else {
                $tel = substr_replace($tel, '-', $offset + 4, 0);
            }
            $transposeTel = substr_replace($tel, ')', $offset, 0);
        }

        return $transposeTel;
    }

    // 알림톡 전송
    // ProcessStep 21, 31, 35, 36
    function sendBizMMessage($param): array
    {
        try {
            if (
                !isset(
                    $param['productGroupIdx'],
                    $param['UsersIdx'],
                    $param['orderIdx'],
                    $param['processStep'],
                    $param['subDivisionType'],
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (
                !preg_match($this->pattern['num'], $param['processStep'])
                || ($param['subDivisionType'] && !in_array($param['subDivisionType'], [1,2]))
            ) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            // BizM 템플릿 조회
            $sql = "SELECT 
                        ProductGroupIdx, ProcessStep, SubDivisionType, TemplateCode, Message
                    FROM s.BizMTemplateManage 
                   WHERE ProductGroupIdx = :productGroupIdx
                     AND ProcessStep = :processStep
                     AND IsUse = b'1'";
            if ($param['subDivisionType']) {
                $sql .= " AND SubDivisonType = :subDivisionType";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':processStep', $param['processStep'], $this->conn::PARAM_INT);
            if ($param['subDivisionType']) {
                $stmt->bindValue(':subDivisionType', $param['subDivisionType'], $this->conn::PARAM_INT);
            }
            $stmt->execute();
            $bizMTemplate = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$bizMTemplate) {
                throw new \Exception("조회되는 알림톡 템플릿이 없습니다.", "404");
            }

            // 회원정보 조회
            $sql = "SELECT 
                        mm.UsersIdx, o.PaysIdx, m.Name, m.Phone,
                        cs.AppointmentHour, cs.AppointmentDate
                    FROM o.Pays o
                    JOIN abc.Users mm 
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members m 
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.Consultant cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                   WHERE (o.UsersIdx, o.PaysIdx) = (:UsersIdx, :orderIdx)
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     AND mm.IsOut = b'0'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $bizMTemplate['ProductGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $orderInfo = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$orderInfo) {
                throw new \Exception("조회되는 주문정보가 없습니다.", "404");
            }

            // BizM 전송 파라미터 만들기
            $sendParam = $this->getBizMMessage($orderInfo, $bizMTemplate);
            $isSuccess = $this->sender($sendParam);

            $this->desc = "sendBizMMessage";
            $this->code = '200';
            $this->msg = $isSuccess ? 'success' : 'failure';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 알림톡 전송 (상담사)
    // ProcessStep 41, 42
    function sendClientBizM($param, $processStep): array
    {
        try {
            if (
                !isset(
                    $param['buyerName'],
                    $param['buyerPhone'],
                    $param['orderDate'],
                    $param['orderQuantity'],
                    $param['orderAmt'],
                    $param['offer***Url'],
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            // BizM 템플릿 조회
            $sql = "SELECT 
                        ProductGroupIdx, ProcessStep, SubDivisionType, TemplateCode, Message
                    FROM s.BizMTemplateManage 
                   WHERE ProcessStep = :processStep
                     AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':processStep', $processStep, $this->conn::PARAM_INT);
            $stmt->execute();
            $bizMTemplate = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$bizMTemplate) {
                throw new \Exception("조회되는 알림톡 템플릿이 없습니다.", "404");
            }

            // BizM 전송 파라미터 만들기
            $sendParam = $this->getClientBizMMessage($param, $bizMTemplate);
            $isSuccess = $this->sender($sendParam);

            $this->desc = "sendBizMMessage";
            $this->code = '200';
            $this->msg = $isSuccess ? 'success' : 'failure';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // BizM 전송 파라미터 만들기 (주문정보 1명에 대하여 즉시 전송인 경우)
    // ProcessStep 21, 31, 35, 36
    private function getBizMMessage($orderInfo, $bizMTemplate): array
    {
        $sendParam = [
            'UsersIdx' => $orderInfo['UsersIdx'],
            'orderIdx' => $orderInfo['OrderIdx'],
            'templateId' => $bizMTemplate['TemplateCode'],
            'messageType' => 'AI',
            'phone' => (isDev) ? '***' : $orderInfo['Phone'],
            'message' => '',
            'title' => "",  //제목
            'reserveDatetime' => '00000000000000',  //수신시간
            'smsKind' => "L",
            'smsSender' => "***",
            'processStep' => $bizMTemplate['ProcessStep'],
            'messageSms' => '',
            'smsLmsTit' => '',
        ];

        $message = $bizMTemplate['Message'];
        $message = str_replace('#{NAME}', $orderInfo['Name'], $message);

        switch ($bizMTemplate['ProcessStep']) {
            case '21':
                $sendParam['messageSms'] = $message;
                $sendParam['smsLmsTit'] = "접수알림";
                break;
            case '31':
                $sendParam['messageSms'] = $message;
                $sendParam['smsLmsTit'] = "신청완료";
                break;
            case '35':
                $consultTimeArr = [
                    '10' => "오전 10시",
                    '11' => "오전 11시",
                    '12' => "오후 12시",
                    '13' => "오후 1시",
                    '14' => "오후 2시",
                    '15' => "오후 3시",
                    '16' => "오후 4시",
                    '17' => "오후 5시",
                    '18' => "오후 6시 이후",
                ];
                $message = str_replace('#{날짜}', date('Y년 m월 d일', strtotime($orderInfo['AppointmentDate'])), $message);
                $message = str_replace('#{시간}', $consultTimeArr[$orderInfo['AppointmentHour']], $message);
                $sendParam['messageSms'] = $message;
                $sendParam['smsLmsTit'] = "상담사 설명듣기";
                break;
            case '36':
                $sendParam['messageSms'] = $message;
                $sendParam['smsLmsTit'] = "나중에 결정";
                break;
        }

        $sendParam['message'] = $message;

        return $sendParam;
    }

    // BizM 전송 파라미터 만들기 (1명에 대하여 즉시 전송인 경우)
    // ProcessStep 41, 42
    private function getClientBizMMessage($orderInfo, $bizMTemplate): array
    {
        $sendParam = [
            'templateId' => $bizMTemplate['TemplateCode'],
            'messageType' => 'AI',
            'phone' => (isDev) ? '***' : $orderInfo['buyerPhone'],
            'message' => '',
            'title' => "",  //제목
            'reserveDatetime' => '00000000000000',  //수신시간
            'smsKind' => "L",
            'smsSender' => "***",
            'processStep' => $bizMTemplate['ProcessStep'],
            'messageSms' => '',
            'smsLmsTit' => '',
        ];

        $message = $bizMTemplate['Message'];
        $message = str_replace('#{구매자}', $orderInfo['buyerName'], $message);

        switch ($bizMTemplate['ProcessStep']) {
            case '41':
                $message = str_replace('#{결제일}', $orderInfo['orderDate'], $message);
                $message = str_replace('#{주문건수}', $orderInfo['orderQuantity'], $message);
                $message = str_replace('#{결제금액}', $orderInfo['orderAmt'], $message);

                $sendParam['messageSms'] = $message;
                break;
            case '42':
                $message = str_replace('#{주문건수}', $orderInfo['orderQuantity'], $message);

                $sendParam['messageSms'] = $message;
                $sendParam['shortUrl'] = $orderInfo['offer***Url'];
                $sendParam['buttonName'] = "검사권 사용하기";
                break;
            default:
                break;
        }

        $sendParam['message'] = $message;

        return $sendParam;
    }

    // 단일 대상 알림톡 전송
    private function sender($sendParam): bool
    {
        try {
            $isSuccess = false;

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $url = "{$this->bizMUrl}/v2/sender/send";

            $header = [
                "Content-type: application/json",
                "userId: {$this->bizMApiId}"
            ];
            // 알림톡 템플릿에 알맞는 프로필키 할당
            if (!isDev) {
                if ($sendParam['templateId'] === '****04') {
                    $this->bizMApiKey = $this->bizMBioAgeApiKey;
                } else if (strpos($sendParam['templateId'], 'earlyq') !== false) {
                    $this->bizMApiKey = $this->bizMEarlyQApiKey;
                } else if (strpos($sendParam['templateId'], 'coupon') !== false) {
                    $this->bizMApiKey = $this->bizMCouponApiKey;
                } else {
                    $this->bizMApiKey = $this->testSendApiKey;
                }
            }

            $body = [
                'message_type' => $sendParam['messageType'],
                'phn' => $sendParam['phone'],
                'profile' => $this->bizMApiKey,
                'reserveDt' => $sendParam['reserveDatetime'] ?? "00000000000000",
                'msg' => $sendParam['message'],
                'tmplId' => $sendParam['templateId'],
                'smsKind' => $sendParam['smsKind'], //대체문자 사용여부
                'msgSms' => $sendParam['messageSms'], //대체문자 MSG
                'smsSender' => $sendParam['smsSender'], //대체문자 발신번호
            ];
            if ($sendParam['smsLmsTit']) {
                $body['smsLmsTit'] = $sendParam['smsLmsTit']; //대체문자 제목
            }
            if (isset($sendParam['shortUrl'])) {
                $body['button1'] = [
                    'name' => $sendParam['buttonName'],
                    'type' => "WL",
                    'url_mobile' => $sendParam['shortUrl'],
                    'url_pc' => $sendParam['shortUrl'],
                    'target' => "out"
                ];
            }

            if (isset($sendParam['UsersIdx'], $sendParam['orderIdx'])) {
                $sql = "INSERT INTO *.SendSmsRequestLog (
                            UsersIdx, OrderIdx, Profile, TemplateId, MessageType, Phone, Message, Title, 
                            ReserveDatetime, SmsKind, SmsSender, MessageSms, SmsLmsTit) 
                        VALUE (
                            :UsersIdx, :orderIdx, :profile, :templateId, :messageType, :phone, :message, :title,
                            :reserveDatetime, :smsKind, :smsSender, :messageSms, :smsLmsTit)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':UsersIdx', $sendParam['UsersIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $sendParam['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':profile', $this->bizMApiKey);
                $stmt->bindValue(':templateId', $sendParam['templateId']);
                $stmt->bindValue(':messageType', $sendParam['messageType']);
                $stmt->bindValue(':phone', $sendParam['phone']);
                $stmt->bindValue(':message', $sendParam['message']);
                $stmt->bindValue(':title', $sendParam['title']);
                $stmt->bindValue(':reserveDatetime', $sendParam['reserveDatetime']);
                $stmt->bindValue(':smsKind', $sendParam['smsKind']);
                $stmt->bindValue(':smsSender', $sendParam['smsSender']);
                $stmt->bindValue(':messageSms', $sendParam['messageSms']);
                $stmt->bindValue(':smsLmsTit', $sendParam['smsLmsTit']);
                $stmt->execute();
            }

            $result = $this->curl('POST', $url, $header, json_encode([$body], true));
            $response = json_decode($result['response'], true)[0];

            if (isset($sendParam['UsersIdx'], $sendParam['orderIdx'])) {
                $sql = "INSERT INTO *.SendSmsResponseLog ( 
                            UsersIdx, OrderIdx, Code, Phone, Type, MessageId, ResponseMessageCode, OriginMessage) 
                        VALUE (
                            :UsersIdx, :orderIdx, :code, :phone, :messageType, :messageId, :responseMessageCode, :originMessage)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':UsersIdx', $sendParam['UsersIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $sendParam['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':code', $response['code']);
                $stmt->bindValue(':phone', $response['data']['phn']);
                $stmt->bindValue(':messageType', $response['data']['type']);
                $stmt->bindValue(':messageId', $response['data']['msgid']);
                $stmt->bindValue(':responseMessageCode', $response['message']);
                $stmt->bindValue(':originMessage', $response['originMessage']);
                $stmt->execute();
            }

            if ($response['code'] == 'success') {
                $isSuccess = true;

                if (isset($sendParam['UsersIdx'], $sendParam['orderIdx'])) {
                    $sql = "INSERT INTO s.SendManage (
                                UsersIdx, OrderIdx, ProcessStep, SendCount) 
                            VALUES (
                                :UsersIdx, :orderIdx, :processStep, 1)
                            ON DUPLICATE KEY UPDATE 
                                SendCount = SendCount + 1, 
                                LatestDatetime = NOW()";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':UsersIdx', $sendParam['UsersIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':orderIdx', $sendParam['orderIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':processStep', $sendParam['processStep'], $this->conn::PARAM_INT);
                    $stmt->execute();

                    $sendDate = ($body['reserveDt'] == '00000000000000') ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($body['reserveDt']));
                    $sql = "INSERT INTO s.SendResult (
                                UsersIdx, OrderIdx, ProcessStep, MsgId, SendDate, IsSend) 
                            VALUES (
                                :UsersIdx, :orderIdx, :processStep, :msgId, :sendDate, b'0')";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':UsersIdx', $sendParam['UsersIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':orderIdx', $sendParam['orderIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':processStep', $sendParam['processStep'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':msgId', $response['data']['msgid']);
                    $stmt->bindValue(':sendDate', $sendDate);
                    $stmt->execute();
                }
            }

            return $isSuccess;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 이벤트 데이터 저장
    function event($param): array
    {
        try {
            if (
                !isset(
                    $param['UsersIdx'],
                    $param['orderIdx'],
                    $param['eventItemManageIdx'],
                    $param['itemCategory'],
                    $param['eventProcess'],
                    $param['dataContent'])
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (
                !preg_match($this->pattern['code'], $param['itemCategory'])
                || ($param['eventProcess'] && !preg_match($this->pattern['eng'], $param['eventProcess']))
                || ($param['dataContent'] && !preg_match($this->pattern['all'], $param['dataContent']))
            ) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "INSERT INTO abc.Event (
                        UsersIdx, OrderIdx, EventItemManageIdx, ItemCategory, EventProcess, DataContent
                    ) VALUES (
                        :UsersIdx, :orderIdx, :eventItemIdx, :itemCategory, :eventProcess, :dataContent)
                    ON DUPLICATE KEY UPDATE
                        ItemCategory = :itemCategory,
                        EventProcess = :eventProcess,
                        DataContent = :dataContent,
                        ModDatetime = NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':eventItemIdx', $param['eventItemManageIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':itemCategory', $param['itemCategory']);
            $stmt->bindValue(':eventProcess', $param['eventProcess']);
            $stmt->bindValue(':dataContent', $param['dataContent']);
            $stmt->execute();

            $EventIdx = $this->conn->lastInsertId();
            if (!$EventIdx) {
                $sql = "SELECT EventIdx FROM abc.Event
                        WHERE UsersIdx = :UsersIdx
                          AND OrderIdx = :orderIdx
                          AND EventItemManageIdx = :eventItemIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':eventItemIdx', $param['eventItemManageIdx'], $this->conn::PARAM_INT);
                $stmt->execute();

                $EventIdx = $stmt->fetch()['EventIdx'];
            }
            if (!$EventIdx) {
                throw new \Exception('이벤트 정보가 없습니다', "404");
            }

            $this->desc = "eventData";
            $this->data['EventIdx'] = $EventIdx;
            $this->code = '200';
            $this->msg = 'success';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 이벤트 데이터 업데이트
    function updateEvent($param): array
    {
        try {
            $param['eventProcess'] = $param['eventProcess'] ?? '';
            $param['dataContent'] = $param['dataContent'] ?? '';

            $eventProcess = preg_match($this->pattern['eng'], $param['eventProcess']) ? $param['eventProcess'] : '';
            $dataContent = preg_match($this->pattern['all'], $param['dataContent']) ? $param['dataContent'] : '';

            if (
                !isset($param['userEventIdx'], $param['UsersIdx'], $param['orderIdx'])
                || (!$eventProcess && !$dataContent)
            ) {
                throw new \Exception("필수 파라미터가 없거나 올바르지 않습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "UPDATE abc.Event SET ";
            if ($eventProcess) {
                $sql .= " EventProcess = :eventProcess, ";
            }
            if ($dataContent) {
                $sql .= " DataContent = :dataContent, ";
            }
            $sql .= " ModDatetime = NOW()";
            $sql .= " WHERE EventIdx = :userEventIdx";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':userEventIdx', $param['userEventIdx'], $this->conn::PARAM_INT);
            if ($eventProcess) {
                $stmt->bindValue(':eventProcess', $eventProcess);
            }
            if ($dataContent) {
                $stmt->bindValue(':dataContent', $dataContent);
            }
            $stmt->execute();

            if ($param['eCode'] === 'personal_link' && in_array($eventProcess, ['b', 'e'])) {
                $consultantType = $eventProcess === 'b' ? 'R' : 'L';
                $sql = "UPDATE abc.Consultant
                        SET ConsultantType = :consultantType
                        WHERE (UsersIdx, OrderIdx) = (:UsersIdx, :orderIdx)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':consultantType', $consultantType);
                $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
            }


            $this->desc = "updateEvent";
            $this->code = '200';
            $this->msg = 'success';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 프로세스 진행상태 및 활성화시점 갱신
    function MemberStatus($param, $status = []): array
    {
        try {
            if (
                !isset(
                    $param['UsersIdx'],
                    $param['orderIdx'],
                    $param['productIdx'],
                    $param['productGroupIdx']
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $statusProcess = $status['process'] ?? ($param['statusProcess'] ?? '');
            $statusCode = $status['statusCode'] ?? ($param['statusCode'] ?? '');
            $activePoint = $param['activePoint'] ?? 0;
            $completePoint =$param['completePoint'] ?? 0;
            $parentProductIdx = $param['parentProductIdx'] ?? 0;
            // 진행 상태 갱신
            $sqlPart1 = "INSERT INTO abc.MemberStatus (`UsersIdx`, `OrderIdx`, `ProductIdx`";
            $sqlPart2 = " ) VALUES (:UsersIdx, :orderIdx, :productIdx";
            $sqlPart3 = " ) ON DUPLICATE KEY UPDATE";
            if ($statusProcess) {
                $sqlPart1 .= " , `Process`";
                $sqlPart2 .= " , :statusProcess";
                $sqlPart3 .= " `Process` = :statusProcess,";
            }
            if ($statusCode) {
                $sqlPart1 .= " , `StatusCode`";
                $sqlPart2 .= " , :statusCode";
                $sqlPart3 .= " `StatusCode` = :statusCode,";
            }
            $sqlPart3 .= " LatestDatetime = NOW()";

            $sql = $sqlPart1 . $sqlPart2 . $sqlPart3;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            if ($statusProcess) {
                $stmt->bindValue(':statusProcess', $statusProcess);
            }
            if ($statusCode) {
                $stmt->bindValue(':statusCode', $statusCode);
            }
            $stmt->execute();
            // 회원 활성화
            if ($statusProcess === 'E' && $activePoint == $parentProductIdx) {
                $sql = "UPDATE o.Pays
                        SET IsActive = b'1'
                        WHERE OrderIdx = :orderIdx
                          AND ProductGroupIdx = :productGroupIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
            }
            if ($statusProcess === 'E' && $completePoint == $parentProductIdx) {
                //서비스 수행 완료 여부 활성화
                $sql = "UPDATE o.Pays
                           SET IsComplete = b'1', CompleteDate = '" . date('Y-m-d') . "'
                         WHERE OrderIdx = :orderIdx
                           AND ProductGroupIdx = :productGroupIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
            }
            $this->desc = 'MemberStatus';
            $this->code = '200';
            $this->msg = 'success';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // curl
    public function curl($method, $url, $header, $body)
    {
        try {
            $curl = curl_init();
            if ($method == 'POST') {
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_HTTPHEADER => $header,
                ]);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
            }

            if ($method == 'GET') {
                $body = !$body ? $body : http_build_query($body, '', '&');
                $url = $url . '?' . $body;

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
            }
            $return = [
                'response' => $response,
                'code' => $http_code
            ];
        } catch (\Exception $e) {
            $return['response'] = $e->getMessage();
            $return['code'] = $e->getCode();
        } finally {
            return $return;
        }
    }

    // 오류기록
    function errorLog($msg, $code, $data): void
    {
        if (!$this->conn) {
            $this->conn = (new PDOFactory)->PDOCreate();
        }

        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        $jsonData = '';
        if (is_array($data)) {
            $jsonData = json_encode($data, true);
        } else {
            $jsonData = $data;
        }

        $sql = "INSERT INTO *.ErrorLog (Code, Msg, Request, Referer, IpAddress) 
                VALUES (:Code, :Msg, :Request, :Referer, :IpAddress)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':Code', $code);
        $stmt->bindValue(':Msg', $msg);
        $stmt->bindValue(':Request', $jsonData);
        $stmt->bindValue(':Referer', $_SERVER['HTTP_REFERER']);
        $stmt->bindValue(':IpAddress', $ipaddress);
        $stmt->execute();

        $this->conn = null;
    }

    // 거래처 사용량 및 사용기간 체크
    function checkClientCustomerStatus($param): array
    {
        try {
            if (!isset($param['ClientControlIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "SELECT ClientControlIdx
                    FROM abc.ClientControl
                    WHERE ClientControlIdx = :clientCustomerIdx
                    AND IsActive = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':clientCustomerIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$row) {
                throw new \Exception("사용 중단된 거래처입니다.", "400");
            }

            $sql = "SELECT COUNT(SaleGoodsIdx) AS cnt FROM abc.IssuedSaleGoods
                    WHERE ClientControlIdx = :ClientControlIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $param['ClientControlIdx']);
            $stmt->execute();
            $issueCnt = $stmt->fetch()['cnt'] ?? 0;

            $row['IssueCnt'] = $issueCnt;

            if ($issueCnt <= 0) {
                throw new \Exception("사용량을 초과하였습니다.", "400");
            }

            $this->desc = "checkClientCustomerStatus";
            $this->data = $row;
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 거래처 사용량 갱신
    function updateIssueCnt($param): array
    {
        try {
            if (!isset($param['ClientControlIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "SELECT ClientControlIdx
                    FROM abc.ClientControl
                    WHERE ClientControlIdx = :clientCustomerIdx
                    AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':clientCustomerIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$row) {
                throw new \Exception("사용 중단된 거래처입니다.", "400");
            }

            $sql = "SELECT itm.IssuedSaleGoodsIdx, itm.SaleGoodsIdx, itm.ClientControlIdx, itm.IssuedDatetime 
                    FROM abc.IssuedSaleGoods  AS itm
                    JOIN abc.SaleGoods AS tm ON itm.SaleGoodsIdx = tm.SaleGoodsIdx
                    WHERE itm.ClientControlIdx = :clientCustomerIdx                
                    ORDER BY tm.TicketType DESC, itm.IssuedSaleGoodsIdx ASC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':clientCustomerIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $issuedSaleGoods = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if(!$issuedSaleGoods){
                throw new \Exception("잔여티켓이 존재하지않습니다.", "400");
            }

            $this->conn->beginTransaction();
            $expiredTicketData = [
                'issuedSaleGoodsIdx' => $issuedSaleGoods['IssuedSaleGoodsIdx'],
                'SaleGoodsIdx' =>$issuedSaleGoods['SaleGoodsIdx'],
                'ClientControlIdx' => $issuedSaleGoods['ClientControlIdx'],
                'UsersIdx' => $param['UsersIdx'],
                'expiredType' => 1,
                'issuedDatetime' => $issuedSaleGoods['IssuedDatetime']
            ];

            $sql = "DELETE FROM abc.IssuedSaleGoods
                    WHERE IssuedSaleGoodsIdx = :issuedSaleGoodsIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':issuedSaleGoodsIdx', $issuedSaleGoods['IssuedSaleGoodsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->insertUpdate([], "***.ExpiredSaleGoods", $expiredTicketData);

            $this->conn->commit();

            $this->desc = "updateIssueCnt";
            $this->data = $row;
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    //유저 진행 상태 조회
    function checkUserStatus($param): array
    {
        try {
            if (!isset($param['orderIdx'], $param['UsersIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "SELECT p.ParentProductIdx, p.ProductName, uls.ProductIdx, uls.StatusCode, uls.Process 
                    FROM o.Pays as o
                    JOIN abc.MemberStatus as uls ON uls.PaysIdx = o.PaysIdx
                    JOIN abc.Product AS p ON p.ProductIdx = uls.ProductIdx
                    WHERE o.PaysIdx = :orderIdx
                      AND uls.UsersIdx = :UsersIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':orderIdx', $param['orderIdx'],$this->conn::PARAM_INT);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'],$this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data[$row['ParentProductIdx']] = [
                    'ProductName' => $row['ProductName'],
                    'ProductIdx' => $row['ProductIdx'],
                    'StatusCode' => $row['StatusCode'],
                    'Process' => $row['Process'],
                ];
            }

            $this->desc = "checkUserStatus";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 회원 찾기, 없으면 등록 후 리턴(Members,Users,Order,Ordering)
    function findMembers($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $this->conn->beginTransaction();

            if (
                !isset(
                    $param['name'],
                    $param['phone'],
                    $param['birth1'],
                    $param['birth2'],
                    $param['gender'],
                    $param['ClientControlIdx'],
                    $param['productGroupIdx'],
                    $param['productIdxArr']
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (
                !preg_match($this->pattern['kor'], $param['name'])
                || !preg_match($this->pattern['num'], $param['phone'])
                || !preg_match($this->pattern['num'], $param['birth1'])
                || !preg_match($this->pattern['num'], $param['birth2'])
                || !preg_match('/^[0-9\,]+$/', $param['productIdxArr'])
            ) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다", "400");
            }

            if (
                // 1958년생까지 서비스 이용 가능
                ($param['productGroupIdx'] === '5' && $param['birth1'] <= "1957")
                // 1949년생까지 서비스 이용 가능
                || ($param['productGroupIdx'] === '6' && $param['birth1'] <= "1948")
            ) {
                throw new \Exception("서비스를 이용할 수 없는 연령입니다.", "409");
            }

            $param['email'] = isset($param['email']) ? (preg_match($this->pattern['email'], $param['email']) ? $param['email'] : '') : '';
            $param['state'] = isset($param['state']) ? (preg_match($this->pattern['kor'], $param['state']) ? $param['state'] : '') : '';
            $param['city'] = isset($param['city']) ? (preg_match($this->pattern['kor'], $param['city']) ? $param['city'] : '') : '';
            $param['fullCity'] = isset($param['fullCity']) ? (preg_match($this->pattern['kor'], $param['fullCity']) ? $param['fullCity'] : '') : '';
            $param['specimenType'] = isset($param['specimenType']) ? (in_array($param['specimenType'], ['blood', 'buccal', 'none']) ? $param['specimenType'] : 'none') : 'none';

            $isNew = false;
            $this->data['MembersIdx'] = 0;
            $this->data['UsersIdx'] = 0;
            $this->data['OrderIdx'] = 0;
            $email = '';
            $state = '';
            $city = '';
            $fullCity = '';

            //회원조회
            $sql = "SELECT 
                        m.MembersIdx, m.Name, m.Email, m.State, m.City, m.FullCity,
                        mm.UsersIdx, mm.ClientControlIdx, 
                        IF(mm.IsOut=b'0','N','Y') AS IsOut
                    FROM abc.Members AS m
                    LEFT JOIN abc.Users AS mm ON mm.MembersIdx = m.MembersIdx
                    WHERE m.Name = :name
                      AND m.Phone = :phone
                      AND m.Birth1 = :birth1
                      AND m.Birth2 = :birth2
                      AND m.Gender = :gender";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':name', $param['name']);
            $stmt->bindValue(':phone', $param['phone']);
            $stmt->bindValue(':birth1', $param['birth1']);
            $stmt->bindValue(':birth2', $param['birth2']);
            $stmt->bindValue(':gender', $param['gender'], $this->conn::PARAM_INT);
            $stmt->execute();
            //Members테이블에 존재하나, Users에 존재하지 않는 회원을 찾기 위함
            while ($row = $stmt->fetch()) {
                $this->data['MembersIdx'] = $row['MembersIdx'];
                $email = $row['Email'];
                $state = $row['State'];
                $city = $row['City'];
                $fullCity = $row['FullCity'];
                if (
                    $param['ClientControlIdx'] === $row['ClientControlIdx']
                    && $row['UsersIdx']
                    && $row['IsOut'] === 'N'
                ) {
                    $this->data['UsersIdx'] = $row['UsersIdx'];
                }
            }

            //가입이력이 없는경우 회원 등록
            if (!$this->data['MembersIdx']) {
                $regist = $this->registMembers($param);
                if ($regist['code'] !== '201') {
                    throw new \Exception($regist['msg'], $regist['code']);
                }
            } else {
                $param['email'] = $param['email'] ?: $email;
                if (!$param['state']) {
                    $param['state'] = $state;
                    $param['city'] = $city;
                    $param['fullCity'] = $fullCity;
                }
                $regist = $this->updateMembersInfo($this->data['MembersIdx'], $param);
                if ($regist['code'] !== '200') {
                    throw new \Exception($regist['msg'], $regist['code']);
                }
            }

            //회원 상세 정보가 존재하지 않는 경우 등록
            if (!$this->data['UsersIdx']) {
                $regist = $this->registUsers($param);
                if ($regist['code'] !== '201') {
                    throw new \Exception($regist['msg'], $regist['code']);
                }
                $isNew = true;
            }

            //회원 활성 여부 조회
            $sql = "SELECT MAX(OrderIdx) as OrderIdx
                    FROM o.Pays
                    WHERE UsersIdx = :UsersIdx
                      AND ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $this->data['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->data['OrderIdx'] = $stmt->fetch()['OrderIdx'];
            if (!$this->data['OrderIdx']) {
                //주문 식별자 없는 경우, 등록
                $regist = $this->registOrder($param);
                if ($regist['code'] !== '201') {
                    throw new \Exception($regist['msg'], $regist['code']);
                }
            }

            if ($isNew) {
                // UsersIdx 신규 생성시, 회원 상태 입력
                $data = [
                    'UsersIdx' => $this->data['UsersIdx'],
                    'orderIdx' => $this->data['OrderIdx'],
                    'productGroupIdx' => $param['productGroupIdx'],
                    'parentProductIdx' => $param['parentProductIdx'],
                    'productIdx' => $param['productIdx'],
                    'activePoint' => $param['activePoint'],
                ];
                $status = [
                    'process' => 'A',
                    'statusCode' => '21000',
                ];
                $this->MemberStatus($data, $status);
            }

            $this->desc = "findMembers";
            $this->code = '200';
            $this->msg = 'success';

            $this->conn->commit();

            return $this->response();
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    // productIdx별 동의 데이터 등록
    function agreement($param): array
    {
        try {
            if (!isset($param['UsersIdx'], $param['orderIdx'], $param['productIdx'],)) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $sql = "INSERT INTO abc.AgreementManage (
                        UsersIdx, OrderIdx, ProductIdx, ALL_AGRE_YN, AGRE_DATE) 
                    VALUES (
                        :UsersIdx, :orderIdx, :productIdx, 'Y', CURDATE())
                    ON DUPLICATE KEY UPDATE
                        AGRE_DATE = CURDATE()";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            //회원 상태 갱신
            $this->MemberStatus($param);

            $this->desc = "agreement";
            $this->code = "200";
            $this->msg = "success";

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //주문 데이터 등록
    function registOrder($param): array
    {
        try {
            $this->code = '201';
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            if (!$param['specimenType']) {
                $param['specimenType'] = 'none';
            }
            $sql = "INSERT INTO o.Pays (UsersIdx, ProductGroupIdx, SpecimenType)
                    VALUES (:UsersIdx, :productGroupIdx, :specimenType)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $this->data['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':specimenType', $param['specimenType']);
            $stmt->execute();

            $this->data['OrderIdx'] = $this->conn->lastInsertId();
            if (!$this->data['OrderIdx']) {
                throw new \Exception('no orderidx', '500');
            }

            $productIdxArr = explode(',', $param['productIdxArr']);
            $insertVal = "";
            foreach ($productIdxArr as $key => $val) {
                if ($key !== 0) {
                    $insertVal .= ",";
                }
                $insertVal .= "({$this->data['OrderIdx']}, {$val})";
            }

            //Ordering 데이터 입력, 최초 데이터 랜딩시 ProductGroupIdx를 모두 알고있는 상태이므로, 해당 데이터를 form으로 받아 일괄 입력처리
            $sql = "INSERT INTO o.Paysing (OrderIdx, ProductIdx)
                    VALUES {$insertVal}";
            $this->conn->query($sql);

        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $this->response();
        }
    }

    // 주소 갱신
    function updateMembersInfo($MembersIdx, $param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "UPDATE abc.Members
                    SET Email = :email,
                        `State` = :state,
                        City = :city,
                        FullCity = :fullCity
                    WHERE MembersIdx = :MembersIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':MembersIdx', $MembersIdx, $this->conn::PARAM_INT);
            $stmt->bindValue(':email', $param['email']);
            $stmt->bindValue(':state', $param['state']);
            $stmt->bindValue(':city', $param['city']);
            $stmt->bindValue(':fullCity', $param['fullCity']);
            $stmt->execute();

            $this->code = "200";
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $this->response();
        }
    }

    /**
     * @date 2023-05-15
     * @brief 회원 정보 등록(Members, Users)
     * @param MembersFormData, 거래처코드 식별자
     * @return int
     * @author hellostellaa
     */
    function registMembers($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "INSERT INTO abc.Members (
                        Name, Phone, Birth1, Birth2, Gender, Email, State, City, FullCity)
                    VALUES (
                        :Name, :Phone, :Birth1, :Birth2, :Gender, :Email, :State, :City, :FullCity)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':Name', $param['name']);
            $stmt->bindValue(':Phone', $param['phone']);
            $stmt->bindValue(':Birth1', $param['birth1']);
            $stmt->bindValue(':Birth2', $param['birth2']);
            $stmt->bindValue(':Gender', $param['gender'], $this->conn::PARAM_INT);
            $stmt->bindValue(':Email', $param['email']);
            $stmt->bindValue(':State', $param['state']);
            $stmt->bindValue(':City', $param['city']);
            $stmt->bindValue(':FullCity', $param['fullCity']);
            $stmt->execute();

            $this->data['MembersIdx'] = $this->conn->lastInsertId();

            $this->code = "201";

        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $this->response();
        }
    }

    //Users 등록
    function registUsers($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            $sql = "INSERT INTO abc.Users (MembersIdx, ClientControlIdx)
                    VALUES (:MembersIdx, :ClientControlIdx)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':MembersIdx', $this->data['MembersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':ClientControlIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->data['UsersIdx'] = $this->conn->lastInsertId();
            $this->code = '201';
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $this->response();
        }
    }

    public function checkDayofWeek($appointmentDay)
    {
        // 1:월요일; ... 7:일요일; 8:상시
        //$availableDayOfWeek = range(1, 8);
        // 1:평일; 6:주말; 8:상시
        $availableDayOfWeek = [1, 6, 8];
        if (in_array($appointmentDay, $availableDayOfWeek)) {
            return $appointmentDay;
        }
        return false;
    }

    public function checkHour($appointmentHour)
    {
        $availableHour = range(0, 24);
        if (in_array($appointmentHour, $availableHour)) {
            return $appointmentHour;
        }
        return false;
    }

    function insertUpdate(array $idx, string $table, array $item): int
    {
        $this->desc = 'model::insertUpdate';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (count($idx) > 0) {
                // 갱신
                $whereQuery = "";
                foreach ($idx as $key => $value) {
                    if ($whereQuery !== "") {
                        $whereQuery .= " AND ";
                    }
                    $column = (gettype($value) === 'integer' || $key === 'isUse') ? $value : "'{$value}'";
                    $whereQuery .= ucfirst($key) . " = " . $column;
                }
                $updateQuery = "";
                if (count($item) > 0) {
                    foreach ($item as $key => $value) {
                        if ($value === '') {
                            unset($item[$key]);
                        } else {
                            if ($updateQuery !== "") {
                                $updateQuery .= ",";
                            }
                            $column = (gettype($value) === 'integer' || $key === 'isUse') ? $value : "'{$value}'";
                            $updateQuery .= ucfirst($key) . " = " . $column;
                        }
                    }
                }
                if (!$table || !$updateQuery || !$whereQuery) {
                    throw new \Exception('필수 파라미터가 없습니다.', '404');
                }
                $sql = "UPDATE {$table}
                        SET {$updateQuery}
                        WHERE {$whereQuery}";
            } else {
                // 등록
                $insertColumns = "";
                $insertValues = "";
                if (count($item) > 0) {
                    foreach ($item as $key => $value) {
                        if ($value === '') {
                            unset($item[$key]);
                        } else {
                            if ($insertColumns !== "") {
                                $insertColumns .= ",";
                                $insertValues .= ",";
                            }
                            $insertColumns .= ucfirst($key);
                            $column = (gettype($value) === 'integer' || $key === 'isUse') ? $value : "'{$value}'";
                            $insertValues .= $column;
                        }
                    }
                }
                if (!$table || !$insertColumns || !$insertValues) {
                    throw new \Exception('필수 파라미터가 없습니다.', '404');
                }
                $sql = "INSERT INTO {$table} ({$insertColumns}) VALUES ({$insertValues})";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $this->conn->lastInsertId();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    function insertDuplicate(array $unique, string $table, array $item, string $addUpdate): int
    {
        $this->desc = 'model::insertDuplicate';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $updateValues = '';
            if (count($unique) > 0) {
                foreach ($unique as $key => $value) {
                    if ($value === '') {
                        unset($unique[$key]);
                    } else {
                        if ($updateValues !== '') {
                            $updateValues .= ",";
                        }
                        $column = (gettype($value) === 'integer' || $key === 'isUse') ? $value : "'{$value}'";
                        $updateValues .= ucfirst($key) . " = " . $column;
                    }
                }
            }
            if ($addUpdate) {
                $updateValues .= ", {$addUpdate}";
            }

            $insertColumns = '';
            $insertValues = '';
            if (count($item) > 0) {
                foreach ($item as $key => $value) {
                    if ($value === '') {
                        unset($item[$key]);
                    } else {
                        if ($insertColumns !== "") {
                            $insertColumns .= ",";
                            $insertValues .= ",";
                        }
                        $insertColumns .= ucfirst($key);
                        $column = (gettype($value) === 'integer' || $key === 'isUse') ? $value : "'{$value}'";
                        $insertValues .= $column;
                    }
                }
            }
            if (!$table || !$insertColumns || !$insertValues) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }

            $sql = "INSERT INTO {$table} ({$insertColumns}) VALUES ({$insertValues})
                    ON DUPLICATE KEY UPDATE {$updateValues}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $this->conn->lastInsertId();

        } catch (\Exception $e) {
            throw $e;
        }
    }

}


