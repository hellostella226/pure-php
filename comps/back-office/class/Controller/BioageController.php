<?php

namespace Controller;
use Model\Admin;

class BioageController extends AdminController
{
    function __construct($groupCode)
    {
        parent::__construct($groupCode);
    }

    // get 요청
    function search($page, $request) : void
    {
        try {
            if(isset($request['purpose'])) {
                parent::setParam($request);
                $response = [];
                $param = $this->getParam();
                switch ($request['purpose']) {
                    case 'menu':
                        $response = parent::setMenu();
                        break;
                    case 'Members' :
                        $response = $this->MembersList();
                        break;
                    case 'product' :
                        $response = $this->productList();
                    default :
                        break;
                }
                foreach ($response as $key => $val) {
                    $this->$key = $val;
                }
                echo parent::jsonResponse();
            } else {
                $this->setPage($page);
            }
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();

            echo parent::jsonResponse();
            exit;
        }
    }

    // 회원정보 조회
    function MembersList() : array
    {
        $this->desc = 'MembersList';
        try {
            $admin = new Admin();
            return $admin->MembersList($this->gIdx);
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        }
    }

    function process($request) : void
    {
        $this->desc = 'process';
        try {
            parent::setParam($request);
            $param = parent::getParam();
            switch ($request['purpose']) {
                case 'list' :
                    $this->data['list'] = $this->MembersList();
                    break;
            }
        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            echo $this->jsonResponse();
            exit;
        }
    }

    public function setPage($page): void
    {
        try {
            $isPage = false;
            foreach ($this->navi[$this->productGroupCode] as $item) {
                if($item['id'] === $page) {
                    $isPage = true;
                    break;
                }
            }
            if(!$isPage) {
                throw new \Exception("유효한 경로가 아닙니다. 개발팀에 문의하세요.");
            }

        } catch (\Exception $e) {
            $this->alert($e->getMessage(),'');
            $page = 'error_500.html';
        } finally {
            parent::views($page);
        }
    }
}