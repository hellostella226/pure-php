<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <title>Success</title>
    <link href="https://***min.css" rel="stylesheet">
    <style>
        h1 {
            font-size : 30px;
            margin-top : 30px;
        }
        a {
            margin-top :30px;
        }
        @media screen and (max-width:950px) {
            h1 {
                font-size : 20px;
            }
        }
    </style>
</head>
<body style="text-align: center; margin-top : 200px;">
<img src="https://img.***.com/datashare/alert.png" style="width: 100px;">
<h1>결제가 실패하였습니다.</h1>
<h1>다시 이용해 주시길 바랍니다.</h1>
<a href='#;' class="btn btn-primary btn-lg" onClick="location.replace('<?= ($info['return_url']) ?? "https://***.com" ?>')"
   style="text-decoration: none; font-size : 20px;"
>돌아가기</a>
</body>
</html>