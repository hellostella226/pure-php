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
                        <option value="BizM-21">접수알림</option>
                        <option value="BizM-22">질환 알림</option>
                        <option value="BizM-23">xxx 알림</option>
                        <option value="BizM-24">상담알림</option>
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
            <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                <thead>
                <tr>
                    <th scope="col" class="text-center">
                        <input type="checkbox" class="form-check-input checkall" name="data-select-all" value="1">
                    </th>
                    <th scope="col">번호</th>
                    <th scope="col">신청일</th>
                    <th scope="col">회원ID</th>
                    <th scope="col">이름</th>
                    <th scope="col">접수알림</th>
                    <th scope="col">질환 알림</th>
                    <th scope="col">xxx 알림</th>
                    <th scope="col">상담알림</th>
                    <th scope="col">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
            <div class="row">
                <div class="col-auto">
                    <button type="button" class="btn btn-secondary sendSmsModal" name="data-sms">비즈엠 보내기
                    </button>
                </div>
            </div>
        </div>
        <div id="viewModal" class="modal fade searchSms" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="telephoneModalTitle"></h4>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <p class="col" id="">접수알림일</p>
                            <p class="col" id="registerBizMDate"></p>
                        </div>
                        <div class="row m-auto p-1">
                            <p class="col" id="">질환알림일</p>
                            <p class="col" id="diseaseBizMDate"></p>
                        </div>
                        <div class="row m-auto p-1">
                            <p class="col" id="">xxx알림일</p>
                            <p class="col" id="geneticBizMDate"></p>
                        </div>
                        <div class="row m-auto p-1">
                            <p class="col" id="">상담알림일</p>
                            <p class="col" id="consultBizMDate"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="sendSmsModal" class="modal fade sendSms" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <input type="hidden" id="idxList" name="idxList" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">전송할 알림톡 종류를 선택하세요.</h5>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <fieldset class="form-group">
                            <legend class="">알림톡 종류</legend>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bizMTemplate" id="registerBizM"
                                       value="21" checked>
                                <label class="form-check-label" for="registerBizM">
                                    접수 알림톡
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bizMTemplate" id="testBizM" value="22">
                                <label class="form-check-label" for="testBizM">
                                    검사 알림톡
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bizMTemplate" id="****BizM" value="23">
                                <label class="form-check-label" for="****BizM">
                                    xxx 알림톡
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bizMTemplate" id="consultingBizM"
                                       value="24">
                                <label class="form-check-label" for="consultingBizM">
                                    상담 알림톡
                                </label>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary regist-btn" data-target="sendSms">Send</button>
                        <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/abc/s.js"></script>