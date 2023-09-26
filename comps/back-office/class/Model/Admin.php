<?

namespace Model;

use NaverShortUrl;
use Pdf;
use SpreadsheetFactory;

class Admin
{
    public ?object $conn = null;
    public string $code = "200";
    public array $data = [];
    public string $msg = "";
    public string $desc = "";
    private string $imgUrl = "https://img.g******com";
    private string $personalLinkUrl = "d.g******com/abc/?eCode=personal_link";
    // 질환 확인 페이지 URL
    private string $kcpCertInfo = "";
    private array $bioMarkerCode = [
        'n' => [
            'name' => 'high_blood_pressure',
            'title' => '고혈압'
        ],
/*        ...생략...*/
    ];
    private array $bioMarkerRank = [
        /*생략*/
    ];
    private array $supplements = [
        /*생략*/
    ];
    public array $pattern = [
        'all' => '/^[가-힣a-zA-Z0-9\_\.\,\-\s\(\)]+$/',
        'code' => '/^[a-zA-Z0-9\_]+$/',
        'kor' => '/^[가-힣\s]+$/',
        'eng' => '/^[a-zA-Z\s]+$/',
        'num' => '/^[0-9]+$/',
        'email' => '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i',
        'date' => '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',
        'survey' => '/^[1-6\,]+$/'
    ];

    public array $defineStatusCode = [
        /*생략*/
        '7' => [
            '8' => [
                'A' => [
                    '21000' => '설문응답 신청 진입'
                ],
                'E' => [
                    '20009' => '설문응답 등록 실패',
                    '20000' => '설문응답 등록 완료 [P]',
                ],
            ],
        ],
        /*생략*/
    ];

    // 엑셀 다운로드
    function excelFileDown($param) : void
    {
        $this->desc = 'excelFileDown';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            switch($param['target']) {
                case 'consultantData':
                    $data = $this->consultantData($param);
                    $headers = [
                        '상담사계정코드', '등록일자', '최종수정일자', '등록방식', '사용처', '회사명', '상담사',
                        /*생략*/
                    ];
                    $fileName = "상담사등록관리_";
                    break;
                default :
                    $headers = [];
                    $fileName = "notitle";
                    break;
            }

            $spreadsheet = new SpreadsheetFactory();
            $spreadsheet->downloadSheet($headers, $data, $fileName);

            exit;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    function __construct()
    {
        if (isDev) {
            // 개발서버 테스트 api
            $this->apiUrlU2 = "https://dev.u_*****_u.com";
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/pay/***tifiabc/***.key")) {
            $this->kcpCertInfo = preg_replace('/\r\n|\r|\n/', '', file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/pay/***tifiabc/***.key"));
        }
        if (!$this->conn) {
            $this->conn = (new \PDOFactory)->PDOCreate();
        }
    }
    
    // 유저 쿠폰 사용 내역 조회
    function userCouponList($param) : array
    {
        $this->desc = 'userCouponList';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['cm.CouponName', 'cm.CouponCode'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, cm.RegDatetime DESC ";
            } else {
                $orderSql .= ' cm.RegDatetime DESC ';
            }

            $sql = "SELECT 
                         ecm.ExpiredCouponIdx,ecm.CouponIdx,ecm.TicketsIdx,ecm.CouponCode
                        ,ecm.ClientControlIdx,ecm.IssuedDatetime,ecm.ExpiredType,ecm.ExpiredDatetime
                        ,cm.CouponName, scm.ServiceCompanyName, ccm.ClientCustomerName
                    FROM
                    abc.`ExpiredTickets` AS ecm
                     JOIN abc.`Tickets` AS cm 
                       ON cm.TicketsIdx = ecm.TicketsIdx
                     JOIN abc.ClientControl AS ccm
                       ON ccm.ClientControlIdx = ecm.ClientControlIdx
                     JOIN abc.ServiceControl AS scm
                       ON scm.ServiceControlIdx = ccm.ServiceControlIdx
                    WHERE cm.IsUse = b'1'
                      AND cm.ProductGroupIdx = :productGroupIdx 
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();

            $data = [];
            while($row = $stmt->fetch()) {
                $data[$row['ExpiredCouponIdx']] = [
                    'expiredCouponIdx' => $row['ExpiredCouponIdx'],
                    'couponIdx' => $row['CouponIdx'],
                    'TicketsIdx' => $row['TicketsIdx'],
                    'couponCode' => $row['CouponCode'],
                    'couponName' => $row['CouponName'],
                    'ClientControlIdx' => $row['ClientControlIdx'],
                    'clientCustomerName' => $row['ClientCustomerName'],
                    'serviceCompanyName' => $row['ServiceCompanyName'],
                    'issuedDatetime' => $row['IssuedDatetime'],
                    'expiredType' => $row['ExpiredType'],
                    'expiredDatetime' => $row['ExpiredDatetime'],
                ];
            }
            $this->data['data'] = $data;
            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }
    // 결제취소 후 BizM 발송 (ProcessStep : 43, 44, 45)
    function sendRefundSms($param) : array
    {
        $this->desc = "model::sendClientSms";
        try {
            if (
                !isset(
                    $param['payOrderCode'],
                    $param['buyerName'],
                    $param['buyerPhone'],
                    $param['refundType'],
                    $param['refundDate'],
                    $param['refundQuantity'],
                    $param['refundAmount'],
                    $param['ClientControlIdx']
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            if ($param['refundType'] === 'STSC') {
                $processStep = [43];
            } else {
                $processStep = [44, 45];
            }

            $sql = "SELECT TemplateIdx, ProductGroupIdx, ProcessStep, SubDivisionType, TemplateCode, Message
                      FROM s.BizMTemplateManage
                     WHERE ProcessStep IN (" . implode(",", $processStep) . ")
                       AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $bizMTemplateList = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];
            if (count($bizMTemplateList) === 0) {
                throw new \Exception("사용 가능한 BizM 템플릿이 없습니다.", "404");
            }

            $bizMParamList = [];
            foreach ($bizMTemplateList as $template) {
                $bizMParam = [
                    'templateId' => $template['TemplateCode'],
                    'messageType' => 'AI',
                    'phone' => (isDev) ? '01041033708' : $param['buyerPhone'],
                    'message' => '',
                    'title' => "",  //제목
                    'reserveDatetime' => '00000000000000',  //수신시간
                    'smsKind' => "L",
                    'smsSender' => "031***6176",
                    'processStep' => $template['ProcessStep'],
                    'messageSms' => '',
                    'smsLmsTit' => '',
                ];

                $message = $template['Message'];
                $message = str_replace('#{구매자}', $param['buyerName'], $message);

                switch ($template['ProcessStep']) {
                    case '43':
                        $message = str_replace('#{취소일}', $param['refundDate'], $message);
                        $message = str_replace('#{취소건수}', $param['refundQuantity'], $message);
                        $message = str_replace('#{취소금액}', $param['refundAmount'], $message);

                        $bizMParam['messageSms'] = $message;
                        break;
                    case '44':
                        $message = str_replace('#{부분취소일}', $param['refundDate'], $message);
                        $message = str_replace('#{부분취소건수}', $param['refundQuantity'], $message);
                        $message = str_replace('#{부분취소금액}', $param['refundAmount'], $message);

                        $bizMParam['messageSms'] = $message;
                        break;
                    case '45':
                        $sql = "SELECT 
                                    OrderQuantity, OrderType 
                                  FROM p.PayssItem
                                 WHERE PayOrderCode = :payOrderCode";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':payOrderCode', $param['payOrderCode']);
                        $stmt->execute();
                        $afterRefundQuantity = 0;
                        while ($row = $stmt->fetch()) {
                            if ($row['OrderType'] === '1') {
                                $afterRefundQuantity += (int)$row['OrderQuantity'];
                            } else {
                                $afterRefundQuantity -= (int)$row['OrderQuantity'];
                            }
                        }

                        $message = str_replace('#{주문건수-부분취소}', $afterRefundQuantity, $message);

                        $sql = "SELECT ClientCustomerCode 
                                  FROM abc.ClientControl 
                                 WHERE ClientControlIdx = :ClientControlIdx";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':ClientControlIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
                        $stmt->execute();
                        $clientCustomerCode = $stmt->fetch()['ClientCustomerCode'] ?? '';
                        if (!$clientCustomerCode) {
                            throw new \Exception("상담사 조회 실패", "404");
                        }
                        $offer***Url = "https://d.g******com/abc/?hCode={$clientCustomerCode}";
                        $result = (new NaverShortUrl())->getResult(['url' => $offer***Url]);
                        if ($result['code'] !== 200) {
                            throw new \Exception("URL 생성에 실패하였습니다.", "400");
                        }
                        $response = json_decode($result['response'], true);
                        $shortUrl = $response['result']['url'];

                        $bizMParam['messageSms'] = $message;
                        $bizMParam['shortUrl'] = $shortUrl;
                        $bizMParam['buttonName'] = "검사권 사용하기";
                        break;
                }

                $bizMParam['message'] = $message;

                $bizMParamList[] = $bizMParam;
            }

            return $this->sender($bizMParamList);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 결제내역 취소
    function refundPayment($param) : array
    {
        $this->desc = "model::refundPayment";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset(
                $param['payOrderIdx'],
                $param['kcpTno'],
                $param['orderType'],
                $param['payType'],
                $param['refundType'],
                $param['orderQuantity'],
                $param['orderAmount'],
                $param['refundDesc'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                !in_array($param['orderType'], [2, 3])
                || !in_array($param['refundType'], ['STSC', 'STPC'])
                || !preg_match($this->pattern['num'], $param['orderQuantity'])
                || !preg_match($this->pattern['num'], $param['orderAmount'])
                || !preg_match($this->pattern['all'], $param['refundDesc'])
            ) {
                throw new \Exception('파라미터가 올바르지 않습니다.', "400");
            }

            $sql = "SELECT 
                        PayOrderIdx, RelatedPayOrderIdx, PayOrderCode, PGCompanyName, SiteCode, 
                        ItemsIdx, GoodsName, SalesPrice, CouponCode, CompanyName,
                        BuyerName, BuyerPhone, PayMethod, KcpTno, PayType, ClientControlIdx,
                        SaleGoodsIdx
                      FROM p.PayssItem
                     WHERE PayOrderIdx = :payOrderIdx
                       AND KcpTno = :kcpTno
                       AND PayType = :payType
                       AND OrderType = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':payOrderIdx', $param['payOrderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':kcpTno', $param['kcpTno']);
            $stmt->bindValue(':payType', $param['payType']);
            $stmt->execute();
            $paymentInfo = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$paymentInfo) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            // 환불수량 vs 남은 티켓 수량 비교 체크
            // $issuedSaleGoodsIdxArr는 추후 아래 티켓 회수 로직에서 사용됨
            $sql = "SELECT IssuedSaleGoodsIdx
                      FROM abc.IssuedSaleGoods
                     WHERE SaleGoodsIdx = {$paymentInfo['SaleGoodsIdx']}
                  ORDER BY IssuedDatetime DESC
                     LIMIT :refundQuantity";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':refundQuantity', $param['orderQuantity'], $this->conn::PARAM_INT);
            $stmt->execute();
            $issuedSaleGoodsIdxArr = $stmt->fetchAll($this->conn::FETCH_COLUMN) ?? [];
            if (count($issuedSaleGoodsIdxArr) < $param['orderQuantity']){
                throw new \Exception('취소 가능 수량을 초과하였습니다.', "400");
            }

            // OrdersItem Insert
            $table = "p.PayssItem";
            $item = [
                'payOrderCode' => $paymentInfo['PayOrderCode'],
                'pGCompanyName' => $paymentInfo['PGCompanyName'],
                'siteCode' => $paymentInfo['SiteCode'],
                'ItemsIdx' => $paymentInfo['ItemsIdx'],
                'goodsName' => $paymentInfo['GoodsName'],
                'salesPrice' => $paymentInfo['SalesPrice'],
                'couponCode' => $paymentInfo['CouponCode'] ?? 'null',
                'companyName' => $paymentInfo['CompanyName'],
                'buyerName' => $paymentInfo['BuyerName'],
                'buyerPhone' => $paymentInfo['BuyerPhone'],
                'payMethod' => $paymentInfo['PayMethod'],
                'orderType' => $param['orderType'],
                'orderQuantity' => $param['orderQuantity'],
                'orderAmount' => $param['orderAmount'],
                'orderStatus' => 0,
                'kcpTno' => $paymentInfo['KcpTno'],
                'payType' => $paymentInfo['PayType'],
                'ClientControlIdx' => $paymentInfo['ClientControlIdx'],
                'SaleGoodsIdx' => $paymentInfo['SaleGoodsIdx'],
            ];
            $payOrderIdx = $this->insertUpdate([], $table, $item);

            if ($param['orderType'] === '2') {
                $keyData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/pay/***tifiabc/splPrikeyPKCS8.pem');
                $priKey = openssl_pkey_get_private($keyData, $this->kcpSignPw);
                $header = [
                    "Content-Type: application/json",
                    "charset=utf-8"
                ];

                // KCP 조회 API
                $targetData = "{$paymentInfo['SiteCode']}^{$paymentInfo['KcpTno']}^{$paymentInfo['PayType']}";
                openssl_sign($targetData, $signature, $priKey, 'sha256WithRSAEncryption');
                $kcpSignData = base64_encode($signature);
                $url = $this->kcpInqueryUrl;
                $data = [
                    "site_cd" => $paymentInfo['SiteCode'],
                    "kcp_cert_info" => $this->kcpCertInfo,
                    "kcp_sign_data" => $kcpSignData,
                    "tno" => $paymentInfo['KcpTno'],
                    "pay_type" => $paymentInfo['PayType'],
                ];
                $result = $this->curl('POST', $url, $header, json_encode($data));
                $resp = json_decode($result['response'], true);
                if ($resp['res_cd'] != '0000') {
                    throw new \Exception("결제 조회 실패: {$resp['res_msg']}", "500");
                }
                $remainingAmount = $resp['rem_mny'];

                // KCP 취소 API
                $cancelTargetData = "{$paymentInfo['SiteCode']}^{$paymentInfo['KcpTno']}^{$param['refundType']}";
                openssl_sign($cancelTargetData, $signature, $priKey, 'sha256WithRSAEncryption');
                $kcpSignData = base64_encode($signature);

                $url = $this->kcpRefundUrl;
                $header = [
                    "Content-Type: application/json",
                    "charset=utf-8"
                ];
                $data = [
                    "site_cd" => $paymentInfo['SiteCode'],
                    "kcp_cert_info" => $this->kcpCertInfo,
                    "kcp_sign_data" => $kcpSignData,
                    "tno" => $paymentInfo['KcpTno'],
                    "mod_type" => $param['refundType'],
                    "mod_mny" => (int)$param['orderAmount'], // 부분취소금액
                    "rem_mny" => (int)$remainingAmount, // 남은 원거래 금액
                    "mod_desc" => $param['refundDesc']
                ];
                $result = $this->curl('POST', $url, $header, json_encode($data));
                $resp = json_decode($result['response'], true);
                if ($resp['res_cd'] != '0000') {
                    throw new \Exception("결제 취소 실패: {$resp['res_msg']}", "500");
                }

                // KCP 취소 API 성공 이후에 DB 오류 발생 시, 대응할 수 있게
                // OrdersItem > OrderStatus = 9 `DB 오류` Update
                $table = "p.PayssItem";
                $idx = [
                    'payOrderIdx' => $payOrderIdx
                ];
                $item = [
                    'orderStatus' => 9,
                ];
                $this->insertUpdate($idx, $table, $item);

                // KCP 취소 API 통신 이후 OrdersItem Update
                $idx = [
                    'payOrderIdx' => $payOrderIdx
                ];
                $table = "p.PayssItem";
                $item = [
                    'approvedOrderAmount' => $resp['mod_mny'] ?? $param['orderAmount'],
                    'approvedDatetime' => date('Y-m-d H:i:s', strtotime($resp['canc_time']))
                ];
                $this->insertUpdate($idx, $table, $item);

                // RefundManage Insert
                $table = "p.RefundManage";
                $item = [
                    'payOrderIdx' => $payOrderIdx,
                    'kcpTno' => $paymentInfo['KcpTno'],
                    'refundType' => $param['refundType'],
                    'refundDesc' => $param['refundDesc'],
                    'refundQuantity' => $param['orderQuantity'],
                    'refundAmount' => $param['orderAmount'],
                    'refundDate' => date('Y-m-d', strtotime($resp['canc_time'])),
                    'partialRefundCode' => $resp['mod_pacn_seq_no'] ?? '',
                    'approvedRefundAmount' => $resp['mod_mny'] ?? $param['orderAmount'],
                    'remainingAmount' => $resp['rem_mny'] ?? '',
                ];

                $this->insertUpdate([], $table, $item);
            }

            // 만료쿠폰 -> 유효쿠폰으로 (전체취소일 경우에 한하여; RefundType = STSC)
            if ($paymentInfo['CouponCode'] && $param['refundType'] === 'STSC') {
                $sql = "SELECT ecm.*, cm.CouponType
                          FROM abc.ExpiredTickets ecm
                          JOIN abc.Tickets cm
                            ON cm.TicketsIdx = ecm.TicketsIdx
                         WHERE ecm.CouponCode = '{$paymentInfo['CouponCode']}'
                           AND ecm.ClientControlIdx = {$paymentInfo['ClientControlIdx']}
                      ORDER BY ecm.ExpiredDatetime DESC
                         LIMIT 1";
                $stmt = $this->conn->query($sql);
                $couponInfo = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
                // 일회용 쿠폰일 경우 IssuedTickets Insert
                // 다회용 쿠폰일 경우 삭제만; 로직상 다회용 쿠폰은 IssuedTickets에 유지되었을테니..
                if ($couponInfo) {
                    if ($couponInfo['CouponType'] === '1'){
                        $table = "***.IssuedTickets";
                        $item = [
                            'couponIdx' => $couponInfo['CouponIdx'],
                            'TicketsIdx' => $couponInfo['TicketsIdx'],
                            'couponCode' => $couponInfo['CouponCode'],
                            'ClientControlIdx' => $couponInfo['ClientControlIdx'],
                            'issuedDatetime' => $couponInfo['IssuedDatetime'],
                        ];
                        $this->insertUpdate([], $table, $item);
                    }
                    $sql = "DELETE FROM abc.ExpiredTickets
                            WHERE ExpiredCouponIdx = {$couponInfo['ExpiredCouponIdx']}";
                    $this->conn->query($sql);
                }
            }

            // 유효쿠폰 삭제
            $issuedSaleGoodsIdxList = implode(',', $issuedSaleGoodsIdxArr);
            $sql = "DELETE FROM abc.IssuedSaleGoods
                    WHERE IssuedSaleGoodsIdx IN ({$issuedSaleGoodsIdxList})";
            $this->conn->query($sql);

            // OrdersItem 정상처리 Update
            $table = "p.PayssItem";
            $idx = [
                'payOrderIdx' => $payOrderIdx
            ];
            $item = [
                'orderStatus' => 1,
            ];
            $this->insertUpdate($idx, $table, $item);

            $this->data = [
                'payOrderCode' => $paymentInfo['PayOrderCode'],
                'buyerName' => $paymentInfo['BuyerName'],
                /*생략*/
                'ClientControlIdx' => $paymentInfo['ClientControlIdx']
            ];

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈관리 조회(수정 Modal)
    function searchPayment($param) : array
    {
        $this->desc = "model::searchPayment";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['payOrderIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            $sql = "SELECT 
                        pm.PayOrderIdx, pm.CouponCode, cm.DiscountMethod, cm.DiscountAmount, cm.DiscountRate,
                        pm.PayOrderCode, pm.PayMethod, pm.GoodsName, pm.PaysAmount, pm.TotalDiscountAmount, pm.PaysQuantity,
                        scm.ServiceCompanyName, pm.CompanyName, pm.BuyerName, 
                        pm.ApprovedDatetime, pm.SalesPrice, pm.KcpTno, pm.PayType, pm.ApprovedOrderAmount, pm.PaysType, pm.SaleGoodsIdx
                      FROM p.PayssItem pm
                      JOIN abc.Items gm
                        ON gm.ItemsIdx = pm.ItemsIdx
                      JOIN abc.ServiceControl scm
                        ON scm.ServiceControlIdx = gm.ServiceControlIdx
                 LEFT JOIN abc.Tickets cm
                        ON cm.CouponCode = pm.CouponCode
                 LEFT JOIN p.RefundManage rm
                        ON rm.PayOrderIdx = pm.PayOrderIdx
                     WHERE pm.PayOrderIdx = :payOrderIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':payOrderIdx', $param['payOrderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch() ?? [];
            if (!$row) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }
            if ($row['OrderType'] != '1'){
                throw new \Exception("취소내역을 선택하셨습니다. \n결제내역을 선택하여 진행하시길 바랍니다.", "400");
            }

            $sql = "SELECT IssuedSaleGoodsIdx 
                      FROM abc.IssuedSaleGoods
                     WHERE SaleGoodsIdx = {$row['SaleGoodsIdx']}";
            $stmt = $this->conn->query($sql);
            $issuedTicketCnt = $stmt->rowCount();

            $this->data = [
                'payOrderIdx' => $row['PayOrderIdx'],
                'payOrderCode' => $row['PayOrderCode'],
                'payMethod' => $row['PayMethod'],
                'goodsName' => $row['GoodsName'],
                /*생략*/
                'kcpTno' => $row['KcpTno'],
                'payType' => $row['PayType'],
            ];

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 결제내역 조회
    function paymentList($param) : array
    {
        $this->desc = "model::paymentList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['pm.GoodsName', 'scm.ServiceCompanyName', 'pm.CompanyName', 'pm.BuyerName', 'pm.BuyerPhone'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'pm.PayOrderIdx') {
                        $addSql .= " AND ({$param['keyword']} = '{$param['value']}' OR pm.RelatedPayOrderIdx = '{$param['value']}')";
                    } else {
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                    }
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, pm.RegDatetime DESC ";
            } else {
                $orderSql .= ' pm.RegDatetime DESC ';
            }

            // 대상 전체 카운트
            $sql = "SELECT
                        pm.PayOrderIdx, pm.RelatedPayOrderIdx, pm.RegDatetime, pm.PayOrderCode, pm.PayMethod, 
                        pm.ItemsIdx, pm.GoodsName, pm.PaysAmount, ccm.ServiceControlIdx, 
                        scm.ServiceCompanyName, pm.CompanyName, pm.BuyerName, pm.BuyerPhone, pm.PaysStatus, 
                        pm.ApprovedDatetime, rm.RefundType, pm.PaysType
                    FROM p.PayssItem pm
                    JOIN abc.Items gm
                      ON gm.ItemsIdx = pm.ItemsIdx
                    JOIN abc.ClientControl ccm
                      ON ccm.ClientControlIdx = pm.ClientControlIdx
                    JOIN abc.ServiceControl scm
                      ON scm.ServiceControlIdx = ccm.ServiceControlIdx
               LEFT JOIN p.RefundManage rm
                      ON rm.PayOrderIdx = pm.PayOrderIdx
                   WHERE gm.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND (pm.PaysStatus = 1 OR (rm.RefundType IS NOT NULL AND pm.PaysStatus = 9))
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                $orderType = '';
                if ($item['OrderType'] === '2') {
                    $orderType = "자동";
                } elseif ($item['OrderType'] === '3') {
                    $orderType = "수동";
                }

                if (!$item['RefundType']) {
                    $orderStatus = "결제완료";
                    $orderAmount = $item['OrderAmount'];
                } else {
                    $orderStatus = "결제취소";
                    if ($item['OrderStatus'] === '9'){
                        $orderStatus = "결제취소(DB오류)";
                    }
                    /*생략*/
                }

                $data[] = [
                    // 테이블 값
                    'PayOrderIdx' => $item['PayOrderIdx'],
                    'PayOrderCode' => $item['PayOrderCode'],
                    'RelatedPayOrderIdx' => $item['RelatedPayOrderIdx'] ?? '',
                    /*생략*/
                    'ApprovedDatetime' => $item['ApprovedDatetime'] ?? '',
                    'OrderStatus' => $orderStatus,
                    'OrderType' => $orderType,
                ];
            }

            $this->data['data'] = $data;
            $this->conn = null;

            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 상담사별 티켓 잔량 조회
    function searchTicketData($param) : array
    {
        $this->desc = 'searchTicketData';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $sql = "SELECT COUNT(itm.SaleGoodsIdx) AS IssuedCount
                          , ccm.ClientControlIdx, ccm.ClientCustomerName, ccm.CCGroup, ccm.ClientCustomerCode
                          , ccm.CCTel
                          , sm.ServiceCompanyName
                          , tm.SaleGoodsIdx
                     FROM abc.ClientControl AS ccm
                     JOIN abc.ServiceControl AS sm
                       ON sm.ServiceControlIdx = ccm.ServiceControlIdx
                     JOIN abc.SaleGoods AS tm
                       ON tm.ClientControlIdx = ccm.ClientControlIdx
                LEFT JOIN abc.IssuedSaleGoods AS itm
                       ON itm.SaleGoodsIdx = tm.SaleGoodsIdx
                    WHERE ccm.ClientControlIdx = :ClientControlIdx
                      AND tm.TicketType = 2
                      AND ccm.IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx',$param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $data = [];
            $row = $stmt->fetch();
            if($row) {
                $data = [
                    'ClientControlIdx' => (int)$row['ClientControlIdx'],
                    'clientCustomerName'      => $row['ClientCustomerName'],
                    'cCGroup'                 => $row['CCGroup'],
                    'cCTel'                   => $row['CCTel'],
                    'clientCustomerCode'      => $row['ClientCustomerCode'],
                    'serviceCompanyName'      => $row['ServiceCompanyName'],
                    'oldIssuedCount'          => (int)$row['IssuedCount'],
                    'SaleGoodsIdx'         => (int)$row['SaleGoodsIdx'],
                ];
            }
            $this->data = $data;

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    //상담사관리::상담사 개별 등록
    function registConsultant($param) : array
    {
        $this->desc = 'registConsultant';
        try {
            // 계약사 식별자
            $ServiceControlIdx = $param['ServiceControlIdx'];

            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT ClientControlIdx
                    FROM abc.ClientControl
                    WHERE ServiceControlIdx = :ServiceControlIdx
                    AND ProductGroupIdx = :productGroupIdx
                    AND Depth = 1
                    AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ServiceControlIdx', $ServiceControlIdx, $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            // 부모 거래처 식별자
            $parentClientControlIdx = $stmt->fetch()['ClientControlIdx'];
            if(!$parentClientControlIdx) {
                throw new \Exception("질환검진권에 등록된 계약사가 아닙니다. 개발팀에 문의하세요","403");
            }

            $sql ="SELECT ClientCustomerCode
                   FROM abc.ClientControl
                   WHERE ClientCustomerName = :clientCustomerName
                    AND CCTel = :cCTel";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':clientCustomerName', $param['clientCustomerName'], $this->conn::PARAM_STR);
            $stmt->bindValue(':cCTel', $param['cCTel'], $this->conn::PARAM_STR);
            $stmt->execute();

            $isExist = $stmt->fetch();
            if($isExist) {
                throw new \Exception("상담사 중복 등록 - 계정 : ".$isExist['ClientCustomerCode'], "404");
            }

            $item = [
                'ServiceControlIdx' => (int)$ServiceControlIdx,
                'productGroupIdx'         => (int)$param['gIdx'],
                'clientCustomerName'      => $param['clientCustomerName'],
                'parentClientCustomerIdx' => (int)$parentClientControlIdx,
                'depth'                   => 2,
                'category'                => $param['category'],
                'cCGroup'                 => $param['cCGroup'],
                'cCTel'                   => $param['cCTel'],
                'cCManager'               => $param['clientCustomerName'],
                'latestAdminIP'           => $_SERVER['REMOTE_ADDR'],
            ];

            if(!isset($param['ClientControlIdx'])) {
                // generate id
                $item['clientCustomerCode'] = $this->generateClientCode($param['gIdx']);

                // generate qrurl :: TODO// 상담사는 qrUrl이 필요없나?
                $orgUrl = "https://d.g******com/abc/?hCode=" . $item['clientCustomerCode'];
                $result = (new NaverShortUrl())->getResult(['url' => $orgUrl]);
                if ($result['code'] !== 200) {
                    throw new \Exception("URL 생성에 실패하였습니다.", "400");
                }
                $response = json_decode($result['response'], true);
                $shortUrl = $response['result']['url'];
                if (!$shortUrl) {
                    throw new \Exception("URL 생성에 실패하였습니다.", 400);
                }

                $item['qRurl'] = "{$shortUrl}.qr";
            } else {
                $item['modDatetime'] = date('Y-m-d H:i:s');
            }

            $table = "***.ClientControl";
            $idx = isset($param['ClientControlIdx']) ? ['ClientControlIdx' => (int)$param['ClientControlIdx']] : [];

            $returnIdx = $this->insertUpdate($idx, $table, $item);
            if($returnIdx) {
                $ClientControlIdx = $returnIdx;
            } else {
                $ClientControlIdx = isset($param['ClientControlIdx']) ? (int)$param['ClientControlIdx'] : '';
            }

            if(!$ClientControlIdx) {
                throw new \Exception('not exist ClientControlIdx', '500');
            } else {
                // 무료 티켓 지급
                if(isset($param['serveCount'])) {
                    $serveCount = (int)$param['serveCount'];
                    if($serveCount > 0) {
                        $table = "***.SaleGoods";
                        $item = [
                            'ticketType' => 2,
                            'ClientControlIdx' => $ClientControlIdx,
                        ];
                        $sql = "SELECT SaleGoodsIdx FROM abc.SaleGoods
                                 WHERE ClientControlIdx = ".$ClientControlIdx. " AND TicketType = 2";
                        $stmt = $this->conn->query($sql);
                        $ticketIdx = $stmt->fetch();

                        // 티켓 meta 갱신
                        if(!$ticketIdx) {
                            $ticketIdx = $this->insertUpdate([],$table, $item);
                        } else {
                            $ticketIdx = (int)$ticketIdx['SaleGoodsIdx'];
                            $item['modDatetime'] = date('Y-m-d H:i:s');
                            $this->insertUpdate(['SaleGoodsIdx'=>$ticketIdx], $table, $item);
                        }

                        // 티켓 지급
                        $table = "***.IssuedSaleGoods";
                        $items = [];
                        for($i=0;$i<$serveCount;$i++) {
                            $items[] = [
                                'SaleGoodsIdx' => $ticketIdx,
                                'ClientControlIdx' => $ClientControlIdx,
                            ];
                        }
                        $this->bulkInsertUpdate([], $table, $items);
                    }
                }
            }
            $this->msg = "상담사 정보가 등록되었습니다.";
            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    //상담사 상태 업데이트
    function updateIsActiveClient($param) : array
    {
        $this->desc = 'updateIsActiveClient';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $idx = ['ClientControlIdx' => (int)$param['idx']];
            $table = '***.ClientControl';
            $value = $param['key'] === 'isActive' ? (int)$param['value'] : $param['value'];
            $item = [
                $param['key'] => $value,
                'modDatetime' => date('Y-m-d H:i:s'),
            ];
            $this->insertUpdate($idx, $table, $item);
            $this->msg = "사용여부가 변경되었습니다.";
            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }


    }

    // 엑셀 데이터 조회 :: 상담사 리스트
    function consultantData($param) : array
    {
        $this->desc = 'consultantData';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT
                          ccm.RegDatetime, ccm.ModDatetime, ccm.CCGroup, ccm.CCManager, ccm.CCTel
                        , ccm.ClientControlIdx, ccm.ClientCustomerCode, ccm.ClientCustomerName
                        , scm.ServiceCompanyName, ccm.ProductGroupIdx, IF(ccm.IsActive = b'1', 'Y','N') AS IsActive
                        , cdm.RegistrationPath
                     FROM
                          abc.ClientControl AS ccm
                     JOIN abc.ServiceControl AS scm
                       ON scm.ServiceControlIdx = ccm.ServiceControlIdx
                LEFT JOIN abc.DetailCustomer AS cdm
                       ON cdm.ClientControlIdx = ccm.ClientControlIdx
                    WHERE ccm.ProductGroupIdx = :productGroupIdx
                      AND ccm.Depth = 2
                ORDER BY ccm.RegDatetime DESC";

            $data = [];
            // 최근 상태 조회
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $pageUrl = 'https://d.g******com';
            if(isDev) {
                $pageUrl = 'http://td.g******com';
            }
            while ($item = $stmt->fetch()) {
                $data[$item['ClientControlIdx']] = [
                    // 테이블 값
                    'clientCustomerCode' => $item['ClientCustomerCode'],
                    'regDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                    'modDatetime' => substr($item['ModDatetime'], 0, 10) ?? '',
                    'registrationPath' => $item['RegistrationPath'],
                    'serviceCompanyName' => $item['ServiceCompanyName'],
                    'cCGroup' => $item['CCGroup'],
                    'cCManager' => $item['CCManager'],
                    'cCTel' => $item['CCTel'],
                    'ticketInfo' => [],
                    'payTicket' => 0,
                    'usedPayTicket' => 0,
                    'freeTicket' => 0,
                    'usedFreeTicket' => 0,
                    'clientUrl' => $pageUrl.'/abc/?hCode='.$item['ClientCustomerCode'],
                    'isActive' => $item['IsActive'],
                ];
            }

            $sql = "SELECT
                           SaleGoodsIdx, ClientControlIdx, TicketType
                      FROM abc.SaleGoods";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                    continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']] = [
                    'ticketType' => $row['TicketType'],
                    'issuedCount'      => 0,
                    'expiredCount'      => 0,
                ];
            }

            // 발행된 티켓 카운팅
            $sql = "SELECT
                          tm.ClientControlIdx, tm.SaleGoodsIdx, COUNT(itm.SaleGoodsIdx) AS IssuedTicketCount
                     FROM abc.SaleGoods AS tm
                LEFT JOIN abc.IssuedSaleGoods AS itm
                       ON itm.SaleGoodsIdx = tm.SaleGoodsIdx
                 GROUP BY tm.ClientControlIdx, tm.SaleGoodsIdx";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                    continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']]['issuedCount'] = (int)$row['IssuedTicketCount'];
            }

            // 사용된 티켓 카운팅
            $sql = "SELECT
                          tm.ClientControlIdx, tm.SaleGoodsIdx, COUNT(etm.SaleGoodsIdx) AS ExpiredTicketCount
                      FROM abc.SaleGoods AS tm
                 LEFT JOIN abc.ExpiredSaleGoods AS etm
                        ON etm.SaleGoodsIdx = tm.SaleGoodsIdx
                     WHERE etm.ExpiredType IN (1,2) #사용완료, 만료
                  GROUP BY tm.ClientControlIdx, tm.SaleGoodsIdx";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                    continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']]['expiredCount'] = (int)$row['ExpiredTicketCount'];
            }

            foreach ($data as $key => $value) {
                $data[$key]['registrationPath'] = $data[$key]['registrationPath'] === '2' ? '자동' : '수동';
                if($value['ticketInfo']) {
                    foreach ($value['ticketInfo'] as $k => $v) {
                        if($v['ticketType'] === '1') {
                            // pay
                            $data[$key]['usedPayTicket'] += (int)$v['issuedCount'];
                            if($v['issuedCount']) {
                                $data[$key]['payTicket'] += (int)$v['issuedCount'];
                            }
                            if($v['expiredCount']) {
                                $data[$key]['payTicket'] += (int)$v['expiredCount'];
                            }
                        } else {
                            // free
                            $data[$key]['usedFreeTicket'] += (int)$v['issuedCount'];
                            if($v['issuedCount']) {
                                $data[$key]['freeTicket'] += (int)$v['issuedCount'];
                            }
                            if($v['expiredCount']) {
                                $data[$key]['freeTicket'] += (int)$v['expiredCount'];
                            }
                        }
                    }
                }
                if($value['cCTel'] !== '') {
                    // 지역번호, 길이에 따른 전화번호 가공
                    if(substr($value['cCTel'],0,2) === '02') {
                        $offset = 2;
                    }else {
                        $offset = 3;
                    }
                    if(mb_strlen(substr($value['cCTel'], $offset)) < 8) {
                        $tel = substr_replace($value['cCTel'],'-',$offset+3,0);
                    } else {
                        $tel = substr_replace($value['cCTel'],'-',$offset+4,0);
                    }
                    $data[$key]['cCTel'] = substr_replace($tel,')',$offset,0);
                }
                unset($data[$key]['ticketInfo']);
            }

            $this->conn = null;
            return $data;

        } catch (\Exception $e) {
            throw $e;
        }
    }


    // 상담사 리스트 조회
    function consultantList($param) : array
    {
        $this->desc = 'consultantList';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['scm.ServiceCompanyName','ccm.CCGroup','ccm.ClientCustomerName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            if(isset($param['startDate'])) {
                if($param['startDate'] !== '' && $param['endDate'] !== '') {
                    $addSql .= " AND ccm.RegDatetime BETWEEN '".$param['startDate']." 00:00:00' AND '".$param['endDate']." 23:59:59' ";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, ccm.RegDatetime DESC ";
            } else {
                $orderSql .= ' ccm.RegDatetime DESC ';
            }


            $sql = "SELECT
                          ccm.RegDatetime, ccm.ModDatetime, ccm.CCGroup, ccm.CCManager, ccm.CCTel
                        , ccm.ClientControlIdx, ccm.ClientCustomerCode, ccm.ClientCustomerName
                        , scm.ServiceCompanyName, ccm.ProductGroupIdx, IF(ccm.IsActive = b'1', 'Y','N') AS IsActive
                        , cdm.RegistrationPath
                     FROM
                          abc.ClientControl AS ccm
                     JOIN abc.ServiceControl AS scm
                       ON scm.ServiceControlIdx = ccm.ServiceControlIdx
                LEFT JOIN abc.DetailCustomer AS cdm
                       ON cdm.ClientControlIdx = ccm.ClientControlIdx
                    WHERE ccm.ProductGroupIdx = :productGroupIdx
                      AND ccm.Depth = 2
                    {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();

            while ($item = $stmt->fetch()) {
                $data[$item['ClientControlIdx']] = [
                    // 테이블 값
                    'productGroupIdx' => $item['ProductGroupIdx'],
                    'registrationPath' => $item['RegistrationPath'],
                    'clientCustomerName' => $item['ClientCustomerName'],
                    /*생략*/
                    'isActive' => $item['IsActive'],
                    'ticketInfo' => [],
                ];
            }

            $sql = "SELECT
                           SaleGoodsIdx, ClientControlIdx, TicketType
                      FROM abc.SaleGoods";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                   continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']] = [
                    'ticketType' => $row['TicketType'],
                    /*생략*/
                    'expiredCount'      => 0,
                ];
            }

            // 발행된 티켓 카운팅
            $sql = "SELECT
                          tm.ClientControlIdx, tm.SaleGoodsIdx, COUNT(itm.SaleGoodsIdx) AS IssuedTicketCount
                     FROM abc.SaleGoods AS tm
                LEFT JOIN abc.IssuedSaleGoods AS itm
                       ON itm.SaleGoodsIdx = tm.SaleGoodsIdx
                 GROUP BY tm.ClientControlIdx, tm.SaleGoodsIdx";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                    continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']]['issuedCount'] = (int)$row['IssuedTicketCount'];
            }

            // 사용된 티켓 카운팅
            $sql = "SELECT
                          tm.ClientControlIdx, tm.SaleGoodsIdx, COUNT(etm.SaleGoodsIdx) AS expiredTicketCount
                      FROM abc.SaleGoods AS tm
                 LEFT JOIN abc.ExpiredSaleGoods AS etm
                        ON etm.SaleGoodsIdx = tm.SaleGoodsIdx
                     WHERE etm.ExpiredType IN (1,2) #사용완료, 만료
                  GROUP BY tm.ClientControlIdx, tm.SaleGoodsIdx";
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if(!isset($data[$row['ClientControlIdx']])) {
                    continue;
                }
                $data[$row['ClientControlIdx']]['ticketInfo'][$row['SaleGoodsIdx']]['expiredCount'] = (int)$row['expiredTicketCount'];
            }

            // 회사 식별자 조회
            $sql = "SELECT 
                          scm.ServiceControlIdx AS `value`, scm.ServiceCompanyName AS `text`
                      FROM abc.ServiceControl AS scm
                      JOIN abc.Items AS gm
                        ON scm.ServiceControlIdx = gm.ServiceControlIdx
                      JOIN abc.ClientControl as ccm
                        ON ccm.ServiceControlIdx = scm.ServiceControlIdx 
                       AND ccm.Depth = 1
                    WHERE scm.IsContract = b'1'
                      AND ccm.ProductGroupIdx = ".$param['gIdx']."
                 GROUP BY ccm.ClientControlIdx";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];

            $this->data['data'] = $data;
            $this->data['select::serviceCompany'] = $row;
            $this->conn = null;

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈관리 삭제
    function deleteGoods($param) : array
    {
        $this->desc = "model::deleteGoods";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['ItemsIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            // 해당 ItemsIdx 정보 조회
            $sql = "SELECT ItemsIdx
                      FROM abc.Items
                     WHERE ItemsIdx = :ItemsIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $ItemsInfo = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$ItemsInfo) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $table = "***.Items";
            $idx = [
                'ItemsIdx' => $param['ItemsIdx']
            ];
            $item = [
                'isUse' => b'0',
                'modDatetime' => date('Y-m-d H:i:s'),
            ];

            $this->insertUpdate($idx, $table, $item);

            $this->msg = "ItemsIdx 삭제완료";


            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈관리 등록
    function registGoods($param) : array
    {
        $this->desc = "model::registGoods";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['ItemsType'], $param['ServiceControlIdx'], $param['salesPrice'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                !in_array($param['ItemsType'], ['edit', 'register'])
                || !preg_match($this->pattern['num'], $param['salesPrice'])
                || ($param['goodsName'] && !preg_match($this->pattern['all'], $param['goodsName']))
            ) {
                throw new \Exception('파라미터가 올바르지 않습니다.', "400");
            }

            $sql = "SELECT ServiceCompanyName, ServiceCompanyId
                      FROM abc.ServiceControl
                     WHERE ServiceControlIdx = :ServiceControlIdx
                       AND IsContract = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ServiceControlIdx', $param['ServiceControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $serviceCompanyInfo = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$serviceCompanyInfo) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            if ($param['ItemsType'] === 'register') {
                // Items
                $table = "***.Items";
                $item = [
                    'ServiceControlIdx' => $param['ServiceControlIdx'],
                    'productGroupIdx' => $param['gIdx'],
                    'goodsName' => $param['goodsName'],
                    'salesPrice' => $param['salesPrice'],
                ];
                $this->data['ItemsIdx'] = $this->insertUpdate([], $table, $item);

                $this->msg = "ItemsIdx 등록완료";
            }
            if ($param['ItemsType'] === 'edit') {
                if (!isset($param['ItemsIdx'])) {
                    throw new \Exception('필수 파라미터가 없습니다.', "404");
                }

                // 해당 ItemsIdx 정보 조회
                $sql = "SELECT ItemsIdx
                          FROM abc.Items
                         WHERE ItemsIdx = :ItemsIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $ItemsInfo = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
                if (!$ItemsInfo) {
                    throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
                }

                // 해당 ItemsIdx 결제 이력 체크
                $sql = "SELECT PayOrderIdx 
                          FROM p.PayssItem
                         WHERE ItemsIdx = :ItemsIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $payHistory = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];
                if (count($payHistory) > 0){
                    throw new \Exception('결제 이력이 존재하여 수정이 불가합니다.', "400");
                }

                $table = "***.Items";
                $idx = [
                    'ItemsIdx' => $param['ItemsIdx']
                ];
                $item = [
                    'ServiceControlIdx' => $param['ServiceControlIdx'],
                    'productGroupIdx' => $param['gIdx'],
                    'goodsName' => $param['goodsName'],
                    'salesPrice' => $param['salesPrice'],
                    'modDatetime' => date('Y-m-d H:i:s'),
                ];

                $this->insertUpdate($idx, $table, $item);

                $this->msg = "ItemsIdx 수정완료";
            }

            // ClientControl 부모 식별자 없을시 생성 (Depth = 1)
            $sql = "SELECT ClientControlIdx FROM abc.ClientControl
                    WHERE ServiceControlIdx = :ServiceControlIdx
                      AND ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ServiceControlIdx', $param['ServiceControlIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $ClientControlIdx = $stmt->fetch($this->conn::FETCH_COLUMN) ?? 0;
            if (!$ClientControlIdx) {
                $table = "***.ClientControl";
                $item = [
                    'ServiceControlIdx' => $param['ServiceControlIdx'],
                    'productGroupIdx' => $param['gIdx'],
                    'ClientCustomerCode' => $serviceCompanyInfo['ServiceCompanyId'],
                    'ClientCustomerName' => $serviceCompanyInfo['ServiceCompanyName'],
                    'Depth' => 1,
                ];
                $this->data['parentClientCustomerIdx'] = $this->insertUpdate([], $table, $item);
            }

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈관리 조회(수정 Modal)
    function searchGoods($param) : array
    {
        $this->desc = "model::searchGoods";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['ItemsIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            // 해당 ItemsIdx 결제 이력 체크
            if ($param['searchType'] === 'edit') {
                $sql = "SELECT PayOrderIdx 
                          FROM p.PayssItem
                         WHERE ItemsIdx = :ItemsIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $payHistory = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];
                if (count($payHistory) > 0){
                    throw new \Exception('결제 이력이 존재하여 수정이 불가합니다.', "400");
                }
            }

            $sql = "SELECT 
                        gm.RegDatetime, gm.ItemsIdx, gm.ServiceControlIdx, 
                        gm.GoodsName, gm.SalesPrice, scm.ServiceCompanyName
                      FROM abc.Items gm
                      JOIN abc.ServiceControl scm
                        ON scm.ServiceControlIdx = gm.ServiceControlIdx
                     WHERE gm.ItemsIdx = :ItemsIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch() ?? [];
            if (!$row) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $this->data = [
                'regDatetime' => substr($row['RegDatetime'], 0, 10),
                'ItemsIdx' => $row['ItemsIdx'],
                'ServiceControlIdx' => $row['ServiceControlIdx'],
                'serviceCompanyName' => $row['ServiceCompanyName'],
                'goodsName' => $row['GoodsName'],
                'salesPrice' => $row['SalesPrice'],
            ];

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 굿즈관리 조회(결제경로 관리)
    function goodsList($param) : array
    {
        $this->desc = "model::goodsList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['scm.ServiceCompanyName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, gm.RegDatetime DESC ";
            } else {
                $orderSql .= ' gm.RegDatetime DESC ';
            }

            // 대상 전체 카운트
            $sql = "SELECT
                        gm.RegDatetime, gm.ItemsIdx, gm.ServiceControlIdx, 
                        gm.GoodsName, gm.SalesPrice, scm.ServiceCompanyName
                    FROM abc.Items gm
                    JOIN abc.ServiceControl scm
                      ON scm.ServiceControlIdx = gm.ServiceControlIdx
                   WHERE gm.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND scm.IsContract = TRUE #사용처가 계약상태인지 확인 필요
                     AND gm.IsUse = TRUE
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                $data[] = [
                    // 테이블 값
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                    /*생략*/
                    'SalesPrice' => $item['SalesPrice'],
                ];
            }

            $sql = "SELECT 
                        ServiceControlIdx AS `value`, ServiceCompanyName AS `text`
                    FROM abc.ServiceControl
                    WHERE IsContract = b'1'";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];

            $this->data['data'] = $data;
            $this->data['select::serviceCompany'] = $row;
            $this->conn = null;

            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 등록된 쿠폰 리스트 조회
    function registCouponList($param) : array
    {
        $this->desc = 'registCouponList';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }
            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['cm.CouponName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, cm.TicketsIdx DESC ";

            } else {
                $orderSql .= ' cm.TicketsIdx DESC';
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                          cm.TicketsIdx, cm.CouponType, cm.CouponCode, cm.CouponName, cm.DiscountMethod
                        , cm.DiscountAmount, cm.DiscountRate, cm.ServiceControlIdx, cm.ClientControlIdx
                        , cm.UseStartDate, cm.UseEndDate, cm.CouponStatus, cm.RegDatetime, cm.ModDatetime
                        , sm.ServiceCompanyName   
                      FROM abc.Tickets AS cm
                      JOIN abc.ServiceControl AS sm
                        ON sm.ServiceControlIdx = cm.ServiceControlIdx 
                     WHERE cm.ProductGroupIdx = :productGroupIdx
                       AND cm.IsUse = b'1'
                          {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                $data[$item['TicketsIdx']] = [
                    'TicketsIdx'         => $item['TicketsIdx'],
                    'couponType'              => $item['CouponType'],
                    /*생략*/
                    'regDatetime'             => substr($item['RegDatetime'], 0, 10) ?? '',
                    'modDatetime'             => substr($item['ModDatetime'], 0, 10) ?? '',
                ];
            }

            // 회사 식별자 조회
            $sql = "SELECT 
                          scm.ServiceControlIdx AS `value`, scm.ServiceCompanyName AS `text`
                      FROM abc.ServiceControl AS scm
                      JOIN abc.Items AS gm
                        ON scm.ServiceControlIdx = gm.ServiceControlIdx
                      JOIN abc.ClientControl as ccm
                        ON ccm.ServiceControlIdx = scm.ServiceControlIdx 
                       AND ccm.Depth = 1
                    WHERE scm.IsContract = b'1'
                      AND ccm.ProductGroupIdx = ".$param['gIdx']."
                 GROUP BY ccm.ClientControlIdx";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];

            $this->data['data'] = $data;
            $this->data['select::serviceCompany'] = $row;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사 계정 생성 ** recursive 주의 **
    function generateClientCode($serviceIdx, $i = 0): string
    {
        try {
            if($i > 10) {
                throw new Exception('create error ID', '503');
            }

            switch ($serviceIdx) {
                case '6' :
                    $idHeader = 'icg_';
                    break;
                case '4' :
                    $idHeader = 'tst_';
                    break;
                case '7' :
                    $idHeader = 'kfg_';
                    break;
                default :
                    $idHeader = 'gen_';
            }

            $rand_str = bin2hex(random_bytes(4));
            $id = $idHeader.$rand_str;

            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 중복 id 확인
            $sql = "SELECT ClientCustomerCode FROM abc.ClientControl WHERE ClientCustomerCode = '".$id."'";
            $stmt = $this->conn->query($sql);
            $isExist = $stmt->fetch();
            // id 중복시 재귀
            if($isExist) {
                $i++;
                return $this->generateClientCode($serviceIdx, $i);
            } else {
                return $id;
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 쿠폰 생성 ** recursive 주의 **
    function generateCoupon($serviceIdx, $i=0): string
    {
        try {
            if($i > 10) {
                throw new \Exception('create error COUPON', '503');
            }

            switch ($serviceIdx) {
                case '6' :
                    $cpnHeader = 'ICG';
                    break;
                case '4' :
                    $cpnHeader = 'TST';
                    break;
                case '7' :
                    $cpnHeader = 'KFG';
                    break;
                default :
                    $cpnHeader = 'GEN';
            }

            $rand_str = strtoupper(bin2hex(random_bytes(8)));
            $coupon = $cpnHeader.$rand_str;

            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 중복 coupon 확인
            $sql = "SELECT CouponCode FROM abc.Tickets WHERE CouponCode = '".$coupon."'";
            $stmt = $this->conn->query($sql);
            $isExist = $stmt->fetch();
            // coupon 중복시 재귀
            if($isExist) {
                $i++;
                return $this->generateCoupon($serviceIdx, $i);
            } else {
                return $coupon;
            }

        } catch (\Exception $e) {
            throw $e;
        }

    }

    // 쿠폰 등록
    function couponRegist($param) : array
    {
        $this->desc = 'couponRegist';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $this->msg = '쿠폰 데이터가 변경되었습니다.';
            $this->conn->beginTransaction();

            $item = [];
            $item['couponType'] = $param['couponType'] ? (int)$param['couponType'] : 1;
            $item['couponName'] = $param['couponName'] ? mb_substr($param['couponName'],0,50) : '';
            $item['discountMethod'] = $param['discountMethod'] ? (int)$param['discountMethod'] : 1;
            $item['discountAmount'] = 0;
            $item['discountRate'] = 0;
            if($item['discountMethod']  === 2) {
                //할인가
                $item['discountAmount'] = (int)$param['amount'];
            } else {
                //할인율
                $item['discountRate'] = (int)$param['amount'];
            }
            $item['ServiceControlIdx'] = $param['parentClientCustomerIdx'] ? (int)$param['parentClientCustomerIdx'] : '';
            $item['ClientControlIdx'] = isset($param['consultantId']) ? (int)$param['ClientControlIdx'] : 'null';
            $item['useStartDate'] = $param['useStartDate'] ? date('Y-m-d', strtotime($param['useStartDate'])) : '';
            $item['useEndDate'] = $param['useEndDate'] ? date('Y-m-d', strtotime($param['useEndDate'])) : '';
            $item['couponStatus'] = $param['couponStatus'] ? (int)$param['couponStatus'] : 1;
            $item['productGroupIdx'] = (int)$param['gIdx'];

            $table = "***.Tickets";
            $idx = isset($param['TicketsIdx']) ? ['TicketsIdx' => (int)$param['TicketsIdx']] : [];

            if(isset($param['TicketsIdx'])) {
                $item['modDatetime'] = date('Y-m-d H:i:s');
            } else {
                $item['couponCode'] = $this->generateCoupon($param['gIdx']);
            }
            $TicketsIdx = $this->insertUpdate($idx, $table, $item);

            if($TicketsIdx > 0) {
                $table = '***.IssuedTickets';
                $couponItem = [
                    'TicketsIdx' => $TicketsIdx,
                    'couponCode' => $item['couponCode'],
                    'ClientControlIdx' => $item['ClientControlIdx'],
                ];

                $TicketsIdx = $this->insertUpdate([], $table, $couponItem);
                $this->data['TicketsIdx'] = $TicketsIdx;
                $this->msg = '쿠폰이 발행되었습니다.';
            }

            $this->conn->commit();
            $this->conn = null;

            return $this->response();

        } catch (\Exception $e) {
            $this->conn->rollBack();
            $this->conn = null;

            throw $e;
        }
    }

    //상담사 조회
    function searchConsultantId($param) : array
    {
        $this->desc = 'searchConsultantId';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $sql = " SELECT ClientControlIdx, ClientCustomerName
                       FROM abc.ClientControl
                      WHERE ServiceControlIdx = :parentClientControlIdx
                        AND ClientCustomerCode  = BINARY(:clientCustomerCode)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':parentClientControlIdx',$param['parentClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':clientCustomerCode', $param['clientCustomerCode']);
            $stmt->execute();

            $row = $stmt->fetch($this->conn::FETCH_ASSOC);

            $ClientControlIdx = '';
            $clientCustomerName = '';
            if($row) {
                $ClientControlIdx = $row['ClientControlIdx'];
                $clientCustomerName = $row['ClientCustomerName'];
            }

            $this->data = [
                'ClientControlIdx' => $ClientControlIdx,
                'ClientCustomerName'      => $clientCustomerName,
            ];

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    //**할당 이력
    function insureIbHistory($param) : array
    {
        $this->desc = 'model::insureIbHistory';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 할당 기준 조회
            $sql = " SELECT   scm.ServiceCompanyName
                            , asmh.ServiceControlIdx, asmh.TotalServeLimit, asmh.RegDatetime
                       FROM abc.`ServeAllocationHistory` AS asmh
                       JOIN abc.ServiceControl AS scm
                		 ON scm.ServiceControlIdx = asmh.ServiceControlIdx
                      WHERE asmh.ProductGroupIdx = ".$param['gIdx']."
                        AND scm.IsContract = b'1'
                   ORDER BY asmh.RegDatetime ASC";
            $stmt = $this->conn->query($sql);
            $data = [];
            while($row = $stmt->fetch()) {
                // 총 제공량(최근 설정)
                $data[$row['ServiceControlIdx']]['ServiceCompanyName'] = $row['ServiceCompanyName'];
                $data[$row['ServiceControlIdx']]['TotalServeLimit'] = $row['TotalServeLimit'];
                // 할당 설정 일자(최근)
                $data[$row['ServiceControlIdx']]['RegDatetime'] = $row['RegDatetime'];
                // 총 제공량(누적)
                if(isset($data[$row['ServiceControlIdx']]['AccumalServeCount'])) {
                    $data[$row['ServiceControlIdx']]['AccumalServeCount'] = $data[$row['ServiceControlIdx']]['AccumalServeCount'] + $row['TotalServeLimit'];
                } else {
                    $data[$row['ServiceControlIdx']]['AccumalServeCount'] = $row['TotalServeLimit'];
                    $data[$row['ServiceControlIdx']]['AccumalAllocationCount'] = 0;
                    $data[$row['ServiceControlIdx']]['LatestAllocationCount'] = 0;
                }
            }
            $total = count($data);
            $this->setPagination($total, $param);
            // 할당량 조회
            $sql = "  SELECT
                            mam.PaysIdx, mam.ServiceControlIdx, mam.RegDatetime
                        FROM abc.AllocationMembers AS mam
                        WHERE mam.ProductGroupIdx = ".$param['gIdx'];
            $stmt = $this->conn->query($sql);
            while($row = $stmt->fetch()) {
                if($row['RegDatetime'] >= $data[$row['ServiceControlIdx']]['RegDatetime']) {
                    // 최근 할당량
                    $data[$row['ServiceControlIdx']]['LatestAllocationCount'] += 1;
                }
                // 총 누적 할당량
                $data[$row['ServiceControlIdx']]['AccumalAllocationCount'] += 1;
            }
            $this->data['data'] = $data;
            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **상담결과 엑셀 업로드
    function uploadConsultingResult($param): array
    {
        $this->desc = 'uploadConsultingResult';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param[0]['consultingFile'])) {
                throw new \Exception("필수 파라미터들이 없습니다.", "404");
            }

            $serverFilename = $param[0]['consultingFile']['tmp_name'];
            $pcFilename = $param[0]['consultingFile']['name'];

            $spreadsheet = new SpreadsheetFactory();
            $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
            if ($result['code'] !== 200) {
                throw new \Exception('read error', 400);
            }

            $spreadData = $result['data'];
            if (count($spreadData) < 1) {
                throw new \Exception("양식이 입력되지 않았습니다.", "401");
            }
            unset($spreadData[0]);

            $success = 0;
            $failure = 0;

            $csTable = "***.Consultant";
            $icmTable = "***.Contract";
            foreach ($spreadData as $value) {
                if (!array_filter($value)) {
                    continue;
                }

                if (!$value[1] || !$value[2] || !$value[4]) {
                    throw new \Exception("회원ID, 주문상품ID 및 거래처ID 입력은 필수입니다.", "401");
                }

                // Consultant UPDATE
                $csIdx = [
                    'UsersIdx' => (int)$value[1],
                    'orderIdx' => (int)$value[2]
                ];

                if ($value[6] && $value[7] && $value[8]) {
                    $csItems = [
                        'consultantIdx' => (int)$value[6],
                        'consultantName' => $value[7],
                        'consultantFixDate' => date('Y-m-d', strtotime($value[8])),
                        'modDatetime' => date('Y-m-d H:i:s'),
                    ];
                } else {
                    $failure++;
                    continue;
                }

                if ($value[9] && $value[10]) {
                    $csItems['statusCode'] = trim($value[9]);
                    $csItems['consultDate1'] = date('Y-m-d H:i:s', strtotime($value[10]));

                    if ($value[11] && $value[12]) {
                        $csItems['statusCode'] .= trim($value[11]);
                        $csItems['consultDate2'] = date('Y-m-d H:i:s', strtotime($value[12]));

                        if ($value[13] && $value[14]) {
                            $csItems['statusCode'] .= trim($value[13]);
                            $csItems['consultDate3'] = date('Y-m-d H:i:s', strtotime($value[14]));
                        }
                    }

                    if (strlen($csItems['statusCode']) > 3) {
                        $failure++;
                        continue;
                    }
                }

                // Contract UPDATE
                $icmIdx = [];
                $icmItems = [];
                if ($value[15] && $value[16] && $value[17] && $value[18] && $value[19]) {
                    //유효한 Insurance인지 체크
                    $sql = "SELECT 
                                im.InsureanceIdx
                            FROM abc.Insureance pim
                            JOIN abc.Insureance im
                              ON im.ParentItemIdx = pim.InsureanceIdx
                            JOIN abc.ServiceControl scm
                              ON scm.ServiceControlIdx = pim.ServiceControlIdx
                           WHERE scm.ServiceCompanyName = :serviceCompanyName
                             AND pim.ItemCode = :parentItemCode
                             AND im.ItemCode = :itemCode
                             AND pim.IsUse = b'1'
                             AND im.IsUse = b'1'";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':serviceCompanyName', $value[4], $this->conn::PARAM_INT);
                    $stmt->bindValue(':parentItemCode', $value[15]);
                    $stmt->bindValue(':itemCode', $value[16]);
                    $stmt->execute();

                    $row = $stmt->fetch($this->conn::FETCH_ASSOC);
                    $InsureanceIdx = $row['InsureanceIdx'] ?? 0;
                    if ($InsureanceIdx) {
                        $icmIdx = [
                            'UsersIdx' => (int)$value[1],
                            'orderIdx' => (int)$value[2],
                            'InsureanceIdx' => (int)$InsureanceIdx
                        ];

                        $icmItems = [
                            'UsersIdx' => (int)$value[1],
                            'orderIdx' => (int)$value[2],
                            'InsureanceIdx' => (int)$InsureanceIdx,
                            'monthlyPremium' => (int)$value[17],
                            'dueDay' => (int)$value[18],
                            'contractDate' => date('Y-m-d', strtotime($value[19])),
                        ];
                    }
                }

                $this->conn->beginTransaction();
                $this->insertUpdate($csIdx, $csTable, $csItems);
                if ($icmIdx) {
                    $this->insertDuplicate($icmIdx, $icmTable, $icmItems, '');
                }
                $this->conn->commit();
                $success++;
            }

            $this->data['success'] = $success;
            $this->data['failure'] = $failure;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    // **상담결과 엑셀 다운로드
    function consultingResultDown($param): void
    {
        $this->desc = 'consultingResultDown';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $data = [];
            $sql = "SELECT
                        o.RegDatetime, o.UsersIdx, o.PaysIdx, m.Name, 
                        scm.ServiceCompanyName, mts.IsComplete, 
                        cs.ConsultantIdx, cs.ConsultantName, cs.ConsultantFixDate, cs.StatusCode,
                        cs.ConsultDate1, cs.ConsultDate2, cs.ConsultDate3, cs.***TransferDatetime,
                        pim.ItemCode AS ParentItemCode, pim.ItemName AS ParentItemName, 
                        im.ItemCode, im.ItemName, icm.MonthlyPremium, icm.DueDay, icm.ContractDate
                    FROM abc.AllocationMembers mam
                    JOIN abc.Consultant cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
                    JOIN o.Pays AS o
                      ON o.PaysIdx = mam.PaysIdx
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN abc.ServiceControl scm
                      ON scm.ServiceControlIdx = mam.ServiceControlIdx
               LEFT JOIN abc.TransferUsers mts
                      ON (mts.UsersIdx, mts.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
               LEFT JOIN abc.Contract icm
                      ON (icm.UsersIdx, icm.PaysIdx) = (mts.UsersIdx, mts.PaysIdx)
               LEFT JOIN abc.Insureance im
                      ON im.InsureanceIdx = icm.InsureanceIdx
               LEFT JOIN abc.Insureance pim
                      ON pim.InsureanceIdx = im.ParentItemIdx
                   WHERE mm.IsOut = b'0' #탈퇴회원 제외
                     AND tm.MembersIdx IS NULL
                     AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND o.IsActive = b'1' #활성회원 선별
                     AND scm.ServiceControlIdx <> 4
                     AND scm.TransferMethodCode = 2 #수동전송인 거래처만 특정";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $statusCode = $item['StatusCode'] ? str_split($item['StatusCode']) : [];

                $data[$item['OrderIdx']] = [
                    // 기본정보
                    'regDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                    'UsersIdx' => $item['UsersIdx'],
                    'orderIdx' => $item['OrderIdx'],
                    'name' => $item['Name'],
                    'serviceCompanyName' => $item['ServiceCompanyName'],
                    //TODO:: 수동전송일 경우, ***TransferDatetime가 업데이트 되지 않음으로 항상 null 상태 -> 해당 부분 사업부가 인지하고 있는지 불명
                    'isSent' => $item['***TransferDatetime'] ? 'Y' : 'N',
                    // 수정필드
                    'consultantIdx' => $item['ConsultantIdx'],
                    'consultantName' => $item['ConsultantName'],
                    'consultantFixDate' => $item['ConsultantFixDate'],
                    'statusCode1' => $statusCode[0] ?? '',
                    'consultDate1' => $item['ConsultDate1'],
                    'statusCode2' => $statusCode[1] ?? '',
                    'consultDate2' => $item['ConsultDate2'],
                    'statusCode3' => $statusCode[2] ?? '',
                    'consultDate3' => $item['ConsultDate3'],
                    'parentItemCode' => $item['ParentItemCode'],
                    'itemCode' => $item['ItemCode'],
                    'monthlyPremium' => $item['MonthlyPremium'],
                    'dueDay' => $item['DueDay'],
                    'contractDate' => $item['ContractDate'],
                ];
            }

            $headers = ['신청일자', '회원ID', '주문상품ID', '이름', 'IB거래처', '질병 제공', '상담자ID', '상담자', '상담배정일시', '1차상담', '1차상담일', '2차상담', '2차상담일', '3차상담', '3차상담일', '**사코드', '**상품코드', '월납**료', '납기', '계약일'];

            $spreadsheet = new SpreadsheetFactory();
            $spreadsheet->downloadSheet($headers, $data, '수동전송_**상담내역');

            $this->conn = null;
            exit;

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }
    
    function searchConsultingResult($param): array
    {
        $this->desc = 'searchConsultingResult';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $this->data['***TransferDatetime'] = $param['***TransferDatetime'] ?? '';
            $this->data['consultantFixDate'] = $param['consultantFixDate'] ?? '';
            $this->data['consultDate1'] = $param['consultDate1'] ?? '';
            $this->data['consultDate2'] = $param['consultDate2'] ?? '';
            $this->data['consultDate3'] = $param['consultDate3'] ?? '';

            if (!isset($param['serviceCompanyIdx'], $param['name'], $param['phone'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            if (
                !preg_match($this->pattern['num'], $param['serviceCompanyIdx'])
                || !preg_match($this->pattern['all'], $param['name'])
                || !preg_match($this->pattern['num'], $param['phone'])
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            if (!isDev && (int)$param['serviceCompanyIdx'] === 5){
                $header = [
                    "mrkim_access: {$this->apiInfisKey}",
                    "Content-Type: application/json; charset=utf-8",
                ];

                $reqParam = [
                    'name' => $param['name'],
                    'phone' => substr($param['phone'], 0, 3) . '-' . substr($param['phone'], 3, 4) . '-' . substr($param['phone'], 7, 4),
                ];

                $result = $this->curl("GET", $this->apiInfisUrl, $header, $reqParam);
                if ($result['code'] !== 200) {
                    throw new \Exception('Infis 통신 실패', "400");
                }
                $response = json_decode($result['response'], true);
                $this->data['requestMemo'] = $response[0]['requestMemo'] ?? '';
            }

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    //**상담결과 조회 - 얼리큐
    function consultingResList($param): array
    {
        $this->desc = "model::consultingResList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['prm.CalcDate', 'm.Name', 'scm.ServiceCompanyName', 'cs.ConsultantName', 'pim.ItemName', 'im.ItemName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'cs.***TransferDatetime') {
                        switch ($param['value']) {
                            case 'Y':
                                $addSql .= " AND {$param['keyword']} IS NOT NULL";
                                break;
                            case 'N':
                                $addSql .= " AND {$param['keyword']} IS NULL";
                                break;
                        }
                    } else if (in_array($param['keyword'], ['cs.StatusCode1', 'cs.StatusCode2', 'cs.StatusCode3'])) {
                        switch ($param['value']) {
                            case 'A':
                            case '계약체결':
                                $statusCodeDef = 'A';
                                break;
                            case 'B':
                            case '종결':
                                $statusCodeDef = 'B';
                                break;
                            case 'C':
                            case '결번':
                                $statusCodeDef = 'C';
                                break;
                            case 'D':
                            case '상담거절':
                                $statusCodeDef = 'D';
                                break;
                            case 'E':
                            case '무응답':
                                $statusCodeDef = 'E';
                                break;
                            case 'F':
                            case '중복':
                                $statusCodeDef = 'F';
                                break;
                            case 'G':
                                /*생략*/
                                break;
                            case 'M':
                            case '상담':
                                $statusCodeDef = 'M';
                                break;
                            case 'Z':
                            case '기타':
                                $statusCodeDef = 'Z';
                                break;
                            default:
                                $statusCodeDef = '';
                                break;
                        }
                        $keyword = substr($param['keyword'], 0, -1);
                        $trial = substr($param['keyword'], -1);

                        if ($trial === '1') {
                            $statusCodeDef = "{$statusCodeDef}%";
                        } else if ($trial === '2') {
                            $statusCodeDef = "_{$statusCodeDef}%";
                        } else if ($trial === '3') {
                            $statusCodeDef = "__{$statusCodeDef}";
                        }

                        $addSql .= " AND {$keyword} LIKE '{$statusCodeDef}'";
                    } else {
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                    }
                }
            }
            // 대상 전체 카운트
            $sql = "SELECT
                        prm.CalcDate, o.UsersIdx, o.PaysIdx,
                        m.Name, m.Phone, tm.MembersIdx AS TestMembers,
                        scm.ServiceControlIdx, scm.ServiceCompanyName, 
                        cs.ConsultantName, cs.StatusCode, cs.ConsultantFixDate, 
                        cs.ConsultDate1, cs.ConsultDate2, cs.ConsultDate3, cs.***TransferDatetime,
                        pim.ItemName AS ParentItemName, im.ItemName, 
                        icm.MonthlyPremium, icm.DueDay, icm.ContractDate
                    FROM abc.AllocationMembers mam
                    JOIN abc.Report prm
                      ON (prm.UsersIdx, prm.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
                    JOIN abc.Consultant cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
                    JOIN o.Pays AS o
                      ON o.PaysIdx = mam.PaysIdx
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN abc.ServiceControl scm
                      ON scm.ServiceControlIdx = mam.ServiceControlIdx
                    JOIN abc.TransferUsers mts
                      ON (mts.UsersIdx, mts.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
               LEFT JOIN abc.Contract icm
                      ON (icm.UsersIdx, icm.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
               LEFT JOIN abc.Insureance im
                      ON im.InsureanceIdx = icm.InsureanceIdx
               LEFT JOIN abc.Insureance pim
                      ON pim.InsureanceIdx = im.ParentItemIdx
                   WHERE mm.IsOut = b'0' #탈퇴회원 제외
                     AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND o.IsActive = b'1' #활성회원 선별
                     AND prm.ReportType = 2
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= " ORDER BY prm.CalcDate DESC ";
            $sql .= " LIMIT :start, :entry ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data[$item['OrderIdx']] = [
                    // 테이블 값
                    'CalcDate' => $item['CalcDate'],
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'Name' => $item['Name'],
                    'Phone' => $item['Phone'],
                    'TestMembers' => $item['TestMembers'],
                    'ServiceControlIdx' => $item['ServiceControlIdx'],
                    'ServiceCompanyName' => $item['ServiceCompanyName'],
                    'ConsultantName' => $item['ConsultantName'],
                    'StatusCode' => $item['StatusCode'],
                    'ParentItemName' => $item['ParentItemName'],
                    'ItemName' => $item['ItemName'],
                    'MonthlyPremium' => $item['MonthlyPremium'],
                    'DueDay' => $item['DueDay'],
                    'ContractDate' => $item['ContractDate'],
                    // Modal 값
                    //TODO:: 수동전송일 경우, ***TransferDatetime가 업데이트 되지 않음으로 항상 null 상태 -> 해당 부분 사업부가 인지하고 있는지 불명
                    '***TransferDatetime' => $item['***TransferDatetime'],
                    'ConsultantFixDate' => $item['ConsultantFixDate'],
                    'ConsultDate1' => $item['ConsultDate1'],
                    'ConsultDate2' => $item['ConsultDate2'],
                    'ConsultDate3' => $item['ConsultDate3'],
                ];
            }

            $this->data['data'] = $data;
            $this->conn = null;

            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **IB관리 더보기 옵션 조회
    function searchIbUserData($param): array
    {
        $this->desc = 'model::searchIbUserData';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'], $param['orderIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            $sql = "SELECT
                        o.PaysIdx, 
                        scm.TransferMethodCode,
                        mam.RegDatetime AS ClientRegDate,
                        mts.RegDatetime AS TransferRegDate,
                        cs.AppointmentDate, cs.AppointmentDay, cs.AppointmentHour
                     FROM abc.Users AS mm
                     JOIN o.Pays AS o
                       ON o.UsersIdx = mm.UsersIdx
                LEFT JOIN abc.AllocationMembers AS mam
                       ON mam.UsersIdx = mm.UsersIdx
                LEFT JOIN abc.ServiceControl AS scm
                       ON scm.ServiceControlIdx = mam.ServiceControlIdx
                LEFT JOIN abc.TransferUsers AS mts
                       ON mts.PaysIdx = mam.PaysIdx
                LEFT JOIN abc.Consultant AS cs
                       ON cs.UsersIdx = mm.UsersIdx
                      AND cs.PaysIdx = o.PaysIdx
                    WHERE mm.IsOut = b'0'
                      AND o.ProductGroupIdx = :productGroupIdx
                      AND o.IsActive = b'1'
                      AND o.PaysIdx = :orderIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data = [
                    'TransferMethodCode' => $row['TransferMethodCode'] ?? '',
                    'ClientRegDate' => $row['ClientRegDate'] ? substr($row['ClientRegDate'], 0, 10) : '',
                    'TransferRegDate' => $row['TransferRegDate'] ? substr($row['TransferRegDate'], 0, 10) : '',
                    'AppointmentDate' => $row['AppointmentDate'] ?? '',
                    'AppointmentDay' => $row['AppointmentDay'] ?? '',
                    'AppointmentHour' => $row['AppointmentHour'] ?? '',
                ];
            }
            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 전체 다운로드
    function allDown($param): void
    {
        $this->desc = 'model::allDown';
        try {
            if (!isset($param['orderIdx'], $param['target'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (!in_array($param['target'], ['ib', 'disease'])) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }

            $orderIdx = [];
            if ($param['orderIdx']) {
                $orderIdx = json_decode($param['orderIdx'], true);
            }
            if (count($orderIdx) < 1) {
                throw new \Exception("다운로드 대상자를 선택하지 않았습니다.", "400");
            }

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $target = $param['target'];
            $files = [];
            $orderList = implode(',', $orderIdx);
            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);
            $filePath = "";
            if ($target === 'ib') {
                $items = [];
                $sql = "SELECT 
                            mam.UsersIdx, mam.PaysIdx, mts.IsComplete,
                            m.Name, m.Gender, m.Birth1, m.Birth2
                        FROM abc.AllocationMembers mam
                        JOIN abc.Users mm
                          ON mm.UsersIdx = mam.UsersIdx
                        JOIN abc.Members m
                          ON m.MembersIdx = mm.MembersIdx
                   LEFT JOIN abc.TransferUsers mts
                          ON (mts.UsersIdx, mts.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
                       WHERE mam.PaysIdx IN ({$orderList})
                         AND mm.IsOut = b'0'";
                $stmt = $this->conn->query($sql);
                while ($row = $stmt->fetch()) {
                    $gender = $row['Gender'] === '1' ? '남' : ($row['Gender'] === '2' ? '여' : '');
                    $age = convertAging($row['Birth1'] . $row['Birth2'], date('Y-m-d'));
                    $files[$row['OrderIdx']] = [
                        'filename' => "{$row['UsersIdx']}_{$row['OrderIdx']}_{$param['gIdx']}",
                        'orderIdx' => $row['OrderIdx'],
                        'UsersIdx' => $row['UsersIdx'],
                        'gIdx' => $param['gIdx'],
                        'downloadName' => "{$row['UsersIdx']}_{$row['Name']}_{$gender}_{$age}",
                    ];
                    if (!$row['IsComplete']) {
                        $items[] = [
                            'UsersIdx' => $row['UsersIdx'],
                            'orderIdx' => $row['OrderIdx'],
                            'isComplete' => true,
                        ];
                    }

                }
                if (count($items) > 0) {
                    $table = "***.TransferUsers";
                    $this->bulkInsertUpdate([], $table, $items);
                }

                $filePath = "{$dir}/image/datashare/priv/ibReport/";
            } else if ($target === 'disease') {
                $sql = "SELECT 
                            prm.UsersIdx, prm.PaysIdx, prm.ReportType, prm.`Uuid`,
                            m.Name
                        FROM abc.Report prm
                        JOIN abc.AllocationMembers mam
                          ON (mam.UsersIdx, mam.PaysIdx) = (prm.UsersIdx, prm.PaysIdx)
                        JOIN abc.Users mm
                          ON mm.UsersIdx = prm.UsersIdx
                        JOIN abc.Members m
                          ON m.MembersIdx = mm.MembersIdx
                       WHERE prm.PaysIdx IN ({$orderList})
                         AND mm.IsOut = b'0'
                    ORDER BY prm.NhisPreviewListIdx DESC";
                $stmt = $this->conn->query($sql);
                while ($row = $stmt->fetch()) {
                    if (isset($files[$row['OrderIdx']])) {
                        continue;
                    }
                    $files[$row['OrderIdx']] = [
                        'filename' => "{$row['UsersIdx']}_{$row['OrderIdx']}_{$row['ReportType']}",
                        'uuid' => $row['Uuid'],
                        '***Type' => $row['ReportType'],
                        'downloadName' => "{$row['UsersIdx']}_{$row['Name']}",
                    ];
                    $files[$row['OrderIdx']]['downloadName'] .= $row['ReportType'] == '1' ? "_테스트" : ($row['ReportType'] == '2' ? "_질환" : "");
                }

                $filePath = "{$dir}/image/datashare/priv/u_*****_u/";
            }

            if (count($files) === 0 || count($orderIdx) != count($files)) {
                throw new \Exception("다운 받을 수 없는 대상자들입니다. 다시 선택하십시오.", "400");
            }

            $zip = new \ZipArchive();

            $time = time();
            $zipName = "{$filePath}{$target}_{$param['gIdx']}_{$time}.zip";
            if (!$zip->open($zipName, \ZipArchive::CREATE)) {
                throw new \Exception("open error", "451");
            }

            if ($target === 'ib') {
                foreach ($files as $item) {
                    if (!file_exists("{$filePath}{$item['filename']}.pdf")) {
                        $userData = $this->getIbData($item);
                        (new Pdf())->createIbPdf($userData);
                    }
                    $zip->addFile("{$filePath}{$item['filename']}.pdf", "{$item['downloadName']}.pdf");
                }
            } else if ($target === 'disease') {
                foreach ($files as $item) {
                    if (!file_exists("{$filePath}{$item['filename']}.pdf")) {
                        $token = $this->createMedtekToken();
                        if (!$token) {
                            throw new \Exception('u2 request error', '401');
                        }
                        $reqParam = $item;
                        $reqParam['u2Token'] = $token;

                        $this->getU2Pdf($reqParam);
                    }
                    $zip->addFile("{$filePath}{$item['filename']}.pdf", "{$item['downloadName']}.pdf");
                }
            }

            $zip->close();
            $downZipName = $target . "_" . date("Y-m-d") . ".zip";

            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=$downZipName");
            readfile($zipName);
            unlink($zipName);
            exit;

        } catch (\Exception $e) {
            throw $e;
        }
    }


    // **상담결과 조회 - 질환
    function consultingResultList($param): array
    {
        $this->desc = "model::consultingResultList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['o.RegDatetime', 'm.Name', 'scm.ServiceCompanyName', 'cs.ConsultantName', 'pim.ItemName', 'im.ItemName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'cs.***TransferDatetime') {
                        switch ($param['value']) {
                            case 'Y':
                                $addSql .= " AND {$param['keyword']} IS NOT NULL";
                                break;
                            case 'N':
                                $addSql .= " AND {$param['keyword']} IS NULL";
                                break;
                        }
                    } else if (in_array($param['keyword'], ['cs.StatusCode1', 'cs.StatusCode2', 'cs.StatusCode3'])) {
                        switch ($param['value']) {
                            case 'A':
                            case '계약체결':
                                $statusCodeDef = 'A';
                                break;
                            case 'B':
                            case '종결':
                                $statusCodeDef = 'B';
                                break;
                            /*생략*/
                            case 'Z':
                            case '기타':
                                $statusCodeDef = 'Z';
                                break;
                            default:
                                $statusCodeDef = '';
                                break;
                        }
                        $keyword = substr($param['keyword'], 0, -1);
                        $trial = substr($param['keyword'], -1);

                        if ($trial === '1') {
                            $statusCodeDef = "{$statusCodeDef}%";
                        } else if ($trial === '2') {
                            $statusCodeDef = "_{$statusCodeDef}%";
                        } else if ($trial === '3') {
                            $statusCodeDef = "__{$statusCodeDef}";
                        }

                        $addSql .= " AND {$keyword} LIKE '{$statusCodeDef}}'";
                    } else {
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                    }
                }
            }
            // 대상 전체 카운트
            $sql = "SELECT
                        o.RegDatetime, o.UsersIdx, o.PaysIdx, 
                        m.Name, m.Phone, tm.MembersIdx AS TestMembers, 
                        scm.ServiceControlIdx, scm.ServiceCompanyName, 
                        cs.ConsultantName, cs.StatusCode, cs.ConsultantFixDate, 
                        cs.ConsultDate1, cs.ConsultDate2, cs.ConsultDate3, cs.***TransferDatetime, 
                        pim.ItemName AS ParentItemName, im.ItemName, 
                        icm.MonthlyPremium, icm.DueDay, icm.ContractDate
                    FROM abc.AllocationMembers mam
                    JOIN abc.Consultant cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
                    JOIN o.Pays AS o
                      ON o.PaysIdx = mam.PaysIdx
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN abc.ServiceControl scm
                      ON scm.ServiceControlIdx = mam.ServiceControlIdx
                    JOIN abc.TransferUsers mts
                      ON (mts.UsersIdx, mts.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
               LEFT JOIN abc.Contract icm
                      ON (icm.UsersIdx, icm.PaysIdx) = (mam.UsersIdx, mam.PaysIdx)
               LEFT JOIN abc.Insureance im
                      ON im.InsureanceIdx = icm.InsureanceIdx
               LEFT JOIN abc.Insureance pim
                      ON pim.InsureanceIdx = im.ParentItemIdx
                   WHERE mm.IsOut = b'0' #탈퇴회원 제외
                     AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND o.IsActive = b'1' #활성회원 선별
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= " ORDER BY o.RegDatetime DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data[$item['OrderIdx']] = [
                    // 테이블 값
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'Name' => $item['Name'],
                    'Phone' => $item['Phone'],
                    'TestMembers' => $item['TestMembers'],
                    'ServiceControlIdx' => $item['ServiceControlIdx'],
                    'ServiceCompanyName' => $item['ServiceCompanyName'],
                    'ConsultantName' => $item['ConsultantName'],
                    'StatusCode' => $item['StatusCode'],
                    'ParentItemName' => $item['ParentItemName'],
                    'ItemName' => $item['ItemName'],
                    'MonthlyPremium' => $item['MonthlyPremium'],
                    'DueDay' => $item['DueDay'],
                    'ContractDate' => $item['ContractDate'],
                    // Modal 값
                    '***TransferDatetime' => $item['***TransferDatetime'],
                    'ConsultantFixDate' => $item['ConsultantFixDate'],
                    'ConsultDate1' => $item['ConsultDate1'],
                    'ConsultDate2' => $item['ConsultDate2'],
                    'ConsultDate3' => $item['ConsultDate3'],
                ];
            }

            $this->data['data'] = $data;
            $this->conn = null;

            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **IB 할당 엑셀 업로드 (질환과 공통 사용)
    function uploadDbAllocation($param): array
    {
        $this->desc = 'model::uploadDbAllocation';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'], $param[0]['dbAllocationFile'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }

            $sql = "SELECT 
                        scm.ServiceControlIdx, scm.ServiceCompanyName, scm.TransferMethodCode,
                        asmh.TotalServeLimit, asmh.WeekServeLimit, asmh.RegDatetime 
                      FROM abc.ServeAllocationHistory AS asmh
                      JOIN abc.ServiceControl AS scm
                        ON scm.ServiceControlIdx = asmh.ServiceControlIdx
                     WHERE asmh.ServeAllocationHistoryIdx IN (
                                SELECT MAX(ServeAllocationHistoryIdx)
                                  FROM abc.ServeAllocationHistory
                                 WHERE ProductGroupIdx = :productGroupIdx
                              GROUP BY ServiceControlIdx 
                           )
                       AND scm.IsContract = b'1'
                       AND scm.TransferMethodCode IS NOT NULL
                       AND asmh.ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll() ?? [];
            if (count($rows) === 0) {
                throw new \Exception('계약중인 거래처가 없거나 제공량 설정이 되어있지 않습니다.', "451");
            }

            $serviceCompanyList = [];
            $serviceCompanyData = [];
            foreach ($rows as $value) {
                $serviceCompanyData[$value['ServiceControlIdx']] = $value;
                $serviceCompanyList[$value['ServiceCompanyName']] = (int)$value['ServiceControlIdx'];

                $sql = "SELECT COUNT(*) AS TotalCnt
                          FROM abc.AllocationMembers AS mam
                          JOIN o.Pays AS o
                            ON o.PaysIdx = mam.PaysIdx
                           AND o.ProductGroupIdx = :productGroupIdx
                         WHERE mam.ServiceControlIdx = :ServiceCompanyIdx
                           AND mam.RegDatetime >= :LatestDatetime";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':ServiceCompanyIdx', $value['ServiceControlIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':LatestDatetime', $value['RegDatetime']);
                $stmt->execute();
                $row = $stmt->fetch();

                $serviceCompanyData[$value['ServiceControlIdx']]['TotalCnt'] = $row['TotalCnt'] ?? 0;

                $today = date('Y-m-d');
                $todayDay = date('w'); // 0:일요일 - 6:토요일
                $endDay = 6 - $todayDay;
                $startOfWeek = date('Y-m-d', strtotime($today . "-{$todayDay} day"));
                $endOfWeek = date('Y-m-d', strtotime($today . "+{$endDay} day"));

                $sql = "SELECT COUNT(*) AS WeekCnt
                          FROM abc.AllocationMembers AS mam
                          JOIN o.Pays AS o
                            ON o.PaysIdx = mam.PaysIdx
                           AND o.ProductGroupIdx = :productGroupIdx
                         WHERE mam.ServiceControlIdx = :ServiceCompanyIdx
                           AND mam.RegDatetime >= :LatestDatetime
                           AND DATE(mam.RegDatetime) BETWEEN :StartOfWeek AND :EndOfWeek";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':ServiceCompanyIdx', $value['ServiceControlIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':LatestDatetime', $value['RegDatetime']);
                $stmt->bindValue(':StartOfWeek', $startOfWeek);
                $stmt->bindValue(':EndOfWeek', $endOfWeek);
                $stmt->execute();
                $row = $stmt->fetch();

                $serviceCompanyData[$value['ServiceControlIdx']]['WeekCnt'] = $row['WeekCnt'] ?? 0;
            }
            $columnName = ['UsersIdx', 'OrderIdx', 'Name', 'Gender', 'Age', 'Address', 'ClientCustomerName', 'ServiceCompanyName'];
            $allIdxList = [];
            $idxPerServiceCompany = [];
            $insertVals = [];

            $serverFilename = $param[0]['dbAllocationFile']['tmp_name'];
            $pcFilename = $param[0]['dbAllocationFile']['name'];
            $spreadsheet = new SpreadsheetFactory();
            $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
            if ($result['code'] !== 200) {
                throw new \Exception("엑셀 읽기 오류", "504");
            }

            $spreadData = $result['data'];
            if (count($spreadData) < 1) {
                throw new \Exception("양식이 입력되지 않았습니다.", "401");
            }
            foreach ($spreadData as $key => $data) {
                if (empty(array_filter($data)) || $key === 0) {
                    continue;
                }
                $rowData = array_combine($columnName, $data);
                $UsersIdx = is_numeric($rowData['UsersIdx']) ? $rowData['UsersIdx'] : 0;
                $orderIdx = is_numeric($rowData['OrderIdx']) ? $rowData['OrderIdx'] : 0;
                if ($UsersIdx === 0 || $orderIdx === 0) {
                    throw new \Exception('error: 주문상품ID 혹은 회원 ID값이 올바르지않습니다.', "452");
                }
                $allIdxList[] = $orderIdx;
                $serviceCompanyIdx = $serviceCompanyList[$rowData['ServiceCompanyName']] ?? 0;
                if ($serviceCompanyIdx === 0) {
                    throw new \Exception('거래처명이 올바르지 않습니다.', "452");
                }
                $insertVals[$orderIdx] = "({$UsersIdx}, {$orderIdx}, {$param['gIdx']}, {$serviceCompanyIdx})";
                $idxPerServiceCompany[$serviceCompanyIdx][] = $orderIdx;
            }

            foreach ($idxPerServiceCompany as $key => $value) {
                $allocationCnt = count($value);
                if ($serviceCompanyData[$key]['TotalServeLimit'] < ($serviceCompanyData[$key]['TotalCnt'] + $allocationCnt)) {
                    throw new \Exception("총 제공량을 초과하게 됩니다: {$serviceCompanyData[$key]['ServiceCompanyName']}", 452);
                }
                if ($serviceCompanyData[$key]['WeekServeLimit'] < ($serviceCompanyData[$key]['WeekCnt'] + $allocationCnt)) {
                    throw new \Exception("주간 제공량을 초과하게 됩니다: {$serviceCompanyData[$key]['ServiceCompanyName']}", 452);
                }
            }

            if (count($insertVals) > 0) {
                $insertVal = implode(",", $insertVals);
                $sql = "INSERT INTO abc.`AllocationMembers` (
                            UsersIdx, OrderIdx, ProductGroupIdx, ServiceControlIdx)
                        VALUES {$insertVal} 
                        ON DUPLICATE KEY UPDATE 
                            ServiceControlIdx = VALUE(ServiceControlIdx) ";
                $this->conn->query($sql);
            }

            //TODO:: API전송 관련 데이터 정의 필요함 [미개발] - 얼리큐
            if ($param['gIdx'] === '2') {
                $method = "GET";
                $url = api . "/schedule/allocation/send?cron=Y&isTest=Y";
                $header = [];
                $body = [
                    'cron' => "Y",
                    'isTest' => "Y",
                ];
                $result = $this->curl($method, $url, $header, $body);
                if ($result['code'] !== 200) {
                    throw new \Exception("error: allocation data send failure", 450);
                };
            }

            $this->msg = "할당 완료";

            return $this->response();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // **IB 유저 데이터 기간별 조회
    function ibAllocationData($param): array
    {
        $this->desc = 'model::ibAllocationData';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'], $param['serviceCompanyIdx'], $param['minDate'], $param['maxDate'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (
                !preg_match($this->pattern['date'], $param['minDate'])
                || !preg_match($this->pattern['date'], $param['maxDate'])
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.' , '400');
            }
            if (strtotime($param['minDate']) > strtotime($param['maxDate'])) {
                throw new \Exception('선택하신 날짜 범위가 올바르지 않습니다.' , '400');
            }

            $data = [];
            $sql = " SELECT 
                          o.PaysIdx
                        , mm.UsersIdx
                        , m.Name, m.Gender, m.State, m.City,m.FullCity, m.Birth1, m.Birth2, m.Phone
                        , ccm.ClientCustomerName, scm.ServiceCompanyName
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN abc.ClientControl AS ccm
                        ON ccm.ClientControlIdx = mm.ClientControlIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.AllocationMembers AS mam
                        ON mam.UsersIdx = mm.UsersIdx
                       AND mam.PaysIdx = o.PaysIdx
                      JOIN abc.ServiceControl as scm
                        ON scm.ServiceControlIdx = mam.ServiceControlIdx
                      WHERE o.IsActive = b'1'
                        AND mm.IsOut = b'0'
                        AND o.ProductGroupIdx = :productGroupIdx
                        AND mam.ServiceControlIdx = :serviceCompanyIdx
                        AND DATE(mam.RegDatetime) BETWEEN :minDate AND :maxDate";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':serviceCompanyIdx', $param['serviceCompanyIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':minDate', $param['minDate']);
            $stmt->bindValue(':maxDate', $param['maxDate']);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $birthDay = $row['Birth1'] . $row['Birth2'];
                $age = convertAging($birthDay, date('Y-m-d'));
                $data[] = [
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'Name' => $row['Name'],
                    'Gender' => $row['Gender'] === "1" ? "남" : ($row['Gender'] === "2" ? "여" : ""),
                    'Age' => $age,
                    'Birth' => $row['Birth1'] . $row['Birth2'],
                    'Phone' => "'{$row['Phone']}",
                    'Address' => trim("{$row['State']} {$row['City']} {$row['FullCity']}"),
                    'ClientCustomerName' => $row['ClientCustomerName'] ?? '',
                    'ServiceCompanyName' => $row['ServiceCompanyName'] ?? '',
                ];
            }
            if (!count($data)) {
                throw new \Exception('조회되는 데이터가 없습니다.', '451');
            }
            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **IB 할당 유저 조회
    function findAllocateUser($param): array
    {
        $this->desc = 'model::findAllocateUser';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }

            $data = [];
            $sql = " SELECT 
                          o.PaysIdx
                        , mm.UsersIdx
                        , m.Name, m.Gender, m.State, m.City,m.FullCity, m.Birth1, m.Birth2
                        , ccm.ClientCustomerName
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN abc.ClientControl AS ccm
                        ON ccm.ClientControlIdx = mm.ClientControlIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.Report AS prm
                        ON prm.PaysIdx = o.PaysIdx
                        AND prm.UsersIdx = mm.UsersIdx
                        AND prm.ReportType = 2
                      JOIN abc.Event AS ed
                        ON ed.Paysidx = o.PaysIdx
                        AND ed.UsersIdx = mm.UsersIdx
                 LEFT JOIN abc.AllocationMembers AS mam
                        ON mam.UsersIdx = mm.UsersIdx
                        AND mam.PaysIdx = o.PaysIdx
                     WHERE o.IsActive = b'1'
                        AND mm.IsOut = b'0'
                        AND o.ProductGroupIdx = :productGroupIdx
                        AND ed.ItemCategory = 'personal_link'
                        AND mam.PaysIdx IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $birthDay = $row['Birth1'] . $row['Birth2'];
                $age = convertAging($birthDay, date('Y-m-d'));
                $data[] = [
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'Name' => $row['Name'],
                    'Gender' => $row['Gender'] === "1" ? "남" : ($row['Gender'] === "2" ? "여" : ""),
                    'Age' => $age,
                    'Address' => trim("{$row['State']} {$row['City']} {$row['FullCity']}"),
                    'ClientCustomerName' => $row['ClientCustomerName'],
                    'ServiceCompanyName' => ""
                ];
            }
            if (!count($data)) {
                throw new \Exception('조회되는 데이터가 없습니다.', '451');
            }
            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **IB 미할당 유저 조회
    function findAllocateUserforTest($param): array
    {
        $this->desc = 'model::findAllocateUserfor***';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            $sql = "SELECT 
                        mm.UsersIdx, o.PaysIdx, m.Name, m.Gender, m.Birth1, m.Birth2,
                        m.State, m.City, m.FullCity, ccm.ClientCustomerName
                    FROM abc.Users AS mm
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
                     AND mm.IsOut = b'0'
                     AND m.MembersIdx NOT IN (SELECT MembersIdx FROM abc.TestMembers WHERE Grade = 9)
                    JOIN abc.ClientControl AS ccm
                      ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN o.Pays AS o 
                      ON o.UsersIdx = mm.UsersIdx
                     AND o.ProductGroupIdx = 2
                    -- xxx 검사 IsComplete & IsSend 완료 체크
                    JOIN abc.Genom AS gcmi
                      ON gcmi.UsersIdx = mm.UsersIdx 
                     AND gcmi.PaysIdx = o.PaysIdx
                     AND gcmi.IsComplete = b'1'
                     AND gcmi.IsSend = b'1'
               LEFT JOIN abc.AllocationMembers AS mam
                      ON mam.UsersIdx = mm.UsersIdx
                     AND mam.PaysIdx = o.PaysIdx
                   WHERE mam.UsersIdx IS NULL
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     AND mm.IsOut = b'0'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll($this->conn::FETCH_ASSOC);
            foreach ($rows as $row) {
                $birthDay = $row['Birth1'] . $row['Birth2'];
                $age = convertAging($birthDay, date('Y-m-d'));
                $this->data['data'][] = [
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'Name' => $row['Name'],
                    'Gender' => $row['Gender'] === "1" ? "남" : ($row['Gender'] === "2" ? "여" : ""),
                    'Age' => $age,
                    'Address' => trim("{$row['State']} {$row['City']} {$row['FullCity']}"),
                    'ClientCustomerName' => $row['ClientCustomerName'],
                    'ServiceCompanyName' => ""
                ];
            }
            if (count($rows) === 0) {
                throw new \Exception('조회 데이터가 없습니다.', '451');
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //**사 또는 **상품 대량등록
    function uploadInsuranceItem($param): array
    {
        $this->desc = 'uploadInsuranceItem';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['registerType'], $param[0]['insuranceItemFile'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            $serverFilename = $param[0]['insuranceItemFile']['tmp_name'];
            $pcFilename = $param[0]['insuranceItemFile']['name'];

            $spreadsheet = new SpreadsheetFactory();
            $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
            if ($result['code'] !== 200) {
                throw new \Exception('read error', 400);
            }

            $spreadData = $result['data'];
            if (count($spreadData) < 2) {
                throw new \Exception("양식이 입력되지 않았습니다.", "401");
            }
            unset($spreadData[0]);
            $items = [];
            $table = "***.Insureance";
            if ($param['registerType'] = 'insurance' && isset($param['ServiceControlIdx'])) {
                foreach ($spreadData as $value) {
                    if (!$value = array_filter($value)) {
                        continue;
                    }
                    if (!$value[0]) {
                        throw new \Exception("**사 코드 입력은 필수 입니다.", "401");
                    }
                    $items[] = [
                        'ServiceControlIdx' => (int)$param['ServiceControlIdx'],
                        'itemCode' => (string)$value[0],
                        'itemName' => (string)$value[1],
                    ];
                }
            } else if ($param['registerType'] = 'item') {
                foreach ($spreadData as $value) {
                    if (!$value = array_filter($value)) {
                        continue;
                    }
                    if (!$value[0]) {
                        throw new \Exception("**사 식별코드 입력은 필수 입니다.", "401");
                    }
                    $items[] = [
                        'parentItemIdx' => (int)$value[0],
                        'itemCode' => (string)$value[1],
                        'itemName' => (string)$value[2],
                    ];
                }
            }
            $this->bulkInsertUpdate([], $table, $items);

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **사 또는 **상품 비활성화
    function deleteInsuranceItem($param): array
    {
        $this->desc = 'model::deleteInsuranceItem';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['InsureanceIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (!$param['InsureanceIdx']) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }

            $sql = "UPDATE abc.Insureance
                    SET IsUse = b'0'
                    WHERE InsureanceIdx = :InsureanceIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':InsureanceIdx', $param['InsureanceIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // **사 또는 **상품 수정
    function updateInsuranceItem($param): array
    {
        $this->desc = 'model::updateInsuranceItem';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (
                !isset(
                    $param['parentInsureanceIdx'],
                    $param['parentItemCode'],
                    $param['parentItemName'],
                )
            ) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (
                !$param['parentInsureanceIdx']
                || !$param['parentItemCode']
                || !$param['parentItemName']
            ) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }

            if (
                !preg_match($this->pattern['code'], $param['parentItemCode'])
                || !preg_match($this->pattern['all'], $param['parentItemName'])
                || (isset($param['itemCode']) && !preg_match($this->pattern['code'], $param['itemCode']))
                || (isset($param['itemName']) && !preg_match($this->pattern['all'], $param['itemName']))
            ) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }
            $this->conn->beginTransaction();

            $table = '***.Insureance';
            // **사 정보 수정
            $idx = [
                'InsureanceIdx' => (int)$param['parentInsureanceIdx'],
            ];
            $item = [
                'itemCode' => $param['parentItemCode'],
                'itemName' => $param['parentItemName']
            ];
            $this->insertUpdate($idx, $table, $item);

            // **상품 정보 수정
            if (isset($param['InsureanceIdx']) && $param['InsureanceIdx']) {
                $idx = [
                    'InsureanceIdx' => $param['InsureanceIdx'],
                ];
                $item = [
                    'itemCode' => $param['itemCode'],
                    'itemName' => $param['itemName']
                ];
                $this->insertUpdate($idx, $table, $item);
            } else {
                if (
                    isset($param['itemCode'], $param['itemName'])
                    && $param['itemCode'] && $param['itemName']
                ) {
                    $item = [
                        'itemCode' => $param['itemCode'],
                        'itemName' => $param['itemName'],
                        'parentItemIdx' => $param['parentInsureanceIdx'],
                    ];
                    $this->insertUpdate([], $table, $item);
                }
            }

            $this->conn->commit();

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    // **상품 조회 insuranceItemList
    function insuranceItemList($param): array
    {
        $this->desc = "model::insuranceItemList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['scm.ServiceCompanyName', 'pi.ItemCode', 'pi.ItemName', 'i.ItemCode', 'i.ItemName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            // 대상 전체 카운트
            $sql = "SELECT 
                        scm.ServiceCompanyName, pi.InsureanceIdx AS ParentInsureanceIdx, 
                        pi.ItemCode AS ParentItemCode, pi.ItemName AS ParentItemName,
                        i.InsureanceIdx, i.ItemCode, i.ItemName
                    FROM abc.Insureance pi
                    JOIN abc.ServiceControl scm 
                      ON scm.ServiceControlIdx = pi.ServiceControlIdx
               LEFT JOIN abc.Insureance i 
                      ON i.ParentItemIdx = pi.InsureanceIdx
                     AND i.IsUse = b'1'
                     AND i.ParentItemIdx IS NOT NULL
                   WHERE scm.IsContract = b'1'
                     AND pi.IsUse = b'1'
                     AND pi.ParentItemIdx IS NULL
                     {$addSql}";

            $stmt = $this->conn->query($sql);
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                $data[] = [
                    'ServiceCompanyName' => $item['ServiceCompanyName'],
                    'ParentInsureanceIdx' => $item['ParentInsureanceIdx'],
                    'ParentItemCode' => $item['ParentItemCode'],
                    'ParentItemName' => $item['ParentItemName'],
                    'InsureanceIdx' => $item['InsureanceIdx'],
                    'ItemCode' => $item['ItemCode'],
                    'ItemName' => $item['ItemName'],
                ];
            }

            $sql = "SELECT ServiceControlIdx AS `value`, ServiceCompanyName AS `text`
                    FROM abc.ServiceControl
                    WHERE IsContract = b'1'";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];

            $this->data['data'] = $data;
            $this->data['select::serviceCompany'] = $row;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // ** IB 리스트 (질환 전용)
    function insureIbListforTest($param): array
    {
        $this->desc = 'model::insureIbListfor***';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['m.Name', 'm.Phone', 'prm.CalcDate', 'm.State', 'm.City', 'ccm.ClientCustomerName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if ($param['keyword'] === 'mts.IsComplete') {
                    if ($param['value'] === '전송') {
                        $addSql .= " AND {$param['keyword']} = b'1'";
                    } else {
                        $addSql .= " AND ( {$param['keyword']} = b'0' OR {$param['keyword']} IS NULL)";
                    }
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                if ($param['column'] === 'Address') {
                    $orderSql .= " m.State {$param['sort']}, m.City {$param['sort']}, m.FullCity {$param['sort']} ";
                } else {
                    $orderSql .= " {$param['column']} {$param['sort']} ";
                }
            } else {
                $orderSql .= ' prm.CalcDate DESC ';
            }

            $sql = "  SELECT 
                          prm.CalcDate
                        , o.PaysIdx
                        , mm.UsersIdx
                        , m.Name, m.State, m.City
                        , mam.RegDatetime
                        , ccm.ClientCustomerName
                        , scm.TransferMethodCode
                        , scm.ServiceCompanyName, scm.ServiceControlIdx
                        , mts.RegDatetime AS IsPost
                        , prm.uuid
                        FROM o.Pays AS o
                        -- MembersIdx, UsersIdx 가져오기
                        JOIN abc.Users AS mm
                          ON mm.UsersIdx = o.UsersIdx
                        JOIN abc.Members AS m
                          ON m.MembersIdx = mm.MembersIdx
                         AND m.MembersIdx NOT IN (SELECT MembersIdx FROM abc.TestMembers WHERE Grade = 9)
                        JOIN abc.ClientControl as ccm
                          ON ccm.ClientControlIdx = mm.ClientControlIdx
                        -- xxx 검사 IsComplete & IsSend 완료 체크
                        JOIN abc.Genom AS gcmi
                          ON gcmi.UsersIdx = o.UsersIdx
                         AND gcmi.PaysIdx = o.PaysIdx
                         AND gcmi.IsComplete = b'1'
                         AND gcmi.IsSend = b'1'
                        JOIN abc.Report AS prm
                          ON prm.UsersIdx = mm.UsersIdx
                         AND prm.PaysIdx = o.PaysIdx
                   LEFT JOIN abc.AllocationMembers AS mam
                          ON mam.UsersIdx = mm.UsersIdx
                         AND mam.PaysIdx = o.PaysIdx
                   LEFT JOIN abc.TransferUsers AS mts
                          ON mts.UsersIdx = mam.UsersIdx
                         AND mts.PaysIdx = mam.PaysIdx                        
                   LEFT JOIN abc.ServiceControl AS scm
                          ON scm.ServiceControlIdx = mam.ServiceControlIdx
                       WHERE o.ProductGroupIdx = :productGroupIdx
                         AND o.IsActive = b'1'
                         AND mm.IsOut = b'0' 
                         {$addSql}
                    GROUP BY o.PaysIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = 0;
            $allocationCount = 0;
            $notAllocationCount = 0;
            while($row = $stmt->fetch()) {
                $total++;
                if($row['RegDatetime']) {
                    $allocationCount ++;
                } else {
                    $notAllocationCount ++;
                }
            }
            $this->data['text::count'] = [
                'allocationCount' => $allocationCount,
                'notAllocationCount' => $notAllocationCount,
            ];
            $this->setPagination($total, $param);

            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data['data']["idx{$row['OrderIdx']}"] = [
                    'CalcDate' => $row['CalcDate'],
                    'Name' => $row['Name'],
                    'State' => $row['State'] ?? '',
                    'City' => $row['City'] ?? '',
                    'RegDatetime' => $row['RegDatetime'] ? substr($row['RegDatetime'], 0, 10) : '',
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'ClientCustomerName' => $row['ClientCustomerName'],
                    'ServiceCompanyName' => $row['ServiceCompanyName'] ?? '',
                    'TransferMethodCode' => $row['TransferMethodCode'] ?? '',
                    'IsPost' => $row['IsPost'] ? substr($row['IsPost'], 0, 10) : '',
                    'uuid' => $row['uuid'] ?? '',
                ];
            }
            //@TODO 노출되어야하는 ServiceCompany에 대한 정의가 없는 상황, 따라 전체 ServiceCompany 노출하도록 현재 설정
            $sql = "SELECT ServiceControlIdx, ServiceCompanyName
                      FROM abc.ServiceControl AS scm
                     #WHERE IsContract = b'1'";
            $stmt = $this->conn->query($sql);
            while ($row = $stmt->fetch()) {
                $this->data['select::serviceCompany'][$row['ServiceControlIdx']] = [
                    'value' => $row['ServiceControlIdx'],
                    'text' => $row['ServiceCompanyName'],
                ];
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // ** IB 리스트
    function insureIbList($param): array
    {
        $this->desc = 'model::insureIbList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['m.Name', 'm.Phone', 'prm.CalcDate', 'm.State', 'm.City', 'ccm.ClientCustomerName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if ($param['keyword'] === 'mts.IsComplete') {
                    if ($param['value'] === '전송') {
                        $addSql .= " AND {$param['keyword']} = b'1'";
                    } else {
                        $addSql .= " AND ({$param['keyword']} = b'0' OR {$param['keyword']} IS NULL)";
                    }
                } else if ($param['keyword'] === 'cs.ConsultantType') {
                    if ($param['value'] === '설명듣기') {
                        $addSql .= " AND {$param['keyword']} = 'R'";
                    } else if ($param['value'] === '나중에') {
                        $addSql .= " AND {$param['keyword']} = 'L'";
                    } else if ($param['value'] === '미응답') {
                        $addSql .= " AND {$param['keyword']} = 'N'";
                    }
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                if ($param['column'] === 'Address') {
                    $orderSql .= " m.State {$param['sort']}, m.City {$param['sort']}, m.FullCity {$param['sort']} ";
                } else {
                    $orderSql .= " {$param['column']} {$param['sort']} ";
                }
            } else {
                $orderSql .= ' prm.CalcDate DESC ';
            }
            $sql = " SELECT 
                          prm.CalcDate
                        , o.PaysIdx
                        , cs.ConsultantType
                        , mm.UsersIdx
                        , m.Name, m.State, m.City
                        , mam.RegDatetime
                        , ccm.ClientCustomerName
                        , scm.TransferMethodCode
                        , scm.ServiceCompanyName, scm.ServiceControlIdx
                        , mts.RegDatetime AS IsPost
                        , prm.uuid
                        , sm.LatestDatetime
                        , prm.Data
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN abc.ClientControl AS ccm
                        ON ccm.ClientControlIdx = mm.ClientControlIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.Report AS prm
                        ON prm.PaysIdx = o.PaysIdx
                       AND prm.UsersIdx = mm.UsersIdx
                       AND prm.ReportType = 2
                 LEFT JOIN abc.Consultant AS cs
                        ON cs.UsersIdx = mm.UsersIdx
                       AND cs.PaysIdx = o.PaysIdx
                 LEFT JOIN abc.AllocationMembers AS mam
                        ON mam.UsersIdx = mm.UsersIdx
                       AND mam.PaysIdx = o.PaysIdx
                 LEFT JOIN abc.TransferUsers AS mts
                        ON mts.UsersIdx = mam.UsersIdx
                       AND mts.PaysIdx = mam.PaysIdx
                 LEFT JOIN abc.ServiceControl AS scm
                        ON scm.ServiceControlIdx = mam.ServiceControlIdx
                 LEFT JOIN s.SendManage sm
                        ON sm.UsersIdx = mm.UsersIdx
                       AND sm.PaysIdx = o.PaysIdx
                       AND sm.ProcessStep = 34
                     WHERE o.IsActive = b'1'
                       AND mm.IsOut = b'0' 
                       AND o.ProductGroupIdx = :productGroupIdx
                       AND cs.ConsultantType IS NOT NULL
                       AND (
                           !(sm.SendCount >= 2 AND DATE(sm.LatestDatetime) <= :targetDate)
                           OR sm.SendCount IS NULL
                       )
                       {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':targetDate', date('Y-m-d', strtotime('-10 days')));
            $stmt->execute();
            //할당 인원, 미할당 인원
            $allocationCount = 0;
            $notAllocationCount = 0;
            $total = 0;
            while($row = $stmt->fetch()) {
                $total++;
                if($row['RegDatetime']) {
                    $allocationCount ++;
                } else {
                    $notAllocationCount ++;
                }
            }
            $this->data['text::count'] = [
                'allocationCount' => $allocationCount,
                'notAllocationCount' => $notAllocationCount,
            ];
            $this->setPagination($total, $param);

            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':targetDate', date('Y-m-d', strtotime('-10 days')));
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data['data']["idx{$row['OrderIdx']}"] = [
                    'CalcDate' => $row['CalcDate'],
                    'Name' => $row['Name'],
                    'State' => $row['State'] ?? '',
                    'City' => $row['City'] ?? '',
                    'RegDatetime' => $row['RegDatetime'] ? substr($row['RegDatetime'], 0, 10) : '',
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'ClientCustomerName' => $row['ClientCustomerName'],
                    'ServiceCompanyName' => $row['ServiceCompanyName'] ?? '',
                    'TransferMethodCode' => $row['TransferMethodCode'] ?? '',
                    'ConsultantType' => $row['ConsultantType'] ?? '',
                    'IsPost' => $row['IsPost'] ? substr($row['IsPost'], 0, 10) : '',
                    'uuid' => $row['uuid'] ?? '',
                    'CWCnt' => 0,
                    'DHCnt' => 0,
                ];

                $***Data = json_decode($row['Data'], true);
                foreach ($***Data as $val) {
                    $stat = $this->bioMarkerGradeCovert($val['rrisk']);
                    if ($stat === '양호') {
                        continue;
                    }
                    if (in_array($stat, ['주의', '경고'])) {
                        $this->data['data']["idx{$row['OrderIdx']}"]['CWCnt']++;
                    }
                    if (in_array($stat, ['위험', '고위험'])) {
                        $this->data['data']["idx{$row['OrderIdx']}"]['DHCnt']++;
                    }
                }
            }
            $sql = "SELECT ServiceControlIdx, ServiceCompanyName
                      FROM abc.ServiceControl AS scm
                     WHERE IsContract = b'1'";
            $stmt = $this->conn->query($sql);
            while ($row = $stmt->fetch()) {
                $this->data['select::serviceCompany'][$row['ServiceControlIdx']] = [
                    'value' => $row['ServiceControlIdx'],
                    'text' => $row['ServiceCompanyName'],
                ];
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 요약 검사 결과
    function summaryList($param): array
    {
        $this->desc = 'model::summaryList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['m.Name', 'm.Phone', 'ed.RegDatetime', 'prm.CalcDate', 'cs.AppointmentDate'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if ($param['keyword'] === 'IsAccess') {
                    if ($param['value'] === 'Y') {
                        $addSql .= " AND cs.ConsultantType IS NOT NULL ";
                    } else if ($param['value'] === 'N') {
                        $addSql .= " AND cs.ConsultantType IS NULL ";
                    }
                } else if ($param['keyword'] === 'cs.ConsultantType') {
                    if ($param['value'] === '설명듣기') {
                        $addSql .= " AND {$param['keyword']} = 'R'";
                    } else if ($param['value'] === '나중에') {
                        $addSql .= " AND {$param['keyword']} = 'L'";
                    } else if ($param['value'] === '미응답') {
                        $addSql .= " AND {$param['keyword']} = 'N'";
                    } else if ($param['value'] === '미접속') {
                        $addSql .= " AND {$param['keyword']} IS NULL";
                    }
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            $sql = " SELECT
                          m.Name, m.Phone
                        , prm.CalcDate
                        , mm.UsersIdx
                        , ccm.ClientCustomerName
                        , o.PaysIdx
                        , p.ProductName, p.ProductIdx
                        , cs.ConsultantType, cs.AppointmentHour, cs.AppointmentDate
                        , ed.RegDatetime AS EventDate
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN abc.ClientControl AS ccm
                        ON ccm.ClientControlIdx = mm.ClientControlIdx
                        AND ccm.Depth =2
                      JOIN abc.ProductGroupManage AS pgm
                        ON pgm.ProductGroupIdx = :productGroupIdx
                      JOIN abc.Product AS p
                        ON p.ProductIdx = pgm.ProductIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.Report AS prm
                        ON prm.PaysIdx = o.PaysIdx
                       AND prm.UsersIdx = o.UsersIdx
                       AND prm.ReportType = 2  #테스트예측
                 LEFT JOIN abc.Consultant AS cs
                        ON cs.UsersIdx = mm.UsersIdx
                       AND cs.PaysIdx = o.PaysIdx  
                 LEFT JOIN abc.Event AS ed
                        ON ed.PaysIdx = o.PaysIdx
                       AND ed.UsersIdx = mm.UsersIdx
                       AND ed.ItemCategory = 'personal_link'
                     WHERE o.ProductGroupIdx = :productGroupIdx
                       AND p.ProductIdx = 4
                       AND o.IsActive = b'1'
                       AND mm.IsOut = b'0'
                       {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = $stmt->fetchAll();
            $this->setPagination(count($total), $param);

            $sql .= " ORDER BY prm.CalcDate DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data['data'][$row['OrderIdx']] = [
                    'CalcDate' => $row['CalcDate'],
                    'Name' => $row['Name'],
                    'Phone' => $row['Phone'],
                    'UsersIdx' => $row['UsersIdx'],
                    'ClientCustomerName' => $row['ClientCustomerName'],
                    'ConsultantType' => $row['ConsultantType'] ?? '',
                    'AppointmentHour' => $row['AppointmentHour'] ?? '',
                    'AppointmentDate' => $row['AppointmentDate'] ?? '',
                    'EventDate' => $row['EventDate'] ? substr($row['EventDate'], 0, 10) : '',
                ];
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 알림톡 전송(복수건 전송)
    function sender(array $senderData): array
    {
        $this->desc = 'model::sender';
        try {
            if (count($senderData) === 0) {
                throw new \Exception("BizM 전송할 데이터가 없습니다.", "404");
            }

            $resultData = [
                'success' => 0,
                'failure' => 0
            ];

            $logParams = [];
            $sendData = [];

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $url = "{$this->apiBizMUrl}/v2/sender/send";

            $header = [
                'Content-type: application/json',
                'userId: ' . $this->apiBizMId
            ];

            // request body, request log 만들기
            foreach ($senderData as $row) {
                if (isset($row['shortUrl'])) {
                    $row['messageSms'] .= $row['shortUrl'];
                }
                if (!isDev) {
                    if ($row['templateId'] === '****04') {
                        $this->apiBizMKey = $this->apiBizMBioAgeKey;
                    } else if (strpos($row['templateId'], 'earlyq') !== false) {
                        $this->apiBizMKey = $this->apiBizMEarlyQKey;
                    } else if (strpos($row['templateId'], 'coupon') !== false) {
                        $this->apiBizMKey = $this->apiBizMCouponKey;
                    } else {
                        $this->apiBizMKey = $this->apitestSendKey;
                    }
                }

                $params = [
                    'UsersIdx' => ($row['UsersIdx']) ?? "",
                    'orderIdx' => ($row['orderIdx']) ?? "",
                    'profile' => $this->apiBizMKey,
                    'templateId' => $row['templateId'],
                    'messageType' => $row['messageType'],
                    'phone' => $row['phone'],
                    'message' => $row['message'],
                    'title' => $row['title'],
                    'reserveDatetime' => ($row['reserveDatetime']) ?? "00000000000000",
                    'smsKind' => $row['smsKind'],
                    'smsSender' => $row['smsSender'],
                    'messageSms' => $row['messageSms'],
                    'smsLmsTit' => $row['smsLmsTit'] ?? "",
                ];
                if (isset($row['shortUrl'])) {
                    $params['button1'] = [
                        'name' => $row['buttonName'],
                        'type' => "WL",
                        'url_mobile' => $row['shortUrl'],
                        'url_pc' => $row['shortUrl'],
                        'target' => "out"
                    ];
                }
                if (isset($row['UsersIdx'], $row['orderIdx'])) {
                    $logParams['request'][] = $params;
                }

                $body = [
                    'message_type' => $params['messageType'],
                    'phn' => $params['phone'],
                    'profile' => $this->apiBizMKey,
                    'reserveDt' => $params['reserveDatetime'],
                    'msg' => $params['message'],
                    'tmplId' => $params['templateId'],
                    'smsKind' => $params['smsKind'], //대체문자 사용여부
                    'msgSms' => $params['messageSms'], //대체문자 MSG
                    'smsSender' => $params['smsSender'] //대체문자 발신번호
                ];
                if (isset($params['title'])) {
                    $body['title'] = $params['title'];
                }
                if (isset($params['button1'])) {
                    $body['button1'] = $params['button1'];
                }
                if (isset($params['smsLmsTit'])) {
                    $body['smsLmsTit'] = $params['smsLmsTit']; //대체문자 제목
                }

                // BizM 통신부
                $result = $this->curl('POST', $url, $header, json_encode([$body], true));
                $response = json_decode($result['response'], true)[0];
                if (isset($row['UsersIdx'], $row['orderIdx'])) {
                    $logParams['response'][] = [
                        'UsersIdx' => $row['UsersIdx'],
                        'orderIdx' => $row['orderIdx'],
                        'code' => $response['code'],
                        'phone' => $response['data']['phn'],
                        'type' => $response['data']['type'],
                        'messageId' => $response['data']['msgid'],
                        'message' => $response['message'],
                        'originMessage' => $response['originMessage']
                    ];
                }

                if ($response['code'] == 'success') {
                    if (isset($row['UsersIdx'], $row['orderIdx'])) {
                        $sendData['manage'][] = [
                            'UsersIdx' => $row['UsersIdx'],
                            'orderIdx' => $row['orderIdx'],
                            'processStep' => (int)$row['processType'],
                        ];

                        //ResultDate 함수 추가
                        $sendDate = ($params['reserveDatetime'] == '00000000000000') ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($params['reserveDatetime']));
                        $sendData['result'][] = [
                            'UsersIdx' => $row['UsersIdx'],
                            'orderIdx' => $row['orderIdx'],
                            'processStep' => (int)$row['processType'],
                            'msgId' => $response['data']['msgid'],
                            'sendDate' => $sendDate,
                        ];
                    }

                    $resultData['success']++;
                } else {
                    $resultData['failure']++;
                }
            }
            if ($logParams) {
                $this->insertLog($logParams);
            }
            if ($sendData && $resultData['success'] > 0) {
                $this->sendDataInsertUpdate($sendData);
            }

            $this->data = $resultData;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    private function sendDataInsertUpdate($params): void
    {
        try {
            $sendManage = $params['manage'];
            $sendResult = $params['result'];

            // SendManage 테이블 입력부
            $placeHolder = "(" . implode(',', array_fill(0, count($sendManage[0]), "?")) . ", 1)";
            $placeHolders = implode(',', array_fill(0, count($sendManage), $placeHolder));

            $sql = "INSERT INTO s.SendManage (
                        UsersIdx, OrderIdx, ProcessStep, SendCount) 
                    VALUES {$placeHolders} 
                    ON DUPLICATE KEY UPDATE 
                        SendCount = SendCount + 1, 
                        LatestDatetime = NOW()";
            $stmt = $this->conn->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $sendManage));
            $stmt->execute($flat);

            // SendResult 테이블 입력부
            $placeHolder = "(" . implode(',', array_fill(0, count($sendResult[0]), "?")) . ", 0)";
            $placeHolders = implode(',', array_fill(0, count($sendResult), $placeHolder));

            $sql = "INSERT INTO s.SendResult (
                        UsersIdx, OrderIdx, ProcessStep, MsgId, SendDate, IsSend) 
                    VALUES {$placeHolders}";
            $stmt = $this->conn->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $sendResult));
            $stmt->execute($flat);

            return;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function insertLog($params): void
    {
        try {
            $requestLog = [];
            $responseLog = $params['response'];
            foreach($params['request'] as $row){
                if (isset($row['button1'])) {
                    unset($row['button1']);
                }
                $requestLog[]  = $row;
            }

            // request log 입력부
            $placeHolder = "(" . implode(',', array_fill(0, count($requestLog[0]), "?")) . ")";
            $placeHolders = implode(',', array_fill(0, count($requestLog), $placeHolder));

            $sql = "INSERT INTO *.SendSmsRequestLog (
                        UsersIdx, OrderIdx, Profile, TemplateId, MessageType, Phone, Message, Title, 
                        ReserveDatetime, SmsKind, SmsSender, MessageSms, SmsLmsTit) 
                    VALUE {$placeHolders}";
            $stmt = $this->conn->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $requestLog));
            $stmt->execute($flat);

            // response log 입력부
            $placeHolder = "(" . implode(',', array_fill(0, count($responseLog[0]), "?")) . ")";
            $placeHolders = implode(',', array_fill(0, count($responseLog), $placeHolder));

            $sql = "INSERT INTO *.SendSmsResponseLog ( 
                        UsersIdx, OrderIdx, Code, Phone, Type, MessageId, ResponseMessageCode, OriginMessage) 
                    VALUE {$placeHolders}";
            $stmt = $this->conn->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $responseLog));
            $stmt->execute($flat);

            return;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 알림톡 일괄전송 sendSms
    function sendSms($param): array
    {
        $this->desc = 'model::sendSms';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['gIdx'], $param['idxList'], $param['bizMTemplate'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (!preg_match('/^[0-9\,]+$/', $param['idxList'])) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }

            $idxList = explode(',', $param['idxList']);

            $sql = "SELECT 
                        * 
                    FROM s.BizMTemplateManage 
                   WHERE ProcessStep = :bizMTemplate
                     AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':bizMTemplate', $param['bizMTemplate'], $this->conn::PARAM_INT);
            $stmt->execute();
            $bizMTemplateList = [];
            while ($row = $stmt->fetch()) {
                $subDivisionType = $row['SubDivisionType'] ?: 0;
                $bizMTemplateList[$subDivisionType] = $row;
            }
            if (!$bizMTemplateList) {
                throw new \Exception("사용 중인 BizM 템플릿이 없습니다.", "404");
            }

            switch ($param['bizMTemplate']) {
                case '10' :
                    $sql = "SELECT
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone,
                                sul.ShortUrl
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ShortUrlList sul
                              ON sul.UsersIdx = sul.UsersIdx
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '21' :
                case '22' :
                case '31':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '23' :
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone,
                                ccm.ClientCustomerName, ccm.ResponseType
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl ccm
                              ON ccm.ClientControlIdx = mm.ClientControlIdx   
                            JOIN abc.Genom gcmi
                              ON (gcmi.UsersIdx, gcmi.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'
                             AND gcmi.IsSend = b'1'";
                    break;
                case '24' :
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone,
                                cs.AppointmentDay, cs.AppointmentHour, cs.ConsultantName, cs.ConsultantIdx,
                                mam.ServiceControlIdx, scm.TransferMethodCode
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.Consultant cs
                              ON (cs.UsersIdx, cs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                            JOIN abc.AllocationMembers mam
                              ON (mam.UsersIdx, mam.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                            JOIN abc.ServiceControl scm
                              ON scm.ServiceControlIdx = mam.ServiceControlIdx
                           WHERE o.PaysIdx IN ({$param['idxList']}) 
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '32':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone, cm.QRurl
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl AS cm
							  ON cm.ClientControlIdx =  mm.ClientControlIdx 
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND mm.IsOut = b'0'";
                    break;
                case '33':
                case '34':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone, cm.ClientCustomerName
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl AS cm
							  ON cm.ClientControlIdx =  mm.ClientControlIdx 
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '35':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone, cs.AppointmentDate,cs.AppointmentHour
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Consultant cs
                              ON (cs.UsersIdx, cs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl AS cm
							  ON cm.ClientControlIdx =  mm.ClientControlIdx 
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'
                             AND cs.AppointmentDate IS NOT NULL
                             ";
                    break;
                case '36':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl AS cm
							  ON cm.ClientControlIdx =  mm.ClientControlIdx 
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '37':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                case '38':
                    $sql = "SELECT 
                                o.UsersIdx, o.PaysIdx, m.Name, m.Phone, cm.ClientCustomerName
                            FROM o.Pays o
                            JOIN abc.Users mm
                              ON mm.UsersIdx = o.UsersIdx
                            JOIN abc.Members m
                              ON m.MembersIdx = mm.MembersIdx
                            JOIN abc.ClientControl AS cm
							  ON cm.ClientControlIdx = mm.ClientControlIdx 
                           WHERE o.PaysIdx IN ({$param['idxList']})
                             AND o.ProductGroupIdx = :productGroupIdx
                             AND o.IsActive = b'1'
                             AND mm.IsOut = b'0'";
                    break;
                default :
                    throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $sendData = [];
            $failureIdx = $idxList;
            while ($row = $stmt->fetch()) {
                $messageType = "AI";
                $devPhone = "01041033708";
                $smsKind = "L";
                $smsSender = "031***6176";
                $templateId = '';
                $message = '';
                $smsLmsTit = '';
                switch ($param['bizMTemplate']) {
                    case '10':
                        $templateId = $bizMTemplateList[0]['TemplateCode'];
                        $message = $bizMTemplateList[0]['Message'];
                        $message = str_replace('#{NAME}', $row['Name'], $message);
                        $smsLmsTit = "결과안내";
                        $shortUrl = $row['ShortUrl'];
                        $btnName = "결과확인 하기";
                        break;
                    case '21':
                    case '24':
                        if (
                            // 알림톡 미대상자
                            // 테스트 거래처일 경우 (ServiceControlIdx = 4)
                            // api전송(transferMethodCode = 1)일 때, 상담원 지정이 되지 않았을 경우
                            $row['ServiceControlIdx'] === '4'
                            || ($row['TransferMethodCode'] === '1' && !$row['ConsultantIdx'])
                        ) {
                            continue 2;
                        }
                        $templateId = $bizMTemplateList[$row['ResponseType']]['TemplateCode'];
                        $message = $bizMTemplateList[$row['ResponseType']]['Message'];
                        $consultTimeArr = [
                            '10' => "오전 10시",
                            '11' => "오전 11시",
                            '12' => "오후 12시",
                        ];
                        $consultWeekArr = [
                            '1' => "평일",
                            '6' => "주말",
                            '8' => "항상가능"
                        ];
                        $message = str_replace('#{NAME}', $row['Name'], $message);
                        $message = str_replace('#{이름}', $row['ConsultantName'], $message);
                        $smsLmsTit = "상담알림";
                        break;
                    case '33':
                    case '35':
                        $templateId = $bizMTemplateList[0]['TemplateCode'];
                        $message = $bizMTemplateList[0]['Message'];

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
                        $message = str_replace('#{NAME}', $row['Name'], $message);
                        $message = str_replace('#{날짜}', date('Y년 m월 d일', strtotime($row['AppointmentDate'])), $message);
                        $message = str_replace('#{시간}', $consultTimeArr[$row['AppointmentHour']], $message);
                        break;
                    case '38':
                        $templateId = $bizMTemplateList[0]['TemplateCode'];
                        $message = $bizMTemplateList[0]['Message'];
                        $message = str_replace('#{NAME}', $row['Name'], $message);
                        $message = str_replace('#{약국}', $row['ClientCustomerName'], $message);
                        $urlParam = "{$row['UsersIdx']}/{$row['OrderIdx']}";
                        $encryptParam = $this->Encrypt($urlParam);
                        $result = (new NaverShortUrl())->getResult(['url' => $url]);
                        if ($result['code'] !== 200) {
                            throw new \Exception("URL 생성에 실패하였습니다.", "400");
                        }
                        $response = json_decode($result['response'], true);
                        $smsLmsTit = "결과안내";
                        $shortUrl = $response['result']['url'];
                        $btnName = "결과 확인 하기";
                        break;
                    default :
                        throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
                }

                $requestParam = [
                    'UsersIdx' => $row['UsersIdx'],
                    'orderIdx' => $row['OrderIdx'],
                    'templateId' => $templateId,
                    'messageType' => $messageType,
                    'phone' => (isDev) ? $devPhone : $row['Phone'],
                    'message' => $message,
                    'title' => '',
                    'reserveDatetime' => '00000000000000',
                    'smsKind' => $smsKind,
                    'smsSender' => $smsSender,
                    'processType' => $param['bizMTemplate'],
                    'smsLmsTit' => $smsLmsTit,
                    'messageSms' => $message,
                ];
                if (isset($shortUrl, $btnName)) {
                    $requestParam['shortUrl'] = $shortUrl;
                    $requestParam['buttonName'] = $btnName;
                }

                $key = array_search($row['OrderIdx'], $failureIdx);
                if ($key) {
                    unset($failureIdx[$key]);
                }
                $sendData[] = $requestParam;
            }

            if (count($sendData) === 0) {
                throw new \Exception("선택하신 대상자들은 해당 알림톡 대상자들이 아닙니다.", "400");
            }

            $response = $this->sender($sendData);
            $this->data = $response['data'];

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    public function Encrypt($str, $secret_key = '********', $secret_iv = '********')
    {
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 32);
        return @str_replace("=", "", base64_encode(
                openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv))
        );
    }

    // 맞춤 영양 리스트
    function supplementList($param): array
    {
        $this->desc = 'model::supplementList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['m.Name', 'm.Phone', 'ed.RegDatetime'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if (strpos($param['keyword'], 'ed.EventItemManageIdx_') !== false) {
                    $_k = explode('_', $param['keyword']);
                    $addSql .= " AND {$_k[0]} = {$_k[1]} AND ed.DataContent LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            $sql = " SELECT o.PaysIdx,
                           m.Name, m.Phone,
                           mm.UsersIdx,
                           ed.RegDatetime
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.Event AS ed
                        ON ed.UsersIdx = mm.UsersIdx
                       AND ed.PaysIdx = o.PaysIdx
                 LEFT JOIN abc.EventItemManage AS ei
                        ON ei.EventItemManageIdx = ed.EventItemManageIdx 
                       AND ed.ItemCategory = 'supplement'
                       AND ei.ProductIdx = 14
                     WHERE o.ProductGroupIdx = :productGroupIdx
                       AND mm.IsOut = b'0'
                       AND o.IsActive = b'1'
                       {$addSql}
                     GROUP BY o.PaysIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = $stmt->fetchAll();
            $this->setPagination(count($total), $param);

            $sql .= " ORDER BY ed.RegDatetime DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            $orderList = [];
            while ($row = $stmt->fetch()) {
                $this->data['data'][$row['OrderIdx']] = [
                    'UsersIdx' => $row['UsersIdx'],
                    'Name' => $row['Name'],
                    'Phone' => $row['Phone'],
                    'RegDatetime' => $row['RegDatetime'] ? substr($row['RegDatetime'], 0, 10) : '',
                    'DataContent' => [],
                ];

                $orderList[] = $row['OrderIdx'];
            }

            //맞춤영양 컨텐츠 조회
            if (count($orderList) > 0) {
                $orderListStr = implode(',', $orderList);

                $sql = " SELECT o.PaysIdx, ed.DataContent, ei.ItemNum, ed.EventItemManageIdx
                          FROM abc.Event AS ed
                          JOIN abc.EventItemManage AS ei
                            ON ei.EventItemManageIdx = ed.EventItemManageIdx 
                          JOIN o.Pays AS o
                            ON o.PaysIdx = ed.PaysIdx
                         WHERE ed.ItemCategory = 'supplement'
                           AND o.ProductGroupIdx = :productGroupIdx
                           AND ei.ProductIdx = 14
                           AND ed.PaysIdx IN ({$orderListStr})";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $searchCol = [];
                while ($row = $stmt->fetch()) {
                    if (isset($this->data['data'][$row['OrderIdx']])) {
                        $this->data['data'][$row['OrderIdx']]['DataContent'][$row['ItemNum']] = $row['DataContent'];
                    }
                    $searchCol[$row['ItemNum']] = [
                        'text' => "맞춤영양{$row['ItemNum']}",
                        'value' => "ed.EventItemManageIdx_{$row['EventItemManageIdx']}"
                    ];
                }
                $this->data['select::searchColumn'] = $searchCol;
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 설문 응답 조회
    function surveyList($param): array
    {
        $this->desc = 'model::surveyList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                switch ($param['keyword']) {
                    case 'm.Name':
                    case 'm.Phone':
                    case 'prm.CalcDate':
                    case 'ed.RegDatetime':
                        $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                        break;
                    case 'e.IsOut' :
                        if ($param['value'] === 'Y') {
                            $addSql .= " AND ed.EventIdx IS NULL AND prm.RegDatetime <= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                        } else if ($param['value'] === 'N') {
                            $addSql .= " AND (ed.EventIdx IS NOT NULL OR prm.RegDatetime > DATE_SUB(NOW(), INTERVAL 1 HOUR))";
                        }
                        break;
                    default :
                        if (strpos($param['keyword'], 'ed.EventItemManageIdx_') !== false) {
                            $_k = explode('_', $param['keyword']);
                            $addSql .= " AND {$_k[0]} = {$_k[1]} AND ed.DataContent LIKE '%{$param['value']}%'";
                            break;
                        }
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                        break;
                }
            }
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 유저 리스트 조회
            $sql = "     SELECT
                              m.Name, m.Phone
                            , prm.CalcDate, prm.RegDatetime AS CalcDatetime
                            , mm.UsersIdx
                            , o.PaysIdx
                            , p.ProductName, p.ProductIdx
                            , ed.RegDatetime AS EventDate, ed.EventIdx, ed.EventItemManageIdx, ed.DataContent, ed.ModDatetime
                          FROM abc.Members AS m
                          JOIN abc.Users AS mm
                            ON mm.MembersIdx = m.MembersIdx
                          JOIN abc.ProductGroupManage AS pgm
                            ON pgm.ProductGroupIdx = 5
                          JOIN abc.Product AS p
                            ON p.ProductIdx = pgm.ProductIdx
                          JOIN o.Pays AS o
                            ON o.UsersIdx = mm.UsersIdx
                          JOIN abc.Report AS prm
                            ON prm.PaysIdx = o.PaysIdx
                           AND prm.UsersIdx = o.UsersIdx
                           AND prm.ReportType = 2  #테스트예측
                     LEFT JOIN abc.Event AS ed
                            ON ed.PaysIdx = o.PaysIdx
                           AND ed.UsersIdx = o.UsersIdx
                           AND ed.ItemCategory = 'survey'
                         WHERE p.ProductIdx = 8
                           AND o.ProductGroupIdx = :productGroupIdx
                           AND mm.IsOut = b'0'
                           AND o.IsActive = b'1'
                           {$addSql}
                      GROUP BY mm.UsersIdx, o.PaysIdx ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = $stmt->fetchAll();
            $this->setPagination(count($total), $param);

            $sql .= " ORDER BY ed.RegDatetime DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            $nowDatetime = strtotime(date('Y-m-d H:i:s'));
            $orderList = [];
            while ($row = $stmt->fetch()) {
                $outStatus = 'N';
                if (!$row['EventDate']) { //참여 이력이 없는 경우
                    $outStatus = 'Y';
                    $userRegTime = strtotime("+1 hours", strtotime($row['CalcDatetime']));
                    if ($nowDatetime < $userRegTime) { // 리포트 발급 1시간 이내인 경우 미이탈자 취급
                        $outStatus = 'N';
                    }
                }
                $this->data['data'][$row['OrderIdx']] = [
                    'Name' => $row['Name'],
                    'Phone' => $row['Phone'],
                    'UsersIdx' => $row['UsersIdx'],
                    'ProductIdx' => $row['ProductIdx'],
                    'ProductName' => $row['ProductName'],
                    'CalcDate' => $row['CalcDate'],
                    'EventDate' => $row['EventDate'] ? substr($row['EventDate'], 0, 10) : '',
                    'EventIdx' => $row['EventIdx'] ?? '',
                    'ModDatetime' => $row['ModDatetime'] ?? '',
                    'OutStatus' => $outStatus,
                    'Survey' => [],
                ];

                $orderList[] = $row['OrderIdx'];
            }

            // 설문 컨텐츠 조회
            if (count($orderList) > 0) {
                $orderListStr = implode(',', $orderList);
                $sql = "SELECT ed.PaysIdx, ei.ItemNum, ed.DataContent, ed.EventItemManageIdx
                      FROM abc.EventItemManage AS ei
                      JOIN abc.Event AS ed
                        ON ed.EventItemManageIdx = ei.EventItemManageIdx
                     WHERE ei.ItemCategory = 'survey'
                       AND ei.ProductIdx = 8
                       AND ed.PaysIdx IN ({$orderListStr})";
                $stmt = $this->conn->query($sql);
                $searchCol = [];
                while ($row = $stmt->fetch()) {
                    if (isset($this->data['data'][$row['OrderIdx']])) { //유효 데이터만 취급
                        $this->data['data'][$row['OrderIdx']]['Survey'][$row['ItemNum']] = $row['DataContent'];
                    }
                    $searchCol[$row['ItemNum']] = [
                        'text' => "답변{$row['ItemNum']}",
                        'value' => "ed.EventItemManageIdx_{$row['EventItemManageIdx']}",
                    ];
                }
                $this->data['select::searchColumn'] = $searchCol;
            }
            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //설문 문항 수정
    function updateSurveyData($param): array
    {
        $this->desc = 'model::updateSurveyData';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['UsersIdx'], $param['orderIdx'], $param['productIdx'], $param['question'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }

            // 상담예약 여부 조회
            $sql = "  SELECT OrderIdx
                       FROM abc.MemberStatus
                      WHERE UsersIdx = :UsersIdx
                        AND Orderidx = :orderIdx
                        AND ProductIdx = 4
                        AND `Process` = 'E'
                        AND StatusCode = '20000'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!$row) {
                throw new \Exception("상담예약 미진행으로 등록할 수 없습니다.", "404");
            }

            $this->conn->beginTransaction();
            // 설문 동의 갱신
            $table = '***.AgreementManage';
            $idx = [
                'UsersIdx' => $param['UsersIdx'],
                'orderIdx' => $param['orderIdx'],
                'productIdx' => $param['productIdx']
            ];
            $item = [
                'UsersIdx' => $param['UsersIdx'],
                'orderIdx' => $param['orderIdx'],
                'productIdx' => $param['productIdx'],
                'aLL_AGRE_YN' => 'Y',
                'aGRE_DATE' => date('Y-m-d'),
            ];
            $this->insertDuplicate($idx, $table, $item, '');

            // 설문 문항 식별자 조회
            $sql = "SELECT ItemNum, EventItemManageIdx
                      FROM abc.EventItemManage
                     WHERE ItemCategory = 'survey'
                       AND ProductIdx = 8
                       AND Depth = 1
                  ORDER BY ItemNum ASC";
            $stmt = $this->conn->query($sql);
            $evtItemIdx = [];
            while ($row = $stmt->fetch()) {
                $evtItemIdx[] = $row['EventItemManageIdx'];
            }

            $item = [];
            if (count($param['question']) != count($evtItemIdx)) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }
            foreach ($evtItemIdx as $key => $idx) {
                $item[] = [
                    'orderIdx' => $param['orderIdx'],
                    'Usersidx' => $param['UsersIdx'],
                    'eventItemManageIdx' => (int)$idx,
                    'itemCategory' => 'survey',
                    'dataContent' => $param['question'][$key],
                ];
            }
            // 설문 컨텐츠 갱신
            $table = "***.Event";
            $this->bulkInsertUpdate([], $table, $item);

            // 최근 상태 갱신
            $table = "***.MemberStatus";
            $idx = [
                'UsersIdx' => $param['UsersIdx'],
                'orderIdx' => $param['orderIdx'],
                'productIdx' => $param['productIdx']
            ];
            $item = [
                'UsersIdx' => $param['UsersIdx'],
                'orderIdx' => $param['orderIdx'],
                'productIdx' => $param['productIdx'],
                'process' => 'E',
                'statusCode' => '20000',
            ];
            $addUpdate = "LatestDatetime = NOW()";
            $this->insertDuplicate($idx, $table, $item, $addUpdate);

            $this->conn->commit();

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    // 알림톡 ProcessStep별 전송일 조회
    function searchSms($param): array
    {
        $this->desc = 'model::searchSms';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['UsersIdx'], $param['orderIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }

            $sql = "SELECT
                        ProcessStep, SendDate
                    FROM s.SendResult
                    WHERE (UsersIdx, OrderIdx) = (:UsersIdx, :orderIdx)
                    ORDER BY SendDate ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $this->data = [
                'registerBizMDate' => '',
                'diseaseBizMDate' => '',
                'geneticBizMDate' => '',
                'consultBizMDate' => '',
                'leaveBizMDate' => '',
                'laterBizMDate' => '',
                'notResponseBizMDate' => '',
                'reportBizMDate' => ''
            ];
            while ($row = $stmt->fetch()) {
                switch ($row['ProcessStep']) {
                    case '21':
                    case '31':
                        $this->data['registerBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '22':
                    case '33':
                        $this->data['diseaseBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '23':
                        $this->data['geneticBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '24':
                    case '35':
                        $this->data['consultBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '32':
                        $this->data['leaveBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '36':
                        $this->data['laterBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '37':
                        $this->data['notResponseBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    case '38':
                        $this->data['reportBizMDate'] = substr($row['SendDate'], 0, 10);
                        break;
                    default:
                        break;
                }
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 알림톡 조회
    function smsList($param): array
    {
        $this->desc = "model::smsList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            $addCountSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['o.RegDatetime', 'm.Name', 'm.Phone'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if (strpos($param['keyword'], 'BizM') !== false) {
                    $_k = explode('-', $param['keyword']);
                    $addCountSql = " AND sm.ProcessStep = {$_k[1]}";
                    if($param['value'] === 'Y') {
                        $addSql .= " AND sm.SendCount > 0";
                    } else {
                        $addSql .= " AND sm.SendCount IS NULL";
                    }
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            // 대상 전체 카운트
            $sql = "SELECT DISTINCT o.PaysIdx 
                    FROM o.Pays AS o
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.ClientControl AS ccm
                      ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
               LEFT JOIN s.SendManage AS sm
                      ON (sm.UsersIdx, sm.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                    {$addCountSql}
                   WHERE mm.IsOut = b'0' #탈퇴회원 제외
                     AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                     AND o.IsActive = b'1' #활성회원 선별
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $data = [];
            // 최근 상태 조회
            $sql = "
            	WITH targetOrder as (
		            SELECT DISTINCT o.PaysIdx, o.RegDatetime, o.UsersIdx, o.ProductGroupIdx 
		            FROM o.Pays AS o
		            JOIN abc.Users AS mm
			        ON mm.UsersIdx = o.UsersIdx
		            JOIN abc.ClientControl AS ccm
			        ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN abc.Members AS m
			        ON m.MembersIdx = mm.MembersIdx
                    LEFT JOIN s.SendManage AS sm
			        ON (sm.UsersIdx, sm.PaysIdx) = (o.UsersIdx, o.PaysIdx)
			        {$addCountSql}
                    WHERE mm.IsOut = b'0' #탈퇴회원 제외
			        AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
			        AND o.IsActive = b'1' #활성회원 선별
			        {$addSql}
			        ORDER BY o.RegDatetime DESC 
		            LIMIT :start, :entry
	                ),
                targetSmsData as  (
		            SELECT
                    o.RegDatetime, o.UsersIdx, o.PaysIdx, 
                    sm.ProcessStep, m.Name, tm.MembersIdx AS TestMembers
                    FROM targetOrder AS o 
                    JOIN abc.Users AS mm
                    ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members AS m
                    ON m.MembersIdx = mm.MembersIdx
                    LEFT JOIN abc.TestMembers AS tm
                    ON tm.MembersIdx = m.MembersIdx
				    LEFT JOIN s.SendManage AS sm
                    ON (sm.UsersIdx, sm.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                    WHERE o.ProductGroupIdx = :productGroupIdx
                )
                select * FROM targetSmsData
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (!isset($data[$item['OrderIdx']])) {
                    switch ($param['gIdx']) {
                        case "2":
                            $data[$item['OrderIdx']] = ['UsersIdx' => $item['UsersIdx'],
                                'OrderIdx' => $item['OrderIdx'],
                                'Name' => $item['Name'],
                                'RegisterBizM' => $item['ProcessStep'] === '21',
                                'DiseaseBizM' => $item['ProcessStep'] === '22',
                                'GeneticBizM' => $item['ProcessStep'] === '23',
                                'ConsultBizM' => $item['ProcessStep'] === '24',
                                'TestMembers' => $item['TestMembers'],
                                'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',];
                            break;
                        case "5":
                            $data[$item['OrderIdx']] = ['UsersIdx' => $item['UsersIdx'],
                                'OrderIdx' => $item['OrderIdx'],
                                'Name' => $item['Name'],
                                'RegisterBizM' => $item['ProcessStep'] === '31', //신청완료
                                'LeaveBizM' => $item['ProcessStep'] === '32', //이탈발생
                                'DiseaseBizM' => $item['ProcessStep'] === '33', //요약검사결과
                                'ReDiseaseBizM' => $item['ProcessStep'] === '34', //요약검사결과 재발송
                                'ConsultBizM' => $item['ProcessStep'] === '35', //상담사 설명
                                'LaterBizM' => $item['ProcessStep'] === '36', //나중에 결정
                                'NotResponseBizM' => $item['ProcessStep'] === '37', //미응답
                                'ReportBizM' => $item['ProcessStep'] === '38', // View
                                'TestMembers' => $item['TestMembers'],
                                'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',];
                            break;
                    }
                    continue;
                }

                switch ($param['gIdx']) {
                    case "2":
                        $target = [
                            '21' => 'RegisterBizM',
                            '22' => 'DiseaseBizM',
                            '23' => 'GeneticBizM',
                            '24' => 'ConsultBizM'
                        ];
                        break;
                    case "5":
                        $target = [
                            '31' => 'RegisterBizM',
                            '32' => 'LeaveBizM',
                            '33' => 'DiseaseBizM',
                            '34' => 'ReDiseaseBizM',
                            '35' => 'ConsultBizM',
                            '36' => 'LaterBizM',
                            '37' => 'NotResponseBizM',
                            '38' => 'ReportBizM',
                        ];
                        break;
                }



                if (!isset($target[$item['ProcessStep']])) {
                    continue;
                }
                $data[$item['OrderIdx']][$target[$item['ProcessStep']]] = true;

            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상담예약 수정
    function updateConsultingData($param): array
    {
        $this->desc = 'model::updateConsultingData';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['UsersIdx'], $param['orderIdx'], $param['productIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (
                !preg_match($this->pattern['num'], $param['UsersIdx'])
                || !preg_match($this->pattern['num'], $param['orderIdx'])
                || !preg_match($this->pattern['num'], $param['productIdx'])
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }

            switch ($param['gIdx']) {
                case "2":
                    $url = api . "/register/consult";
                    $data = [
                        'UsersIdx' => $param['UsersIdx'],
                        'AppointmentHour' => $param['appointmentHour'],
                        'AppointmentDay' => $param['appointmentDay'],
                        'AllAgreeYn' => 'Y',
                        'AgreeDate' => date('Y-m-d')
                    ];
                    $result = $this->curl("POST", $url, [], $data);
                    if ($result['code'] === 200) {
                        return $this->response();
                    } else {
                        throw new \Exception('통신 실패', '450');
                    }

                    break;
                default :
                    $sql = "    SELECT OrderIdx
                                  FROM abc.Event
                                 WHERE ItemCategory = 'supplement'
                                   AND Orderidx = :orderIdx
                                   AND UsersIdx = :UsersIdx";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                    $stmt->execute();
                    $data = $stmt->fetch();
                    if (!$data) {
                        // *** - 질환결과 데이터 불러오기
                        $sql = "SELECT 
                                    M.Gender, PRM.ReportType, PRM.Uuid, PRM.Data, PRM.ExamDate
                                FROM abc.Report PRM
                                JOIN abc.Users MM ON MM.UsersIdx = PRM.UsersIdx
                                JOIN abc.Members M ON M.MembersIdx = MM.MembersIdx
                                WHERE (PRM.UsersIdx, PRM.PaysIdx) = (:UsersIdx, :orderIdx)
                                AND PRM.ReportType = 2
                                ORDER BY PRM.NhisPreviewListIdx DESC, PRM.ExamDate DESC 
                                LIMIT 1";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                        $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch($this->conn::FETCH_ASSOC);
                        if (!$row) {
                            throw new \Exception("질환 검사결과를 찾을 수 없습니다.", "404");
                        }

                        $gender = (int)$row['Gender'];
                        $***Data = json_decode($row['Data'], true);
                        $***Result = [];
                        foreach ($***Data as $key => $row) {
                            if (in_array($key, [11,12,15,16,24])){
                                $dataName = $this->bioMarkerCode[$key];
                                $stat = $this->bioMarkerGradeCovert($row['rrisk']);

                                $***Result['level'][$dataName['title']] = $stat;
                                $***Result['percentage'][$dataName['title']] = floor($this->bioMarkerPercentCovert($row['rrisk'])) . "%";
                            }
                        }

                        arsort($***Result['percentage']);

                        $recommendSupplements = $this->generateSupplements($***Result, $gender);

                        // 맞춤영양추천 Event 입력
                        // EventItemManageIdx 불러오기
                        $sql = "SELECT ProductIdx, EventItemManageIdx, ItemCategory FROM abc.EventItemManage
                                WHERE ItemCategory = 'supplement'
                                ORDER BY ItemNum ASC";
                        $stmt = $this->conn->query($sql);

                        // Insert Value 생성하기
                        $placeholder = [];
                        $i = 0;
                        while ($row = $stmt->fetch($this->conn::FETCH_ASSOC)) {
                            $suppleProductIdx = (int)$row['ProductIdx'];
                            $eventIdx = (int)$row['EventItemManageIdx'];
                            $itemCategory = $row['ItemCategory'];

                            $placeholder[] = "(:UsersIdx, :orderIdx, {$eventIdx}, '{$itemCategory}', '{$recommendSupplements[$i]}')";
                            $i++;
                        }

                        $placeholders = implode(",", $placeholder);
                        $sql = "INSERT INTO abc.Event (
                                    UsersIdx, OrderIdx, EventItemManageIdx, ItemCategory, DataContent)
                                VALUES {$placeholders}
                                ON DUPLICATE KEY UPDATE
                                    ItemCategory = VALUE(ItemCategory),
                                    DataContent = VALUE(DataContent),
                                    ModDatetime = NOW()";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                        $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                        $stmt->execute();

                        //상태정보 갱신
                        $sql = "UPDATE abc.MemberStatus 
                                   SET `Process` = 'E', `StatusCode` = '20000'
                                 WHERE (UsersIdx, OrderIdx) = (:UsersIdx, :orderIdx)
                                   AND ProductIdx = 14";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':UsersIdx', $param['UsersIdx'], $this->conn::PARAM_INT);
                        $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
                        $stmt->execute();
                    }

                    $this->conn->beginTransaction();

                    // 상담 동의 갱신
                    $table = '***.AgreementManage';
                    $idx = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                        'productIdx' => $param['productIdx']
                    ];
                    $item = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                        'productIdx' => $param['productIdx'],
                        'aLL_AGRE_YN' => 'Y',
                        'aGRE_DATE' => date('Y-m-d'),
                    ];
                    $this->insertDuplicate($idx, $table, $item, '');

                    // 상담 정보 갱신
                    $table = '***.Consultant';
                    $idx = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                    ];
                    $item = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                        'appointmentHour' => $param['appointmentHour'] !== '' ? (int)$param['appointmentHour'] : '',
                        'appointmentDay' => $param['appointmentDay'] !== '' ? (int)$param['appointmentDay'] : '',
                    ];
                    $addUpdate = "ModDatetime = '" . date('Y-m-d H:i:s') . "'";
                    if($param['appointmentHour'] !== ''){
                        $addUpdate .= ", AppointmentHour = {$param['appointmentHour']}";
                    }
                    if($param['appointmentDay'] !== '') {
                        $addUpdate .= ", AppointmentDay = {$param['appointmentDay']}";
                    }

                    $this->insertDuplicate($idx, $table, $item, $addUpdate);

                    // 최근 상태 갱신
                    $table = "***.MemberStatus";
                    $idx = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                        'productIdx' => $param['productIdx']
                    ];
                    $item = [
                        'UsersIdx' => $param['UsersIdx'],
                        'orderIdx' => $param['orderIdx'],
                        'productIdx' => $param['productIdx'],
                        'process' => 'E',
                        'statusCode' => '20000',
                    ];
                    $addUpdate = "LatestDatetime = '" . date('Y-m-d H:i:s') . "'";
                    $this->insertDuplicate($idx, $table, $item, $addUpdate);

                    $this->conn->commit();
                    $this->conn = null;
                    return $this->response();
                    break;
            }
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    // 맞춤영양추천 알고리즘
    public function generateSupplements($***Result, int $gender): array
    {
        $recommendSupplements = [];

        $level = $***Result['level'];
        $percentage = $***Result['percentage'];

        $top3BiomarkerPercentage = array_slice($percentage,0,3);
        if (in_array('췌장암', array_keys($top3BiomarkerPercentage)) && in_array('당뇨병', array_keys($top3BiomarkerPercentage))) {
            $top3BiomarkerPercentage = array_slice($percentage,0,4);
        }
        $top3Biomarker = array_keys($top3BiomarkerPercentage);
        foreach ($top3Biomarker as $val) {
            if ($val === '신장질환') {
                $recommendSupplements[] = $this->supplements[$val][$gender][$level[$val]];
            } else {
                $recommendSupplements[] = $this->supplements[$val][$level[$val]];
            }
        }

        $supplements = array_values(array_unique($recommendSupplements));

        return $supplements;
    }

    // biomarker rrisk-위험도 코딩 규칙
    public function bioMarkerGradeCovert($rRisk)
    {
        $target = [1, 1.15, 1.3, 1.5];
        $name = ["양호", "주의", "경고", "위험"];

        $result = "";
        foreach ($target as $key => $row) {
            if ($rRisk <= $row) {
                $result = $name[$key];
                break;
            }
        }
        return ($result !== "") ? $result : "고위험";
    }

    // biomarker rrisk-퍼센트 변환
    public function bioMarkerPercentCovert($rRisk)
    {
        if ($rRisk > 1.9) {
            return 90;
        } else if ($rRisk > 1) {
            return ($rRisk - 1) * 100;
        } else if ($rRisk == 1) {
            return 0;
        } else {
            return -(1 - $rRisk) * 100;
        }
    }

    //상담예약 조회
    // 상품그룹: 얼리큐, 상품: **상담
    function consultingList($param): array
    {
        $this->desc = 'model::consultingList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                switch ($param['keyword']) {
                    case 'm.Name':
                    case 'm.Phone':
                    case 'prm.CalcDate':
                        $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                        break;
                    case 'cs.IsOut' :
                        if ($param['value'] === 'Y') {
                            $addSql .= " AND (cs.AppointmentDay = '' OR cs.AppointmentDay IS NULL ) AND (cs.AppointmentHour = '' OR cs.AppointmentHour IS NULL) AND prm.RegDatetime <= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                        } else if ($param['value'] === 'N') {
                            $addSql .= " AND ((cs.AppointmentDay != ''  AND cs.AppointmentHour != '') OR prm.RegDatetime > DATE_SUB(NOW(), INTERVAL 1 HOUR))";
                        }
                        break;
                    case 'cs.IsAgree' :
                        if ($param['value'] === 'Y') {
                            $addSql .= " AND cs.AppointmentDay != ''  AND cs.AppointmentHour != ''";
                        } else if ($param['value'] === 'N') {
                            $addSql .= " AND (cs.AppointmentDay = '' OR cs.AppointmentDay IS NULL ) AND (cs.AppointmentHour = '' OR cs.AppointmentHour IS NULL)";
                        }
                        break;
                    default :
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                        break;
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                if ($param['column'] === 'IsOut') {
                    $orderSql .= " cs.AppointmentHour {$param['sort']}, prm.RegDatetime {$param['sort']} ";
                } else if ($param['column'] === 'IsAgree') {
                    $orderSql .= " cs.AppointmentHour {$param['sort']} ";
                } else {
                    $orderSql .= " {$param['column']} {$param['sort']} ";
                }
            } else {
                $orderSql .= ' prm.RegDatetime DESC, prm.NhisPreviewListIdx DESC ';
            }

            $sql = " SELECT
                          m.Name, m.Phone
                        , mm.UsersIdx
                        , o.PaysIdx
                        , prm.CalcDate, prm.RegDatetime as CalcDatetime
                        , pgm.ProductIdx
                        , p.ProductName
                        , cs.AppointmentHour, cs.AppointmentDay, cs.ConsultantType
                        , cs.RegDatetime
                      FROM abc.Members AS m
                      JOIN abc.Users AS mm
                        ON mm.MembersIdx = m.MembersIdx
                      JOIN o.Pays AS o
                        ON o.UsersIdx = mm.UsersIdx
                      JOIN abc.ProductGroupManage AS pgm
                        ON pgm.ProductGroupIdx = :productGroupIdx
                      JOIN abc.Product AS p
                        ON p.ProductIdx = pgm.ProductIdx
                 LEFT JOIN abc.Consultant AS cs
                        ON cs.UsersIdx = o.UsersIDx
                       AND cs.PaysIdx = o.PaysIdx
                      JOIN abc.Report AS prm
                        ON prm.PaysIdx = o.PaysIdx
                       AND prm.UsersIdx = o.UsersIdx
                     WHERE p.ProductIdx = 4
                       AND o.ProductGroupIdx = :productGroupIdx
                       AND prm.ReportType = 2 #테스트예측
                       AND mm.IsOut = b'0'
                       AND o.IsActive = b'1'
                       {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll();
            $total = count($row);
            $this->setPagination($total, $param);

            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();

            $nowDatetime = strtotime(date('Y-m-d H:i:s'));
            while ($row = $stmt->fetch()) {
                $userRegTime = strtotime("+1 hours", strtotime($row['CalcDatetime']));
                $outStatus = 'N';
                $consultAgree = 'N';
                if ((!$row['AppointmentDay'] || !$row['AppointmentHour'])) {
                    if ($nowDatetime > $userRegTime) { // 리포트 발급 1시간 이내인 경우 미이탈자 취급
                        $outStatus = 'Y';
                    }
                } else {
                    $consultAgree = 'Y';
                }
                $this->data['data']["idx{$row['OrderIdx']}"] = [
                    'CalcDate' => $row['CalcDate'],
                    'ReservationDate' => $row['RegDatetime'] ? substr($row['RegDatetime'], 0, 10) : '',
                    'UsersIdx' => $row['UsersIdx'],
                    'OrderIdx' => $row['OrderIdx'],
                    'Name' => $row['Name'],
                    'Phone' => $row['Phone'],
                    'OutStatus' => $outStatus,
                    'ConsultAgree' => $consultAgree,
                    'AppointmentDay' => $row['AppointmentDay'],
                    'AppointmentHour' => $row['AppointmentHour'],
                    'ProductIdx' => $row['ProductIdx'],
                ];
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 전화상담 조회
    function telephoneList($param): array
    {
        $this->desc = "model::telephoneList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['o.RegDatetime', 'm.Name', 'm.Phone'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'culs.StatusCode') {
                        switch ($param['value']) {
                            case 'Y':
                                $addSql .= " AND ({$param['keyword']} <> '20000' OR {$param['keyword']} IS NULL)";
                                break;
                            case 'N':
                                $addSql .= " AND {$param['keyword']} = '20000'";
                                break;
                        }
                    } else if ($param['keyword'] === 'am.ALL_AGRE_YN') {
                        switch ($param['value']) {
                            case 'Y':
                                $addSql .= " AND {$param['keyword']} = 'Y'";
                                break;
                            case 'N':
                                $addSql .= " AND ({$param['keyword']} <> 'Y' OR {$param['keyword']} IS NULL)";
                                break;
                        }
                    } else if ($param['keyword'] === 'cs.AppointmentDay') {
                        switch ($param['value']) {
                            case '평일':
                                $param['value'] = '1';
                                break;
                            case '주말':
                                $param['value'] = '6';
                                break;
                            case '항상가능':
                                $param['value'] = '8';
                                break;
                        }
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                    } else {
                        $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                    }
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']} ";
            } else {
                $orderSql .= ' o.RegDatetime DESC ';
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                       o.RegDatetime, o.UsersIdx, o.PaysIdx, 
                       culs.Process, culs.StatusCode,
                       cs.AppointmentDay, cs.AppointmentHour, am.ALL_AGRE_YN,
                       m.Name, m.Phone, tm.MembersIdx AS TestMembers
                    FROM o.Pays AS o 
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
               LEFT JOIN abc.Consultant AS cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
               LEFT JOIN abc.MemberStatus AS culs
                      ON (culs.UsersIdx, culs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                     AND culs.ProductIdx = 4
               LEFT JOIN abc.AgreementManage AS am
                      ON (am.UsersIdx, am.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                     AND am.ProductIdx = 4
                   WHERE mm.IsOut = b'0'
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data["idx{$item['OrderIdx']}"] = [
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'Process' => $item['Process'],
                    'StatusCode' => $item['StatusCode'],
                    'Name' => $item['Name'],
                    'Phone' => $item['Phone'],
                    'AppointmentDay' => $item['AppointmentDay'],
                    'AppointmentHour' => $item['AppointmentHour'],
                    'ALL_AGRE_YN' => $item['ALL_AGRE_YN'],
                    'TestMembers' => $item['TestMembers'],
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                ];
            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // xxx검사 신청서 발송오류 조회
    function agreementFailList($param): array
    {
        $this->desc = "model::agreementFailList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                       apsel.RegDatetime, apsel.UsersIdx, apsel.PaysIdx, 
                       ccm.ClientControlIdx, ccm.ClientCustomerName,
                       m.Name, tm.MembersIdx AS TestMembers
                    FROM abc.AgreementPaperSendErrorList AS apsel
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = apsel.UsersIdx
                    JOIN abc.ClientControl AS ccm
                      ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN o.Pays AS o 
                      ON o.PaysIdx = apsel.PaysIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
                   WHERE mm.IsOut = b'0'
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= " ORDER BY apsel.RegDatetime DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data[$item['OrderIdx']] = [
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'ClientControlIdx' => $item['ClientControlIdx'],
                    'ClientCustomerName' => $item['ClientCustomerName'],
                    'Name' => $item['Name'],
                    'TestMembers' => $item['TestMembers'],
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                ];
            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // xxx검사 신청서 다운로드
    function getGeneticAgreement($param): void
    {
        try {
            if (!isset($param['orderIdx'])) {
                throw new \Exception("필수 파라미터가 존재하지 않습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        gcmi.UsersIdx, gcmi.PaysIdx, gcmi.AgreementPaperDir, 
                        am.AGRE_DATE, og.PaysingIdx, m.Name
                    FROM abc.Genom gcmi
                    JOIN abc.AgreementManage am
                      ON (am.UsersIdx, am.PaysIdx) = (gcmi.UsersIdx, gcmi.PaysIdx)
                    JOIN abc.Users mm
                      ON mm.UsersIdx = gcmi.UsersIdx
                    JOIN abc.Members m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN o.Paysing og
                      ON og.PaysIdx = gcmi.PaysIdx
                   WHERE gcmi.PaysIdx = :orderIdx
                     AND am.ProductIdx IN (5,6)
                     AND og.ProductIdx IN (5,6)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$row) {
                throw new \Exception("xxx검사 데이터가 조회되지 않습니다.", "404");
            }
            $agreeDate = date('Ymd', strtotime($row['AGRE_DATE']));
            $filename = $row['AgreementPaperDir'];
            if (!$filename) {
                $filename = "/api-service/priv/genetic-agreement/";
                $filename .= "{$agreeDate}_{$row['UsersIdx']}_{$row['OrderIdx']}";
            }
            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);
            $agreementDir = "{$dir}/image{$filename}";
            if (!file_exists($agreementDir)) {
                $filename = "/api-service/priv/genetic-agreement/";
                $filename .= "{$agreeDate}_{$row['UsersIdx']}_{$row['OrderingIdx']}.png";
                $agreementDir = "{$dir}/image{$filename}";
            }
            if (!file_exists($agreementDir)) {
                throw new \Exception("xxx검사 신청서 파일을 찾을 수 없습니다.", "404");
            }

            $agreementFile = file_get_contents($agreementDir, FILE_USE_INCLUDE_PATH);

            $downloadName = "{$row['UsersIdx']}_{$row['Name']}_xxx검사신청서.png";

            header("Pragma: public");
            header("Expires: 0");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename={$downloadName}");
            header("Content-Transfer-Encoding: binary");

            echo $agreementFile;
            exit;

        } catch (\Exception $e) {
            alert($e->getMessage());
            echo "<script>window.close();</script>";
            exit;
        }
    }

    // xxx검사신청 조회
    function geneticList($param): array
    {
        $this->desc = "model::geneticList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['o.RegDatetime', 'ccm.ClientCustomerName', 'm.Name', 'gcmi.GCRegDate'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if (in_array($param['keyword'], ['ccm.ResponseType', 'gcmi.IsSend'])) {
                        switch ($param['value']) {
                            case '이메일':
                                $param['value'] = '1';
                                break;
                            case '직접출력':
                                $param['value'] = '2';
                                break;
                            case 'Y':
                            case 'y':
                                $param['value'] = "b'1'";
                                break;
                            case 'N':
                            case 'n':
                                $param['value'] = "b'0'";
                                break;
                            default:
                                $param['value'] = null;
                        }
                    }
                    if ($param['value']) {
                        $addSql .= " AND {$param['keyword']} = {$param['value']}";
                    }
                }
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                       o.RegDatetime, gcmi.UsersIdx, gcmi.PaysIdx, gcmi.GCRegNo, gcmi.GCRegDate, 
                       gcmi.AgreementPaperDir, gcmi.IsSend,
                       ccm.ClientCustomerName, ccm.ResponseType,
                       m.Name, tm.MembersIdx AS TestMembers
                    FROM abc.Genom AS gcmi
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = gcmi.UsersIdx
                    JOIN abc.ClientControl AS ccm
                      ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN o.Pays AS o 
                      ON o.PaysIdx = gcmi.PaysIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
                   WHERE mm.IsOut = b'0'
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= " ORDER BY o.RegDatetime DESC ";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data[$item['OrderIdx']] = [
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'ClientCustomerName' => $item['ClientCustomerName'],
                    'ResponseType' => $item['ResponseType'],
                    'Name' => $item['Name'],
                    'GCRegNo' => $item['GCRegNo'],
                    'GCRegDate' => $item['GCRegDate'],
                    'IsSend' => $item['IsSend'],
                    'AgreementPaperDir' => $item['AgreementPaperDir'],
                    'TestMembers' => $item['TestMembers'],
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                ];
            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // IB 리포트 다운로드
    function getIbReport($param): void
    {
        try {
            if (!isset($param['orderIdx'], $param['tCode'])) {
                throw new \Exception("필수 파라미터가 존재하지 않습니다.", "404");
            }
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        o.UsersIdx, o.PaysIdx,
                        m.Name, m.Gender, m.Birth1, m.Birth2
                    FROM o.Pays o
                    JOIN abc.Users mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members m
                      ON m.MembersIdx = mm.MembersIdx
                    WHERE o.PaysIdx = :orderIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$row) {
                throw new \Exception("주문 정보가 조회되지 않습니다.", "404");
            }

            $UsersIdx = (int)$row['UsersIdx'];
            $orderIdx = (int)$row['OrderIdx'];
            $name = $row['Name'];
            $gender = $row['Gender'] === '1' ? '남' : ($row['Gender'] === '2' ? '여' : '');
            $age = convertAging($row['Birth1'] . $row['Birth2'], date('Y-m-d'));
            $downloadName = "{$UsersIdx}_{$name}_{$gender}_{$age}.pdf";

            if ($param['tCode'] === '2') {
                $sql = "INSERT IGNORE INTO abc.TransferUsers (
                            UsersIdx, OrderIdx, IsComplete)
                        VALUES ({$UsersIdx}, {$orderIdx}, b'1')";
                $this->conn->query($sql);
            }

            $filename = "{$UsersIdx}_{$orderIdx}_{$param['gIdx']}.pdf";

            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);

            $pdfPath = "{$dir}/image/datashare/priv/ibReport/{$filename}";

            if (!file_exists($pdfPath)) {
                $userData = $this->getIbData($param);
                (new Pdf())->createIbPdf($userData);
            }

            $pdfFile = file_get_contents($pdfPath, FILE_USE_INCLUDE_PATH);

            header('Cache-Control: no-cache,no-store,max-age=0,must-revalidate');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . strlen($pdfFile));
            header('Content-type: application/pdf;charset=UTF-8');
            header('Expires: 0');

            $this->conn = null;
            echo $pdfFile;
            exit;

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // IB 가져오기 및 생성
    private function getIbData($param)
    {
        try {
            if (!in_array($param['gIdx'], ['2', '5'])) {
                throw new \Exception('IB가 제공되지 않은 상품그룹입니다.', '400');
            }

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 필요 데이터 가져오기
            $userData = [];

            // 회원정보 조회
            $sql = "SELECT 
                        o.UsersIdx, o.PaysIdx, o.ProductGroupIdx,
                        m.Name, m.Gender, m.Birth1, m.Birth2, m.Phone, m.State, m.City, m.FullCity, m.Email,
                        ccm.ClientCustomerName, ccm.ResponseType,
                        ccm.State AS ClientState, ccm.City AS ClientCity, ccm.FullCity AS ClientFullCity,
                        cs.AppointmentDay, cs.AppointmentHour, cs.AppointmentDate, cs.ConsultantType,
                        ued.EventProcess
                    FROM o.Pays o
                    JOIN abc.Users mm
                      ON mm.UsersIdx = o.UsersIdx
                    JOIN abc.Members m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN abc.ClientControl ccm
                      ON ccm.ClientControlIdx = mm.ClientControlIdx
                    JOIN abc.Consultant cs
                      ON (cs.UsersIdx, cs.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                    -- 할당된 회원만 IB 다운로드 가능
                    JOIN abc.AllocationMembers mam
                      ON (mam.UsersIdx, mam.PaysIdx) = (o.UsersIdx, o.PaysIdx)
               LEFT JOIN abc.Event ued
                      ON (ued.UsersIdx, ued.PaysIdx) = (o.UsersIdx, o.PaysIdx)
                     AND ued.ItemCategory = 'personal_link'
                   WHERE o.PaysIdx = {$param['orderIdx']}
                     AND mm.IsOut = b'0'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $MembersData = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$MembersData) {
                throw new \Exception('회원정보를 찾을 수 없습니다.', '404');
            }
            $UsersIdx = $MembersData['UsersIdx'];
            $orderIdx = $MembersData['OrderIdx'];

            $MembersData['Age'] = convertAging($MembersData['Birth1'] . $MembersData['Birth2'], date('Y-m-d'));
            $MembersData['Birth'] = date('Y.m.d', strtotime($MembersData['Birth1'] . $MembersData['Birth2']));
            $MembersData['GenderStr'] = $MembersData['Gender'] === '1' ? '남' : ($MembersData['Gender'] === '2' ? '여' : '');

            $userData['MembersData'] = $MembersData;

            // *** 결과 조회
            $sql = "SELECT 
                        Data, Uuid, ReportType, ExamDate
                    FROM abc.Report
                   WHERE (UsersIdx, OrderIdx) = ({$UsersIdx}, {$orderIdx})
                     AND ReportType = 2
                ORDER BY ExamDate DESC
                   LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $item = $stmt->fetch($this->conn::FETCH_ASSOC);
            if (!$item) {
                throw new \Exception('질환검사 결과를 찾을 수 없습니다.', '404');
            }
            $***Data = json_decode($item['Data'], true);
            $***Result = [];
            if (count($***Data) === 0) {
                throw new \Exception("질환검사 결과가 없습니다.", '404');
            }
            foreach ($***Data as $key => $row) {
                $dataName = $this->bioMarkerCode[$key];
                $stat = $this->bioMarkerGradeCovert($row['rrisk']);
                $***Result[$stat][$dataName['title']] = floor($this->bioMarkerPercentCovert($row['rrisk'])) . "%";
            }
            $userData['***Data'] = $***Result;
            switch ($param['gIdx']) {
                // 질환 IB
                case '2':
                    # xxx 검사결과 조회
                    $sql = "SELECT 
                                grl.PaysIdx, grl.ResultCode, pc.CatalogName 
                            FROM abc.GeneticResultList grl
                            JOIN abc.ProductCatalog pc
                              ON pc.ProductCatalogIdx = grl.ProductCatalogIdx
                           WHERE grl.ResultCode IN ('H', 'A', 'L')
                             AND (grl.UsersIdx, grl.PaysIdx) = ({$UsersIdx}, {$orderIdx})";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $geneticResult = $stmt->fetchAll($this->conn::FETCH_ASSOC) ?? [];
                    if (count($geneticResult) === 0) {
                        throw new \Exception('xxx 검사결과를 찾을 수 없습니다.', '404');
                    }

                    $geneResult = [
                        'H' => [],
                        'A' => [],
                        'L' => []
                    ];
                    foreach ($geneticResult as $item) {
                        $geneResult[$item['ResultCode']][] = $item['CatalogName'];
                    }

                    $userData['geneResult'] = $geneResult;

                    break;
                // 얼리큐 IB
                case '5':
                    # 설문문항 조회
                    $sql = "SELECT 
                                EventItemManageIdx, Content, Depth, ParentEventItemManageIdx, ItemNum
                            FROM abc.EventItemManage
                           WHERE ItemCategory = 'survey'
                        ORDER BY Depth ASC, ParentEventItemManageIdx ASC, ItemNum ASC";
                    $stmt = $this->conn->query($sql);
                    $surveyQ = [];
                    while ($item = $stmt->fetch()) {
                        if($item['Depth'] === '1') {
                            $surveyQ['question'][$item['EventItemManageIdx']] = "{$item['ItemNum']}. {$item['Content']}";
                        } else {
                            $surveyQ['answer'][$item['ParentEventItemManageIdx']][$item['ItemNum']] = $item['Content'];
                        }
                    }

                    # 설문응답 조회
                    $sql = "SELECT 
                                EventItemManageIdx, DataContent
                            FROM abc.Event
                           WHERE (UsersIdx, OrderIdx) = ({$UsersIdx}, {$orderIdx})
                             AND ItemCategory = 'survey'";
                    $stmt = $this->conn->query($sql);
                    $surveyA = [];
                    while($item = $stmt->fetch()) {
                        $surveyA[$item['EventItemManageIdx']] = $item['DataContent'];
                    }

                    if (empty($surveyQ) || empty($surveyA)) {
                        throw new \Exception('설문 데이터를 찾을 수 없습니다.', '404');
                    }

                    $userData['surveyQuestion'] = $surveyQ;
                    $userData['surveyData'] = $surveyA;

                    break;
                default:
                    break;
            }
            $this->conn = null;

            return $userData;

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // *** 리포트 다운로드
    function get***Report($param): void
    {
        try {
            if (!isset($param['orderIdx'], $param['uuid'])) {
                throw new \Exception("필수 파라미터가 존재하지 않습니다.", "404");
            }

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT 
                        prm.UsersIdx, prm.PaysIdx, prm.ReportType, prm.`Uuid`,
                        m.Name
                    FROM abc.Report prm
                    JOIN abc.Users mm
                      ON mm.UsersIdx = prm.UsersIdx
                    JOIN abc.Members m
                      ON m.MembersIdx = mm.MembersIdx
                   WHERE prm.PaysIdx = :orderIdx
                     AND prm.`Uuid` = :uuid
                ORDER BY prm.NhisPreviewListIdx DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':orderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':uuid', $param['uuid']);
            $stmt->execute();
            $resqData = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];
            if (!$resqData) {
                throw new \Exception("*** 데이터가 조회되지 않습니다.", "404");
            }

            $token = $this->createMedtekToken();
            if (!$token) {
                throw new \Exception('u2 request error', '401');
            }

            $requestParam = [
                'uuid' => $resqData['Uuid'],
                '***Type' => $resqData['ReportType'],
                'filename' => "{$resqData['UsersIdx']}_{$resqData['OrderIdx']}_{$resqData['ReportType']}",
                'u2Token' => $token
            ];

            $filename = "{$requestParam['filename']}.pdf";
            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);
            $pdfPath = "{$dir}/image/datashare/priv/u_*****_u/{$filename}";
            if (!file_exists($pdfPath)) {
                $this->getU2Pdf($requestParam);
            }

            $pdfFile = file_get_contents($pdfPath, FILE_USE_INCLUDE_PATH);

            $downloadName = "{$resqData['UsersIdx']}_{$resqData['Name']}";
            $downloadName .= $resqData['ReportType'] == '1' ? "_테스트.pdf" : ($resqData['ReportType'] == '2' ? "_질환.pdf" : ".pdf");

            header('Cache-Control: no-cache,no-store,max-age=0,must-revalidate');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . strlen($pdfFile));
            header('Content-type: application/pdf;charset=UTF-8');
            header('Expires: 0');

            $this->conn = null;
            echo $pdfFile;
            exit;

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // xxxxxx  다운로드
    function getU2Pdf($param): void
    {
        try {
            //$type = 1 - 테스트; 2 - 질환
            $variant = $param['***Type'] == '2' ? '1' : '0';

            $url = "{$this->apiUrlU2}/api/open/age/{$param['uuid']}/pdf";
            $header = ["Authorization: {$param['u2Token']}"];
            $body = [
                'uuid' => $param['uuid'],
                'variant' => $variant,
                'filename' => $param['filename'],
            ];

            $result = $this->curl('GET', $url, $header, $body);
            if ($result['code'] != '200') {
                throw new \Exception("*** pdf 파일 통신 실패", "500");
            }
            $filename = "{$param['filename']}.pdf";
            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);
            $pdfPath = "{$dir}/image/datashare/priv/u_*****_u/{$filename}";
            file_put_contents($pdfPath, $result['response']);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // xxxxxx 토큰 생성
    private function createMedtekToken(): string
    {
        try {
            $response = [];
            $url = "{$this->apiUrlU2}/api/open/auth";
            $apiIdU2 = "*********";
            $apiSecretU2 = "*********9572!";
            $data = [
                'id' => $apiIdU2,
                'password' => $apiSecretU2
            ];
            $header = [
                'Content-Type: application/json; charset=utf-8'
            ];

            $tokenData = $this->curl('POST', $url, $header, json_encode($data));
            $response = json_decode($tokenData['response'], true)['token'];
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $response;
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
                $url = "{$url}?{$body}";

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

    // 질환검사 조회
    function diseaseList($param): array
    {
        $this->desc = "model::diseaseList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }
            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['prm.RegDatetime', 'm.Name'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, prm.NhisPreviewListIdx DESC ";

            } else {
                $orderSql .= ' prm.RegDatetime DESC, prm.NhisPreviewListIdx DESC ';
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                       prm.RegDatetime, prm.UsersIdx, prm.PaysIdx, 
                       prm.NhisPreviewListIdx, prm.Uuid, m.Name, tm.MembersIdx AS TestMembers
                    FROM abc.Report AS prm
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = prm.UsersIdx
                    JOIN o.Pays AS o 
                      ON o.PaysIdx = prm.PaysIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
                   WHERE mm.IsOut = b'0'
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     {$addSql}
                   GROUP BY o.PaysIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data["idx{$item['OrderIdx']}"] = [
                    'UsersIdx' => $item['UsersIdx'],
                    'OrderIdx' => $item['OrderIdx'],
                    'Name' => $item['Name'],
                    'NhisPreviewListIdx' => $item['NhisPreviewListIdx'],
                    'Uuid' => $item['Uuid'],
                    'TestMembers' => $item['TestMembers'],
                    'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                ];
            }
            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 질환검사 리스트
    function bioageList($param): array
    {
        $this->desc = 'model::bioageList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['m.Name', 'm.Phone', 'prm.CalcDate', 'ccm.ClientCustomerName', 'pccm.ClientCustomerName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                $orderSql .= " {$param['column']} {$param['sort']}, prm.NhisPreviewListIdx DESC ";
            } else {
                $orderSql .= ' prm.RegDatetime DESC, prm.NhisPreviewListIdx DESC ';
            }

            $data = [];
            // 대상 전체 카운트
            $sql = "SELECT
                       prm.CalcDate, prm.UsersIdx, prm.PaysIdx, prm.Data,
                       prm.NhisPreviewListIdx, prm.Uuid, m.Name, tm.MembersIdx AS TestMembers,
                       ccm.ClientCustomerName, 
                       ccm.CCTel, pccm.ClientCustomerName AS ParentClientCustomerName, ccm.ClientCustomerCode, m.Phone
                    FROM abc.Report AS prm
                    JOIN abc.Users AS mm
                      ON mm.UsersIdx = prm.UsersIdx
                    JOIN o.Pays AS o 
                      ON o.PaysIdx = prm.PaysIdx
                    JOIN abc.Members AS m
                      ON m.MembersIdx = mm.MembersIdx
                    JOIN abc.ClientControl AS ccm
   	                  ON ccm.ClientControlIdx = mm.ClientControlIdx
               LEFT JOIN abc.ClientControl as pccm
                      ON ccm.ParentClientCustomerIdx = pccm.ClientControlIdx   
               LEFT JOIN abc.TestMembers AS tm
                      ON tm.MembersIdx = m.MembersIdx
                   WHERE mm.IsOut = b'0'
                     AND o.ProductGroupIdx = :productGroupIdx
                     AND o.IsActive = b'1'
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);


            // 최근 상태 조회
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                if (isset($data[$item['OrderIdx']])) {
                    continue;
                }
                $data["idx{$item['OrderIdx']}"] = [
                    'UsersIdx' => $item['UsersIdx'],
                    'ClientCustomerCode' => $item['ClientCustomerCode'],
                    'ClientCustomerName' => $item['ClientCustomerName'],
                    'OrderIdx' => $item['OrderIdx'],
                    'Name' => $item['Name'],
                    'NhisPreviewListIdx' => $item['NhisPreviewListIdx'],
                    'Uuid' => $item['Uuid'],
                    'TestMembers' => $item['TestMembers'],
                    'CalcDate' => $item['CalcDate'],
                    'ParentClientCustomerName' => $item['ParentClientCustomerName'],
                    'CCTel' => $item['CCTel'],
                    'Phone' => $item['Phone'],
                    '***ReportUrl' => "{$this->***ReportUrl}&reg=" . $this->Encrypt("{$item['UsersIdx']}/{$item['OrderIdx']}"),
                    'CWCnt' => 0,
                    'DHCnt' => 0,
                ];

                $***Data = json_decode($item['Data'], true);
                foreach ($***Data as $val) {
                    $stat = $this->bioMarkerGradeCovert($val['rrisk']);
                    if ($stat === '양호') {
                        continue;
                    }
                    if (in_array($stat, ['주의', '경고'])) {
                        $data["idx{$item['OrderIdx']}"]['CWCnt']++;
                    }
                    if (in_array($stat, ['위험', '고위험'])) {
                        $data["idx{$item['OrderIdx']}"]['DHCnt']++;
                    }
                }
            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 무료 잔량 수정
    function updateFreeTicket($param) : array
    {
        $this->desc = 'updateFreeTicket';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $ClientControlIdx = isset($param['ClientControlIdx']) ? (int)$param['ClientControlIdx'] : '';
            $SaleGoodsIdx = isset($param['SaleGoodsIdx']) ? (int)$param['SaleGoodsIdx'] : '';
            $updateCount = $param['updateCount'];

            $table = "***.SaleGoods";
            if($updateCount >= 0) {
                // 티켓 meta 갱신
                if(!$SaleGoodsIdx) {
                    $item = [
                        'ClientControlIdx' => $ClientControlIdx,
                        'ticketType'              => 2,
                    ];
                    $SaleGoodsIdx = $this->insertUpdate([],$table, $item);
                } else {
                    $item['modDatetime'] = date('Y-m-d H:i:s');
                    $this->insertUpdate(['SaleGoodsIdx'=>$SaleGoodsIdx], $table, $item);
                }

                // 티켓 지급
                $table = "***.IssuedSaleGoods";
                $items = [];
                for($i=0;$i<$updateCount;$i++) {
                    $items[] = [
                        'SaleGoodsIdx' => $SaleGoodsIdx,
                        'ClientControlIdx' => $ClientControlIdx,
                    ];
                }
                $this->bulkInsertUpdate([], $table, $items);
                $this->msg = "무료지급권을 ".$updateCount."개 부여하였습니다.";
                // 지급 (insert)
            } else {
                // 삭제
                $updateCount = -($updateCount);
                if(!$SaleGoodsIdx) {
                    throw new \Exception("develop error","503");
                }
                $sql = "SELECT IssuedSaleGoodsIdx
                         FROM abc.IssuedSaleGoods
                        WHERE ClientControlIdx = ".$ClientControlIdx."
                          AND SaleGoodsIdx = ".$SaleGoodsIdx."
                     ORDER BY IssuedDatetime DESC
                        LIMIT ".$updateCount;
                $stmt = $this->conn->query($sql);
                $delTicket = [];
                while($row = $stmt->fetch()) {
                    $delTicket[] = $row['IssuedSaleGoodsIdx'];
                }
                $delTicket = count($delTicket)>1 ? implode(',', $delTicket) : $delTicket[0];

                $sql = "DELETE FROM abc.IssuedSaleGoods
                              WHERE IssuedSaleGoodsIdx IN (".$delTicket.")
                              LIMIT ".$updateCount;
                $this->conn->query($sql);
                $this->msg = "무료지급권을 ".$updateCount."개 삭제하였습니다.";
            }
            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상담사 정보 수정
    function updateClientData($param) : array
    {
        $this->desc = 'updateClientData';
        try {
            if(!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $ClientControlIdx = isset($param['ClientControlIdx']) ? (int)$param['ClientControlIdx'] : '';
            $clientCustomerName = isset($param['clientCustomerName']) ? $param['clientCustomerName'] : '';
            $cCGroup = $param['cCGroup'] !== '' ? $param['cCGroup'] : 'null';
            $cCTel = isset($param['cCTel']) ? $param['cCTel'] : '';
            $item = [
                'clientCustomerName' => $clientCustomerName,
                'cCManager'          => $clientCustomerName,
                'cCGroup'            => $cCGroup,
                'cCTel'              => $cCTel,
            ];
            $table = "***.ClientControl";
            $item['modDatetime'] = date('Y-m-d H:i:s');
            $this->insertUpdate(['ClientControlIdx'=>$ClientControlIdx], $table, $item);
            $this->msg = "상담사 정보를 수정하였습니다.";
            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }


    // 사용자 정보 대량 등록
    function uploadOfferCompanyDb($param): array
    {
        $this->desc = 'uploadOfferCompanyDb';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['gIdx'], $param['category'], $param[0]['companyFile'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }

            $sql = "SELECT ClientControlIdx, ClientCustomerName, ServiceControlIdx 
                      FROM abc.ClientControl
                     WHERE ProductGroupIdx = :productGroupIdx
                      AND IsUse = b'1'
                      AND `Depth` = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll($this->conn::FETCH_ASSOC);
            if (count($row) === 0) {
                throw new \Exception('등록된 **사가 존재하지않습니다.', 400);
            }
            $parentClientCustomer = [];
            foreach ($row as $data) {
                $parentClientCustomer[$data['ClientCustomerName']] = [
                    'serviceIdx' => $data['ServiceControlIdx'],
                    'clientIdx' => $data['ClientControlIdx'],
                ];
            }

            $serverFilename = $param[0]['companyFile']['tmp_name'];
            $pcFilename = $param[0]['companyFile']['name'];

            $spreadsheet = new SpreadsheetFactory();
            $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
            if ($result['code'] !== 200) {
                throw new \Exception('read error', 400);
            }

            $spreadData = $result['data'];
            if (count($spreadData) < 1) {
                throw new \Exception("양식이 입력되지 않았습니다." , "401");
            }
            unset($spreadData[0]);
            unset($spreadData[1]);
            unset($spreadData[2]);

            foreach ($spreadData as $key => $value) {
                if (!array_filter($value)) {
                    continue;
                }
                if (!isset($parentClientCustomer[$value[0]])) {
                    throw new \Exception("사용처명이 올바르지않거나, 등록되지않은 사용처입니다." , "401");
                }
                if (!$value[2]) {
                    throw new \Exception("상담사명 입력은 필수입니다." , "401");
                }
                if (!$value[3] || !preg_match($this->pattern['num'], $value[3])) {
                    throw new \Exception("전화번호 입력은 필수입니다." , "401");
                }

                $clientCustomerCode = $this->generateClientCode((int)$parentClientCustomer[$value[0]]['serviceIdx']);
                $table = "***.ClientControl";
                $item = [
                    'parentClientCustomerIdx' => (int)$parentClientCustomer[$value[0]]['clientIdx'],
                    'ServiceControlIdx' => (int)$parentClientCustomer[$value[0]]['serviceIdx'],
                    'category' => (string)$param['category'],
                    'clientCustomerCode' => $clientCustomerCode,
                    'clientCustomerName' => (string)$value[2],
                    'cCGroup' => (string)$value[1],
                    'cCManager' => (string)$value[2],
                    'cCTel' => (string)$value[3],
                    'productGroupIdx' => (int)$param['gIdx'],
                    'isUse' => 1,
                    'isActive' => 1,
                    'depth' => 2,
                    'latestAdminIP' => $_SERVER['REMOTE_ADDR'],
                ];

                $ClientControlIdx = $this->insertUpdate([], $table, $item);

                $serveCount = (int)$value['4'];
                if($serveCount > 0) {

                    $table = "***.SaleGoods";
                    $item = [
                        'ticketType' => 2,
                        'ClientControlIdx' => $ClientControlIdx,
                    ];
                    $sql = "SELECT SaleGoodsIdx FROM abc.SaleGoods
                                 WHERE ClientControlIdx = ".$ClientControlIdx. " AND TicketType = 2";
                    $stmt = $this->conn->query($sql);
                    $ticketIdx = $stmt->fetch();

                    // 티켓 meta 갱신
                    if(!$ticketIdx) {
                        $ticketIdx = $this->insertUpdate([],$table, $item);
                    } else {
                        $ticketIdx = (int)$ticketIdx['SaleGoodsIdx'];
                        $item['modDatetime'] = date('Y-m-d H:i:s');
                        $this->insertUpdate(['SaleGoodsIdx'=>$ticketIdx], $table, $item);
                    }

                    // 티켓 지급
                    $table = "***.IssuedSaleGoods";
                    $items = [];
                    for($i=0;$i<$serveCount;$i++) {
                        $items[] = [
                            'SaleGoodsIdx' => $ticketIdx,
                            'ClientControlIdx' => $ClientControlIdx,
                        ];
                    }
                    $this->bulkInsertUpdate([], $table, $items);
                }
            }
            $this->msg = "상담사 정보 대량등록이 완료되었습니다.";
            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //사용처 대량등록
    function uploadCompanyDb($param): array
    {
        $this->desc = 'uploadCompanyDb';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['parentClientCustomerIdx'], $param[0]['companyFile'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            $serverFilename = $param[0]['companyFile']['tmp_name'];
            $pcFilename = $param[0]['companyFile']['name'];

            $spreadsheet = new SpreadsheetFactory();
            $result = $spreadsheet->readSheet($serverFilename, $pcFilename);
            if ($result['code'] !== 200) {
                throw new \Exception('read error', "400");
            }

            $spreadData = $result['data'];
            if (count($spreadData) < 1) {
                throw new \Exception("양식이 입력되지 않았습니다." , "401");
            }
            unset($spreadData[0]);
            $items = [];
            foreach ($spreadData as $value) {
                if (!array_filter($value)) {
                    continue;
                }
                if (!$value[1]) {
                    throw new \Exception("거래처 코드 입력은 필수입니다." , "401");
                }
                if (
                    !in_array($value[0], ['H', 'P', 'I'])
                    || !preg_match($this->pattern['code'], $value[1])
                    || !preg_match($this->pattern['all'], $value[2])
                    || ($value[3] && !preg_match($this->pattern['kor'], $value[3]))
                    || ($value[4] && !preg_match($this->pattern['num'], $value[4]))
                    || ($value[5] && !preg_match($this->pattern['num'], $value[5]))
                    || ($value[6] && !preg_match($this->pattern['kor'], $value[6]))
                    || ($value[7] && !preg_match($this->pattern['kor'], $value[7]))
                    || ($value[8] && !preg_match($this->pattern['kor'], $value[8]))
                    || ($value[9] && !preg_match($this->pattern['all'], $value[9]))
                    || ($value[10] && !in_array($value[10], [1, 2]))
                    || ($value[11] && !in_array($value[11], ['blood', 'buccal', 'none']))
                ) {
                    throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
                }

                $sql = "SELECT ServiceControlIdx FROM abc.ClientControl WHERE ClientControlIdx=".$param['parentClientCustomerIdx'];
                $stmt =  $this->conn->query($sql);
                $row = $stmt->fetch();
                if(!$row) {
                    throw new \Exception("등록 데이터가 올바르지 않습니다.",'504');
                }
                $ServiceControlIdx = (int)$row['ServiceControlIdx'];

                $items[] = [
                    'ServiceControlIdx' => $ServiceControlIdx ?? '',
                    'parentClientCustomerIdx' => (int)$param['parentClientCustomerIdx'],
                    'category' => (string)$value[0],
                    'clientCustomerCode' => (string)$value[1],
                    'clientCustomerName' => (string)$value[2],
                    'cCManager' => (string)$value[3],
                    'cCTel' => (string)$value[4],
                    'postCode' => (string)$value[5],
                    'state' => (string)$value[6],
                    'city' => (string)$value[7],
                    'fullCity' => (string)$value[8],
                    'addressDetail' => htmlspecialchars($value[9]),
                    'responseType' => (int)$value[10],
                    'specimenType' => (string)$value[11],
                    'productGroupIdx' => (int)$value[12] ?? NULL,
                    'depth' => 2,
                ];
            }
            $table = "***.ClientControl";
            $this->bulkInsertUpdate([], $table, $items);

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }


    //QR코드 다운
    function qrDown($param): void
    {
        $this->desc = 'qrDown';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            if (!isset($param['ClientControlIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            $ClientControlIdx = (int)$param['ClientControlIdx'];
            $sql = "SELECT
                        CCM.QRurl, CCM.ClientCustomerCode, CCM.ClientCustomerName, CCM.ProductGroupIdx 
                      FROM abc.ClientControl CCM
                      JOIN abc.ProductGroup PG
                        ON PG.ProductGroupIdx = CCM.ProductGroupIdx
                     WHERE CCM.ClientControlIdx = :ClientControlIdx
                       AND PG.BusinessManageIdx = 1 AND PG.IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $ClientControlIdx, $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch($this->conn::FETCH_ASSOC);

            if (!is_array($row)) {
                throw new \Exception("등록된 상품그룹 정보가 없습니다.", "400");
            }
            if (!$row['ClientCustomerCode']) {
                throw new \Exception("병원코드가 등록되지 않았습니다.", "400");
            }
            $clientCustomerName = $row['ClientCustomerName'];
            if (!$row['QRurl']) {
                $orgUrl = "https://d.g******com/abc/?hCode=" . $row['ClientCustomerCode'];
                $result = (new NaverShortUrl())->getResult(['url' => $orgUrl]);
                if ($result['code'] !== 200) {
                    throw new \Exception("URL 생성에 실패하였습니다.", "400");
                }
                $response = json_decode($result['response'], true);
                $shortUrl = $response['result']['url'];
                if (!$shortUrl) {
                    throw new \Exception("URL 생성에 실패하였습니다.", 400);
                }

                $idx = ['ClientControlIdx' => $ClientControlIdx];
                $item = ['qRurl' => "{$shortUrl}.qr"];
                $table = "***.ClientControl";
                $this->insertUpdate($idx, $table, $item);

                $row['QRurl'] = "{$shortUrl}.qr";
            }
            list($width, $height) = getimagesize($row['QRurl']);

            $newWidth = $width * 5;
            $newHeight = $height * 5;
            $thumb = imagecreatetruecolor($newWidth, $newHeight);

            $white = imagecolorallocate($thumb, 0, 0, 0);
            $dir = explode('/', $_SERVER['DOCUMENT_ROOT']);
            array_pop($dir);
            array_pop($dir);
            $dir = implode('/', $dir);
            $font = "{$dir}/image/NanumGothic.ttf";

            $bbox = imagettfbbox(18, 0, $font, $clientCustomerName);
            $center = (imagesx($thumb) / 2) - (($bbox[2] - $bbox[0]) / 2);

            $qrImage = imagecreatefrompng($row['QRurl']);
            $resizedQrImage = imagecopyresized($thumb, $qrImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $qrImageWithText = imagettftext($thumb, 18, 0, $center, 390, $white, $font, $clientCustomerName);
            $finalImage = imagecrop($thumb, ['x' => 3 * 5, 'y' => 3 * 5, 'width' => 78 * 5, 'height' => 78 * 5]);

            $filename = "{$clientCustomerName} QrCode.png";

            header("Pragma: public");
            header("Expires: 0");
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=utf-8\' \'' . rawurlencode($filename));
            header("Content-Transfer-Encoding: binary");

            imagepng($finalImage);
            imagedestroy($finalImage);

            exit;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @brief 단일 충돌 등록,수정
     * @param array $unique : UniqueKey, array $table : table, array $items : [column=>value,..], $addUpdate = "
     *     ModDatetime = yyyy-mm-dd.."
     * @return int : lastInsertId, INSERT=>idx, UPDATE=>0
     * @throws \Exception
     * @author hellostellaa
     */
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
                        $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
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
                        $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
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

    /**
     * @brief 단일 등록,수정
     * @param array $idx : PrimaryKey or UniqueKey, string $table : table, array $item : [column=>value,..]
     * @return int : lastInsertId, INSERT=>idx, UPDATE=>0
     * @throws \Exception
     * @author hellostellaa
     */
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
                    $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
                    $whereQuery .= ucfirst($key) . " = " . $column;
                }

                $updateQuery = "";
                if (count($item) > 0) {
                    foreach ($item as $key => $value) {
                        if ($value === '') {
                            unset($item[$key]);
                        } else if($value === 'null') {
                            if ($updateQuery !== "") {
                                $updateQuery .= ",";
                            }
                            $updateQuery .= ucfirst($key) . " = NULL";
                        } else {
                            if ($updateQuery !== "") {
                                $updateQuery .= ",";
                            }
                            $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
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
                        } else if($value === 'null') {
                            if ($insertColumns !== "") {
                                $insertColumns .= ",";
                                $insertValues .= ",";
                            }
                            $insertColumns .= ucfirst($key);
                            $insertValues .= "NULL";
                        } else {
                            if ($insertColumns !== "") {
                                $insertColumns .= ",";
                                $insertValues .= ",";
                            }
                            $insertColumns .= ucfirst($key);
                            $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
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

    /**
     * @brief 복수 등록,수정
     * @param array $idx : PrimaryKey or UniqueKey, string $table : table, array $items : [column=>value,..]
     * @return int : lastInsertId, INSERT=>idx, UPDATE=>0
     * @throws \Exception
     * @author hellostellaa
     */
    function bulkInsertUpdate(array $idx, string $table, array $items): int
    {
        $this->desc = 'model::bulkInsertUpdate';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (count($idx) > 0) {
                $whereQuery = "";
                foreach ($idx as $key => $value) {
                    if ($whereQuery !== "") {
                        $whereQuery .= " AND ";
                    }

                    $whereQuery .= ucfirst($key) . " IN ({$value})";
                }
                $updateQuery = "";
                if (count($items) > 0) {
                    foreach ($items as $key => $value) {
                        if ($value === '') {
                            unset($items[$key]);
                        } else {
                            if ($updateQuery !== "") {
                                $updateQuery .= ",";
                            }
                            $column = (gettype($value) === 'integer' || $key === 'isUse' || $key === 'isActive') ? $value : "'{$value}'";
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
                // 컬럼의 데이터타입 확인
                $types = [];
                foreach ($items[0] as $item) {
                    $types[] = gettype($item);
                }
                // 입력 컬럼, 입력 값 배열 정의
                $insertColumns = "";
                $insertVal = [];
                if (count($items) > 0) {
                    foreach ($items as $key => $value) {
                        if ($key === 0) {
                            $columns = array_keys($value);
                            foreach ($columns as $k => $col) {
                                if ($k > 0) {
                                    $insertColumns .= ",";
                                }
                                $insertColumns .= ucfirst($col);
                            }
                        }
                        $insertVal[] = array_values($value);
                    }
                }
                // 입력 값 배열 내 데이터 타입 확인 및 교정
                $_D = [];
                $_d = [];
                foreach ($insertVal as $keys => $values) {
                    foreach ($values as $key => $value) {
                        if (!$value) {
                            $_d[$keys][] = "NULL";
                            continue;
                        }
                        if ($types[$key] === 'string') {
                            $_d[$keys][] = "'{$value}'";
                        } else {
                            $_d[$keys][] = $value;
                        }
                    }
                    $_D[] = "(" . implode(',', $_d[$keys]) . ")";
                }
                $insertValues = implode(',', $_D);
                // sql
                if (!$table || !$insertColumns || !$insertValues) {
                    throw new \Exception('필수 파라미터가 없습니다.', '404');
                }
                $sql = "INSERT INTO {$table} ({$insertColumns}) VALUES {$insertValues}";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $this->conn->lastInsertId();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    //사용처 등록:수정
    function registCompany($param): array
    {
        $this->desc = 'model::registCompany';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['parentClientCustomerIdx'], $param['category'], $param['companyCode'], $param['companyName'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (
                !in_array($param['category'], ['H', 'P', 'I'])
                || !preg_match($this->pattern['code'], $param['companyCode'])
                || !preg_match($this->pattern['all'], $param['companyName'])
                || (isset($param['manager']) && !preg_match($this->pattern['kor'], $param['manager']))
                || (isset($param['phone']) && !preg_match($this->pattern['num'], $param['phone']))
                || (isset($param['mainTel']) && !preg_match($this->pattern['num'], $param['mainTel']))
                || (isset($param['postcode']) && !preg_match($this->pattern['num'], $param['postcode']))
                || (isset($param['sido']) && !preg_match($this->pattern['kor'], $param['sido']))
                || (isset($param['sigungu']) && !preg_match($this->pattern['kor'], $param['sigungu']))
                || (isset($param['roadname']) && !preg_match($this->pattern['kor'], $param['roadname']))
                || (isset($param['addressDetail']) && !preg_match($this->pattern['all'], $param['addressDetail']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }
            $idx = isset($param['ClientControlIdx']) ? ['ClientControlIdx' => (int)$param['ClientControlIdx']] : [];

            if (isset($param['mainTel'])) {
                $sql = "SELECT CCMainTel FROM abc.ClientCustomerContractManage WHERE ClientControlIdx = ".$param['ClientControlIdx'];
                $stmt = $this->conn->query($sql);
                $row = $stmt->fetch();
                if (!$row) {
                    throw new \Exception("등록 데이터가 올바르지 않습니다.", '504');
                }
                $table = "***.ClientCustomerContractManage";
                $item = [
                    'cCMainTel' => (string)$param['mainTel'],
                ];
                $this->insertUpdate($idx, $table, $item);
            }

            $table = "***.ClientControl";

            $sql = "SELECT ServiceControlIdx FROM abc.ClientControl WHERE ClientControlIdx=".$param['parentClientCustomerIdx'];
            $stmt =  $this->conn->query($sql);
            $row = $stmt->fetch();
            if(!$row) {
                throw new \Exception("등록 데이터가 올바르지 않습니다.",'504');
            }
            $ServiceControlIdx = (int)$row['ServiceControlIdx'];

            $parentClientCustomerIdx = (int)$param['parentClientCustomerIdx'];
            $category = $param['category'];
            $depth = isset($param['depth']) ? (int)$param['depth'] : 2;
            $clientCustomerCode = $param['companyCode'];
            $sql = "SELECT COUNT(*) AS CodeCnt FROM abc.ClientControl
                    WHERE ClientCustomerCode = :clientCustomerCode";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':clientCustomerCode', $clientCustomerCode);
            $stmt->execute();
            $codeCnt = $stmt->fetch()['CodeCnt'] ?? 0;
            if ($codeCnt > 0 && !isset($param['ClientControlIdx'])) {
                $clientCustomerCode = "{$clientCustomerCode}_{$codeCnt}";
            }

            $clientCustomerName = $param['companyName'];
            $cCManager = isset($param['manager']) ? (string)$param['manager'] : '';
            $cCTel = isset($param['phone']) ? (string)$param['phone'] : '';
            $postCode = isset($param['postcode']) ? (string)$param['postcode'] : '';
            $state = isset($param['sido']) ? (string)$param['sido'] : '';
            $city = isset($param['sigungu']) ? (string)$param['sigungu'] : '';
            $fullCity = isset($param['roadname']) ? (string)$param['roadname'] : '';
            $addressDetail = isset($param['addressDetail']) ? (string)$param['addressDetail'] : '';
            $productGroup = isset($param['productGroup']) ? (int)$param['productGroup'] : '';
            $isUse = isset($param['isUse']) ? $param['isUse'] : b'1';
            $isActive = isset($param['isActive']) ? $param['isActive'] : b'1';

            $item = [
                'ServiceControlIdx' => $ServiceControlIdx ?? '',
                'parentClientCustomerIdx' => $parentClientCustomerIdx ?? '',
                'category' => $category,
                'depth' => $depth,
                'clientCustomerCode' => $clientCustomerCode,
                'clientCustomerName' => $clientCustomerName,
                'cCManager' => $cCManager,
                'cCTel' => $cCTel,
                'postCode' => $postCode,
                'state' => $state,
                'city' => $city,
                'fullCity' => $fullCity,
                'addressDetail' => $addressDetail,
                'productGroupIdx' => $productGroup,
                'latestAdminIP' => $_SERVER['REMOTE_ADDR'],
                'modDatetime' => date('Y-m-d H:i:s'),
                'isUse' => $isUse,
                'isActive' => $isActive
            ];
            $this->insertUpdate($idx, $table, $item);

            $this->conn = null;
            return $this->response();// 등록
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //사용처 조회 (식별자 기준)
    function searchCompany($param): array
    {
        $this->desc = 'model::searchCompany';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['ClientControlIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            $sql = "SELECT
                      ccm.ClientControlIdx, ccm.ProductGroupIdx, ccm.ClientCustomerCode, ccm.ClientCustomerName
                    , ccm.ParentClientCustomerIdx, ccm.Depth, ccm.Category, ccm.State, ccm.City, ccm.FullCity, ccm.AddressDetail
                    , ccm.PostCode, ccm.CCTel, ccm.CCManager, ccm.QRurl, ccm.RegDatetime
                    , pg.ProductGroupName, pg.ProductGroupCode
                    , ccm2.ClientCustomerName AS ParentClientCustomerName
                    , ccm.ModDatetime, ccm.IsUse
                    , cccm.CCMainTel, cccm.ContractDocId
                    FROM abc.ClientControl AS ccm
                    JOIN abc.ClientControl AS ccm2
                    ON ccm2.ClientControlIdx = ccm.ParentClientCustomerIdx
                    LEFT JOIN abc.ClientCustomerContractManage AS cccm
                    ON cccm.ClientControlIdx = ccm.ClientControlIdx
                    LEFT JOIN abc.ProductGroup AS pg
                    ON pg.ProductGroupIdx = ccm.ProductGroupIdx
                    WHERE ccm.ClientControlIdx = :ClientControlIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch() ?? '';
            if (!$row) {
                throw new \Exception("필수 파라미터가 올바르지 않습니다.", "400");
            }
            $this->data = [
                'ClientControlIdx' => $row['ClientControlIdx'] ?? '',
                'ProductGroupIdx' => $row['ProductGroupIdx'] ?? '',
                'ClientCustomerCode' => $row['ClientCustomerCode'] ?? '',
                'ClientCustomerName' => $row['ClientCustomerName'] ?? '',
                'ParentClientCustomerIdx' => $row['ParentClientCustomerIdx'] ?? '',
                'Depth' => $row['Depth'] ?? '',
                'Category' => $row['Category'] ?? '',
                'State' => $row['State'] ?? '',
                'City' => $row['City'] ?? '',
                'FullCity' => $row['FullCity'] ?? '',
                'AddressDetail' => $row['AddressDetail'] ?? '',
                'PostCode' => $row['PostCode'] ?? '',
                'CCTel' => $row['CCTel'] ?? '',
                'CCManager' => $row['CCManager'] ?? '',
                'QRurl' => $row['QRurl'] ?? '',
                'RegDatetime' => substr($row['RegDatetime'], 10) ?? '',
                'ProductGroupCode' => $row['ProductGroupCode'] ?? '',
                'ParentClientCustomerName' => $row['ParentClientCustomerName'] ?? '',
                'IsUse' => $row['IsUse'] ?? '',
            ];
            if ($row['ContractDocId']) {
                $this->data['CCMainTel'] = $row['CCMainTel'];
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }


    //사용처 조회 (식별자 기준)
    function searchTicketsData($param): array
    {
        $this->desc = 'model::searchServiceCompany';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }

            $sql = "SELECT
                          cm.TicketsIdx, cm.CouponType, cm.CouponCode, cm.CouponName, cm.DiscountMethod
                        , cm.DiscountAmount, cm.DiscountRate, cm.ServiceControlIdx, cm.ClientControlIdx
                        , cm.UseStartDate, cm.UseEndDate, cm.CouponStatus, cm.RegDatetime, cm.ModDatetime
                        , sm.ServiceCompanyName
                      FROM abc.Tickets AS cm
                      JOIN abc.ServiceControl AS sm
                        ON sm.ServiceControlIdx = cm.ServiceControlIdx
                     WHERE cm.TicketsIdx = :TicketsIdx
                       AND cm.ProductGroupIdx = :productGroupIdx
                       AND cm.IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':TicketsIdx', $param['TicketsIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            while($row = $stmt->fetch()) {
                $this->data = [
                    'TicketsIdx'         => $row['TicketsIdx'],
                    'couponType'              => $row['CouponType'],
                    'couponCode'              => $row['CouponCode'],
                    'couponName'              => $row['CouponName'],
                    'discountMethod'          => $row['DiscountMethod'],
                    'discountAmount'          => $row['DiscountAmount'],
                    'discountRate'            => $row['DiscountRate'],
                    'ServiceControlIdx' => $row['ServiceControlIdx'],
                    'serviceCompanyName'      => $row['ServiceCompanyName'],
                    'ClientControlIdx' => $row['ClientControlIdx'],
                    'useStartDate'            => $row['UseStartDate'],
                    'useEndDate'              => $row['UseEndDate'],
                    'couponStatus'            => $row['CouponStatus'],
                    'clientCustomerCode'      => '',
                ];
            }

            if($this->data['ClientControlIdx']) {
                $sql = "SELECT ClientCustomerCode
                          FROM abc.ClientControl
                         WHERE ClientControlIdx = ".$this->data['ClientControlIdx'];
                $stmt = $this->conn->query($sql);
                $this->data['clientCustomerCode'] = $stmt->fetch()['ClientCustomerCode'];
            }

            $sql = "SELECT ServiceControlIdx AS `value`, ServiceCompanyName AS `text`
                    FROM abc.ServiceControl
                    WHERE IsContract = b'1'";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll() ?? [];
            $this->data['select::ServiceControl'] = $row;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //사용처 리스트 조회
    function companyList($param, $gIdx): array
    {
        $this->desc = 'model::companyList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if (isset($param['category']) && $param['category'] === 'I'){
                $addSql .= " AND ccm.Category = 'I'";
            }
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['ccm2.ClientCustomerName', 'ccm.ClientCustomerName', 'pg.ProductGroupName', 'ccm.CCManager'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if (in_array($param['keyword'], ['ccm.CCTel', 'cccm.CCMainTel'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'ccm.Category') {
                        if ($param['value'] === '병원') {
                            $param['value'] = 'H';
                        } else if ($param['value'] === '약국') {
                            $param['value'] = 'P';
                        }
                    }
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                switch ($param['column']) {
                    case 'Address':
                        $orderSql .= " ccm.State {$param['sort']}, ccm.City {$param['sort']}, ccm.FullCity {$param['sort']} ";
                        break;
                    default:
                        $orderSql .= " {$param['column']} {$param['sort']} ";
                }
            } else {
                $orderSql .= ' ccm.RegDatetime DESC ';
            }

            $data = [];
            $sql = "SELECT
                      ccm.ClientControlIdx, ccm.ProductGroupIdx, ccm.ClientCustomerCode, ccm.ClientCustomerName
                    , ccm.ParentClientCustomerIdx, ccm.Depth, ccm.Category, ccm.State, ccm.City, ccm.FullCity, ccm.AddressDetail
                    , ccm.PostCode, ccm.CCTel, ccm.CCManager, ccm.QRurl, ccm.RegDatetime, ccm.ResponseType, ccm.SpecimenType
                    , ccm.ModDatetime, ccm.IsUse, ccm.IsActive
                    , pg.ProductGroupName, pg.ProductGroupCode
                    , ccm2.ClientCustomerName AS ParentClientCustomerName
                    , cccm.Contents, cccm.CCMainTel
                    FROM abc.ClientControl AS ccm
                    JOIN abc.ClientControl AS ccm2
                      ON ccm2.ClientControlIdx = ccm.ParentClientCustomerIdx
                    LEFT JOIN abc.ClientCustomerContractManage as cccm
                        ON ccm.ClientControlIdx = cccm.ClientControlIdx
                    LEFT JOIN abc.ProductGroup AS pg
                      ON pg.ProductGroupIdx = ccm.ProductGroupIdx
                   WHERE ccm.Depth = 2
                     AND (ccm.ProductGroupIdx = :productGroupIdx OR ccm.ProductGroupIdx IS NULL)
                     AND (cccm.ContractStatus = 'E' OR cccm.ClientControlIdx IS NULL)
                     {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $gIdx, $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);
            //main query
            $sql .= $orderSql;
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $gIdx, $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $data[] = [
                    'ClientControlIdx' => $row['ClientControlIdx'] ?? '',
                    'ProductGroupIdx' => $row['ProductGroupIdx'] ?? '',
                    'ClientCustomerCode' => $row['ClientCustomerCode'] ?? '',
                    'ClientCustomerName' => $row['ClientCustomerName'] ?? '',
                    'ParentClientCustomerIdx' => $row['ParentClientCustomerIdx'] ?? '',
                    'Depth' => $row['Depth'] ?? '',
                    'Category' => $row['Category'] ?? '',
                    'State' => $row['State'] ?? '',
                    'City' => $row['City'] ?? '',
                    'FullCity' => $row['FullCity'] ?? '',
                    'AddressDetail' => $row['AddressDetail'] ?? '',
                    'PostCode' => $row['PostCode'] ?? '',
                    'CCTel' => $row['CCTel'] ?? '',
                    'CCMainTel' => $row['CCMainTel'] ?? '',
                    'CCManager' => $row['CCManager'] ?? '',
                    'QRurl' => $row['QRurl'] ?? '',
                    'RegDatetime' => substr($row['RegDatetime'], 0, 10) ?? '',
                    'ProductGroupName' => $row['ProductGroupName'] ?? '',
                    'ProductGroupCode' => $row['ProductGroupCode'] ?? '',
                    'ParentClientCustomerName' => $row['ParentClientCustomerName'] ?? '',
                    'ResponseType' => $row['ResponseType'] ?? '',
                    'SpecimenType' => $row['SpecimenType'] ?? '',
                    'ModDatetime' => $row['ModDatetime'] ?? '',
                    'Contents' => $row['Contents'] ?? '',
                    'IsUse' => $row['IsUse'] ?? '',
                    'IsActive' => $row['IsActive'] ?? '',
                ];
            }
            $this->data['data'] = $data;

            // 거래처 셀렉트박스용
            $sql = "SELECT ClientControlIdx AS `value`, ClientCustomerName AS `text`
                    FROM abc.ClientControl
                    WHERE Depth = 1";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll() ?? [];
            $this->data['select::client'] = $row;
            $this->data['select::clientCustomer'] = $row;

            // 상품그룹 셀렉트박스용
            $sql = "SELECT ProductGroupIdx AS `value`, ProductGroupName AS `text`
                    FROM abc.ProductGroup
                    WHERE IsUse = b'1'";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll() ?? [];
            $this->data['select::productGroup'] = $row;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 상품그룹 정보 세팅
    function setGroupInfo($groupCode): array
    {
        $this->desc = 'model::setGroupInfo';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $sql = "SELECT ProductGroupCode, ProductGroupIdx, ProductGroupName
                    FROM abc.ProductGroup
                    WHERE IsUse = b'1'
                    ORDER BY ProductGroupIdx DESC";
            $stmt = $this->conn->query($sql);
            while ($row = $stmt->fetch()) {
                if ($row['ProductGroupCode'] === $groupCode) {
                    $this->data['ProductGroupIdx'] = $row['ProductGroupIdx'];
                    $this->data['ProductGroupCode'] = $groupCode;
                }
                $this->data['ProductGroup'][] = $row;
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 상품그룹 비활성화
    function disableItemGroup($param): array
    {
        $this->desc = "model::disableItemGroup";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productGroupIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '400');
            }
            $sql = "SELECT ccm.ProductGroupIdx FROM abc.ClientControl as ccm
                    WHERE ccm.ProductGroupIdx = :productGroupIdx
                    ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());

            if($total > 0){
                throw new \Exception('상품그룹이 이미 사용처에 등록되어있어 비활성화가 불가능합니다.', '400');
            }
            //상품 비활성화
            $sql = "UPDATE abc.ProductGroup 
                    SET IsUse = b'0'
                    WHERE ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 상품그룹 등록
    function itemGroupInsert($param): array
    {
        $this->desc = 'model::itemGroupInsert';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productGroupName'], $param['childProductIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (
                !preg_match($this->pattern['all'], $param['productGroupName'])
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }
            $this->conn->beginTransaction();
            $table = '***.ProductGroup';
            $item = [
                'businessManageIdx' => 1,
                'productGroupName' => $param['productGroupName'] ?? '',
                'productGroupCode' => $param['productGroupCode'] ?? 'DEVELOPER DEFINE',
                'activePoint' => $param['activePoint'] ?? 1,
                'isUse' => b'1'
            ];
            $childProductIdx = json_decode($param['childProductIdx'], true);
            $productGroupIdx = $this->insertUpdate([], $table, $item);
            if (count($childProductIdx) > 0) {
                $items = [];
                foreach ($childProductIdx as $key => $value) {
                    $items[] = [
                        'productGroupIdx' => $productGroupIdx,
                        'productIdx' => (int)$value,
                        'sort' => ($key + 1),
                    ];
                }
                $table = "***.ProductGroupManage";
                $this->bulkInsertUpdate([], $table, $items);
            }

            $this->conn->commit();

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    //그룹명 수정
    function updateGroupName($param): array
    {
        $this->desc = 'model::updateGroupName';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productGroupName'], $param['productGroupIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (!preg_match($this->pattern['all'], $param['productGroupName'])) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다..', '400');
            }
            $sql = "UPDATE abc.ProductGroup
                    SET ProductGroupName = :productGroupName
                    WHERE ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupName', $param['productGroupName']);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //그룹명 조회
    function searchProductGroupName($param): array
    {
        $this->desc = 'model::searchProductGroupName';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productGroupIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            $sql = "SELECT ProductGroupIdx, ProductGroupName
                    FROM abc.ProductGroup
                    WHERE ProductGroupIdx = :productGroupIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['productGroupIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $this->data = $stmt->fetch() ?? [];

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상품 조회(상품 식별자)
    function searchProductItem($param): array
    {
        $this->desc = 'model::searchProductItem';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            $sql = "SELECT 
                        p.ProductIdx, p.ProductName, p.ParentProductIdx, p.Gender,
                        pc.ProductCatalogIdx, pc.CatalogName, pc.RefCode
                    FROM abc.Product AS p 
                    LEFT JOIN abc.ProductCatalog AS pc
                    ON pc.ProductIdx = p.ProductIdx
                    WHERE p.ProductIdx = :productIdx
                    AND p.IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data['ProductIdx'] = $row['ProductIdx'];
                $this->data['ProductName'] = $row['ProductName'];
                $this->data['ParentProductIdx'] = $row['ParentProductIdx'];
                $this->data['Gender'] = $row['Gender'];
                if ($row['ProductCatalogIdx']) {
                    $this->data['ProductCatalogIdx'][] = $row['ProductCatalogIdx'];
                    $this->data['CatalogName'][] = $row['CatalogName'];
                    $this->data['RefCode'][] = $row['RefCode'];
                }
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상품 비활성화
    function disableProduct($param): array
    {
        $this->desc = "model::disableProduct";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '400');
            }

            $sql = "SELECT pgm.ProductGroupIdx FROM abc.ProductGroupManage AS pgm
                    JOIN abc.ProductGroup AS pg ON pgm.ProductGroupIdx = pg.ProductGroupIdx AND pg.IsUse IS TRUE
                    WHERE pgm.ProductIdx = :productIdx
                    ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());

            if($total > 0){
                throw new \Exception('상품그룹에 등록되어있어 비활성화가 불가능합니다.', '400');
            }

            //상품 비활성화
            $sql = "UPDATE abc.Product 
                    SET IsUse = b'0'
                    WHERE ProductIdx = :productIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //상품 등록 및 수정
    function createProduct($param): array
    {
        $this->desc = "model::createProduct";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['categoryIdx'], $param['productName'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (!preg_match($this->pattern['all'], $param['productName'])) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }

            $this->conn->beginTransaction();

            $productIdx = $param['productIdx'] ?: null;
            $productName = $param['productName'];
            $parentProductIdx = $param['categoryIdx'];
            $gender = $param['subdivision'] ?: null;
            $catalogList = $param['catalogList'] ? json_decode($param['catalogList'], true) : null;
            $delCatalogArr = $param['delCatalogArr'] ? json_decode($param['delCatalogArr'], true) : null;

            if ($productIdx) {
                //상품 수정
                $sql = "UPDATE abc.Product 
                        SET ProductName = :productName, 
                            ParentProductIdx = :parentProductIdx,";
                $sql .= ($gender) ? "Gender = :gender," : "";
                $sql .= "ModDatetime = NOW()
                         WHERE ProductIdx = :productIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productName', $productName);
                $stmt->bindValue(':parentProductIdx', $parentProductIdx, $this->conn::PARAM_INT);
                $stmt->bindValue(':productIdx', $productIdx, $this->conn::PARAM_INT);
                if ($gender) {
                    $stmt->bindValue(':gender', $gender, $this->conn::PARAM_INT);
                }
                $stmt->execute();

                if ($delCatalogArr) {
                    $delCatalogArr = implode(',', $delCatalogArr);
                    if ($delCatalogArr[-1] === ',') {
                        $delCatalogArr = substr($delCatalogArr, 0, -1);;
                    }
                    $sql = "DELETE FROM abc.ProductCatalog
                            WHERE ProductCatalogIdx IN ({$delCatalogArr})";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                }
                if ($catalogList) {
                    foreach ($catalogList as $item) {
                        if ($item['idx'] !== '') {
                            $sql = "UPDATE abc.ProductCatalog
                                    SET CatalogName = '{$item['name']}'
                                      , RefCode = '{$item['code']}'
                                    WHERE ProductCatalogIdx = {$item['idx']}";
                        } else {
                            $sql = "INSERT INTO abc.ProductCatalog (ProductIdx, CatalogName, RefCode) 
                                    VALUES ({$productIdx}, '{$item['name']}', '{$item['code']}')";
                        }
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                    }
                }
            } else {
                //상품 등록
                $sql = "INSERT INTO abc.Product (ProductName, ParentProductIdx";
                $sql .= ($gender) ? ", Gender" : "";
                $sql .= ") VALUES (:productName, :parentProductIdx";
                $sql .= ($gender) ? ", :gender" : "";
                $sql .= ")";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productName', $productName);
                $stmt->bindValue(':parentProductIdx', $parentProductIdx, $this->conn::PARAM_INT);
                if ($gender) {
                    $stmt->bindValue(':gender', $gender, $this->conn::PARAM_INT);
                }
                $stmt->execute();

                $productIdx = $this->conn->lastInsertId();
                $insertVal = '';
                if ($catalogList) {
                    foreach ($catalogList as $key => $val) {
                        if ($key > 0) {
                            $insertVal .= ",";
                        }
                        $insertVal .= "({$productIdx}, '{$val['name']}', '{$val['code']}')";
                    }
                    $sql = "INSERT INTO abc.ProductCatalog (ProductIdx, CatalogName, RefCode) 
                            VALUES {$insertVal}";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                }
            }

            $this->conn->commit();

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    // 그룹 전체 조회
    function justGroupList($param): array
    {
        $this->desc = 'justGroupList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            $sql = "    SELECT 
                            p.ProductIdx, p.ProductName
                            , p2.ProductIdx AS ChildProductIdx
                            , p2.ProductName AS ChildProductName
                            , p2.Gender AS ChildGender
                          FROM abc.Product AS p
                     LEFT JOIN abc.Product AS p2
                            ON p2.ParentProductIdx = p.ProductIdx
                           AND p2.ParentProductIdx IS NOT NULL
                         WHERE p.ParentProductIdx IS NULL
                           AND p.IsUse = b'1'
                           AND p2.IsUse = b'1'";
            $stmt = $this->conn->query($sql);
            while ($row = $stmt->fetch()) {
                $this->data[$row['ProductIdx']]['ProductIdx'] = $row['ProductIdx'];
                $this->data[$row['ProductIdx']]['ProductName'] = $row['ProductName'];
                $this->data[$row['ProductIdx']]['ChildProductIdx'][] = $row['ChildProductIdx'];
                $this->data[$row['ProductIdx']]['ChildProductName'][] = $row['ChildProductName'];
                $this->data[$row['ProductIdx']]['ChildGender'][] = $row['ChildGender'];
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 상품 카탈로그 조회
    function catalogList($param): array
    {
        $this->desc = 'productList';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['productIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            $sql = "SELECT ProductCatalogIdx, RefCode, CatalogName
                    FROM abc.ProductCatalog
                    WHERE ProductIdx = :productIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productIdx', $param['productIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data[] = $row;
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 리턴
    function response(): array
    {
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg, 'desc' => $this->desc];
    }

    //상품그룹 조회
    function productGroupList($param): array
    {
        $this->desc = "model::productGroupList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = '';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['pg.ProductGroupName'])) {
                    $addSql = " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql = " AND {$param['keyword']} = '{$param['value']}'";
                }
            }
            // 기본 쿼리문
            $sql = "SELECT 
                        pgm.ProductGroupIdx, p.ParentProductIdx, p.ProductName, pg.ProductGroupName, pg.RegDatetime, pgm.Sort
                    FROM abc.ProductGroupManage pgm
                    JOIN abc.ProductGroup pg ON pg.ProductGroupIdx = pgm.ProductGroupIdx
                    JOIN abc.Product p ON p.ProductIdx = pgm.ProductIdx
                    WHERE pg.BusinessManageIdx = 1 AND pg.IsUse = b'1'
                    {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $sql .= " ORDER BY pg.ProductGroupIdx DESC, pgm.Sort ASC";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($item = $stmt->fetch()) {
                $this->data['data'][$item['ProductGroupIdx']]['ProductGroupName'] = $item['ProductGroupName'];
                $this->data['data'][$item['ProductGroupIdx']]['ProductGroupIdx'] = $item['ProductGroupIdx'];
                $this->data['data'][$item['ProductGroupIdx']]['ParentProductIdx'] = $item['ParentProductIdx'];
                $this->data['data'][$item['ProductGroupIdx']]['ProductName'][] = $item['ProductName'];
                $this->data['data'][$item['ProductGroupIdx']]['RegDatetime'] = $item['RegDatetime'];
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 회원 조회
    function MembersList($param): array
    {
        $this->desc = "model::MembersList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !in_array($param['keyword'], ['o.RegDatetime', 'ccm.Category', 'ccm.ClientCustomerName', 'mm.UsersIdx', 'm.Name', 'm.Birth1', 'm.Gender', 'm.Phone', 'm.State', 'm.City']))
                || ($param['keyword'] !== 'm.Email' && $param['value'] && !preg_match($this->pattern['all'], $param['value']))
                || ($param['keyword'] === 'm.Email' && $param['value'] && !preg_match($this->pattern['email'], $param['value']))
                || ($param['column'] && !in_array($param['column'], ['o.RegDatetime', 'ccm.Category', 'ccm.ClientCustomerName', 'mm.UsersIdx', 'm.Name', 'Birth', 'Age', 'm.Gender', 'm.Phone', 'Address']))
                || ($param['sort'] && !in_array($param['sort'], ['asc', 'desc']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['o.RegDatetime', 'm.Name', 'm.Phone', 'm.Email', 'm.State', 'm.City', 'ccm.ClientCustomerName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    if ($param['keyword'] === 'ccm.Category') {
                        if ($param['value'] === '병원') {
                            $param['value'] = 'H';
                        } else if ($param['value'] === '약국') {
                            $param['value'] = 'P';
                        }
                    }
                    if ($param['keyword'] === 'm.Gender') {
                        if (in_array($param['value'], ['남', '남자', '남성', 'M'])) {
                            $param['value'] = 1;
                        } else if (in_array($param['value'], ['여', '여자', '여성', 'F'])) {
                            $param['value'] = 2;
                        }
                    }
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $orderSql = ' ORDER BY ';
            if ($param['column'] !== '' && $param['sort'] !== '') {
                switch ($param['column']) {
                    case 'Birth':
                    case 'Age':
                        $orderSql .= " m.Birth1 {$param['sort']}, m.Birth2 {$param['sort']} ";
                        break;
                    case 'Address':
                        $orderSql .= " m.State {$param['sort']}, m.City {$param['sort']}, m.FullCity {$param['sort']} ";
                        break;
                    default:
                        $orderSql .= " {$param['column']} {$param['sort']} ";
                }
            } else {
                $orderSql .= ' o.RegDatetime DESC ';
            }

            // 대상 전체 카운트
            $sql = " SELECT m.Name, m.Phone, m.Birth1, m.Birth2, m.Gender, m.Email, m.State, m.City,
                            m.FullCity,m.MembersIdx,
                            o.PaysIdx, o.ProductGroupIdx, o.RegDatetime,
                            tm.MembersIdx AS TestMembers,
                            mm.UsersIdx,
                            ccm.ClientControlIdx, ccm.ClientCustomerName, ccm.Category
                       FROM o.Pays AS o
                       JOIN abc.Users AS mm
                         ON mm.UsersIdx = o.UsersIdx
                       JOIN abc.Members AS m
                         ON m.MembersIdx = mm.MembersIdx
                       JOIN abc.ClientControl AS ccm
                         ON ccm.ClientControlIdx = mm.ClientControlIdx
                  LEFT JOIN abc.TestMembers AS tm
                         ON tm.MembersIdx = m.MembersIdx 
                      WHERE mm.IsOut = b'0' #탈퇴회원 제외
                        AND o.ProductGroupIdx = :productGroupIdx #그룹식별자 특정
                        AND o.IsActive = b'1' #활성회원 선별
                        {$addSql}
                        {$orderSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll($this->conn::FETCH_ASSOC);
            $total = count($rows);
            $this->setPagination($total, $param);

            $items = array_slice($rows, $this->data['pagination']['start'], $param['entry']);
            $data = [];
            $orderIdxList = [];
            if (count($items) > 0) {
                foreach ($items as $item) {
                    $orderIdxList[] = $item['OrderIdx'];

                    $data["idx{$item['OrderIdx']}"] = [
                        'MembersIdx' => $item['MembersIdx'],
                        'UsersIdx' => $item['UsersIdx'],
                        'OrderIdx' => $item['OrderIdx'],
                        'Name' => $item['Name'],
                        'Phone' => $item['Phone'],
                        'Birth1' => $item['Birth1'],
                        'Birth2' => $item['Birth2'],
                        'Gender' => $item['Gender'] == '1' ? '남' : '여',
                        'Email' => $item['Email'],
                        'State' => $item['State'],
                        'City' => $item['City'],
                        'FullCity' => $item['FullCity'],
                        'ProductGroupIdx' => $item['ProductGroupIdx'],
                        'TestMembers' => $item['TestMembers'],
                        'RegDatetime' => substr($item['RegDatetime'], 0, 10) ?? '',
                        'ClientControlIdx' => $item['ClientControlIdx'],
                        'ClientCustomerName' => $item['ClientCustomerName'],
                        'Category' => $item['Category'],
                    ];
                }

                $sql = "SELECT ProductIdx
                        FROM abc.ProductGroupManage
                        WHERE ProductGroupIdx = :productGroupIdx
                        ORDER BY Sort ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $sort = "";
                while ($row = $stmt->fetch()) {
                    if ($sort !== "") {
                        $sort .= ",";
                    }
                    $sort .= $row['ProductIdx'];
                }
                $orderIdxStr = implode(',', $orderIdxList);

                $sql = "SELECT uls.PaysIdx, uls.ProductIdx, uls.Process, uls.StatusCode,
                               p.ParentProductIdx, p.ProductName
                          FROM abc.MemberStatus uls
                          JOIN abc.Product p
                            ON p.ProductIdx = uls.ProductIdx
                         WHERE uls.PaysIdx IN ({$orderIdxStr})
                      ORDER BY FIELD(uls.ProductIdx, {$sort}) ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                while ($item = $stmt->fetch()) {
                    $data["idx{$item['OrderIdx']}"]['ProductIdx'] = $item['ProductIdx'];
                    $data["idx{$item['OrderIdx']}"]['Process'] = $item['Process'];
                    $data["idx{$item['OrderIdx']}"]['StatusCode'] = $item['StatusCode'];
                    $data["idx{$item['OrderIdx']}"]['ParentProductIdx'] = $item['ParentProductIdx'];
                    $data["idx{$item['OrderIdx']}"]['ProductName'] = $item['ProductName'];
                    $data["idx{$item['OrderIdx']}"]['IsSuccess'] = $item['Process'] === 'E' && $item['StatusCode'] === '20000' ? 'success' : 'fail';
                    $data["idx{$item['OrderIdx']}"]['LatestStatus'] = $this->defineStatusCode[$item['ParentProductIdx']][$item['ProductIdx']][$item['Process']][$item['StatusCode']] ?? '';
                }
            }

            $this->data['data'] = $data;

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 회원 정보 수정
    function updateMembers($param): array
    {
        $this->desc = 'model::updateMembers';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['orderIdx'], $param['MembersIdx'], $param['state'], $param['city'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }
            if (
                !preg_match($this->pattern['kor'], $param['state'])
                || !preg_match($this->pattern['kor'], $param['city'])
                || (isset($param['email']) && !preg_match($this->pattern['email'], $param['email']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', '400');
            }

            $sql = "SELECT MM.UsersIdx, M.MembersIdx, M.Email, M.State, M.City, M.FullCity, AM.AGRE_DATE
                    FROM o.Pays O
                    JOIN abc.Users MM ON MM.UsersIdx = O.UsersIdx
                    JOIN abc.Members M ON M.MembersIdx = MM.MembersIdx
                    JOIN abc.AgreementManage AM ON (AM.UsersIdx, AM.PaysIdx) = (MM.UsersIdx, O.PaysIdx) 
                    AND AM.ProductIdx IN (5,6)
                    WHERE O.PaysIdx = :OrderIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':OrderIdx', $param['orderIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $MembersData = $stmt->fetch($this->conn::FETCH_ASSOC);

            $table = "***.Members";
            $idx = ['MembersIdx' => (int)$param['MembersIdx']];
            $item = [
                'state' => $param['state'],
                'city' => $param['city'],
            ];
            if (isset($param['email'])) {
                $item['email'] = $param['email'];
            }
            $this->insertUpdate($idx, $table, $item);

            if (isset($param['email']) && $MembersData['Email'] != $item['email'] && !isDev) {

                $method = "POST";
                $url = $this->apiLabgeUrl;
                $header = [
                    "Authorization: Bearer {$this->apiLabgeKey}",
                    "Content-Type: application/json"
                ];
                $body = [
                    'CompOrderNo' => (int)$MembersData['UsersIdx'],
                    'CompOrderDate' => $MembersData['AGRE_DATE'],
                    'EmailAddress' => $item['email'],
                ];
                $result = $this->curl($method, $url, $header, json_encode($body, true));
                $response = json_decode($result['response'], true);
                if ($result['code'] !== 200 || $response['status'] !== "200") {
                    throw new \Exception("failure: send updated info to lab****mics " . json_encode($response), "450");
                }
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 상품 리스트 조회
    function productList($param): array
    {
        $this->desc = "productList";
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }

            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['pp.ProductName', 'p.ProductName'])) {
                    $addSql = " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else {
                    $addSql = " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            // 카탈로그 개수 조회
            $sql = "SELECT ProductIdx, COUNT(ProductCatalogIdx) AS Cnt
                      FROM abc.ProductCatalog
                  GROUP BY ProductIdx";
            $stmt = $this->conn->query($sql);
            while ($row = $stmt->fetch()) {
                $rows[$row['ProductIdx']] = $row['Cnt'];
            }

            // 기본 쿼리문
            $sql = "SELECT 
                        p.RegDatetime, p.ProductIdx, p.ProductName, p.Gender,
                        pp.ProductName AS CategoryName
                    FROM abc.Product p 
                    JOIN abc.Product pp ON pp.ProductIdx = p.ParentProductIdx
                   WHERE p.IsUse = b'1'
                    {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);

            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $this->data['data'][] = [
                    'RegDatetime' => substr($row['RegDatetime'], 0, 10),
                    'ProductIdx' => $row['ProductIdx'],
                    'ProductName' => $row['ProductName'],
                    'Gender' => $row['Gender'],
                    'CategoryName' => $row['CategoryName'],
                    'CatalogNum' => $rows[$row['ProductIdx']] ?? '-',
                ];
            }

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //**거래처 리스트 조회
    function insuranceList($param): array
    {
        $this->desc = 'model::insuranceList';

        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['keyword'], $param['value'], $param['entry'], $param['page'])) {
                throw new \Exception('필수 파라미터가 없습니다.', "404");
            }
            if (
                ($param['keyword'] && !preg_match($this->pattern['all'], $param['keyword']))
                || ($param['value'] && !preg_match($this->pattern['all'], $param['value']))
            ) {
                throw new \Exception('필수 파라미터가 올바르지 않습니다.', "400");
            }
            $addSql = ' ';
            if ($param['keyword'] !== '' && $param['value'] !== '') {
                if (in_array($param['keyword'], ['ASMH.RegDateTime', 'SCM.ServiceCompanyName'])) {
                    $addSql .= " AND {$param['keyword']} LIKE '%{$param['value']}%'";
                } else if ($param['keyword'] === 'ASMH.TransferMethodCode') {
                    if ($param['value'] === '수동') {
                        $addSql .= " AND {$param['keyword']} = 2";
                    } else if ($param['value'] === 'API') {
                        $addSql .= " AND {$param['keyword']} = 1";
                    }
                } else {
                    $addSql .= " AND {$param['keyword']} = '{$param['value']}'";
                }
            }

            $data = [];
            $sql = "SELECT 
                        ASMH.ServeAllocationHistoryIdx, ASMH.RegDatetime, SCM.ServiceControlIdx, SCM.ServiceCompanyName, 
                        ASMH.TransferMethodCode, ASMH.AllocationServeType, ASMH.IsManual, ASMH.TotalServeLimit, ASMH.WeekServeLimit 
                    FROM abc.ServeAllocationHistory ASMH
                    JOIN abc.ServiceControl SCM ON SCM.ServiceControlIdx = ASMH.ServiceControlIdx 
                    AND SCM.IsContract = b'1'
                    WHERE ASMH.ProductGroupIdx = :productGroupIdx
                    {$addSql}";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $total = count($stmt->fetchAll());
            $this->setPagination($total, $param);
            //main query
            $sql .= " ORDER BY ASMH.ServeAllocationHistoryIdx DESC";
            $sql .= " LIMIT :start, :entry";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $param['gIdx'], $this->conn::PARAM_INT);
            $stmt->bindValue(':start', $this->data['pagination']['start'], $this->conn::PARAM_INT);
            $stmt->bindValue(':entry', $param['entry'], $this->conn::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $data[] = [
                    'ServeAllocationHistoryIdx' => $row['ServeAllocationHistoryIdx'],
                    'RegDatetime' => $row['RegDatetime'] ? substr($row['RegDatetime'], 0, 10) : '',
                    'ServiceControlIdx' => $row['ServiceControlIdx'],
                    'ServiceCompanyName' => $row['ServiceCompanyName'],
                    'TransferMethodCode' => $row['TransferMethodCode'],
                    'AllocationServeType' => $row['AllocationServeType'],
                    'IsManual' => $row['IsManual'],
                    'TotalServeLimit' => $row['TotalServeLimit'],
                    'WeekServeLimit' => $row['WeekServeLimit']
                ];
            }
            $this->data['data'] = $data;

            // 거래처 셀렉트박스용
            $sql = "SELECT ServiceControlIdx AS `value`, ServiceCompanyName AS `text`
                    FROM abc.ServiceControl
                    WHERE IsContract = 1";
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetchAll() ?? [];
            $this->data['select::ServiceControl'] = $row;

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    //**거래처 등록 및 수정
    function insuranceUpdate($param): array
    {
        $this->desc = 'model::registInsurance';
        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (
                !isset(
                    $param['ServiceControl'],
                    $param['transferMethod'],
                    $param['isPilot'],
                    $param['isManual'],
                    $param['totalServeLimit'],
                    $param['weekServeLimit'],
                    $param['gIdx'])
            ) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }


            $table = "***.ServeAllocationHistory";
            $idx = isset($param['ServeAllocationHistoryIdx']) ? ['ServeAllocationHistoryIdx' => (int)$param['ServeAllocationHistoryIdx']] : [];
            $ServiceControlIdx = (int)$param['ServiceControl'];
            $transferMethod = (int)$param['transferMethod'];
            $allocationServeType = $param['isPilot'];
            $isManual = (int)$param['isManual'];
            $totalServeLimit = (int)$param['totalServeLimit'];
            $weekServeLimit = (int)$param['weekServeLimit'];
            $productGroupIdx = (int)$param['gIdx'];

            $item = [
                'transferMethodCode' => $transferMethod,
                'allocationServeType' => ($allocationServeType) ? 'pilot' : null,
                'isManual' => $isManual,
                'totalServeLimit' => $totalServeLimit,
                'weekServeLimit' => $weekServeLimit
            ];

            $this->conn->beginTransaction();

            //업데이트 조건문 추가 - 기존 백오피스 로직
            if (isset($param['ServeAllocationHistoryIdx'])) {
                $sql = "SELECT ServiceControlIdx, TransferMethodCode, AllocationServeType, IsManual, TotalServeLimit, WeekServeLimit
                        FROM {$table}
                        WHERE ServeAllocationHistoryIdx = :allocationServeIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':allocationServeIdx', $param['ServeAllocationHistoryIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $allocationServeData = $stmt->fetch($this->conn::FETCH_ASSOC) ?? [];

                if ($allocationServeData['TransferMethodCode'] == $item['transferMethodCode']) {
                    unset($item['transferMethodCode']);
                }
                if ($allocationServeData['AllocationServeType'] == $item['allocationServeType']) {
                    unset($item['allocationServeType']);
                }
                if ($allocationServeData['IsManual'] == $item['isManual']) {
                    unset($item['isManual']);
                }
                if ($allocationServeData['TotalServeLimit'] == $item['totalServeLimit']) {
                    unset($item['totalServeLimit']);
                }
                if ($allocationServeData['WeekServeLimit'] == $item['weekServeLimit']) {
                    unset($item['weekServeLimit']);
                }
                if (count($item) === 0) {
                    throw new \Exception("변경사항이 없습니다.", "453");
                }

                $transferMethod = (int)$allocationServeData['TransferMethodCode'] ?? 0;
            } else {
                $sql = "SELECT TransferMethodCode FROM abc.ServiceControl
                        WHERE ServiceControlIdx = :serviceCompanyIdx AND IsContract = b'1'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':serviceCompanyIdx', $ServiceControlIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch($this->conn::FETCH_ASSOC);
                $transferMethod = (int)$row['TransferMethodCode'] ?? 0;
                if (!$transferMethod) {
                    throw new \Exception("IB거래처 조회 오류", "451");
                }
            }
            if (isset($item['transferMethodCode'])) {
                if ($transferMethod != $item['transferMethodCode']) {
                    $sql = "UPDATE abc.ServiceControl
                            SET TransferMethodCode = :transferMethod
                            WHERE ServiceControlIdx = :serviceCompanyIdx";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':transferMethod', $item['transferMethodCode'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':serviceCompanyIdx', $ServiceControlIdx, $this->conn::PARAM_INT);
                    $stmt->execute();
                }
            }

            $item['ServiceControlIdx'] = $ServiceControlIdx;
            $item['productGroupIdx'] = $productGroupIdx;
            if (!$allocationServeType) {
                unset($item['allocationServeType']);
            }

            $this->insertUpdate($idx, $table, $item);

            $this->conn->commit();

            $this->conn = null;
            return $this->response();
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn = null;
            throw $e;
        }
    }

    //** 거래처 제공량 조회
    function searchInsurance($param): array
    {
        $this->desc = 'model::searchInsurance';

        try {
            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            if (!isset($param['ServeAllocationHistoryIdx'])) {
                throw new \Exception('필수 파라미터가 없습니다.', '404');
            }

            $sql = "SELECT 
                        ASMH.ServeAllocationHistoryIdx, ASMH.RegDatetime, SCM.ServiceControlIdx, SCM.ServiceCompanyName, 
                        ASMH.TransferMethodCode, ASMH.AllocationServeType, ASMH.IsManual, ASMH.TotalServeLimit, ASMH.WeekServeLimit 
                    FROM abc.ServeAllocationHistory ASMH
                    JOIN abc.ServiceControl SCM ON SCM.ServiceControlIdx = ASMH.ServiceControlIdx 
                    AND SCM.IsContract = b'1'
                    WHERE ASMH.ServeAllocationHistoryIdx = :ServeAllocationHistoryIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ServeAllocationHistoryIdx', $param['ServeAllocationHistoryIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch() ?? [];
            if ($row) {
                $this->data = [
                    'ServeAllocationHistoryIdx' => $row['ServeAllocationHistoryIdx'],
                    'RegDatetime' => $row['RegDatetime'],
                    'ServiceControlIdx' => $row['ServiceControlIdx'],
                    'ServiceCompanyName' => $row['ServiceCompanyName'],
                    'TransferMethodCode' => $row['TransferMethodCode'],
                    'AllocationServeType' => $row['AllocationServeType'],
                    'IsManual' => $row['IsManual'],
                    'TotalServeLimit' => $row['TotalServeLimit'],
                    'WeekServeLimit' => $row['WeekServeLimit']
                ];
            }

            $this->conn = null;
            return $this->response();

        } catch (\Exception $e) {
            $this->conn = null;
            throw $e;
        }
    }

    // 페이징
    function setPagination($total, $param): void
    {
        try {
            if (!isset($param['entry'], $param['page'])) {
                throw new \Exception('페이징 필수 파라미터가 없습니다.', '404');
            }

            /* paging : 한 페이지 당 데이터 개수 */
            $list_num = $param['entry'];

            /* paging : 블록 노출 개수*/
            $page_num = 5;

            /* paging : 현재 페이지 */
            $page = $param['page'] ?: 1;

            /*page가 1보다 작을 경우 1로 고정*/
            if ($page < 1) {
                $page = 1;
            }

            /* paging : 전체 페이지 수 = 전체 데이터 / 페이지당 데이터 개수, ceil : 올림값, floor : 내림값, round : 반올림 */
            $total_page = ceil($total / $list_num);
            // echo "전체 페이지 수 : ".$total_page;

            /* paging : 전체 블럭 수 = 전체 페이지 수 / 블럭 당 페이지 수 */
            $totalBlock = ceil($total_page / $page_num);

            /* paging : 현재 블럭 번호 = 현재 페이지 번호 / 블럭 당 페이지 수 */
            $now_block = ceil($page / $page_num);

            /* paging : 블럭 당 시작 페이지 번호 = (해당 글의 블럭번호 - 1) * 블럭당 페이지 수 + 1 */
            $sPage = ($now_block - 1) * $page_num + 1;
            // 데이터가 0개인 경우
            if ($sPage <= 0) {
                $sPage = 1;
            };

            /* paging : 블럭 당 마지막 페이지 번호 = 현재 블럭 번호 * 블럭 당 페이지 수 */
            $ePage = $now_block * $page_num;
            // 마지막 번호가 전체 페이지 수를 넘지 않도록
            if ($ePage > $total_page) {
                $ePage = $total_page;
            };

            $start = ($param['page'] - 1) * $param['entry'];

            $response = [
                'sPage' => $sPage,
                'ePage' => $ePage,
                'totalPage' => $total_page,
                'currentBlock' => $now_block,
                'totalBlock' => $totalBlock,
                'start' => $start,
                'totalCnt' => $total,
            ];

            $this->data['pagination'] = $response;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 오류기록
    function errorLog($msg, $code, $data): void
    {
        if (!$this->conn) {
            $this->conn = (new \PDOFactory)->PDOCreate();
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
        $stmt->bindValue(':Referer', $_SERVER['HTTP_REFERER'] ?? '');
        $stmt->bindValue(':IpAddress', $ipaddress);
        $stmt->execute();

        $this->conn = null;
    }

}