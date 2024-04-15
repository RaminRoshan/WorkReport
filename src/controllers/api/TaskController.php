<?php

namespace Pishgaman\WorkReport\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\WorkReport\Database\Models\Task;
use Pishgaman\Pishgaman\Database\Models\User\User;
use Pishgaman\Pishgaman\Database\Models\Department\DepartmentUser;
use Hekmatinasser\Verta\Verta;

class TaskController extends Controller
{
    private $validActions = [
        'saveNewTask' , 'getTasksInProgress' , 'getTask' , 'taskDone' , 'deleteTask' , 'saveEditTask' , 'getTasks'
    ];

    protected $validMethods = [
        'GET' => ['getTasksInProgress','getTask','getTasks'], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => ['saveNewTask'], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => ['taskDone','saveEditTask'],
        'DELETE' => ['deleteTask']
    ];

    protected $currentUser;
    protected $logRepository;

    public function __construct(logRepository $logRepository)
    {
        $this->logRepository = $logRepository;
        $this->middleware(CheckMenuAccess::class);
        $this->currentUser = auth()->user();
    }

    public function action(Request $request)
    {
        if ($request->has('action')) {
            $functionName = $request->action;
            $method = $request->method();
            // Log::error('method: ' . $method);

            if ($this->isValidAction($functionName, $method)) {
                return $this->$functionName($request);
            } else {
                return response()->json(['errors' => 'requestNotAllowed'], 422);
            }
        }

        return abort(404);
    }

    private function isValidAction($functionName, $method)
    {
        return in_array($functionName, $this->validActions) && in_array($functionName, $this->validMethods[$method]);
    }

    private function isAdmin()
    {
        $currentUser = auth()->user();
        return (DepartmentUser::where([['user_id',$currentUser->id],['job_position','like','admin']])->count() > 0) ? true : false;
    }

    public function getTasks(Request $request)
    {
        if (!$this->isValidAction('getTasks', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
    
        $currentUser = auth()->user();
        $isAdmin = $this->isAdmin();
    
        if ($isAdmin) {
            $departmentUser = DepartmentUser::where('user_id', $currentUser->id)->first();
            $employees = DepartmentUser::where('department_id', $departmentUser->department_id)->pluck('user_id')->toArray();
            $tasks = Task::whereIn('employee_id', $employees);
        } else {
            $tasks = Task::where('employee_id', $currentUser->id);
        }
    
        if($request->status ?? false)
        {
            if($request->status == 'DelayToDo')
            {
                $tasks = $tasks->where('status','InProgress');
                $tasks = $tasks->whereDate('end_date', '<', now());
            }
            else if($request->status == 'lock')
            {
                $tasks = $tasks->where('lock','1');
            }
            else
            {
                $tasks = $tasks->where('status',$request->status);
            }
        }

        $tasks = $tasks->orderBy('start_date')->with('employee:id,username')->paginate(10);
        return response()->json(['Tasks' => $tasks], 200);
    }

    public function saveEditTask($request)
    {
        if (!$this->isValidAction('saveEditTask', 'PUT')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'id' => 'required',
            'eventTitle' => 'required',
            'eventStartDate' => 'required|date',
            'eventEndDate' => 'required|date',
            'id.required' => 'وظیفه به درستی انتخاب نشده است',
        ], [
            'eventStartDate.after_or_equal' => 'فیلد تاریخ شروع باید برابر با امروز یا بزرگتر از امروز باشد.',
        ]);  
        
        $currentUser = auth()->user();
        $today = date('Y-m-d 00:00:00');

        if ($today > $request->eventStartDate) {
            $msg = "تاریخ شروع کوچکتر از تاریخ امروز است.";
            return response()->json(['errors' => $msg], 422);
        } 

        if ($today > $request->eventEndDate) {
            $msg = "تاریخ پایان کوچکتر از تاریخ امروز است.";
            return response()->json(['errors' => $msg], 422);
        } 
        
        if ($request->eventStartDate > $request->eventEndDate) {
            $msg = "تاریخ پایان کوچکتر از تاریخ شروع است.";
            return response()->json(['errors' => $msg], 422);
        } 
        
        $Task = Task::where([['employee_id',$currentUser->id],['id',$request->id]])->first();

        if($Task->lock == 1)
        {
            return response()->json(['errors' => 'شما اجازه ویرایش این وظیفه را ندارید'], 422); 
        }

        $timestamp1 = strtotime($date);
        $timestamp2 = strtotime($request->eventEndDate);
        $date_only1 = date("Y-m-d", $timestamp1);
        $date_only2 = date("Y-m-d", $timestamp2);

        if($request->allDay)
        {
            $start_date = $date_only1 . ' 00:00:00';
            $end_date = $date_only2 . ' 23:59:59';
        }
        else
        {
            $start_date = $request->eventStartDate;
            $end_date = $request->eventEndDate;            
        }

        $Task->employee_id = $currentUser->id;
        $Task->title = $request->eventTitle;
        $Task->status = 'InProgress';
        $Task->level = $request->eventLevel ?? 0;
        $Task->start_date = $start_date;
        $Task->end_date = $end_date;
        $Task->all_day = $request->allDay ?? false;
        $Task->location = $request->eventLocation;
        $Task->description = $request->eventDescription;
        $Task->save();  
        
        return response()->json([], 200);

    }

    public function deleteTask($request)
    {
        if (!$this->isValidAction('deleteTask', 'DELETE')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'id' => 'required',
        ], [
            'id.required' => 'وظیفه به درستی انتخاب نشده است',
        ]);  
        
        $currentUser = auth()->user();
        $isAdmin = $this->isAdmin();

        if($isAdmin)
        {
            $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
            $Employees = DepartmentUser::where('department_id',$DepartmentUser->department_id )->pluck('user_id'); 
            $Task = Task::where([['id',$request->id]])->whereIn('employee_id',$Employees)->delete();
        }
        else
        {
            $Task = Task::where([['employee_id',$currentUser->id],['id',$request->id],['lock','0']])->delete();
        }

        

        if ($Task === 1) {
            return response()->json([], 200); 
        } else {
            return response()->json(['errors' => 'شما اجازه حذف این وظیفه را ندارید'], 422); 
        }
    }

    public function taskDone($request)
    {
        if (!$this->isValidAction('taskDone', 'PUT')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'taskId' => 'required',
        ], [
            'taskId.required' => 'وظیفه به درستی انتخاب نشده است',
        ]);  
        
        $currentUser = auth()->user();

        $Task = Task::where([['employee_id',$currentUser->id],['id',$request->taskId]])->update(['status'=>'taskDone','done_at'=>date('Y-m-d H:i:s')]);
        
        return response()->json([], 200);
    }

    public function getTask($request)
    {
        if (!$this->isValidAction('getTask', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'id' => 'required',
        ], [
            'id.required' => 'وظیفه به درستی انتخاب نشده است',
        ]);

        $currentUser = auth()->user();
        $isAdmin = $this->isAdmin();

        if($isAdmin)
        {
            $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
            $Employees = DepartmentUser::where('department_id',$DepartmentUser->department_id )->pluck('user_id'); 
            $Task = Task::where([['id',$request->id]])->whereIn('employee_id',$Employees);
        }
        else
        {
            $Task = Task::where([['employee_id',$currentUser->id],['id',$request->id]]);
        }

        $Task = $Task->first();
        
        return response()->json(['Task'=>$Task], 200);       
    }

    public function getTasksInProgress($request)
    {
        if (!$this->isValidAction('getTasksInProgress', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $isAdmin = $this->isAdmin();

        if($isAdmin)
        {
            $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
            $Employees = DepartmentUser::where('department_id',$DepartmentUser->department_id )->pluck('user_id'); 
            $TasksInProgress = Task::where([['status','like','InProgress']])->whereIn('employee_id',$Employees);
        }
        else
        {
            $TasksInProgress = Task::where([['employee_id',$currentUser->id],['status','like','InProgress']]);
        }
        
        $TasksInProgress = $TasksInProgress->with(['employee:id,username'])->get();
        return response()->json(['TasksInProgress'=>$TasksInProgress,'isAdmin'=>$isAdmin], 200);       
    }

    public function saveNewTask($request)
    {
        if (!$this->isValidAction('saveNewTask', 'POST')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'eventTitle' => 'required',
            'eventStartDate' => 'required|date',
            'eventEndDate' => 'required|date',
        ], [
            'eventStartDate.after_or_equal' => 'فیلد تاریخ شروع باید برابر با امروز یا بزرگتر از امروز باشد.',
        ]);

        $currentUser = auth()->user();
        $today = date('Y-m-d 00:00:00');

        if ($today > $request->eventStartDate) {
            $msg = "تاریخ شروع کوچکتر از تاریخ امروز است.";
            return response()->json(['errors' => $msg], 422);
        } 

        if ($today > $request->eventEndDate) {
            $msg = "تاریخ پایان کوچکتر از تاریخ امروز است.";
            return response()->json(['errors' => $msg], 422);
        } 
        
        if ($request->eventStartDate > $request->eventEndDate) {
            $msg = "تاریخ پایان کوچکتر از تاریخ شروع است.";
            return response()->json(['errors' => $msg], 422);
        } 

        $date = $request->eventStartDate;
        $EmployeeId = $currentUser->id;
        $lock = 0;

        if($request->employee_id)
        {
            $User = User::where('username',$request->employee_id);
            if($User->count() == 0)
            {
                $msg = "کاربر مسئول را به درستی وارد کنید";
                return response()->json(['errors' => $msg], 422);
            }

            $User = $User->first();
            $lock = 1;
            $EmployeeId = $User->id;
        }

        if($request->repTask)
        {
            while ($date <= $request->eventEndDate) {
                $timestamp1 = strtotime($date);
                $timestamp2 = strtotime($request->eventEndDate);
                $date_only1 = date("Y-m-d", $timestamp1);
                $time_only2 = date("H:i:s", $timestamp2);

                if($request->allDay ?? false)
                {
                    $time_only1 = date("H:i:s", $timestamp1);
                    $start_date = $date_only1 . ' 00:00:00';
                    $end_date = $date_only1 . ' 23:59:59';
                }
                else
                {
                    $start_date = $date;
                    $end_date = $date_only1 . ' ' . $time_only2;
                }
                $Task = new Task;
                $Task->employee_id = $EmployeeId ;
                $Task->lock = $lock ;
                $Task->title = $request->eventTitle;
                $Task->status = 'InProgress';
                $Task->level = $request->eventLevel ?? 0;
                $Task->start_date = $start_date;
                $Task->end_date = $end_date;
                $Task->all_day = $request->allDay ?? false;
                $Task->location = $request->eventLocation;
                $Task->description = $request->eventDescription;
                $Task->save();
                $date = date('Y-m-d H:i:s',strtotime($date . ' + 1 day'));
            }
        }
        else
        {
            $timestamp1 = strtotime($date);
            $timestamp2 = strtotime($request->eventEndDate);
            $date_only1 = date("Y-m-d", $timestamp1);
            $date_only2 = date("Y-m-d", $timestamp2);

            if($request->allDay)
            {
                $start_date = $date_only1 . ' 00:00:00';
                $end_date = $date_only2 . ' 23:59:59';
            }
            else
            {
                $start_date = $request->eventStartDate;
                $end_date = $request->eventEndDate;            
            }

            $Task = new Task;
            $Task->employee_id = $EmployeeId ;
            $Task->lock = $lock ;
            $Task->title = $request->eventTitle;
            $Task->status = 'InProgress';
            $Task->level = $request->eventLevel ?? 0;
            $Task->start_date = $start_date;
            $Task->end_date = $end_date;
            $Task->all_day = $request->allDay ?? false;
            $Task->location = $request->eventLocation;
            $Task->description = $request->eventDescription;
            $Task->save();
        }


        return response()->json([], 200);
    }
}
