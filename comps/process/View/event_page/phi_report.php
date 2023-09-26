<?php
$isTest = false;
if(isset($_GET['isTest'])){
    $isTest = $_GET['isTest'] ? true : false;
}
$icoArr = array(
    1 => '카카오톡',
    2 => '페이코',
    3 => '삼성패스',
    4 => '국민은행',
    5 => 'PASS',
    6 => '네이버',
    7 => '신한은행',
    8 => '토스',
);
$retry = false;
if (isset($_COOKIE['retry'])) {
    $retry = ($_COOKIE['retry'] === '1') ? true : false;
    setcookie("retry", "", time() - 3600, "/");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name=”robots” content=”noindex, nofollow”>
    <meta name="format-detection" content="telephone=no"/>
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    ///중략///
    <link rel="stylesheet" href="/abc/css/main.css?<?=time()?>"/>
    <link rel="stylesheet" href="/abc/css/product_group/6.css?<?=time()?>"/>
    <script>
        let isTest = <?= ($isTest) ? "true" : "false"?>;
    </script>
</head>
<body>
<section class="view-section" id="section-1">
    <div class="row container fin">
        <div class="col-sm-12">
            <img id="reportImg" src="https://picsum.photos/300/2000">
            <button class="btn-next"   onClick="ibProject.get***Report('pdf');">검사결과 파일 다운로드</button>
        </div>
    </div>
</section>
<section class="view-section exception-section">
    <div class="row container exception-1 hidden event-exception">
        <div class="exception-image">
            <img src="https://img.g******com/datashare/ibproject/alert.png">
        </div>
        <div class="notification">
            <p>주문정보를 확인할 수 없습니다.</p>
        </div>
    </div>

</section>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . "/abc/inc/coupon_modal.php"; ?>
<footer>
    <div class="footer-text">
        <b>(주)***</b>
        <p>경기 성남시 분당구 *** 중략 *** 1008-1호</p>
        <p><b>TEL</b>: 031)6**-**** <b>FAX</b>: 031)6**-****</p>
        <p><b>SUPPORT</b> : develop@***.com CopyRight © 2022 abc.</p>
        <p>AllRight Reserved. designed by ***</p>
    </div>
    <div class="footer-link">
        <p>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#userAgree-18">개인정보정책</a>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#userAgree-19">서비스이용약관</a>
        </p>
    </div>
</footer>
<div id="loading"><div class="spinner"></div><p></p></div>

<script src="/abc/js/main.js?<?=time()?>"></script>
<script src="/abc/js/event_page/***_report.js?<?=time()?>"></script>
</body>
</html>