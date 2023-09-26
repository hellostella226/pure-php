<?php

namespace Model;

class Pay extends Base
{
    public ?object $conn = null;

    public string $kcpPaymentUrl = "https://abc/gw/enc/v1/payment";
    public string $kcpRefundUrl = "https://abc/gw/mod/v1/cancel";
    public string $kcpTradeUrl = "https://abc/std/tradeReg/register";
    public string $kcpCertInfo = "";
    public string $kcpSignPw = "***##";

    public function __construct()
    {
        parent::__construct();
        if (isDev) {
            $this->kcpPaymentUrl = "https://stg-abc/gw/enc/v1/payment";
            $this->kcpRefundUrl = "https://stg-abc/gw/mod/v1/cancel";
            $this->kcpTradeUrl = "https://stg-abc/std/tradeReg/register";
            $this->kcpSignPw = "***";
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/pay/***tifiabc/***.key")) {
            $this->kcpCertInfo = preg_replace('/\r\n|\r|\n/', '', file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/pay/abc/***.pem"));
        }
        $this->conn = (new PDOFactory)->PDOCreate();
    }

    public function getBizMParam($param): array
    {
        try {
            $bizMParam = [
                'buyerName' => '',
                'buyerPhone' => '',
                'orderDate' => date('Y-m-d'),
                'orderQuantity' => 0,
                'orderAmt' => 0,
                'offer***Url' => 'https://d.***.com/abc/?hCode=',
            ];

            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }
            if (!isset($param['SaleGoodsIdx'])) {
                throw new \Exception("필수 파라미터가 없습니다.", "404");
            }
            if (!$param['SaleGoodsIdx']) {
                throw new \Exception("필수 파라미터가 옳바르지 않습니다", "400");
            }

            $sql = "SELECT 
                        ccm.ClientCustomerCode, ccm.ClientCustomerName, ccm.CCTel
                      FROM abc.SaleGoods tm
                      JOIN abc.ClientControl ccm
                        ON ccm.ClientControlIdx = tm.ClientControlIdx
                     WHERE tm.SaleGoodsIdx = :SaleGoodsIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':SaleGoodsIdx', $param['SaleGoodsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $clientCustomerInfo = $stmt->fetch() ?? [];
            if (!$clientCustomerInfo) {
                throw new \Exception("상담사 조회 실패", "404");
            }
            $bizMParam['buyerName'] .= $clientCustomerInfo['ClientCustomerName'];
            $bizMParam['buyerPhone'] .= $clientCustomerInfo['CCTel'];
            $offer***Url = $bizMParam['offer***Url'] . $clientCustomerInfo['ClientCustomerCode'];
            $shortUrlResult = $this->createShortUrl($offer***Url);
            $bizMParam['offer***Url'] = $shortUrlResult['result']['url'];

            $sql = "SELECT IssuedSaleGoodsIdx FROM abc.IssuedSaleGoods WHERE SaleGoodsIdx = :SaleGoodsIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':SaleGoodsIdx', $param['SaleGoodsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $bizMParam['orderQuantity'] = $stmt->rowCount();

            if (isset($param['tno'])) {
                $sql = "SELECT 
                            OrderQuantity, ApprovedOrderAmount, ApprovedDatetime
                          FROM p.PayssItem
                         WHERE KcpTno = :kcpTno
                           AND SaleGoodsIdx = :SaleGoodsIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':kcpTno', $param['tno']);
                $stmt->bindValue(':SaleGoodsIdx', $param['SaleGoodsIdx'], $this->conn::PARAM_INT);
                $stmt->execute();
                $paymentInfo = $stmt->fetch() ?? [];
                if (!$paymentInfo) {
                    throw new \Exception("주문 내역 조회 실패", "404");
                }
                $bizMParam['orderQuantity'] = $paymentInfo['OrderQuantity'];
                $bizMParam['orderAmt'] = $paymentInfo['ApprovedOrderAmount'];
                $bizMParam['orderDate'] = substr($paymentInfo['ApprovedDatetime'], 0, 10);
            } else {
                $bizMParam['orderAmt'] = 0;
            }

            return $bizMParam;
        } catch (\Exception $e) {
            $bizMParam = [];
            parent::errorLog($e->getMessage(), $e->getCode(), $param);
        } finally {
            return $bizMParam;
        }
    }

    public function registerPayOrder($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            $this->conn->beginTransaction();

            // 상품 가격 및 총 주문 가격 확인
            $sql = "SELECT 
                        ServiceControlIdx, ProductGroupIdx, SalesPrice 
                     FROM abc.Items 
                    WHERE ItemsIdx = :ItemsIdx 
                      AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            $ServiceControlIdx = $row['ServiceControlIdx'];
            $productGroupIdx = $row['ProductGroupIdx'];

            $price = $row['SalesPrice'] ?? 0;
            $couponCode = '';
            if (!$param['couponIdx']) {
                if (
                    $price != $param['itemPrice']
                    || $param['orderAmount'] != ($price * $param['orderQuantity'])
                ) {
                    throw new \Exception("상품가격이 올바르지 않습니다.", "400");
                }
            } else {

                if ($row['SalesPrice'] !== $param['itemPrice']) {
                    throw new \Exception("상품가격이 올바르지 않습니다.", "400");
                }

                $sql = "SELECT 
                            icm.CouponIdx, cm.DiscountMethod, cm.TicketsIdx, cm.CouponCode, cm.CouponType, 
                            cm.DiscountAmount, cm.DiscountRate, cm.ServiceControlIdx, cm.ClientControlIdx, 
                            cm.CouponStatus, icm.IssuedDatetime
                          FROM abc.IssuedTickets AS icm
                          JOIN abc.Tickets AS cm 
                            ON cm.TicketsIdx = icm.TicketsIdx
                         WHERE cm.TicketsIdx = :TicketsIdx 
                           AND icm.CouponIdx = :couponIdx 
                           AND cm.IsUse = b'1' 
                           AND cm.CouponStatus = 1 
                           AND cm.UseStartDate <= :nowDate 
                           AND cm.UseEndDate >= :nowDate 
                           AND cm.ProductGroupIdx = :productGroupIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':TicketsIdx', $param['TicketsIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':couponIdx', $param['couponIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':nowDate', date('Y-m-d'));
                $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $couponData = $stmt->fetch();
                $couponCode = $couponData['CouponCode'];
                $totalAmount = 0;
                if ($couponData['DiscountMethod'] === '1') {
                    $discountAmount = $row['SalesPrice'] * ((100 - $couponData['DiscountRate']) / 100);
                    $totalAmount = $discountAmount * $param['orderQuantity'];
                } else if ($couponData['DiscountMethod'] === '2') {
                    $discountAmount = ($row['SalesPrice'] * $param['orderQuantity']) - $couponData['DiscountAmount'];
                    $totalAmount = $discountAmount;
                }

                if ($param['orderAmount'] != $totalAmount) {
                    throw new \Exception("상품가격이 올바르지 않습니다.", "400");
                }
            }

            $payOrderIdx = 0;

            $payType = 'pay';
            if ($param['orderAmount'] > 0) {
                //유료결제 처리부
                switch ($param['payMethod']) {
                    case "100000000000" :
                        $paymethod = "CARD";
                        break;
                    case "010000000000" :
                        $paymethod = "BANK";
                        break;
                    default :
                        $paymethod = $param['payMethod'];
                        break;
                }

                $unique = [
                    'payOrderCode' => $param['payOrderCode'],
                ];
                $table = "p.PayssItem";
                $item = [
                    'payOrderCode' => $param['payOrderCode'],
                    'pGCompanyName' => $param['pGCompany'],
                    'siteCode' => $param['siteCode'],
                    'ItemsIdx' => $param['ItemsIdx'],
                    'goodsName' => $param['itemName'],
                    'orderType' => 1, // 1: 결제
                    'salesPrice' => $param['itemPrice'],
                    'companyName' => $param['companyName'],
                    'buyerName' => $param['buyerName'],
                    'buyerPhone' => $param['buyerPhone'],
                    'payMethod' => $paymethod,
                    'orderQuantity' => $param['orderQuantity'],
                    'orderAmount' => $param['orderAmount'],
                    'orderStatus' => 0
                ];

                if ($couponCode) {
                    $item['couponCode'] = $couponCode;
                    $item['totalDiscountAmount'] = $param['orderAmount'];
                }

                if (!isset($param['ClientControlIdx'])) {
                    $param['ClientControlIdx'] = "";
                }
                $clientCustomerData = [];
                if ($param['ClientControlIdx']) {
                    $sql = "SELECT 
                                ClientControlIdx, ClientCustomerName, CCManager, CCTel
                              FROM abc.ClientControl
                             WHERE ClientControlIdx = :ClientControlIdx
                               AND IsUse = b'1'";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':ClientControlIdx', $param['ClientControlIdx'], $this->conn::PARAM_INT);
                    $stmt->execute();
                    $clientCustomerData = $stmt->fetch();

                }

                if ($clientCustomerData) {
                    //재결제라면 관련 결제 식별자 및 ClientCustomer insert시 추가
                    $item['ClientControlIdx'] = $clientCustomerData['ClientControlIdx'];

                    $sql = "SELECT 
                                PayOrderIdx
                              FROM p.PayssItem
                             WHERE ClientControlIdx = :ClientControlIdx
                               AND ItemsIdx = :ItemsIdx
                               AND RelatedPayOrderIdx IS NULL 
                               AND OrderStatus != 0";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':ClientControlIdx', $clientCustomerData['ClientControlIdx'], $this->conn::PARAM_INT);
                    $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
                    $stmt->execute();
                    $paymentData = $stmt->fetch();
                    if ($paymentData) {
                        $item['relatedPayOrderIdx'] = $paymentData['PayOrderIdx'];
                    }
                }

                $payOrderIdx = parent::insertDuplicate($unique, $table, $item, "");
                if (!$payOrderIdx) {
                    throw new \Exception("주문정보 저장에 실패하였습니다.", "400");
                }

            } else {
                $payType = 'free';
                $sql = "SELECT ClientControlIdx
                         FROM abc.ClientControl
                        WHERE ProductGroupIdx = :productGroupIdx 
                          AND ServiceControlIdx = :ServiceControlIdx
                          AND Depth = 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->conn::PARAM_INT);
                $stmt->bindValue(':ServiceControlIdx', $ServiceControlIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $parentClientCustomerIdx = $stmt->fetch()['ClientControlIdx'] ?? 0;
                if (!$parentClientCustomerIdx) {
                    throw new \Exception("거래처 정보가 존재하지않습니다.", "400");
                }

                $table = "***.ClientControl";
                $idx = [];

                $item = [
                    'clientCustomerName' => $param['buyerName'],
                    'cCManager' => $param['buyerName'],
                    'cCTel' => $param['buyerPhone'],
                    'cCGroup' => $param['companyName'],
                    'category' => 'I',
                    'productGroupIdx' => $productGroupIdx,
                    'ServiceControlIdx' => $ServiceControlIdx,
                    'parentClientCustomerIdx' => $parentClientCustomerIdx,
                    'depth' => 2
                ];
                //결제시 ClientData가 존재하는 경우
                if (!$param['ClientControlIdx']) {
                    $param['ClientControlIdx'] = "";
                    $item['clientCustomerCode'] = $this->generateClientCode($productGroupIdx);
                } else {
                    unset($item['parentClientCustomerIdx']);
                    $idx['ClientControlIdx'] = $param['ClientControlIdx'];
                    $item['modDatetime'] = date('Y-m-d H:i:s');
                }

                $insertClientManageIdx = parent::insertUpdate($idx, $table, $item);
                if ($insertClientManageIdx) {
                    $ClientControlIdx = $insertClientManageIdx;
                } else {
                    $ClientControlIdx = $idx['ClientControlIdx'];
                }

                $sql = "SELECT RegistrationPath 
                          FROM abc.DetailCustomer 
                         WHERE ClientControlIdx = :ClientControlIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ClientControlIdx', $ClientControlIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $registrationPath = $stmt->fetch()['RegistrationPath'] ?? 0;
                if (!$registrationPath) {
                    $clientDetailData = [
                        'ClientControlIdx' => $ClientControlIdx,
                        'registrationPath' => 2
                    ];
                    parent::insertUpdate([], "***.DetailCustomer", $clientDetailData);
                }

                if ($couponData) {
                    $expiredCouponData = [
                        'couponIdx' => $couponData['CouponIdx'],
                        'TicketsIdx' => $couponData['TicketsIdx'],
                        'couponCode' => $couponData['CouponCode'],
                        'ClientControlIdx' => $ClientControlIdx,
                        'issuedDatetime' => $couponData['IssuedDatetime'],
                        'expiredType' => 1,
                        'expiredDatetime' => date('Y-m-d H:i:s')
                    ];
                    if ($couponData['CouponType'] === '1') {
                        $sql = "DELETE FROM abc.IssuedTickets WHERE CouponIdx = :couponIdx";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':couponIdx', $couponData['CouponIdx'], $this->conn::PARAM_INT);
                        $stmt->execute();
                    }
                    parent::insertUpdate([], "***.ExpiredTickets", $expiredCouponData);
                }

                $table = "***.SaleGoods";
                $item = [
                    'ClientControlIdx' => $ClientControlIdx,
                    'ticketType' => 1
                ];

                if ($couponData) {
                    $item['couponCode'] = $couponData['CouponCode'];
                }
                $SaleGoodsIdx = parent::insertUpdate([], $table, $item);

                $table = "***.IssuedSaleGoods";
                $item = [
                    'SaleGoodsIdx' => $SaleGoodsIdx,
                    'ClientControlIdx' => $ClientControlIdx
                ];

                for ($i = 0; $i < $param['orderQuantity']; $i++) {
                    parent::insertUpdate([], $table, $item);
                }
            }

            $this->conn->commit();
            if ($payType !== 'free') {
                $this->data['payOrderIdx'] = $payOrderIdx;
            }
            $this->data['payType'] = $payType;
            $this->data['SaleGoodsIdx'] = $SaleGoodsIdx ?? '';
            $this->data['productGroupIdx'] = $productGroupIdx ?? '';
            $this->data['ServiceControlIdx'] = $ServiceControlIdx ?? '';

            $this->code = "200";
            $this->msg = "success";
            $this->desc = "registerPayOrder";

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

    public function tryKcpAuth($param): array
    {
        $bSucc = "false";
        $response = [];
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            // 총 결제금액 체크
            $sql = "SELECT 
                        PayOrderIdx, OrderAmount, OrderQuantity, ClientControlIdx, CouponCode, CompanyName
                      FROM p.PayssItem
                     WHERE PayOrderCode = :payOrderCode
                       AND OrderAmount = :orderAmount 
                       AND OrderStatus = 0 
                  ORDER BY PayOrderIdx DESC 
                     LIMIT 1";

            if (!isset($param['req_tx'])) {
                $payOrderCode = explode("|", $param['ordr_chk'])[0];
                $orderAmount = explode("|", $param['ordr_chk'])[1];
            } else {
                $payOrderCode = $param['ordr_idxx'];
                $orderAmount = $param['good_mny'];
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':payOrderCode', $payOrderCode);
            $stmt->bindValue(':orderAmount', $orderAmount, $this->conn::PARAM_INT);
            $stmt->execute();
            $paymemtManage = $stmt->fetch();

            if (!$paymemtManage) {
                throw new \Exception("결제정보가 일치하지 않거나 결제가 이미 완료되었습니다.", "400");
            }

            $goodManageIdx = $param['ItemsIdx'] ?? $param['gCode'];

            $orderAmount = $paymemtManage['OrderAmount'] ?? 0;
            $orderQuantity = $paymemtManage['OrderQuantity'] ?? 0;
            $payOrderIdx = $paymemtManage['PayOrderIdx'] ?? 0;
            $ClientControlIdx = $paymemtManage['ClientControlIdx'] ?? 0;
            $companyName = $paymemtManage['CompanyName'];

            $sql = "SELECT ServiceControlIdx,ProductGroupIdx 
                      FROM abc.Items 
                     WHERE ItemsIdx = :ItemsIdx 
                       AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $goodManageIdx, $this->conn::PARAM_INT);
            $stmt->execute();
            $goodManage = $stmt->fetch();

            $ServiceControlIdx = $goodManage['ServiceControlIdx'];
            $productGroupIdx = $goodManage['ProductGroupIdx'];

            $Tickets = [];

            if ($paymemtManage['CouponCode']) {
                $sql = "SELECT 
                            cm.CouponType, icm.CouponIdx, icm.TicketsIdx, icm.CouponCode, 
                            icm.ClientControlIdx, icm.IssuedDatetime
                        FROM abc.IssuedTickets AS icm
                        JOIN abc.Tickets AS cm 
                          ON cm.TicketsIdx = icm.TicketsIdx
                       WHERE icm.CouponCode = :couponCode 
                         AND cm.IsUse = b'1'
                         AND cm.UseStartDate <= :nowDate 
                         AND cm.UseEndDate >= :nowDate 
                         AND cm.ProductGroupIdx = :productGroupIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':couponCode', $paymemtManage['CouponCode']);
                $stmt->bindValue(':nowDate', date('Y-m-d'));
                $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->conn::PARAM_INT);
                $stmt->execute();
                $Tickets = $stmt->fetch();
                if (!$Tickets) {
                    throw new \Exception("이미 사용되거나 만료된 쿠폰입니다.", "400");
                }
            }

            $sql = "SELECT ClientControlIdx
                     FROM abc.ClientControl
                    WHERE ProductGroupIdx = :productGroupIdx 
                      AND ServiceControlIdx = :ServiceControlIdx
                      AND Depth = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->conn::PARAM_INT);
            $stmt->bindValue(':ServiceControlIdx', $ServiceControlIdx, $this->conn::PARAM_INT);
            $stmt->execute();
            $parentClientCustomerIdx = $stmt->fetch()['ClientControlIdx'] ?? 0;
            if (!$parentClientCustomerIdx) {
                throw new \Exception("거래처 정보가 존재하지않습니다.", "400");
            }

            $param['ordr_mony'] = $orderAmount;

            $response = $this->authKcpPayment($param);
            if ($response['res_cd'] != '0000') {
                throw new \Exception("결제 실패: " . $response['res_msg'], "500");
            }
            $this->conn->beginTransaction();

            $table = "***.ClientControl";
            $idx = [];
            $item = [
                'clientCustomerName' => $param['buyr_name'],
                'cCManager' => $param['buyr_name'],
                'cCGroup' => $companyName,
                'cCTel' => $param['buyr_tel2'],
                'category' => 'I',
                'productGroupIdx' => $productGroupIdx,
                'ServiceControlIdx' => $ServiceControlIdx,
                'parentClientCustomerIdx' => $parentClientCustomerIdx,
                'depth' => 2
            ];

            //결제시 ClientData가 존재하는 경우
            if (!$ClientControlIdx) {
                $param['ClientControlIdx'] = "";
                $item['clientCustomerCode'] = $this->generateClientCode($productGroupIdx);
            } else {
                unset($item['parentClientCustomerIdx']);
                $idx['ClientControlIdx'] = $ClientControlIdx;
                $item['ModDatetime'] = date('Y-m-d H:i:s');
            }

            $insertClientManageIdx = parent::insertUpdate($idx, $table, $item);

            if ($insertClientManageIdx) {
                $ClientControlIdx = $insertClientManageIdx;
            }

            $sql = "SELECT RegistrationPath 
                      FROM abc.DetailCustomer 
                     WHERE ClientControlIdx = :ClientControlIdx";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ClientControlIdx', $ClientControlIdx, $this->conn::PARAM_INT);
            $stmt->execute();
            $registrationPath = $stmt->fetch()['RegistrationPath'] ?? 0;

            if (!$registrationPath) {
                $clientDetailData = [
                    'ClientControlIdx' => $ClientControlIdx,
                    'registrationPath' => 2
                ];
                parent::insertUpdate([], "***.DetailCustomer", $clientDetailData);
            }

            if ($Tickets) {
                $expiredCouponData = [
                    'couponIdx' => $Tickets['CouponIdx'],
                    'TicketsIdx' => $Tickets['TicketsIdx'],
                    'couponCode' => $Tickets['CouponCode'],
                    'ClientControlIdx' => $ClientControlIdx,
                    'issuedDatetime' => $Tickets['IssuedDatetime'],
                    'expiredType' => 1,
                    'expiredDatetime' => date('Y-m-d H:i:s')
                ];

                if ($Tickets['CouponType'] === '1') {
                    $sql = "DELETE FROM abc.IssuedTickets WHERE CouponIdx = :couponIdx";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':couponIdx', $Tickets['CouponIdx'], $this->conn::PARAM_INT);
                    $stmt->execute();
                }

                parent::insertUpdate([], "***.ExpiredTickets", $expiredCouponData);
            }

            $table = "***.SaleGoods";
            $item = [
                'ClientControlIdx' => $ClientControlIdx,
                'ticketType' => 1
            ];

            if ($Tickets) {
                $item['couponCode'] = $Tickets['CouponCode'];
            }

            $SaleGoodsIdx = parent::insertUpdate([], $table, $item);
            $response['SaleGoodsIdx'] = $SaleGoodsIdx;

            $table = "***.IssuedSaleGoods";
            $item = [
                'SaleGoodsIdx' => $SaleGoodsIdx,
                'ClientControlIdx' => $ClientControlIdx
            ];

            for ($i = 0; $i < $orderQuantity; $i++) {
                parent::insertUpdate([], $table, $item);
            }

            $table = "p.PayssItem";
            $idx = [
                'payOrderIdx' => $payOrderIdx
            ];

            $item = [
                'kcpTno' => $response['tno'],
                'approvedOrderAmount' => $response['amount'],
                'payType' => $response['pay_method'],
                'orderStatus' => 1,
                'ClientControlIdx' => $ClientControlIdx,
                'SaleGoodsIdx' => $SaleGoodsIdx,
                'approvedDatetime' => date('Y-m-d H:i:s', strtotime($response['app_time']))
            ];
            parent::insertUpdate($idx, $table, $item);

            $this->conn->commit();
            $bSucc = "true";
            $response['Ret_URL'] = $param['Ret_URL'];

            $this->data = $response;
            $this->code = "200";
            $this->msg = "success";
            $this->desc = "tryKcpAuth";

            return $this->response();
        } catch (\Exception $e) {
            if ($response['res_cd'] === '0000' && $bSucc === "false") {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                return $this->refundKcpPayment($param, $response);
            }
            throw $e;
        }
    }

    private function authKcpPayment($param): array
    {
        try {
            $url = $this->kcpPaymentUrl;
            $header = [
                "Content-Type: application/json",
                "charset=utf-8"
            ];
            $data = [
                'tran_cd' => $param['tran_cd'],
                'site_cd' => $param['site_cd'],
                'kcp_cert_info' => $this->kcpCertInfo,
                'enc_data' => $param['enc_data'],
                'enc_info' => $param['enc_info'],
                'ordr_mony' => $param['ordr_mony'],
            ];

            $result = $this->curl('POST', $url, $header, json_encode($data));
            if ($result['code'] != '200') {
                throw new \Exception("Pay 통신 실패", "500");
            }

            return json_decode($result['response'], true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function refundKcpPayment($param, $response): array
    {
        try {
            $cancel_target_data = "{$param['site_cd']}^{$response['tno']}^STSC";
            $key_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/pay/***tifiabc/splPrikeyPKCS8.pem');
            $pri_key = openssl_pkey_get_private($key_data, $this->kcpSignPw);
            openssl_sign($cancel_target_data, $signature, $pri_key, 'sha256WithRSAEncryption');
            $kcp_sign_data = base64_encode($signature);

            $url = $this->kcpRefundUrl;
            $header = [
                "Content-Type: application/json",
                "charset=utf-8"
            ];
            $data = [
                "site_cd" => $param['site_cd'],
                "kcp_cert_info" => $this->kcpCertInfo,
                "kcp_sign_data" => $kcp_sign_data,
                "tno" => $response['tno'],
                "mod_type" => "STSC",
                "mod_desc" => "가맹점 DB 처리 실패(자동취소)"
            ];

            $result = $this->curl('POST', $url, $header, json_encode($data));
            $resp = json_decode($result['response'], true);
            if ($resp['res_cd'] != '0000') {
                throw new \Exception("결제 취소 실패", "500");
            }

            $table = "p.PayManage";
            $idx = [
                'payOrderCode' => $response['order_no']
            ];
            $item = [
                'kcpTno' => $resp['tno'],
                'orderStatus' => 9,
            ];
            parent::insertUpdate($idx, $table, $item);

            $this->data = [
                'tno' => $resp['tno'],
                'cancelTime' => date('Y-m-d H:i:s', strtotime($resp['canc_time'])),
            ];
            $this->code = "200";
            $this->msg = $resp['res_msg'];
            $this->desc = "refundKcpPayment";

        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            return $this->response();
        }
    }

    public function registerPayTrade($param): array
    {
        try {
            if (!$this->conn) {
                $this->conn = (new PDOFactory)->PDOCreate();
            }

            // 상품 가격 및 총 주문 가격 확인
            $sql = "SELECT ServiceControlIdx, ProductGroupIdx, SalesPrice 
                      FROM abc.Items 
                     WHERE ItemsIdx = :ItemsIdx 
                       AND IsUse = b'1'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ItemsIdx', $param['ItemsIdx'], $this->conn::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            $productGroupIdx = $row['ProductGroupIdx'];

            $price = $row['SalesPrice'] ?? 0;

            if (!$param['couponIdx']) {
                if (
                    $price != $param['itemPrice']
                    || $param['orderAmount'] != ($price * $param['orderQuantity'])
                ) {
                    throw new \Exception("상품가격이 올바르지 않습니다.", "400");
                }
            } else {
                if ($row['SalesPrice'] !== $param['itemPrice']) {
                    throw new \Exception("상품가격이 올바르지 않습니다.", "400");
                }

                $sql = "SELECT 
                            icm.CouponIdx, cm.DiscountMethod, cm.TicketsIdx, cm.CouponCode, cm.CouponType, 
                            cm.DiscountAmount, cm.DiscountRate, cm.ServiceControlIdx, cm.ClientControlIdx
                        FROM abc.IssuedTickets AS icm
                        JOIN abc.Tickets AS cm 
                          ON cm.TicketsIdx = icm.TicketsIdx
                       WHERE cm.TicketsIdx = :TicketsIdx 
                         AND icm.CouponIdx = :couponIdx 
                         AND cm.IsUse = b'1'
                         AND cm.UseStartDate <= :nowDate 
                         AND cm.UseEndDate >= :nowDate 
                         AND cm.ProductGroupIdx = :productGroupIdx";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':TicketsIdx', $param['TicketsIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':couponIdx', $param['couponIdx'], $this->conn::PARAM_INT);
                $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->conn::PARAM_INT);
                $stmt->bindValue(':nowDate', date('Y-m-d'));
                $stmt->execute();
                $couponData = $stmt->fetch();

                $totalAmount = 0;
                if ($couponData['DiscountMethod'] === '1') {
                    $discountAmount = $row['SalesPrice'] * ((100 - $couponData['DiscountRate']) / 100);
                    $totalAmount = $discountAmount * $param['orderQuantity'];
                } else if ($couponData['DiscountMethod'] === '2') {
                    $discountAmount = ($row['SalesPrice'] * $param['orderQuantity']) - $couponData['DiscountAmount'];
                    $totalAmount = $discountAmount;
                }

                if ($param['orderAmount'] != $totalAmount) {
                    throw new \Exception("상품가격이 올바르지 않습니다.3", "400");
                }
            }

            if ($param['orderAmount'] > 0) {
                //유료 결제처리
                $url = $this->kcpTradeUrl;
                $header = [
                    "Content-Type: application/json",
                    "charset=utf-8"
                ];
                $data = [
                    "site_cd" => $param['siteCode'],
                    "kcp_cert_info" => $this->kcpCertInfo,
                    "ordr_idxx" => $param['payOrderCode'],
                    "good_mny" => $param['orderAmount'],
                    "good_name" => $param['itemName'],
                    "pay_method" => $param['payMethod'],
                    "Ret_URL" => $param['returnUrl'],
                    "escw_used" => "N",
                    "user_agent" => $param['userAgent'],
                ];

                $result = $this->curl('POST', $url, $header, json_encode($data));
                if ($result['code'] != '200') {
                    throw new \Exception("Pay 통신 실패", "500");
                }
                $response = json_decode($result['response'], true);
                if ($response['Code'] != '0000') {
                    throw new \Exception($response['Code'] . ": " . $response['Message'], "500");
                }

                $this->data = $response;
                $this->data['actionResult'] = $param['actionResult'];
                $this->data['vanCode'] = $param['vanCode'];
            } else {
                //무료결제처리
                $this->data = [];
            }

            $this->code = '200';
            $this->msg = 'success';
            $this->desc = 'registerPayTrade';

            return $this->response();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    function generateClientCode($gIdx, $i = 0): string
    {
        try {
            if ($i > 10) {
                throw new \Exception('create error ID', '503');
            }

            switch ($gIdx) {
                case '6' :
                    $idHeader = 'icg_';
                    break;
                default :
                    $idHeader = 'gen_';
            }

            $rand_str = bin2hex(random_bytes(4));
            $id = $idHeader . $rand_str;

            if (!$this->conn) {
                $this->conn = (new \PDOFactory)->PDOCreate();
            }
            // 중복 id 확인
            $sql = "SELECT ClientCustomerCode FROM abc.ClientControl WHERE ClientCustomerCode = '" . $id . "'";
            $stmt = $this->conn->query($sql);
            $isExist = $stmt->fetch();
            // id 중복시 재귀
            if ($isExist) {
                $i++;
                return $this->generateClientCode($gIdx, $i);
            } else {
                return $id;
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

}