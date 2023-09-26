<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">상담 예약</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="o.RegDatetime">신청일자(YYYY-mm-dd)</option>
                        <option value="o.UsersIdx">회원ID</option>
                        <option value="m.Name">이름</option>
                        <option value="m.Phone">전화번호</option>
                        <option value="culs.StatusCode">이탈여부(Y,N)</option>
                        <option value="am.ALL_AGRE_YN">동의여부(Y,N)</option>
                        <option value="cs.AppointmentDay">상담요일(평일,주말,항상)</option>
                        <option value="cs.AppointmentHour">상담시간(숫자만:10~18)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <label for="searchValue"></label>
                        <input type="text" class="form-control form-control-sm col" name="searchValue" id="searchValue" value="">
                        <button class="btn btn-sm btn-info col-md-3" id="searchBtn">검색</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container-fluid table-responsive">
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
            <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%">
                <thead>
                <tr>
                    <th scope="col" class="no-sort">번호</th>
                    <th scope="col" data-column="o.RegDatetime"><button class="sort-btn">신청일<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="o.UsersIdx"><button class="sort-btn">회원ID<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Name"><button class="sort-btn">이름<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Phone"><button class="sort-btn">전화번호<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="culs.Process"><button class="sort-btn">상담예약이탈<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="am.ALL_AGRE_YN"><button class="sort-btn">상담동의<span aria-hidden="true"></span></button></th>
                    <th scope="col" class="no-sort">상담요일</th>
                    <th scope="col" class="no-sort">상담시간</th>
                    <th scope="col" class="no-sort">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
        </div>
        <div id="modifyModal" class="modal fade updateConsultingData" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <input type="hidden" id="orderIdx" name="orderIdx" value="">
                    <input type="hidden" id="productIdx" name="productIdx" value="">
                    <div class="modal-header">
                        <h4 class="modal-title" id="telephoneModalTitle"></h4>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <label for="UsersIdx" class="col-form-label col-sm-4 ">회원ID</label>
                            <input readonly="readonly" type="text" id="UsersIdx" name="UsersIdx" class="form-control form-control-sm col" value="">
                        </div>
                        <div class="row m-auto p-1">
                            <label for="MembersName" class="col-form-label col-sm-4 ">회원명</label>
                            <input readonly="readonly" type="text" id="MembersName" name="MembersName" class="form-control form-control-sm col" value="">
                        </div>

                        <div class="row m-auto p-1">
                            <label for="consultAgree" class="col-form-label col-sm-4">
                                <span class="badge badge-sm bg-danger">필수</span>  상담동의
                            </label>
                            <input type="checkbox" id="consultAgree" name="consultAgree" class="form-control form-control-sm form-check-input required-value" value="Y" style="width: 2em">
                        </div>
                        <div class="row m-auto p-1">
                            <label for="appointmentDay" class="col-form-label col-sm-4">
                                <span class="badge bg-danger">필수</span>  상담요일
                            </label>
                            <select id="appointmentDay" name="appointmentDay" class="form-select form-select-sm col required-value">
                                <option value="" selected>요일 선택</option>
                                <option value="1">평일</option>
                                <option value="6">주말</option>
                                <option value="8">항상가능</option>
                            </select>
                        </div>
                        <div class="row m-auto p-1">
                            <label for="appointmentHour" class="col-form-label col-sm-4">
                                <span class="badge bg-danger">필수</span>  상담시간
                            </label>
                            <select id="appointmentHour" name="appointmentHour" class="form-select form-select-sm col required-value">
                                <option value="" selected>시간 선택</option>
                                <option value="10">오전10시</option>
                                <option value="11">오전11시</option>
                                <option value="12">오후12시</option>
                                <option value="13">오후1시</option>
                                <option value="14">오후2시</option>
                                <option value="15">오후3시</option>
                                <option value="16">오후4시</option>
                                <option value="17">오후5시</option>
                                <option value="18">오후6시이후</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary regist-btn" data-target="updateConsultingData">등록</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/abc/telephone.js"></script>