<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">쿠폰 사용 내역 조회</h3>
                <span>* 현재 사용 전 쿠폰은 노출되지 않습니다. 피드백 내역 확인 후 일괄 작업 처리 예정입니다.</span>
            </div>
            <div class="form-group">
                <hr>
                <div class="container-fluid table-responsive">
                    <div class="row">
                        <!-- 검색영역 -->
                        <div class="row justify-content-end">
                            <div class="col-md-1" id="searchDiv">
                                <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                                    <option value="">검색컬럼 선택</option>
                                    <option value="scm.ServiceCompanyName">사용처</option>
                                    <option value="cm.CouponName">쿠폰명</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                    <label for="searchValue"></label>
                                    <input type="text" class="form-control form-control-sm col" name="searchValue"
                                           id="searchValue" value="" />
                                    <button class="btn btn-sm btn-info col-md-3" id="searchBtn">검색</button>
                                </div>
                            </div>
                        </div>
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
                    </div>
                    <table class="table table-hover table-bordered text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">번호</th>
                            <th scope="col">쿠폰코드</th>
                            <th scope="col">쿠폰명</th>
                            <th scope="col">상담사</th>
                            <th scope="col">사용처</th>
                            <th scope="col">발행일</th>
                            <th scope="col">만료일</th>
                            <th scope="col">만료타입</th>
                        </tr>
                        </thead>
                        <tbody id="adminTable"></tbody>
                    </table>
                    <ul class="pagination justify-content-center" id="pagination"></ul>
                </div>
            </div>
            <script src="/b***-*abc/resources/js/offerabc/couponList.js?v1"></script>
            <style>
                .datepicker-controls button {
                    background-color: #fff;
                    border: 1px solid #fff;
                    padding-top: 5px;
                    padding-bottom: 5px;
                }

            </style>
            <style>
                .table-bordered {
                    font-size: 15px;
                }

                @media (max-width: 2000px) {
                    .table-bordered {
                        font-size: 14px;
                    }
                }

                @media (max-width: 1800px) {
                    .table-bordered {
                        font-size: 13px;
                    }
                }

                @media (max-width: 1600px) {
                    .table-bordered {
                        font-size: 12px;
                    }
                }

                @media (max-width: 1400px) {
                    .table-bordered {
                        font-size: 11px;
                    }
                }

                @media (max-width: 1200px) {
                    .table-bordered {
                        font-size: 10px;
                    }
                }
            </style>
    </div>
</div>