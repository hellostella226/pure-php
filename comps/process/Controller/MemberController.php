<?php

namespace Controller;

use Model\MemberStatus;

class MembersController extends BaseController
{
    public function __construct()
    {
    }


    public function userStatusChange()
    {
        $this->isTestCheck();

        if (!isset($_POST['UsersIdx']) || !isset($_POST['ProductIdx'])) {
            $this->error(441, "Invalid params");
            exit;
        }

        $UsersIdx = $_POST['UsersIdx'];
        $processCode = $_POST['ProcessCode'];
        $productIdx = $_POST['ProductIdx'];
        $statusCode = $_POST['StatusCode'];
        $param = [
            'process' => $processCode,
            'UsersIdx' => $UsersIdx,
            'productIdx' => $productIdx,
            "statusCode"=> $statusCode
        ];
        if ($statusCode === '') {
            unset($param['statusCode']);
        }
        if ($processCode === '') {
            unset($param['process']);
        }

        $MemberStatus = new MemberStatus();
        $MemberStatus->updateMemberStatus($param);

        $result = [
            'result' => "success",
            'code' => "0000"
        ];

        echo json_encode($result, true);
    }

}