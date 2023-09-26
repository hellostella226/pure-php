<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">** 할당 이력</h3>
            </div>
            <hr class="mb-1">
            <div class="row">
                <!-- 검색영역 -->
            </div>
            <div class="row mb-3">
                <div class="col-sm-1">
                    <select class="form-select form-select-sm" id="searchEntry">
                        <option>50 entries</option>
                        <option>100 entries</option>
                        <option>150 entries</option>
                        <option>200 entries</option>
                        <option>250 entries</option>
                    </select>
                </div>
                <div class="col-sm-11 mt-3" style="text-align:right;">
                    <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-name="얼리큐_**할당이력" type="button">Excel</button>
                </div>
            </div>
            <div class="container-fluid table-responsive">
                <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>**사명</th>
                        <th>총 설정 제공량(누적)</th>
                        <th>총 할당량(누적)</th>
                        <th>최근 설정제공량</th>
                        <th>최근 할당량</th>
                        <th>최근 설정일</th>
                    </tr>
                    </thead>
                    <tbody id="adminTable">
                    </tbody>
                </table>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </div>
        </div>
    </div>
</div>
<style>
    @media (min-width: 992px) {
        .left-side {
            padding-right: 0px;
        }
        .sub-menu-left {
            padding-left : 0px; padding-right : 0px;
        }
        .sub-menu-left button {
            width: 100%;
        }
        .col-lg-1 {
            width: 150px !important;
        }
        .col-lg-11 {
            width: calc(100% - 155px) !important;
        }
    }
</style>
<script src="/b***-*abc/resources/js/pharmacy/history.js"></script>