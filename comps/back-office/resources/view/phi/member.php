<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">회원 정보</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="o.RegDatetime">신청일자(YYYY-mm-dd)</option>
                        <option value="ccm.Category">구분(약국,병원)</option>
                        <option value="ccm.ClientCustomerName">사용처명</option>
                        <option value="mm.UsersIdx">회원ID</option>
                        <option value="m.Name">이름</option>
                        <option value="m.Birth1">탄생년도(ex:2001)</option>
                        <option value="m.Gender">성별</option>
                        <option value="m.Phone">전화번호</option>
                        <option value="m.Email">이메일</option>
                        <option value="m.State">거주지(도/시)</option>
                        <option value="m.City">거주지(군/구)</option>
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
                    <th scope="col" data-column="o.RegDatetime"><button class="sort-btn">등록일<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.Category"><button class="sort-btn">구분<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="ccm.ClientCustomerName"><button class="sort-btn" >사용처명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="mm.UsersIdx"><button class="sort-btn" >회원ID<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Name"><button class="sort-btn" >이름<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="Birth"><button class="sort-btn" >생년월일<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="Age"><button class="sort-btn" >나이(만)<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Gender"><button class="sort-btn" >성별<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Phone"><button class="sort-btn" >전화번호<span aria-hidden="true"></span></button></th>
                    <th scope="col" class="no-sort">이메일</th>
                    <th scope="col" data-column="Address"><button class="sort-btn" >거주지<span aria-hidden="true"></span></button></th>
                    <th scope="col" class="no-sort">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
        </div>
        <div id="MembersEditModal" class="modal fade updateMembers" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="MembersModalTitle"></h4>
                        <button type="button" class="btn-close closeModal" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="MembersIdx" name="MembersIdx" class="required-value" value="">
                        <input type="hidden" id="orderIdx" name="orderIdx" class="required-value" value="">
                        <div class="mb-3 row">
                            <label for="UsersIdx" class="col-sm-3 col-form-label">회원ID</label>
                            <div class="col-sm-9">
                                <input type="text" name="UsersIdx" class="form-control" id="UsersIdx" value="" disabled>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputName" class="col-sm-3 col-form-label">이름</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" id="inputName" value="" disabled>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">이메일</label>
                            <div class="col-sm-9">
                                <input type="text" name="email" class="form-control" id="inputEmail" value="">
                            </div>
                        </div>
                        <div class="mb-3 row updateAddress">
                            <label for="inputAddress" class="col-sm-3 col-form-label"><span class="badge bg-danger">필수</span> 거주지</label>
                            <div class="col-sm-9">
                                <select class="state form-control mb-2 required-value" name="state" id="state">
                                    <option value="">시/도 선택</option>
                                </select>
                                <select class="city form-control required-value" name="city" id="city">
                                    <option value="">구/군 선택</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-primary regist-btn" data-target="updateMembers">수정</button>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/abc/Members.js"></script>