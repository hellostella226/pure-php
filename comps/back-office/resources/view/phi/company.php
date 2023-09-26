<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">사용처 정보</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="ccm.Category">구분(약국,병원)</option>
                        <option value="ccm2.ClientCustomerName">거래처명</option>
                        <option value="ccm.ClientCustomerName">사용처명</option>
                        <option value="pg.ProductGroupName">상품그룹명</option>
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
            <table class="table table-hover table-bordered text-nowrap sortable" >
                <thead>
                <tr>
                    <th scope="col" class="no-sort">번호</th>
                    <th scope="col" data-column="ccm.RegDatetime"><button class="sort-btn">등록일<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm2.ClientCustomerName"><button class="sort-btn">거래처명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.Category"><button class="sort-btn">구분<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.ClientCustomerCode"><button class="sort-btn">사용처 ID<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.ClientCustomerName"><button class="sort-btn">사용처 명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.CCManager"><button class="sort-btn">담당자<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.CCTel"><button class="sort-btn">전화번호<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="Address"><button class="sort-btn">소재지<span aria-hidden="true"></span></button></th>
                    <th scope="col" class="no-sort">URL</th>
                    <th scope="col" data-column="ccm.ResponseType"><button class="sort-btn">제공방식<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.SpecimenType"><button class="sort-btn">검체종류<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pg.ProductGroupCode"><button class="sort-btn">그룹코드<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pg.ProductGroupName"><button class="sort-btn">그룹상품명<span aria-hidden="true"></span></button></th>
                    <th scope="col" class="no-sort">QR코드</th>
                    <th scope="col" class="no-sort">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </div>
        <div class="row">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <div class="m-1 text-lg-end">
                    <a class="link-info" href="https://g******daouoffice.com/app/board/26267/post/423377" target="_blank"><strong>엑셀 업로드 가이드</strong></a>
                    <br>
                    <a class="link-info" href="https://img.g******com/b***-*abc/template/병원_등록.xlsx"
                       target="_blank"><strong>엑셀 업로드 샘플 다운로드</strong></a>
                </div>
                <div class="input-group justify-content-end">
                    <button type="button" class="btn btn-secondary modal-init-btn" data-bs-toggle="modal"
                            data-bs-target="#companyUpdate" id="register">개별등록
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                            data-bs-target="#companyInsert">대량등록
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="companyUpdate" class="modal fade registCompany" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="allocationModalTitle"></h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="ClientControlIdx" name="ClientControlIdx" value="">
                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 거래처명</p>
                            <p class="col-sm-8" id="companyList">
                                <select class="form-select form-select-sm" id="client" name="parentClientCustomerIdx">
                                    <option value="">거래처 선택</option>
                                </select>
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 구분</p>
                            <p class="col-sm-8">
                                <select class="form-select form-select-sm" id="category" name="category">
                                    <option value="">구분 선택</option>
                                    <option value="P">약국</option>
                                    <option value="H">병원</option>
                                </select>
                            </p>
                        </div>
                        <div class="mb-3 row" id="companyCodeDiv">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 사용처 ID</p>
                            <p class="col-sm-8">
                                <input type="text" id="companyCode" name="companyCode" class="form-control form-control-sm" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 사용처 명</p>
                            <p class="col-sm-8">
                                <input type="text" id="companyName" name="companyName" class="form-control form-control-sm" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">담당자</p>
                            <p class="col-sm-8">
                                <input type="text" id="manager" name="manager" class="form-control form-control-sm" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">전화번호</p>
                            <p class="col-sm-8">
                                <input type="text" id="phone" name="phone" class="form-control form-control-sm" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">우편번호(신)</p>
                            <p class="col-sm-6">
                                <input type="text" id="postcode" name="postcode" class="form-control form-control-sm">
                            </p>
                            <div class="col-sm-2 m-0">
                                <button type="button" class="btn btn-sm btn-primary searchAddress"
                                        onclick="adminScript.execDaumPostcode();">찾기
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">주소</p>
                            <p class="col-sm-8">
                                <input type="text" id="address" name="address" class="form-control form-control-sm" value="">
                                <input type="hidden" id="sido" name="sido" value="">
                                <input type="hidden" id="sigungu" name="sigungu" value="">
                                <input type="hidden" id="roadname" name ="roadname" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">상세주소</p>
                            <p class="col-sm-8">
                                <input type="text" id="addressDetail" name="addressDetail"
                                       class="form-control form-control-sm" value="">
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">제공방식</p>
                            <p class="col-sm-8">
                                <select class="form-select form-select-sm" id="responseType" name="responseType">
                                    <option value="">제공방식 선택</option>
                                    <option value="1">이메일</option>
                                    <option value="2">직접출력</option>
                                </select>
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">검체종류</p>
                            <p class="col-sm-8">
                                <select class="form-select form-select-sm" id="specimenType" name="specimenType">
                                    <option value="none">none</option>
                                    <option value="blood">blood</option>
                                    <option value="buccal">buccal</option>
                                </select>
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <p class="col-sm-4">상품그룹명</p>
                            <p class="col-sm-8">
                                <select class="form-select form-select-sm" id="productGroup" name="productGroup">
                                    <option value="">선택 안함</option>
                                </select>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="registCompany">등록</button>
                </div>
            </div>
        </div>
    </div>
    <div id="companyInsert" class="modal fade uploadCompanyDb" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">사용처 등록하기</h5>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form id="uploadCompanyDbForm" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <label for="companyName" class="col-form-label col-sm-3 ">거래처명</label>
                            <select class="form-select form-select-sm col" id="clientCustomer" name="parentClientCustomerIdx">
                                <option value="">거래처 선택</option>
                            </select>
                        </div>
                        <hr>
                        <div class="row m-auto p-1">
                            <label for="companyFile">엑셀파일 선택 .(csv, xlsx, xls)</label>
                            <input type="file" id="companyFile" name="companyFile" class="form-control" value=""
                                   accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary excel-btn" data-target="uploadCompanyDb">업로드</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .table-bordered{
            font-size : 15px;
        }
        @media (max-width: 2000px) {
            .table-bordered{
                font-size : 14px;
            }
        }
        @media (max-width: 1800px) {
            .table-bordered{
                font-size : 13px;
            }
        }
        @media (max-width: 1600px){
            .table-bordered{
                font-size : 12px;
            }
        }
        @media (max-width: 1400px){
            .table-bordered{
                font-size : 11px;
            }
        }
        @media (max-width: 1200px){
            .table-bordered{
                font-size : 10px;
            }
        }
    </style>
    <script src="/b***-*abc/resources/js/abc/company.js"></script>