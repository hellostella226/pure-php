<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <span class="text-left" style="font-size:25px;">** IB 할당</span>(할당:<span id="allocationCount">0</span>,미할당:<span id="notAllocationCount">0</span>)
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
                            <option value="m.State">거주지(도/시)</option>
                            <option value="m.City">거주지(군/구)</option>
                            <option value="ccm.ClientCustomerName">사용처명</option>
                            <option value="mts.IsComplete">전송상태</option>
                            <option value="cs.ConsultantType">상담상태</option>
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
                <div class="col-sm-3">
                    <p style="font-size: 20px;">일괄 다운로드 한번에 선택 가능한 최대 개수는 <span style="color : red;font-weight: bold;">30</span>개 입니다.</p>
                </div>
                <div class="col-sm-8 mt-3" style="text-align:right;">
                    <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="1,15,16,17" data-name="얼리큐_**IB할당" type="button">Excel</button>
                </div>
            </div>
            <div class="container-fluid table-responsive">
                <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%">
                    <thead>
                    <tr>
                        <th scope="col" class="no-sort"><input type="checkbox" class="form-check-input" name="checkall" value="1" id="selectall"></th>
                        <th scope="col" class="no-sort">번호</th>
                        <th scope="col" data-column="prm.CalcDate"><button class="sort-btn">검사일<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="mam.RegDatetime"><button class="sort-btn">할당일<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="o.UsersIdx"><button class="sort-btn">회원ID<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="m.Name"><button class="sort-btn">이름<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="Address"><button class="sort-btn">거주지<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="ccm.ClientCustomerName"><button class="sort-btn">사용처명<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="scm.ServiceCompanyName"><button class="sort-btn">거래처<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="scm.TransferMethodCode"><button class="sort-btn">전송방식<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="mts.RegDatetime"><button class="sort-btn">전송상태<span aria-hidden="true"></span></button></th>
                        <th scope="col" data-column="cs.ConsultantType"><button class="sort-btn">상담상태<span aria-hidden="true"></span></button></th>
                        <th scope="col" class="no-sort">주의-경고</th>
                        <th scope="col" class="no-sort">위험-고위험</th>
                        <th scope="col" class="no-sort">IB<br>다운로드</th>
                        <th scope="col" class="no-sort">질환<br>다운로드</th>
                        <th scope="col" class="no-sort">옵션</th>
                    </tr>
                    </thead>
                    <tbody id="adminTable">
                    </tbody>
                </table>
                <ul class="pagination justify-content-center" id="pagination"></ul>
                <div class="col-md m-1">
                    <div class="text-lg-end">
                        <a class="link-info" href="https://g******daouoffice.com/app/board/26267/post/423377" target="_blank"><strong>엑셀 업로드 가이드</strong></a>
                        <br>
                        <a class="link-info" href="https://img.g******com/b***-*abc/template/IB거래처할당.xlsx"
                           target="_blank"><strong>엑셀 업로드 샘플 다운로드</strong></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <button class="btn btn-secondary btn-dbExcel" data-bs-toggle="modal" data-bs-target="#dbExcelModal"
                                    type="button">DB엑셀
                            </button>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="input-group justify-content-end">
                            <button class="btn btn-primary downloadDbAllocation" type="button">신규</button>
                            <button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#ibInsert">할당
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <div class="input-group justify-content-end">
                            <button class="btn btn-danger all-down" type="button" data-value="ib">IB 일괄 다운</button>
                            <button class="btn btn-success all-down" type="button" data-value="disease">질병 일괄 다운</button>
                        </div>
                    </div>
                </div>
                <div class="modal fade userInsure" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="allocationModalTitle"></h4>
                                <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <input type="hidden" id="" value="">
                                    <div class="mb-3 row">
                                        <p class="col-sm-5">거래처 배정일</p>
                                        <p class="col-sm-7" id="clientRegDate"></p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-5">송신일<br>(수동전송:다운로드일)</p>
                                        <p class="col-sm-7" id="sendDate"></p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-5">상담희망일자</p>
                                        <p class="col-sm-7" id="appointmentDate"></p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-5">상담희망요일</p>
                                        <p class="col-sm-7" id="appointmentDay"></p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-5">상담희망시간</p>
                                        <p class="col-sm-7" id="appointmentHour"></p>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="ibInsert" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">할당등록하기</h5>
                                <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <form id="uploadDbAllocation" class="uploadDbAllocation" method="post" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="dbAllocationFile" class="form-label mt-4">엑셀파일 선택.(csv, xlsx, xls)</label>
                                        <input class="form-control" type="file" id="file-selector" name="dbAllocationFile" value=""
                                               accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </div>
                                    <div class="file-list text-center mt-1">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary excel-btn" data-target="uploadDbAllocation">업로드</button>
                                    <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="dbExcelModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">DB엑셀 - 다운로드할 조건들을 선택하시오.</h5>
                                <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <label for="serviceCompany" class="col-md-3 col-form-label">거래처 선택</label>
                                    <div class="col-md-9">
                                        <select class="form-select" name="serviceCompany" id="serviceCompany">
                                            <option value="">검색컬럼 선택</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="dateRange" class="col-md-3 col-form-label">기간 선택</label>
                                    <div class="col-md-9 input-group">
                                        <input type="date" name="minDate" id="minDate" class="form-control">
                                        <input type="date" name="maxDate" id="maxDate" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary dbExcelDownload">엑셀출력</button>
                                <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="/b***-*abc/resources/js/pharmacy/insureib.js"></script>