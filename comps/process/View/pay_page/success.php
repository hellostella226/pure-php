<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <title>*** 결제 완료</title>
    <link rel="stylesheet" as="style" crossorigin
          href="https://cdn.jsdelivr.net/gh***e-dynamic-subset.css"/>
    <link href="https://cdn.jsdelivr.net/n***p.min.css" rel="stylesheet">
    <style>
        body{
            font-family: "Pretendard Variable", Pretendard, -apple-system, BlinkMacSystemFont, system-ui, Roboto, "Helvetica Neue", "Segoe UI", "Apple SD Gothic Neo", "Noto Sans KR", "Malgun Gothic", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            max-width : 400px;
            margin-left : auto;
            margin-right : auto;
        }
        h1 {
            font-size : 32px;
            margin-top : 30px;
            font-weight: 900;
        }
        h2{
            margin-top : 30px;
            font-size : 20px;
            font-weight: 900;
        }
        a {
            margin-top :30px;
        }
        .tel{
            text-decoration: none;
            font-size : 25px;
            font-weight: 600;
            color : #053B8C;
        }
        p{
            font-size : 16px;
            font-weight: 700;
        }
        .btn-primary{
            background-color: #053B8C;
        }

        @media screen and (max-width:950px) {
            h1 {
                font-size : 20px;
            }
            body{
                max-width : 300px;
            }
        }

    </style>

</head>
<body style="text-align: center; margin-top : 200px;">
<h1>결제 완료</h1>
<h2>사용권 URL은<br>알림톡으로 발송됩니다.</h2>
<p class="mb-0 mt-4">알림톡 미수신시</p>
<p class="mb-5">고객센터로 연락주세요.</p>
<a class="tel" href="tel:031-6**-6***">031-6**-****</a>
<br>
<div class="mx-auto d-grid mt-3">
<a href='#;' class="btn btn-primary btn-lg" onClick="location.replace('<?= ($_REQUEST['Ret_URL']) ?? "https://***.com" ?>')"  style="text-decoration: none; font-size : 20px;"
>완료</a>
</div>
</body>
</html>
