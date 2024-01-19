<?php

namespace App\Repositories;

use App\Interfaces\TaskRepositoryInterface;
use App\Leave;
use App\LeaveDate;
use Illuminate\Http\Request;

class TaskRepository implements TaskRepositoryInterface
{
    public function CreateLeave($request)
    {
        $storeLeave = Leave::create($request);
        if($storeLeave){
            return $storeLeave;
        }else{
            return false;
        }
    }

    public function CreateLeaveDate($request)
    {
        $storeLeaveDate = LeaveDate::insert($request);
        if($storeLeaveDate){
            return $storeLeaveDate;
        }else{
            return false;
        }
    }

    public function GetLeaveDate()
    {
        return $data = Leave::with(['user','dates'])->get()->all();
    }

    public function GetLeaveDateById($id)
    {
        return $data = Leave::with(['user','dates'])->where('id', $id)->get()->first();
    }
}
