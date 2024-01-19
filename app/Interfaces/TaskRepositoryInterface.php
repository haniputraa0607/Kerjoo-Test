<?php

namespace App\Interfaces;

interface TaskRepositoryInterface
{
    public function CreateLeave($request);
    public function CreateLeaveDate($request);
    public function GetLeaveDate();
    public function GetLeaveDateById($id);
}
