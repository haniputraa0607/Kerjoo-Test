<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLeave;
use App\Http\Requests\LoginRequest;
use App\Interfaces\TaskRepositoryInterface;
use App\Leave;
use App\LeaveDate;
use App\Repository\TaskRepository\TaskRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LeavesController extends Controller
{
    private $repository;

    public function __construct(TaskRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function annualLeaves(CreateLeave $request)
    {
        $post = $request->all();

        try{
            $encode = base64_encode(fread(fopen($post['file'], "r"), filesize($post['file'])));
        }catch(\Exception $e) {
            return $this->error('Error');
        }
        $originalName = $post['file']->getClientOriginalName();
        if($originalName == ''){
            $ext = 'pdf';
        }else{
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        }

        $decoded = base64_decode($encode);

        // set picture name
        if ($originalName != null) {
            $pictName = $originalName . '.' . $ext;
        } else {
            $pictName = mt_rand(0, 1000) . '' . time() . '.' . $ext;
        }

        // path
        $upload = "img/file/" . $pictName;

        if (env('STORAGE')) {
            $save = Storage::disk(env('STORAGE'))->put($upload, $decoded, 'public');
            if ($save) {
                    $path = $upload;
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to upload file']);
            }
        } else {
            $save = File::put($upload, $decoded);
            if ($save) {
                $path = $upload;
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to upload file']);
            }
        }


        $dataLeave = [
            'id_user' => $request->user()->id,
            'purpose' => $post['purpose'],
            'notes' => $post['notes'],
            'file' => $path
        ];

        DB::beginTransaction();

        $storeLeave = $this->repository->CreateLeave($dataLeave);
        if (!$storeLeave){
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to create Leave']);
        }

        $dataLeaveDate = [];
        foreach($post['date'] ?? [] as $key => $date){
            $dataLeaveDate[] = [
                'id_leave' => $storeLeave['id'],
                'date' => $date,
                'created_at' => date('Y-m-d'),
                'updated_at' => date('Y-m-d')
            ];
        }

        $storeLeaveDate = $this->repository->CreateLeaveDate($dataLeaveDate);
        if (!$storeLeaveDate){
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to create Leave']);
        }

        DB::commit();
        return response()->json(['status' => 'success', 'result' => $storeLeave]);
    }

    public function listAnnualLeaves(Request $request)
    {
        if ($request->user()->role != 'admin'){
            return response()->json(['status' => 'error', 'message' => 'Not Admin']);
        }

        $data = $this->repository->GetLeaveDate();
        if(!$data){
            return response()->json(['status' => 'error', 'message' => 'failed']);
        }

        $response = [];
        foreach($data ?? [] as $key => $leave){

            $dates = [];
            foreach($leave['dates'] ?? [] as $date){
                $dates[] = date('d F Y', strtotime($date['date']));
            }

            $response[] = [
                'id' => $leave['id'],
                'user_name' => $leave['user']['name'],
                'dates' => $dates,
                'purpose' => $leave['purpose'],
                'notes' => $leave['notes'],
                'file' => URL::to('/').'/'.$leave['file']
            ];
        }

        return response()->json(['status' => 'success', 'result' => $response]);

    }

    public function detailAnnualLeaves(Request $request, $id)
    {
        if ($request->user()->role != 'admin'){
            return response()->json(['status' => 'error', 'message' => 'Not Admin']);
        }

        $data = $this->repository->GetLeaveDateById($id);
        if(!$data){
            return response()->json(['status' => 'error', 'message' => 'failed']);
        }

        $dates = [];
        foreach($data['dates'] ?? [] as $date){
            $dates[] = date('d F Y', strtotime($date['date']));
        }

        $response = [
            'id' => $data['id'],
            'user_name' => $data['user']['name'],
            'dates' => $dates,
            'purpose' => $data['purpose'],
            'notes' => $data['notes'],
            'file' => URL::to('/').'/'.$data['file']
        ];

        return response()->json(['status' => 'success', 'result' => $response]);

    }

}
