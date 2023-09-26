<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">쿠폰 생성 관리</h3>
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
                                    <option value="sm.ServiceCompanyName">사용처</option>
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
                            <th scope="col">등록일</th>
                            <th scope="col">수정일</th>
                            <th scope="col">쿠폰코드</th>
                            <th scope="col">쿠폰명</th>
                            <th scope="col">회사명</th>
                            <th scope="col">사용여부</th>
                            <th scope="col">옵션</th>
                        </tr>
                        </thead>
                        <tbody id="adminTable"></tbody>
                    </table>
                    <ul class="pagination justify-content-center" id="pagination"></ul>
                </div>
                <div class="row">
                    <div class="col-md-12 d-flex justify-content-end">
                        <div>
                            <button class="btn btn-primary float-end modal-init-btn" data-bs-toggle="modal" data-bs-target="#couponRegist">쿠폰 생성</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="couponRegist" class="modal fade couponRegist" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="allocationModalTitle">쿠폰 신규 생성</h4>
                            <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <input type="hidden" id="TicketsIdx" name="TicketsIdx" value="">

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>발행 타입</p>
                                    <p class="col-sm-8">
                                        <input type="radio" name="couponType" value="1" checked /> 일회용&nbsp;&nbsp;
                                        <input type="radio" name="couponType" value="2" /> 다회용
                                    </p>
                                </div>

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>쿠폰명</p>
                                    <p class="col-sm-8">
                                        <input type="text" id="couponName" name="couponName"
                                               class="form-control form-control-sm required-value" value="" maxlength="30" />
                                    </p>
                                </div>

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>할인 구분</p>
                                    <p class="col-sm-5">
                                        <input type="radio" name="discountMethod" value="1" checked /> 할인율&nbsp;&nbsp;
                                        <input type="radio" name="discountMethod" value="2" /> 할인가
                                    </p>
                                    <p class="col-sm-3">
                                        <input type="text" id="amount" name="amount" class="form-control form-control-sm required-value" value="" maxlength="20">
                                    </p>
                                </div>

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>적용 회사</p>
                                    <p class="col-sm-8">
                                        <select class="form-select form-select-sm required-value" id="serviceCompany" name="parentClientCustomerIdx">
                                            <option value="">회사명 선택</option>
                                        </select>
                                    </p>
                                </div>
                                <input type="hidden" id="consultantIdx" name="ClientControlIdx">
                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge">선택</span>상담사 ID</p>
                                    <p class="col-sm-8">
                                        <input type="text" class="col-md-6" name="consultantId" id="consultantId" value="">
                                        <button class="btn btn-sm btn-dark col-md-3" id="innerSearchBtn">검색</button>
                                    </p>
                                </div>

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>사용 기간</p>

                                    <p class="col-sm-3">
                                        <input type="text" id="useStartDate" name="useStartDate"
                                               class="form-control required-value" value="" maxlength="8">
                                    </p>~
                                    <p class="col-sm-3">
                                        <input type="text" id="useEndDate" name="useEndDate"
                                               class="form-control required-value" value="" maxlength="8">
                                    </p>
                                </div>

                                <div class="mb-3 row">
                                    <p class="col-sm-4"><span class="badge bg-danger">필수</span>쿠폰 상태</p>
                                    <p class="col-sm-8">
                                        <input type="radio" name="couponStatus" value="1" checked /> 사용&nbsp;&nbsp;
                                        <input type="radio" name="couponStatus" value="2" /> 미사용
                                    </p>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary regist-btn" data-target="couponRegist">발행</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="/b***-*abc/resources/js/offerabc/registCoupon.js?v1"></script>
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