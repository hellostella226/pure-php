<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">상담사 등록 관리</h3>
    </div>
    <div class="form-group">
        <hr>
        <div class="container-fluid table-responsive">
            <div class="row">
                <!-- 검색영역 -->
                <div class="row">
                    <div class="col-md-3 pull-right">
                        <div class="btn excel-down-btn" data-target="consultantData" style="color:black;border:1px solid black">excel 다운로드</div>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="col-md-2">
                        <div class="input-group">검색기간
                            <input type="date" id="startDate" class="form-control form-control-sm">&nbsp;~&nbsp;
                            <input type="date" id="endDate" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-1" id="searchDiv">
                        <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                            <option value="">검색컬럼 선택</option>
                            <option value="scm.ServiceCompanyName">사용처</option>
                            <option value="ccm.CCGroup">회사명</option>
                            <option value="ccm.ClientCustomerName">상담사</option>
                            <option value="ccm.CCTel">전화번호</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="row">
                            <label for="searchValue"></label>
                            <input type="text" class="form-control form-control-sm col" name="searchValue"
                                   id="searchValue" value="">
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
                    <th scope="col">상담사계정코드</th>
                    <th scope="col">등록일자</th>
                    <th scope="col">최종수정일자</th>
                    <th scope="col">등록방식</th>
                    <th scope="col">사용처</th>
                    <th scope="col">회사명</th>
                    <th scope="col">상담사</th>
                    <th scope="col">전화번호</th>
                    <th scope="col">결제수량</th>
                    <th scope="col">결제잔량</th>
                    <th scope="col">무료지급량</th>
                    <th scope="col">무료잔량</th>
                    <th scope="col">질환서비스url</th>
                    <th scope="col">수정</th>
                    <th scope="col">사용여부</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex justify-content-end">
                <div>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#registConsultant">단건등록</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#companyInsert">대량등록</button>
                </div>
            </div>
        </div>
    </div>
    <div id="updateFreeTicket" class="modal fade updateFreeTicket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="allocationModalTitle">수정</h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="ClientControlIdx" name="ClientControlIdx" value="">
                        <input type="hidden" id="SaleGoodsIdx" name="SaleGoodsIdx" value="">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button style="width:50%;height:60px;" class="nav-link active update-data" id="update-client-tab" data-bs-toggle="tab" data-bs-target="#update-client" type="button" role="tab" aria-controls="update-client" aria-selected="true">정보수정</button>
                                <button style="width:50%;height:60px;" class="nav-link update-data" id="update-ticket-tab" data-bs-toggle="tab" data-bs-target="#update-ticket" type="button" role="tab" aria-controls="update-ticket" aria-selected="false">잔량수정</button>
                            </div>
                        </nav>
                        <div class="row" style="margin-top: 20px;">
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="update-client" role="tabpanel" aria-labelledby="update-client-tab">
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>상담사계정코드</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="clientCustomerCodeClient"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>사용처</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="serviceCompanyNameClient"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>회사명</p>
                                        <p class="col-sm-6">
                                            <input class="form-control" id="cCGroupClientValue" list="cCGroupClient" name="cCGroupsClient" autocomplete="off" name="buyr_company" value="" required>
                                            <datalist id="cCGroupClient">
                                                <option value="(주)씨제이이엔엠"></option>
                                                <option value="(주)글로벌금융판매"></option>
                                                <option value="(주)드림라이프"></option>
                                                <option value="(주)리더스에셋어드바이저"></option>
                                                <option value="(주)리치앤코(Rich&Co.)"></option>
                                                <option value="(주)밸류마크"></option>
                                                <option value="(주)사랑모아금융서비스"></option>
                                                <option value="(주)삼성생명금융서비스**대리점"></option>
                                                <option value="(주)삼성화재금융서비스**대리점"></option>
                                                <option value="(주)세안뱅크"></option>
                                                <option value="(주)스카이블루에셋"></option>
                                                <option value="(주)아이에프씨그룹"></option>
                                                <option value="(주)에이플러스에셋어드바이저"></option>
                                                <option value="(주)에즈금융서비스"></option>
                                                <option value="(주)에프엠에셋"></option>
                                                <option value="(주)엠금융서비스"></option>
                                                <option value="(주)영진에셋"></option>
                                                <option value="(주)우리홈쇼핑"></option>
                                                <option value="(주)유어즈에셋**대리점"></option>
                                                <option value="(주)인슈코아"></option>
                                                <option value="(주)케이엠아이에셋"></option>
                                                <option value="(주)현대홈쇼핑"></option>
                                                <option value="AIA생명"></option>
                                                <option value="AIG손보"></option>
                                                <option value="AXA손해**"></option>
                                                <option value="BNP파리바카디프생명"></option>
                                                <option value="DAS법률비용**"></option>
                                                <option value="DB생명"></option>
                                                <option value="DB손보"></option>
                                                <option value="DGB생명"></option>
                                                <option value="IBK연금**"></option>
                                                <option value="ICG"></option>
                                                <option value="KB생명"></option>
                                                <option value="KB손해**"></option>
                                                <option value="KDB생명"></option>
                                                <option value="KFG㈜"></option>
                                                <option value="MG손해**"></option>
                                                <option value="㈜인포유금융서비스"></option>
                                                <option value="교보라이프플래닛생명"></option>
                                                <option value="교보생명"></option>
                                                <option value="농협생명"></option>
                                                <option value="농협손해**"></option>
                                                <option value="더베스트금융서비스 주식회사"></option>
                                                <option value="더블유에셋(주)"></option>
                                                <option value="더좋은**금융(주)"></option>
                                                <option value="동양생명**"></option>
                                                <option value="디비금융서비스(주)"></option>
                                                <option value="디비엠앤에스(주)"></option>
                                                <option value="라이나생명"></option>
                                                <option value="롯데손보"></option>
                                                <option value="리더스금융판매(주)"></option>
                                                <option value="메가(주)"></option>
                                                <option value="메리츠화재"></option>
                                                <option value="메트라이프금융서비스 **대리점"></option>
                                                <option value="메트라이프생명"></option>
                                                <option value="무지개세무회계연구소 주식회사"></option>
                                                <option value="미래에셋금융서비스(주)"></option>
                                                <option value="미래에셋생명"></option>
                                                <option value="삼성생명"></option>
                                                <option value="삼성화재"></option>
                                                <option value="서울법인재무설계센터㈜"></option>
                                                <option value="서울보증**"></option>
                                                <option value="신한EZ손해**"></option>
                                                <option value="신한금융플러스 주식회사"></option>
                                                <option value="신한라이프생명"></option>
                                                <option value="아이에프에이(주)"></option>
                                                <option value="에스케이엠앤서비스(주)"></option>
                                                <option value="에이비에이금융서비스 유한회사"></option>
                                                <option value="에이스손보"></option>
                                                <option value="에이아이지어드바이저 주식회사"></option>
                                                <option value="에이원금융판매주식회사"></option>
                                                <option value="엑셀금융서비스(주)"></option>
                                                <option value="유퍼스트**마케팅(주)"></option>
                                                <option value="인카금융서비스(주)"></option>
                                                <option value="주식회사 뉴니케 **대리점"></option>
                                                <option value="주식회사 더탑아이앤아이"></option>
                                                <option value="주식회사 메가인포에셋"></option>
                                                <option value="주식회사 아너스금융서비스"></option>
                                                <option value="주식회사 어센틱금융그룹"></option>
                                                <option value="주식회사 에인스금융서비스"></option>
                                                <option value="주식회사 지금융코리아"></option>
                                                <option value="주식회사 지에스리테일 **대리점"></option>
                                                <option value="주식회사 케이금융파트너스"></option>
                                                <option value="주식회사원금융서비스"></option>
                                                <option value="지에이스타금융서비스 주식회사"></option>
                                                <option value="지에이코리아주식회사"></option>
                                                <option value="처브라이프생명"></option>
                                                <option value="캐롯손해**"></option>
                                                <option value="케이비라이프파트너스"></option>
                                                <option value="케이지에이에셋(주)"></option>
                                                <option value="코리안리"></option>
                                                <option value="키움에셋플래너주식회사"></option>
                                                <option value="푸르덴셜생명"></option>
                                                <option value="푸본현대생명"></option>
                                                <option value="프라임에셋(주)"></option>
                                                <option value="피플라이프(주)"></option>
                                                <option value="하나생명"></option>
                                                <option value="하나손해**"></option>
                                                <option value="한국**금융(주)"></option>
                                                <option value="한화라이프랩(주)"></option>
                                                <option value="한화생명"></option>
                                                <option value="한화생명금융서비스㈜"></option>
                                                <option value="한화손해**"></option>
                                                <option value="현대해상"></option>
                                                <option value="흥국생명"></option>
                                                <option value="흥국화재"></option>
                                            </datalist>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>상담사</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="clientCustomerNameClient"
                                                   class="form-control form-control-sm" value=""/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>전화번호</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="cCTelClient"
                                                   class="form-control form-control-sm" value=""/>
                                        </p>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="update-ticket" role="tabpanel" aria-labelledby="update-ticket-tab">
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>상담사계정코드</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="clientCustomerCode"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>사용처</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="serviceCompanyName"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>회사명</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="cCGroup"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>상담사</p>
                                        <p class="col-sm-6">
                                            <input type="text" id="clientCustomerName"
                                                   class="form-control form-control-sm" value="" disabled/>
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>현재 무료잔량</p>
                                        <p class="col-sm-6">
                                            <input type="number" id="oldIssuedCount"
                                                   class="form-control form-control-sm number" value="" disabled />
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge"></span>수정 무료잔량</p>
                                        <p class="col-sm-6">
                                            <input type="number" id="updateCount"
                                                   class="form-control form-control-sm" value="0" maxlength="3" />
                                        </p>
                                    </div>
                                    <div class="mb-3 row">
                                        <p class="col-sm-6"><span class="badge bg-danger">필수</span>최종 무료잔량</p>
                                        <p class="col-sm-6">
                                            <input type="number" id="issuedCount" name="issuedCount"
                                                   class="form-control form-control-sm number" value="" disabled />
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="updateTicketCount" data-target="updateFreeTicket">수정</button>
                </div>
            </div>
        </div>
    </div>
    <div id="registConsultant" class="modal fade registConsultant" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="allocationModalTitle">단건 등록</h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="ClientControlIdx" name="ClientControlIdx" value="">
                        <input type="hidden" name="category" value="I">
                        <input type="hidden" name="qRurl" value="">

                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span>사용처</p>
                            <p class="col-sm-8">
                                <select class="form-select form-select-sm required-value" id="serviceCompany" name="ServiceControlIdx">
                                    <option value="">선택</option>
                                </select>
                            </p>
                        </div>

                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span>회사명</p>
                            <p class="col-sm-8">
                                <input class="form-control required-value" list="cCGroups" name="cCGroup" autocomplete="off" name="buyr_company" value="" required>
                                <datalist id="cCGroups">
                                    <option value="(주)씨제이이엔엠"></option>
                                    <option value="(주)글로벌금융판매"></option>
                                    <option value="(주)드림라이프"></option>
                                    <option value="(주)리더스에셋어드바이저"></option>
                                    <option value="(주)리치앤코(Rich&Co.)"></option>
                                    <option value="(주)밸류마크"></option>
                                    <option value="(주)사랑모아금융서비스"></option>
                                    <option value="(주)삼성생명금융서비스**대리점"></option>
                                    <option value="(주)삼성화재금융서비스**대리점"></option>
                                    <option value="(주)세안뱅크"></option>
                                    <option value="(주)스카이블루에셋"></option>
                                    <option value="(주)아이에프씨그룹"></option>
                                    <option value="(주)에이플러스에셋어드바이저"></option>
                                    <option value="(주)에즈금융서비스"></option>
                                    <option value="(주)에프엠에셋"></option>
                                    <option value="(주)엠금융서비스"></option>
                                    <option value="(주)영진에셋"></option>
                                    <option value="(주)우리홈쇼핑"></option>
                                    <option value="(주)유어즈에셋**대리점"></option>
                                    <option value="(주)인슈코아"></option>
                                    <option value="(주)케이엠아이에셋"></option>
                                    <option value="(주)현대홈쇼핑"></option>
                                    <option value="AIA생명"></option>
                                    <option value="AIG손보"></option>
                                    <option value="AXA손해**"></option>
                                    <option value="BNP파리바카디프생명"></option>
                                    <option value="DAS법률비용**"></option>
                                    <option value="DB생명"></option>
                                    <option value="DB손보"></option>
                                    <option value="DGB생명"></option>
                                    <option value="IBK연금**"></option>
                                    <option value="ICG"></option>
                                    <option value="KB생명"></option>
                                    <option value="KB손해**"></option>
                                    <option value="KDB생명"></option>
                                    <option value="KFG㈜"></option>
                                    <option value="MG손해**"></option>
                                    <option value="㈜인포유금융서비스"></option>
                                    <option value="교보라이프플래닛생명"></option>
                                    <option value="교보생명"></option>
                                    <option value="농협생명"></option>
                                    <option value="농협손해**"></option>
                                    <option value="더베스트금융서비스 주식회사"></option>
                                    <option value="더블유에셋(주)"></option>
                                    <option value="더좋은**금융(주)"></option>
                                    <option value="동양생명**"></option>
                                    <option value="디비금융서비스(주)"></option>
                                    <option value="디비엠앤에스(주)"></option>
                                    <option value="라이나생명"></option>
                                    <option value="롯데손보"></option>
                                    <option value="리더스금융판매(주)"></option>
                                    <option value="메가(주)"></option>
                                    <option value="메리츠화재"></option>
                                    <option value="메트라이프금융서비스 **대리점"></option>
                                    <option value="메트라이프생명"></option>
                                    <option value="무지개세무회계연구소 주식회사"></option>
                                    <option value="미래에셋금융서비스(주)"></option>
                                    <option value="미래에셋생명"></option>
                                    <option value="삼성생명"></option>
                                    <option value="삼성화재"></option>
                                    <option value="서울법인재무설계센터㈜"></option>
                                    <option value="서울보증**"></option>
                                    <option value="신한EZ손해**"></option>
                                    <option value="신한금융플러스 주식회사"></option>
                                    <option value="신한라이프생명"></option>
                                    <option value="아이에프에이(주)"></option>
                                    <option value="에스케이엠앤서비스(주)"></option>
                                    <option value="에이비에이금융서비스 유한회사"></option>
                                    <option value="에이스손보"></option>
                                    <option value="에이아이지어드바이저 주식회사"></option>
                                    <option value="에이원금융판매주식회사"></option>
                                    <option value="엑셀금융서비스(주)"></option>
                                    <option value="유퍼스트**마케팅(주)"></option>
                                    <option value="인카금융서비스(주)"></option>
                                    <option value="주식회사 뉴니케 **대리점"></option>
                                    <option value="주식회사 더탑아이앤아이"></option>
                                    <option value="주식회사 메가인포에셋"></option>
                                    <option value="주식회사 아너스금융서비스"></option>
                                    <option value="주식회사 어센틱금융그룹"></option>
                                    <option value="주식회사 에인스금융서비스"></option>
                                    <option value="주식회사 지금융코리아"></option>
                                    <option value="주식회사 지에스리테일 **대리점"></option>
                                    <option value="주식회사 케이금융파트너스"></option>
                                    <option value="주식회사원금융서비스"></option>
                                    <option value="지에이스타금융서비스 주식회사"></option>
                                    <option value="지에이코리아주식회사"></option>
                                    <option value="처브라이프생명"></option>
                                    <option value="캐롯손해**"></option>
                                    <option value="케이비라이프파트너스"></option>
                                    <option value="케이지에이에셋(주)"></option>
                                    <option value="코리안리"></option>
                                    <option value="키움에셋플래너주식회사"></option>
                                    <option value="푸르덴셜생명"></option>
                                    <option value="푸본현대생명"></option>
                                    <option value="프라임에셋(주)"></option>
                                    <option value="피플라이프(주)"></option>
                                    <option value="하나생명"></option>
                                    <option value="하나손해**"></option>
                                    <option value="한국**금융(주)"></option>
                                    <option value="한화라이프랩(주)"></option>
                                    <option value="한화생명"></option>
                                    <option value="한화생명금융서비스㈜"></option>
                                    <option value="한화손해**"></option>
                                    <option value="현대해상"></option>
                                    <option value="흥국생명"></option>
                                    <option value="흥국화재"></option>
                                </datalist>
                            </p>
                        </div>

                        <input type="hidden" name="cCManager" id= "cCManager" value="" />
                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span>상담사</p>
                            <p class="col-sm-8">
                                <input type="text" id="clientCustomerName" name="clientCustomerName"
                                       class="form-control form-control-sm required-value" value="" maxlength="30" />
                            </p>
                        </div>

                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span>전화번호</p>
                            <p class="col-sm-8">
                                <input type="text" id="cCTel" name="cCTel"
                                       class="form-control form-control-sm required-value number" value="" maxlength="12" />
                            </p>
                        </div>

                        <div class="mb-3 row">
                            <p class="col-sm-4"><span class="badge">선택</span>무료지급수량</p>
                            <p class="col-sm-8">
                                <input type="number" id="serveCount" name="serveCount"
                                       class="form-control form-control-sm number" value="" maxlength="12" />
                            </p>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="registConsultant">단건등록</button>
                </div>
            </div>
        </div>
    </div>
    <div id="companyInsert" class="modal fade uploadOfferCompanyDb" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">상담사 대량 등록</h5>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form id="uploadCompanyDbForm" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div>
                            <div class="btn btn-sm" id="sampleDownBtn" data-filename="consultant_upload_sample.xlsx"
                                 data-downloadname="상담사_등록_양식.xlsx" style="background-color: #f3969a">* 대량등록 양식 다운</div>
                        </div>
                        <div class="row m-auto p-1">
                            <label for="companyFile">엑셀파일 선택(xlsx)</label>
                            <input type="file" id="companyFile" name="companyFile" class="form-control" value=""
                                   accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary excel-btn" data-target="uploadOfferCompanyDb">업로드
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/offerabc/company.js?v1"></script>
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

