<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>근무시간확인</title>

    <link rel="stylesheet" href="https://bootswatch.com/5/minty/bootstrap.css">
    <link rel="stylesheet" href="/b***-*abc/resources/css/index.css"/>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">*********</a>
    </div>
</nav>
<div class="container text-center" style="margin: 5%">
    <h1>Welcome! Developer Only!!!</h1>
</div>

<div class="container">
    <div class="alert alert-dismissible alert-secondary text-center" id="warningDev">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <h4 class="alert-heading">Warning!</h4>
        <p class="mb-0">Please go back if you are not a developer. <a href="/" class="alert-link">Go Back</a></p>
    </div>
</div>

<div class="container" style="display: none" id="mainRecordTime">
    <div class="col-md-4">
        <h3><?= date('m'); ?>월 <?= date('d'); ?>일, <?= date('D');?></h3>
    </div>
    <div class="form-control" style="margin-bottom: 1%">
        <div class="row">
            <div class="col-md-auto">
                    <p>이번 주 최소 근무시간 : <input type="number" class="text-center" id="totalTime"> 시간</p>
                    <p>
                        완료근무시간:
                        <input type="number" class="text-center" id="workHour"> 시간
                        <input type="number" class="text-center" id="workMin" min="0" max="60"> 분
                    </p>

            </div>
            <div class="col-md-auto d-flex" style="margin-bottom: 1%">
                <button class="btn btn-sm btn-secondary" type="button" id="btnInsertWorkTime">입력</button>
            </div>
        </div>

        <div class="progress" style="height:50px;margin-bottom: 2%">
            <div class="progress-bar bg-info" style="width:0%"></div>
        </div>
        <div class="row">
            <div class="remainTime">
                <p id="leftTime">남은 근무 시간: </p>
            </div>
            <div class="leaveTime">
                <p id="leaveTime">퇴근 가능 시간: </p>
            </div>
        </div>

    </div>

    <div class="form-control">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <label class="form-label mt-4">근무시작시간 입력</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="startTime" placeholder="hh:mm tt" onfocus="(this.type='time')" onblur="(this.type='text')" >
                    <button class="btn btn-primary" type="button" id="btnInsertStartTime">입력</button>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label mt-4">근무종료시간 입력</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="endTime" placeholder="hh:mm tt" onfocus="(this.type='time')" onblur="(this.type='text')">
                    <button class="btn btn-primary" type="button" id="btnInsertEndTime">입력</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="footer">
        <div class="row col-sm-auto justify-content-center">
            <div class="col-md-auto footer-logo">
                <img src="https://img.g******com/logo/optimize/eng_vertical_basic_min.png">
            </div>
            <div class="col-md-auto footer-text">
                <b>(주)xxxxxxx</b><br>
                **(xx동, **건물) B동 1008-1호<br>
                TEL: 031)***-**** FAX: 031)***-****<br>
                SUPPORT: develop@g******com CopyRight © 2022 g******
                AllRight Reserved. designed by Hellostellaa
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>

<script src="/b***-*abc/resources/js/main.js"></script>
</body>
</html>

<script type="text/javascript">
    $(document).ready(function () {

        if(!!$.cookie('TotalWorkTime')){
            var totalWorkTime = $.cookie('TotalWorkTime');
        } else {
            $.cookie('TotalWorkTime', 45, {expires: 6, path: '/b***-****'});
            var totalWorkTime = $.cookie('TotalWorkTime');
        }
        $('#totalTime').val(totalWorkTime);
        $('#workHour').attr({
            "min" : 0,
            "max" : totalWorkTime
        });

        if(!!$.cookie('WorkHour')){
            var workHour = $.cookie('WorkHour');
        } else {
            $.cookie('WorkHour', 0, {expires: 2, path: '/b***-****'});
            var workHour = $.cookie('WorkHour');
        }
        $('#workHour').val(workHour);

        if(!!$.cookie('WorkMin')){
            var workMin = $.cookie('WorkMin');
        } else {
            $.cookie('WorkMin', 0, {expires: 2, path: '/b***-****'});
            var workMin = $.cookie('WorkMin');
        }
        $('#workMin').val(workMin);

        var workMinCal = (workMin/60).toFixed(2)
        var workTimeCal = parseFloat(workHour) + parseFloat(workMinCal);
        var workTimePercent = (workTimeCal/totalWorkTime)*100;
        $(".progress-bar").css("width", workTimePercent + "%").text(workHour + "시간 " + workMin + " 분 근무 완료");

        if(!!$.cookie('LeftHour')){
            var leftHour = $.cookie('LeftHour');
        } else {
            if(workMin==0){
                $.cookie('LeftHour', (totalWorkTime-workHour), {expires: 2, path: '/b***-****'});
            } else {
                $.cookie('LeftHour', (totalWorkTime-workHour-1), {expires: 2, path: '/b***-****'});
            }
            var leftHour = $.cookie('LeftHour');
        }
        if(!!$.cookie('LeftMin')){
            var leftMin = $.cookie('LeftMin');
        } else {
            $.cookie('LeftMin', 0, {expires: 2, path: '/b***-****'});
            var leftMin = $.cookie('LeftMin');
        }
        $('#leftTime').text("남은 근무 시간: " + leftHour + " 시간 " + leftMin + " 분");

        if(!!$.cookie('StartTime')){
            var startTime = $.cookie('StartTime');
            $('#startTime').val(startTime);
        }
        if(!!$.cookie('EndTime')){
            var endTime = $.cookie('EndTime');
            $('#endTime').val(endTime);
        }
    });

    $('.btn-close').on('click', function () {
        changeDisplayToNoneById('warningDev');
        changeDisplayToShowById('mainRecordTime');
    });

    $('#btnInsertWorkTime').on('click', function(){
        var inputTotalTime = $('#totalTime').val();
        var inputworkHour = $('#workHour').val();
        var inputworkMin = $('#workMin').val();

        $.cookie('TotalWorkTime', inputTotalTime, {expires: 6, path: '/b***-****'});
        $.cookie('WorkHour', inputworkHour, {expires: 2, path: '/b***-****'});
        $.cookie('WorkMin', inputworkMin, {expires: 2, path: '/b***-****'});

        var totalWorkTime = $.cookie('TotalWorkTime');
        var workHour = $.cookie('WorkHour');
        var workMin = $.cookie('WorkMin');
        var workMinCal = (workMin/60).toFixed(2)
        var workTimeCal = parseFloat(workHour) + parseFloat(workMinCal);
        var workTimePercent = (workTimeCal/totalWorkTime)*100;
        $(".progress-bar").css("width", workTimePercent + "%").text(workHour + "시간 " + workMin + " 분 근무 완료");

        if(workMin==0){
            $.cookie('LeftHour', (totalWorkTime-workHour), {expires: 2, path: '/b***-****'});
        } else {
            $.cookie('LeftHour', (totalWorkTime-workHour-1), {expires: 2, path: '/b***-****'});
            $.cookie('LeftMin', (60-workMin), {expires: 2, path: '/b***-****'});
        }
        var leftHour = $.cookie('LeftHour');
        var leftMin = $.cookie('LeftMin');

        $('#leftTime').text("남은 근무 시간: " + leftHour + " 시간 " + leftMin + " 분");
    });

    $('#btnInsertStartTime').on('click', function (){
        var inputStartTime = $('#startTime').val();
        $.cookie('StartTime', inputStartTime, {expires: 12/24, path: '/b***-****'});
        var startTime = $.cookie('StartTime');
        $('#startTime').val(startTime);

        var leftHour = $.cookie('LeftHour');
        var leftMin = $.cookie('LeftMin');

        if(leftHour <= 12){
            var startTimeArray=startTime.split(':');
            var startHour = startTimeArray[0];
            var startMin = startTimeArray[1];
            var endHour = parseFloat(startHour) + parseFloat(leftHour);
            var endMin = parseFloat(startMin) + parseFloat(leftMin);

            if(endMin >= 60){
                endHour = endHour + 1;
                endMin = endMin - 60;
            }
            var currentTime = new Date();
            currentTime.setHours(endHour);
            currentTime.setMinutes(endMin);
            currentTime.setSeconds(0);

            timeEnd = currentTime.toTimeString().split(' ');

            $('#leaveTime').text("퇴근 가능 시간: " + timeEnd[0].substring(0,5));
        }
    });

    $('#btnInsertEndTime').on('click', function (){
        var inputEndTime = $('#endTime').val();
        $.cookie('EndTime', inputEndTime, {expires: 12/24, path: '/b***-****'});
        var endTime = $.cookie('EndTime');
        $('#endTime').val(endTime);

        if($('#startTime').val()) {
            var start = $('#startTime').val();
            var end = $('#endTime').val();

            s = start.split(':');
            e = end.split(':');
            min = e[1]-s[1];
            hour_carry = 0;
            if(min < 0){
                min += 60;
                hour_carry += 1;
            }
            hour = e[0]-s[0]-hour_carry;

            var workHour = $.cookie('WorkHour');
            var workMin = $.cookie('WorkMin');
            var leftHour = $.cookie('LeftHour');
            var leftMin = $.cookie('LeftMin');

            workHour = parseFloat(workHour) + parseFloat(hour);
            workMin = parseFloat(workMin) + parseFloat(min);
            leftHour = parseFloat(leftHour) - parseFloat(hour);
            leftMin = parseFloat(leftMin) - parseFloat(min);
            if(leftMin < 0){
                leftHour -= 1;
                leftMin += 60;
            }

            $.cookie('WorkHour', workHour, {expires: 6, path: '/b***-****'});
            $.cookie('WorkMin', workMin, {expires: 6, path: '/b***-****'});
            $.cookie('LeftHour', leftHour, {expires: 6, path: '/b***-****'});
            $.cookie('LeftMin', leftMin, {expires: 6, path: '/b***-****'});

            var totalWorkTime = $.cookie('TotalWorkTime');
            var workMinCal = (workMin/60).toFixed(2)
            var workTimeCal = parseFloat(workHour) + parseFloat(workMinCal);
            var workTimePercent = (workTimeCal/totalWorkTime)*100;

            $(".progress-bar").css("width", workTimePercent + "%").text(workHour + "시간 " + workMin + " 분 근무 완료");
            $('#leftTime').text("남은 근무 시간: " + leftHour + " 시간 " + leftMin + " 분");
            $('#workHour').val(workHour);
            $('#workMin').val(workMin);
        }
    });

</script>
