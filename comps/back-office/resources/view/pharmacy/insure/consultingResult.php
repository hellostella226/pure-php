<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">**상담결과</h3>
            </div>
            <div class="form-group">
                <div class="searchContainer" style="display: block">
                    <!-- 검색영역 -->
                    <div class="row justify-content-end">
                        <div class="col">
                            <div class="col-md-7">
                                <button type="button" class="btn btn-sm btn-info mt-1 consultCode" data-toggle="popover">상담상태치환규칙</button>
                                <div id="popoverContent" style="display: none;">
                                    <table style="margin: auto; text-align: center;">
                                        <thead>
                                        <tr>
                                            <th>상태</th>
                                            <th>상태코드</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>계약체결</td>
                                            <td>A</td>
                                        </tr>
                                        <tr>
                                            <td>종결</td>
                                            <td>B</td>
                                        </tr>
                                        <tr>
                                            <td>결번</td>
                                            <td>C</td>
                                        </tr>
                                        <tr>
                                            <td>상담거절</td>
                                            <td>D</td>
                                        </tr>
                                        <tr>
                                            <td>무응답</td>
                                            <td>E</td>
                                        </tr>
                                        <tr>
                                            <td>중복</td>
                                            <td>F</td>
                                        </tr>
                                        <tr>
                                            <td>부재</td>
                                            <td>G</td>
                                        </tr>
                                        <tr>
                                            <td>병력</td>
                                            <td>H</td>
                                        </tr>
                                        <tr>
                                            <td>통화예약</td>
                                            <td>I</td>
                                        </tr>
                                        <tr>
                                            <td>상담완료</td>
                                            <td>J</td>
                                        </tr>
                                        <tr>
                                            <td>방문약속</td>
                                            <td>K</td>
                                        </tr>
                                        <tr>
                                            <td>계약대기</td>
                                            <td>L</td>
                                        </tr>
                                        <tr>
                                            <td>상담</td>
                                            <td>M</td>
                                        </tr>
                                        <tr>
                                            <td>거절</td>
                                            <td>N</td>
                                        </tr>
                                        <tr>
                                            <td>보완</td>
                                            <td>O</td>
                                        </tr>
                                        <tr>
                                            <td>인수불가</td>
                                            <td>P</td>
                                        </tr>
                                        <tr>
                                            <td>신청오류</td>
                                            <td>Q</td>
                                        </tr>
                                        <tr>
                                            <td>기타</td>
                                            <td>Z</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1" id="searchDiv">
                            <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                                <option value="">검색컬럼 선택</option>
                                <option value="prm.CalcDate">신청일</option>
                                <option value="o.UsersIdx">회원ID</option>
                                <option value="m.Name">이름</option>
                                <option value="scm.ServiceCompanyName">IB거래처</option>
                                <option value="cs.ConsultantName">상담자</option>
                                <option value="cs.***TransferDatetime">질병 제공</option>
                                <option value="cs.StatusCode1">1차상담</option>
                                <option value="cs.StatusCode2">2차상담</option>
                                <option value="cs.StatusCode3">3차상담</option>
                                <option value="pim.ItemName">**사</option>
                                <option value="im.ItemName">상품명</option>
                                <option value="icm.MonthlyPremium">월납**료</option>
                                <option value="icm.DueDay">납기</option>
                                <option value="icm.ContractDate">계약일</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="row">
                                <label for="searchValue"></label>
                                <input type="text" class="form-control form-control-sm col" name="searchValue" id="searchValue"
                                       value="">
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
                        <div class="col-sm-11 mt-3" style="text-align:right;">
                            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="16" data-name="얼리큐_**상담결과" type="button">Excel</button>
                        </div>
                    </div>
                    <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                        <thead>
                        <tr>
                            <th scope="col">번호</th>
                            <th scope="col">신청일자</th>
                            <th scope="col">회원ID</th>
                            <th scope="col">이름</th>
                            <th scope="col">DB거래처</th>
                            <th scope="col">상담자</th>
                            <th scope="col">질병 제공</th>
                            <th scope="col">1차상담</th>
                            <th scope="col">2차상담</th>
                            <th scope="col">3차상담</th>
                            <th scope="col">**사</th>
                            <th scope="col">상품명</th>
                            <th scope="col">월납**료</th>
                            <th scope="col">납기</th>
                            <th scope="col">계약일</th>
                            <th scope="col">옵션</th>
                        </tr>
                        </thead>
                        <tbody id="adminTable"></tbody>
                    </table>
                    <ul class="pagination justify-content-center" id="pagination">

                    </ul>
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <div class="m-1 text-lg-end">
                                <a class="link-info" href="https://g******daouoffice.com/app/board/26267/post/423377" target="_blank"><strong>엑셀 업로드 가이드</strong></a>
                                <br>
                                <a class="link-info" href="https://img.g******com/b***-*abc/template/상담결과_수동전송.xlsx"
                                   target="_blank"><strong>엑셀 업로드 샘플 다운로드</strong></a>
                            </div>
                            <button class="btn btn-primary" type="button" name="data-download">다운로드</button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#consultingInsert" type="button">업로드</button>
                        </div>
                    </div>
                </div>
                <div id="searchConsulting" class="modal fade" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5"><strong>**상품 등록하기 <span class="badge bg-danger">모든 항목 필수 입력</span></strong>
                                </h1>
                                <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3 row">
                                    <p class="col-sm-5">질환  제공일</p>
                                    <p class="col-sm-7" id="***TransferDatetime"></p>
                                </div>
                                <div class="mb-3 row">
                                    <p class="col-sm-5">상담원 배정일시</p>
                                    <p class="col-sm-7" id="consultantFixDate"></p>
                                </div>
                                <div class="mb-3 row">
                                    <p class="col-sm-5">1차 상담일</p>
                                    <p class="col-sm-7" id="consultDate1"></p>
                                </div>
                                <div class="mb-3 row">
                                    <p class="col-sm-5">2차 상담일</p>
                                    <p class="col-sm-7" id="consultDate2"></p>
                                </div>
                                <div class="mb-3 row">
                                    <p class="col-sm-5">3차 상담일</p>
                                    <p class="col-sm-7" id="consultDate3"></p>
                                </div>
                                <div class="mb-3 row">
                                    <p class="col-sm-5">메모</p>
                                    <p class="col-sm-7" id="requestMemo"></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary closeModal" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="consultingInsert" class="modal fade uploadConsulting" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">상담결과등록하기</h5>
                                <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="consultingFile" class="form-label mt-4">엑셀파일 선택.(csv, xlsx, xls)</label>
                                    <input class="form-control" type="file" id="file-selector" name="consultingFile" value=""
                                           accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary excel-btn" data-target="uploadConsulting">업로드</button>
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
<script src="/b***-*abc/resources/js/pharmacy/consultingResult.js"></script>