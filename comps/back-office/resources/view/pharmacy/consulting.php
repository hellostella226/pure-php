<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">**상담 예약</h3>
    </div>
    <hr class="mb-1">
    <div class="row">
        <!-- 검색영역 -->
        <div class="row justify-content-end">
            <div class="col-md-1" id="searchDiv">
                <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                    <option value="">검색컬럼 선택</option>
                    <option value="prm.CalcDate">검사일</option>
                    <option value="mm.UsersIdx">회원ID</option>
                    <option value="m.Name">이름</option>
                    <option value="m.Phone">전화번호(01012345678)</option>
                    <option value="cs.IsOut">상담예약이탈(Y,N)</option><!--**상담동의,요일지정,시간지정,지정시간 모두 발생시에만??-->
                    <option value="cs.IsAgree">상담동의(Y,N)</option><!--영양동의,상담동의,요일,시간 모두 발생시에만 Y-->
                    <option value="cs.AppointmentDay">상담요일(1:평일,6:주말,8:항상가능)</option>
                    <option value="cs.AppointmentHour">상담시간(10~18)</option>
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
            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="11" data-name="얼리큐_**상담예약" type="button">Excel</button>
        </div>
    </div>
    <div class="container-fluid table-responsive">
        <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%" id="diseaseTable">
            <thead>
            <tr>
                <th scope="col" class="no-sort">번호</th>
                <th scope="col" data-column="prm.CalcDate"><button class="sort-btn">검사일<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="cs.RegDatetime"><button class="sort-btn">예약일<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="mm.UsersIdx"><button class="sort-btn">회원ID<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="m.Name"><button class="sort-btn">이름<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="m.Phone"><button class="sort-btn">전화번호<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="IsOut"><button class="sort-btn">상담예약이탈<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="IsAgree"><button class="sort-btn">상담동의<span aria-hidden="true"></span></button></th>
                <th scope="col" class="no-sort">상담요일</th>
                <th scope="col" class="no-sort">상담시간</th>
                <th scope="col" class="no-sort">옵션</th>
            </tr>
            </thead>
            <tbody id="adminTable">
            </tbody>
        </table>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </div>
    <div id="modifyModal" class="modal fade updateConsultingData" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <input type="hidden" id="orderIdx" name="orderIdx" value="">
                <input type="hidden" id="productIdx" name="productIdx" value="">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"><strong>상담예약</strong></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"  aria-label="Close"></button>
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
                        <input type="checkbox" id="consultAgree" name="consultAgree" class="form-control form-control-sm form-check-input required-value" value="" style="width: 2em">
                    </div>

                    <div class="row m-auto p-1">
                        <label for="appointmentDay" class="col-form-label col-sm-4">
                            <span class="badge bg-danger">필수</span>  상담요일
                        </label>
                        <select id="appointmentDay" name="appointmentDay" class="form-select form-select-sm col required-value">
                            <option value="" selected>요일 선택</option>
                            <option value="1" selected>평일</option>
                            <option value="6" selected>주말</option>
                            <option value="8" selected>항상가능</option>
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
                    <button type="button" class="btn btn-primary regist-btn" data-target="updateConsultingData">수정</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/pharmacy/consulting.js"></script>