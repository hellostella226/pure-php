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
    <title>***</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" crossorigin
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.7/dist/web/variable/pretendardvariable-dynamic-subset.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/abc/css/main.css?<?=time()?>"/>
    <link rel="stylesheet" href="/abc/css/product_group/5.css?<?=time()?>"/>
    <script>
        let isTest = <?= ($isTest) ? "true" : "false"?>;
    </script>
</head>
<body>
<header class="hidden">
    <div class="header">
        <div class="header-left">
            <!--의사이미지-->
            <img src="https://img.***.com/datashare/***project/header-1.jpg">
            <!--의사이미지-->
        </div>
        <div class="header-icon">
            <!--상담톡아이콘-->
            <a href="tel:031-***-****"><img src="https://img.***.com/datashare/***project/header-icon-1.jpg"></a>
            <a href="javascript:void(0);" onClick="sendTalk();"><img src="https://img.***.com/datashare/***project/header-icon-2.jpg"></a>
        </div>
    </div>
</header>
<section class="sequence"  <?= ($retry == 1) ? "style='display:block;'" : "" ?> >
    <ul class="clearfix">
        <li class="active"><span>1단계</span></li>
        <li><span>2단계</span></li>
        <li><span>3단계</span></li>
        <li><span>4단계</span></li>
    </ul>
    <div class="sequence-border"></div>
    <!--    <span class="sequence-span">마지막 단계</span>-->
</section>
<section class="view-section intro" id="section-1" <?= ($retry == 1) ? "style='display:none;'" : "" ?>>
    <div class="row container intro-container">
        <div class="col-sm-12">
            <div class="intro-text">
                <p>2가지 검사서비스를 한 번에!</p>
                <img src="https://img.***.com/datashare/***project/pharmacy/logo.png">
                <p>EarlyQ</p>
                <div class="intro-text-div">
                    <div>5년내<br>암 발병<br>위험도 검사</div>
                    <div>지금<br>필요한<br>영양성분 검사</div>
                </div>
            </div>
            <div class="intro-btn btn-section" style="display:none;">
                <button class="btn-next" onClick="displayChange('next');">신청시작</button>
            </div>
            <div class="intro-footer">
                <img src="https://img.***.com/datashare/***project/pharmacy/nhis.png">
                <p>***는 국민건강**공단의 건강검진 결과를 활용하여 분석제공합니다.</p>
            </div>
        </div>
    </div>
</section>
<section class="view-section intro-info" id="section-2" >
    <div class="row container intro-container">
        <div class="col-sm-12">
            <h3><img src="https://img.***.com/datashare/***project/pharmacy/logo.png">는 확실해요!</h3>
            <div class="intro-info-text">
                <p>귀하의<br><strong>국가 건강검진 결과 활용</strong></p>
                <img src="https://img.***.com/datashare/***project/pharmacy/arr_down.png">
                <p>100만명의 12년치<br><strong>빅데이터 5억건과 비교분석</strong></p>
                <img src="https://img.***.com/datashare/***project/pharmacy/arr_down.png">
                <p><strong>5년내<br>암 발병 위험도 체크!</strong></p>
            </div>
            <div class="intro-btn btn-section">
                <button class="btn-next" onClick="displayChange('next');">인지했습니다.</button>
            </div>
        </div>
    </div>
</section>
<section class="view-section"  id="section-3" >
    <div class="row container intro-info-container">
        <div class="col-sm-12">
            <div class="intro-info-title">
                <h3>앗!</h3>
                <h5>혹시<br>나쁜 결과가 나온다면?</h5>
                <h5>▼</h5>
            </div>
            <div class="intro-info-txt mobile">
                나쁜 결과 대비 **이 적절한지<br>살짝만 <span>[**분석상담]</span>예정입니다.
            </div>
            <div class="btn-section">
                <button class="btn-next btn-start" onClick="displayChange('next');">살짝만 **분석상담 인지했어요.</button>
            </div>
        </div>
    </div>
</section>
<section class="view-section add-sequance padding-add" data-sequence="1" id="section-4" <?= ($retry == 1) ? "style='display:block;'" : "" ?>>
    <div class="row container user-data">
        <div class="col-sm-12">
            <div class="user-data-text">
                <h4 class="user-data-title"><span>이름</span>을 입력해주세요.</h4>
            </div>
            <div class="user-data-form">
                <div class="agree-group user-data-group hidden" data-order="5">
                    <div class="card ">
                        <h5 class="card-header">
                            <input class="form-check-input userAllCheck" type="checkbox" value="">
                            <label class="form-check-label" for="userAllCheck">
                                전체동의
                            </label>
                        </h5>
                        <div class="card-body">
                            <div class="user-agree">
                                <input class="form-check-input userAgree authCheck" type="checkbox" value="">
                                <label class="form-check-label" for="userAllCheck">
                                    [필수] 개인정보 수집.이용동의
                                </label>
                                <a href="#;" data-bs-toggle="modal" data-bs-target="#userAgree-1" style="float:right;">보기</a>
                            </div>
                            <div class="user-agree">
                                <input class="form-check-input userAgree authCheck" type="checkbox" value="">
                                <label class="form-check-label" for="userAllCheck">
                                    [필수] 서비스 이용약관 동의
                                </label>
                                <a href="#;" data-bs-toggle="modal" data-bs-target="#userAgree-2" style="float:right;">보기</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="address-group user-data-group  hidden" data-order="4">
                    <label class="hidden">실 거주지</label>
                    <div class="form-group ">
                        <div class="input-select">
                            <select class="state form-control" name="state" id="state">
                                <option value="">시/도 선택</option>
                            </select>
                            <span>&#9660;</span>
                        </div>
                        <div class="input-select">
                            <select class="city form-control" name="city" id="city">
                                <option value="">구/군 선택</option>
                            </select>
                            <span>&#9660;</span>
                        </div>
                    </div>
                    <button class="btn input-check btn-address" type="button">확인</button>
                </div>

                <div class="phone-group user-data-group  hidden" data-order="3">
                    <label>전화번호</label>
                    <div class="form-group input-group get-name">
                        <div class="col-sm-9 col-9">
                            <input type="text" class="form-control" id="phone" oninput="autoHyphen(this)"
                                   placeholder="-없이 입력해주세요" maxlength="13" autocomplete="off">
                        </div>
                        <div class="col-sm-3 col-3 btn-div">
                            <i class="fas fa-times-circle clear-input"></i>
                            <button class="btn btn-outline-info btn-sm input-check" type="button">확인</button>
                        </div>
                    </div>
                    <p class="notification"></p>
                </div>

                <div class="birth-group  user-data-group hidden" data-order="2">
                    <label>주민등록번호</label>
                    <div class="form-group input-group get-birth">
                        <div class="col-sm-10 col-9  birth-input">
                            <input type="number" class="form-control number" id="birth" placeholder="생년월일 6자리" maxlength="6"
                                   min="0" max="999999" autocomplete="off">
                            <i class="fas fa-times-circle clear-input"></i>
                            <span>-</span>
                            <input type="number" class="form-control number" id="sex" placeholder="●" maxlength="1" min="1"
                                   max="4" autocomplete="off">
                            <span>●●●●●●</span>
                        </div>
                        <div class="col-sm-2 col-3 btn-div">
                            <button class="btn btn-outline-info btn-sm input-check" type="button">확인</button>
                        </div>
                    </div>
                    <p class="notification"></p>
                </div>

                <div class="name-group  user-data-group" data-order="1">
                    <label>이름</label>
                    <div class="form-group input-group get-name">
                        <div class="col-sm-8 col-8">
                            <input type="text" class="form-control" id="name" placeholder="직접 입력" maxlength="10" minlength="2" autocomplete="off">
                        </div>
                        <div class="col-sm-4 col-4 btn-div">
                            <i class="fas fa-times-circle clear-input"></i>
                            <button class="btn btn-outline-info btn-sm input-check" type="button">확인</button>
                        </div>
                    </div>
                    <p class="notification"></p>
                </div>
            </div>
            <div class="user-data-btn  btn-section  user-data-group  hidden">
                <button class="btn-next" onClick="***project.request('findMembers');">다음</button>
            </div>
        </div>
    </div>
</section>
<section class="view-section add-sequance" data-sequence="2" id="section-5" >
    <div class="row container biomarker-auth mt-3">
        <img src="https://img.***.com/datashare/***project/check.png">
        <h3>5년내 질환 검사</h3>
        <h5>15종의 암, 질병 발병 위험도를 확인해보세요.</h5>
        <div class="agree-bio-group">
            <div class="card ">
                <h5 class="card-header">
                    <input class="form-check-input user-biomarker-all-agree" type="checkbox" value="">
                    <label class="form-check-label" for="">
                        전체동의
                    </label>
                </h5>
                <div class="card-body">
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 개인정보 수집.이용동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-3">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 민감정보 수집 및 이용 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-4">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 민감정보 위탁 취급 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-5">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 개인정보 제3자 제공 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-6">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 민감정보 제3자 제공 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-7">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 개인정보 제3자 마케팅 활용 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-8">보기</a>
                    </div>
                    <div class="bio-agree">
                        <label class="form-check-label">
                            <input class="form-check-input user-biomarker-agree authCheck" type="checkbox" value="">
                            [필수] 민감정보 제3자 마케팅 활용 동의
                        </label>
                        <a href="#;" style="float:right;" data-bs-toggle="modal" data-bs-target="#userAgree-9">보기</a>
                    </div>
                </div>
            </div>
            <div class="btn-section">
                <button class="btn-next" onClick="***project.agreement(22000);">질환검사 시작하기 ></button>
            </div>
        </div>
    </div>
</section>
<section class="view-section add-sequance add-header"  data-sequence="2" id="section-6" >
    <div class="row container simple-auth">
        <div class="col-sm-12 simple-auth-body" style="">
            <h3>본인인증 단계입니다.</h3>
            <h5>간편인증 방법을 선택해주세요.</h5>
            <?php for ($i = 1; $i < 9; $i++) { ?>
                <div class="card mb-3 loginTypeLevel" data-id="<?= $i ?>" data-name="<?= $icoArr[$i] ?>" onClick="***project.tryAuth(this);">
                    <div class="card-body">
                        <img src="https://img.***.com/datashare/***project/ico/<?= $i ?>.png"
                             class="img-fluid rounded-start <?= ($i === 2) ? "img-border" : "" ?>"
                             alt="...">
                        <span class="card-text"><?= $icoArr[$i] ?> 간편인증</span>
                        <span><i class="fas fa-angle-right"></i></span>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="simple-auth-message" style="display:none;">
            <div>
                <p>먼저 <b>인증문자를 확인</b>해주세요!</p>
            </div>
            <p class="arrow-down">
                <svg xmlns="http://www.w3.org/2000/svg" width="14.243" height="21" viewBox="0 0 14.243 21">
                    <g data-name="8666722_log_in_icon" fill="none" stroke="#2584d8" stroke-linecap="round"
                       stroke-linejoin="round" stroke-width="3">
                        <path data-name="패스 2818" d="m10 17 5-5-5-5" transform="rotate(90 7.31 11.81)"/>
                        <path data-name="선 119" d="M14 12H-3" transform="rotate(90 7.31 11.81)"/>
                    </g>
                </svg>
            </p>
            <div>
                <p><b>인증수락</b>을 한 후에</p>
            </div>
            <p class="arrow-down">
                <svg xmlns="http://www.w3.org/2000/svg" width="14.243" height="21" viewBox="0 0 14.243 21">
                    <g data-name="8666722_log_in_icon" fill="none" stroke="#2584d8" stroke-linecap="round"
                       stroke-linejoin="round" stroke-width="3">
                        <path data-name="패스 2818" d="m10 17 5-5-5-5" transform="rotate(90 7.31 11.81)"/>
                        <path data-name="선 119" d="M14 12H-3" transform="rotate(90 7.31 11.81)"/>
                    </g>
                </svg>
            </p>
            <div>
                <p>그대로 <b>현재 이 페이지</b>로 돌아오세요</p>
            </div>
            <p class="arrow-down">
                <svg xmlns="http://www.w3.org/2000/svg" width="14.243" height="21" viewBox="0 0 14.243 21">
                    <g data-name="8666722_log_in_icon" fill="none" stroke="#2584d8" stroke-linecap="round"
                       stroke-linejoin="round" stroke-width="3">
                        <path data-name="패스 2818" d="m10 17 5-5-5-5" transform="rotate(90 7.31 11.81)"/>
                        <path data-name="선 119" d="M14 12H-3" transform="rotate(90 7.31 11.81)"/>
                    </g>
                </svg>
            </p>
            <button class="btn-bio-scrap" onClick="***project.nhis();">인증문자 수락 후 클릭</button>
        </div>
    </div>
</section>
<section class="view-section  add-sequance" data-sequence="2" id="section-7">
    <div class="row container bio-confirm">
        <div class="col-sm-12 bio-confirm-body" style="">
            <img src="https://img.***.com/datashare/***project/clapping.png">
            <h4>질환<br>검사신청 성공</h4>
            <h5>아직 신청이 끝나지 않았어요</h5>
            <div class="btn-section">
                <button class="btn-next"  onClick="displayChange('next');">계속</button>
            </div>
        </div>
    </div>
</section>
<section class="view-section  add-sequance" data-sequence="3" id="section-8">
    <div class="row container nut-start">
        <div class="col-sm-12 nut-start-header" style="">
            <h3>지금 필요한</h3>
            <h3>영양성분 분석 신청</h3>
        </div>
        <div class="call-auth-label">
            <input class="form-check-input call-auth-checkbox authCheck" type="checkbox">
            <label class="form-check-label" for="">
                [필수] 맞춤 영양성분 분석 서비스 동의
            </label>
        </div>
        <div class="btn-section">
            <button class="btn-next" onClick="***project.supplements();">계속</button>
        </div>
        <div class="intro-footer">
            <img src="https://img.***.com/datashare/***project/pharmacy/nhis_dark.png">
            <p>***는 국민건강**공단의 건강검진 결과를 활용하여 분석제공합니다.</p>
        </div>
    </div>
</section>
<section class="view-section  add-sequance" data-sequence="4" id="section-9">
    <div class="row container call-auth">
        <div class="col-sm-12">
            <div>
                <h3><span style="color:#e92d2d">(필수)</span><br>혹시 나쁜결과가 나온다면?<br><span>살짝만 **분석상담 동의</span></h3>
                <img src="https://img.***.com/datashare/***project/pharmacy/insure.png">
                <p>미 동의시 검사취소가 될 수 있어요.</p>
                <div class="call-auth-label">
                    <input class="form-check-input call-auth-checkbox authCheck" type="checkbox">
                    <label class="form-check-label" for="">
                        [필수]**분석상담 동의
                    </label>
                </div>
            </div>
            <button class="btn-call-auth-check" onClick="***project.agreement(20100);">계속</button>
        </div>

    </div>
</section>
<section class="view-section  add-sequance" id="section-10" data-sequence="4" >
    <div class="row container call-time-check">
        <div class="col-sm-12">
            <h3><span>살짝만 **분석상담 한다면,</span><br>요일,시간 선택하기</h3>
            <div class="week">
                <h5>요일 선택(1개)</h5>
                <input type="radio" class="btn-check btn-week" name="week" id="week1" autocomplete="off" value="1">
                <label class="btn btn-outline-primary" for="week1">평일</label>
                <input type="radio" class="btn-check btn-week" name="week" id="week2" autocomplete="off" value="6">
                <label class="btn btn-outline-primary" for="week2">주말</label>
                <input type="radio" class="btn-check btn-week" name="week" id="week3" autocomplete="off" value="8">
                <label class="btn btn-outline-primary" for="week3">항상가능</label>
            </div>
            <div class="time">
                <h5>시간 선택(1개)</h5>
                <div class="d-inline-block w-100 pb-2">
                    <input type="radio" class="btn-check btn-week" name="time" id="time1" autocomplete="off" value="10">
                    <label class="btn btn-outline-primary" for="time1">오전<br>10시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time2" autocomplete="off" value="11">
                    <label class="btn btn-outline-primary" for="time2">오전<br>11시</label>
                </div>
                <div class="d-inline-block w-100 pb-2">
                    <input type="radio" class="btn-check btn-week" name="time" id="time3" autocomplete="off" value="12">
                    <label class="btn btn-outline-primary" for="time3">오후<br>12시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time4" autocomplete="off" value="13">
                    <label class="btn btn-outline-primary" for="time4">오후<br>1시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time5" autocomplete="off" value="14">
                    <label class="btn btn-outline-primary" for="time5">오후<br>2시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time6" autocomplete="off" value="15">
                    <label class="btn btn-outline-primary" for="time6">오후<br>3시</label>
                </div>
                <div class="d-inline-block w-100">
                    <input type="radio" class="btn-check btn-week" name="time" id="time7" autocomplete="off" value="16">
                    <label class="btn btn-outline-primary" for="time7">오후<br>4시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time8" autocomplete="off" value="17">
                    <label class="btn btn-outline-primary" for="time8">오후<br>5시</label>
                    <input type="radio" class="btn-check btn-week" name="time" id="time9" autocomplete="off" value="18">
                    <label class="btn btn-outline-primary" for="time9">오후<br>6시이후</label>
                </div>
            </div>

            <button class="btn-next" onClick="***project.consult();">계속</button>
        </div>
        <div class="call-time-back">

        </div>
    </div>
</section>
<section class="view-section" id="section-11">
    <div class="row container survey-container">
        <div class="col-sm-12">
            <div class="survey-header">
                <p>(필수)</p>
                <p>간단한 질문에 응답해주세요.</p>
                <p>(10초 소요)</p>
            </div>
            <div class="survey-item">
                <p class="survey-question">1. 다음 중 본인 또는 가족 중 진단 또는 치<br>료를 받은 적 있는 질환은 무엇인가요?<br><span>(중복선택 가능)</span></p>
                <label><input type="checkbox" name="survey_1" value="1"> <span>암</span></label>
                <label><input type="checkbox" name="survey_1" value="2"> <span>뇌.심장질환</span></label>
                <label><input type="checkbox" name="survey_1" value="3"> <span>고혈압, 당뇨</span></label>
                <label><input type="checkbox" name="survey_1" value="4"> <span>치매</span></label>
                <label><input type="checkbox" name="survey_1" value="5"> <span>없음</span></label>
            </div>
            <button class="btn-next"  onClick="***project.survey(1);">계속</button>
        </div>
        <div class="call-time-back">

        </div>
    </div>
</section>
<section class="view-section" id="section-12" >
    <div class="row container survey-container-second">
        <div class="col-sm-12">
            <div class="survey-item">
                <p class="survey-question">2. 5년 이내 입원, 수술 받았거나 한달분<br>이상 약처방 받은적이 있으신가요?<br><span>(1개 선택)</span></p>
                <label><input type="radio" name="survey_2" value="1"> <span>예</span></label>
                <label><input type="radio" name="survey_2" value="2"> <span>아니오</span></label>
            </div>
            <div class="survey-item">
                <p class="survey-question">3. 현재 내고 계신 월 **료는 어느정도<br>인가요? <span>(1개 선택)</span></p>
                <label><input type="radio" name="survey_3" value="1"> <span>10만원 이하</span></label>
                <label><input type="radio" name="survey_3" value="2"> <span>20만원 이하</span></label>
                <label><input type="radio" name="survey_3" value="3"> <span>30만원 이상</span></label>
                <label><input type="radio" name="survey_3" value="4"> <span>50만원 이상</span></label>
                <label><input type="radio" name="survey_3" value="5"> <span>잘 모르겠다.</span></label>
            </div>
            <button class="btn-next"  onClick="***project.survey(2);">다음</button>
        </div>
        <div class="call-time-back">

        </div>
    </div>
</section>
<section class="view-section" id="section-13" >
    <div class="row container survey-container-fin">
        <div class="col-sm-12">
            <div class="survey-header">
                <h3>마지막 질문입니다.</h3>
            </div>
            <div class="survey-item">
                <p class="survey-question">4. 현재 어떤 **에 가입되어 있나요?<br><span>(중복선택 가능)</span></p>
                <label><input type="checkbox" name="survey_4" value="1"> <span>실비**</span></label>
                <label><input type="checkbox" name="survey_4" value="2"> <span>종합(건강)**</span></label>
                <label><input type="checkbox" name="survey_4" value="3"> <span>종신 및 CI**</span></label>
                <label><input type="checkbox" name="survey_4" value="4"> <span>운전자**</span></label>
                <label><input type="checkbox" name="survey_4" value="5"> <span>기타</span></label>
                <label><input type="checkbox" name="survey_4" value="6"> <span>잘 모르겠다.</span></label>
            </div>
            <button class="btn-next" onClick="***project.survey(3);">완료</button>
        </div>
        <div class="call-time-back">

        </div>
    </div>
</section>
<section class="view-section" id="section-14" >
    <div class="row container fin">
        <div class="col-sm-12">
            <img src="https://img.***.com/datashare/***project/clapping.png">
            <h4>모든 신청 완료!</h4>
            <h5>1~2일 내로<br>결과확인 방법을<br>안내 드립니다.</h5>
        </div>
    </div>
</section>
<section class="view-section exception-section">
    <div class="row container exception-1  hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>죄송합니다.</h4>
            <h4><span>1958년 이전 출생하신분은 검사신청이 불가</span>합니다.</h4>
            <p>이용해 주셔서 감사합니다.</p>
        </div>
        <div class="restart">
            <b>[안내]</b>
            <p>만약 생년월일을 잘못 입력했을 경우에만 <b>다시 검사신청</b>해보세요.</p>
            <div class="btn-restart-group">
                <button class="btn-restart" onClick="***project.resend('reset');">다시 신청하기</button>
            </div>
        </div>
    </div>
    <div class="row container exception-2  hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>본인인증에</h4>
            <h4>실패했습니다.</h4>
        </div>
        <div class="btn-restart-group">
            <button class="btn-error-noti">인증실패 이유 알아보기</button>
            <div class="error-message-row">
                <div class="error-message">
                    1. 인증 수락 없이 완료 버튼을 눌렀을 경우<br>
                    2. 본인인증 제한시간 초과된 경우<br>
                    3. 본인명의 핸드폰이 아닌 경우<br>
                    4. 본인인증서를 재발급 받아야 하는 경우<br>
                    ※ 문의사항 : 031-***-****
                </div>
                <button class="btn-error-noti-close">접기</button>
            </div>
            <button class="btn-reauth" onClick="***project.resend('nhis')">다시 해보기</button>
        </div>
    </div>
    <div class="row container exception-3 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4><span>${NAME}</span>님의</h4>
            <h4><b>인증서의 오류로 인해 검사신청이 불가</b>합니다.</h4>
            <p>이용해 주셔서 감사합니다.</p>
        </div>
        <div class="restart">
            <b>[안내]</b>
            <p>해당 인증매체 인증서 고객센터를 통해 오류 및 갱신신청을 할 수 있습니다.</p>
        </div>
    </div>
    <div class="row container exception-4 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4><span>${NAME}</span>님은</h4>
            <h4><b>국가건강검진 이력이 없으므로 전체 검사신청이 불가</b>합니다.</h4>
            <p>이용해 주셔서 감사합니다.</p>
        </div>
        <div class="restart">
            <b>[안내]</b>
            <p>2년에 1번씩 받는 국가건강검진을 1 회 이상 받으신 분에 한하여 신청이 가능합니다.</p>
        </div>
    </div>
    <div class="row container exception-5 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4><span>${NAME}</span>님은</h4>
            <h4><b>검사신청을 완료하지 않았습니다.</b></h4>
            <p>고객센터로 전화하셔서<br>검사신청을 완료해주세요.</p>
            <div>
                <a href="tel:031-***-****" class="btn-success" style="text-decoration: none;background-color: #002d68;border-radius: 5px;border: 0px; color: #fff; font-size: 18px;padding: 10px 30px; margin-top: 20px">전화하기</a>
            </div>
        </div>
    </div>
    <div class="row container exception-6 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>질환 검사신청에<br>오류가 발생했어요.</h4>
        </div>
        <div class="exception-button-group">
            <button class="btn-success" onClick="***project.resend('nhis')">다시 한번 시도하기</button>
        </div>
    </div>
    <div class="row container exception-7 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>질환 검사신청에<br>오류가 발생했어요.</h4>
        </div>
        <div class="exception-button-group">
            <button class="btn-success" onClick="***project.resend('nhis')"  >다시 불러오기</button>
        </div>
    </div>
    <div class="row container exception-8 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>간편인증 도중 오류가<br>발생했어요.</h4>
        </div>
        <div class="exception-button-group">
            <button class="btn-success" onClick="***project.resend('***')" >다시 한번 시도하기</button>
        </div>
    </div>
    <div class="row container exception-9 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/***project/alert.png">
        </div>
        <div class="notification">
            <h4>검사 신청을<br>완료하지 않으셨네요.</h4>
        </div>
        <div class="exception-button-group">
            <button class="btn-success" onClick="***project.continues();">이어서 검사 신청하기</button>
        </div>
    </div>
    <div class="row container exception-10 hidden">
        <div class="exception-image">
            <img src="https://img.***.com/datashare/abc/alert.png">
        </div>
        <div class="notification">
            <h4>이미 검사를 받으셨네요.</h4>
            <p>이용해주셔서 감사합니다.</p>
        </div>
    </div>
</section>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . "/abc/inc/pharmacy_modal.php"; ?>
<footer>
    <div class="footer-text">    
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
<script src="/abc/js/product_group/5.js?<?=time()?>"></script>
</body>
</html>