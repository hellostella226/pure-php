<?php

namespace Controller;

class GeneticController extends ServiceController
{
    function __construct() {
        parent::__construct();
    }

    //프로세스 처리부
    public function requestProcess()
    {
        try {
            $param = parent::getParam();
            $response = [];
            switch ($param['process']) {
                case 'checkUserStatus' :
                    $response = parent::checkUserStatus($param);
                    break;
                case 'MemberStatus' :
                    $response = parent::MemberStatus($param);
                    break;
                default :
                    break;
            }
            foreach ($response as $key => $val) {
                $this->$key = $val;
            }

        } catch (\Exception $e) {
            $this->code = $e->getCode();
            $this->msg = $e->getMessage();
        } finally {
            echo parent::jsonResponse();
            exit;
        }
    }

    /**
    * @date 2023-05-09
    * @brief View 페이지 할당
    * @author hellostellaa
    */
    function views($views, $data) {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/process/View/'.$views;
        if(file_exists($filename)) {
            require_once $filename;
        }
    }

    function link($views) {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/process/View/'.$views;
        if(file_exists($filename)) {
            echo "<script type='text/javascript'>
                    location.href='http://ld.***.com/process/View/error_500.html';
              </script>";
            exit;
        }
    }

    /**
    * @date 2023-05-09
    * @brief 얼럿 후 종료
    * @author hellostellaa
    * @param msg: 얼럿메시지, redirect: 리디렉션 경로 (옵션)
    */
    function alert($msg, $redirect) {
        echo "<script type='text/javascript'>
                alert('".$msg."');
              </script>";
        exit;
    }
}