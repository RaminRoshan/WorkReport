<?php

namespace Pishgaman\WorkReport\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\WorkReport\Database\Repository\WorkReportRepository;
use Pishgaman\WorkReport\Database\Models\WorkReport;
use Pishgaman\WorkReport\Database\Models\Project;
use App\Http\Controllers\Controller;
use Pishgaman\WorkReport\Database\Models\Newsletter;
use Pishgaman\WorkReport\Database\Models\WorkPoint;
use Pishgaman\Pishgaman\Database\Models\User\User;
use Pishgaman\Pishgaman\Database\Models\Department\DepartmentUser;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Hekmatinasser\Verta\Verta;
use Log;
use DB;

class WorkReportController extends Controller
{
    private $validActions = [
        'getWorkList',
        'saveNewWorkReport',
        'deleteWorkReport',
        'updateWorkReport',
        'getNewsletter',
        'userStatistics',
        'getStatistics',
        'getSetting',
        'saveNewProject',
        'deleteProject',
        'saveNewSelectItem',
        'getSelectItem',
        'getProjectForShow',
        'getProjectsForShow',
        'getProjectItemsForShow',
        'editProject'
    ];

    protected $validMethods = [
        'GET' => ['getWorkList','getNewsletter','userStatistics','getStatistics','getSetting','getSelectItem','getProjectForShow','getProjectsForShow','getProjectItemsForShow'], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => ['saveNewWorkReport','saveNewProject','saveNewSelectItem'], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => ['updateWorkReport','editProject'],
        'DELETE' => ['deleteWorkReport','deleteProject']
    ];

    protected $user;
    protected $logRepository;
    protected $WorkReportRepository;
    public function __construct(logRepository $logRepository,WorkReportRepository $WorkReportRepository)
    {
        $this->logRepository = $logRepository;
        $this->WorkReportRepository = $WorkReportRepository;
        $this->middleware(CheckMenuAccess::class);
        $this->user = auth()->user();
    }

    public function action(Request $request)
    {
        if ($request->has('action')) {
            $functionName = $request->action;
            $method = $request->method();

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

    public function getSelectItem(Request $request)
    {
        if (!$this->isValidAction('getSelectItem', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $subSelectItem = Project::where([['department_id',$departmentId],['pid',$request->id]])->get();

        return response()->json(['subSelectItem'=>$subSelectItem], 200); 
    }

    public function saveNewSelectItem(Request $request)
    {
        if (!$this->isValidAction('saveNewSelectItem', 'POST')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $validatedData = $request->validate([
            'select_item_id' => 'required',
            'select_name' => 'required',
        ], [
            'select_item_id.required' => 'آیتم به درستی انتخاب نشده است',
            'select_name.required' => 'نام نتیجه را وارد کنید'
        ]);    
        
        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $Projects = new Project;
        $Projects->pid = $request->select_item_id;
        $Projects->department_id = $departmentId;
        $Projects->name = $request->select_name;
        $Projects->save();  
        
        return response()->json([], 200); 

    }    

    public function deleteProject(Request $request)
    {
        if (!$this->isValidAction('deleteProject', 'DELETE')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;
        
        $Project = Project::where([['department_id',$departmentId],['id',$request->id ?? 0]])->delete();

        if ($Project === 1) {
            return response()->json([], 200); 
        } else {
            return response()->json(['errors' => 'شما اجازه حذف این پروژه را ندارید'], 422); 
        }        
    }

    public function getSetting(Request $request)
    {
        if (!$this->isValidAction('getSetting', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
        
        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $Projects = Project::where([['department_id',$departmentId],['pid',null]])->get();
        $selectedItem = Project::where([['department_id',$departmentId],['result_type','select'],['pid',null]])->get();

        return response()->json(['Projects' => $Projects,'selectedItem'=>$selectedItem], 200); 
    }

    public function saveNewProject(Request $request)
    {
        if (!$this->isValidAction('saveNewProject', 'POST')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
        
        $validatedData = $request->validate([
            'project_name' => 'required',
            'project_result' => 'required',
        ], [
            'name.required' => 'عنوان پروژه الزامی است',
            'project_result.required' => 'نوع خروجی پروژه الزامی است'
        ]); 

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $Projects = new Project;
        $Projects->department_id = $departmentId;
        $Projects->name = $request->project_name;
        $Projects->en_name = $request->project_en_name;
        $Projects->icon = $request->project_icon;
        $Projects->result_type = $request->project_result;
        $Projects->save();

        return response()->json([], 200); 
    }

    public function editProject(Request $request)
    {
        if (!$this->isValidAction('editProject', 'PUT')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
        
        $validatedData = $request->validate([
            'project_name' => 'required',
            'project_result' => 'required',
        ], [
            'name.required' => 'عنوان پروژه الزامی است',
            'project_result.required' => 'نوع خروجی پروژه الزامی است'
        ]); 

        $Projects = Project::where('id',$request->proId)->first();
        $Projects->name = $request->project_name;
        $Projects->en_name = $request->project_en_name;
        $Projects->icon = $request->project_icon;
        $Projects->result_type = $request->project_result;
        $Projects->save();

        return response()->json([], 200); 
    }
    
    public function getStatistics(Request $request)
    {
        $currentUser = auth()->user();

        $now = new Verta();

        $currentYear = $now->year;
        $currentMonth = $now->month;
        
        $firstDayOfMonth = Verta::parse("{$currentYear}-{$currentMonth}-01");
        $lastDayOfMonth = $firstDayOfMonth->addMonth()->subDay();
        
        $startDate = $firstDayOfMonth->DateTime()->format('Y-m-d');
        $endDate = $lastDayOfMonth->DateTime()->format('Y-m-d');
        
        // $startDate = ($request->date_start == '') ? $startDate : Verta::parse($request->date_start)->DateTime();
        // $endDate = ($request->date_end == '') ? $endDate : Verta::parse($request->date_end)->DateTime();

        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $allWorkReport = WorkReport::whereBetween('date', [$startDate, $endDate])
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();
                 
        $identification1 = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','شناسایی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();  

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "شناسایی"],
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],
            'sum' => [
                ['column' => 'outcome', 'alias' => 'sum_outcome'],
            ],            
            'get' => true,
        ];
       
        $identificationSum = $this->WorkReportRepository->Get($options)->first()->sum_outcome; 
        
        $findingPeople = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','سوژه‌یابی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "سوژه‌یابی"],
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],
            'sum' => [
                ['column' => 'outcome', 'alias' => 'sum_outcome'],
            ],            
            'get' => true,
        ];
       
        $findingPeopleSum = $this->WorkReportRepository->Get($options)->first()->sum_outcome; 

        $Documentation = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','مستندسازی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "مستندسازی"],
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],
            'sum' => [
                ['column' => 'outcome', 'alias' => 'sum_outcome'],
            ],            
            'get' => true,
        ];
       
        $DocumentationSum = $this->WorkReportRepository->Get($options)->first()->sum_outcome;  

        $sendNews = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','ارسال خبر')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $writeBulltan = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','ارسال خبرنامه')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $sendNewspaper = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','ارسال بصر')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $programDevelop = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','برنامه نویسی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();
        
        $teaching = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','آموزش')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $other = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','سایر')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();
                
        $translate = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','ترجمه')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();        

        $works = [
            'findingPeople'=>$findingPeople,
            'Documentation'=>$Documentation,
            'sendNews'=>$sendNews,
            'writeBulltan'=>$writeBulltan,
            'sendNewspaper'=>$sendNewspaper,
            'programDevelop'=>$programDevelop,
            'teaching'=>$teaching,
            'other'=>$other,
            'translate'=>$translate,
        ];

        arsort($works);

        $worksPercent = [
            'findingPeople' =>($allWorkReport != 0) ? round($findingPeople / $allWorkReport * 100,2) : 0,
            'Documentation' =>($allWorkReport != 0) ? round($Documentation / $allWorkReport * 100,2) : 0,
            'sendNews'      =>($allWorkReport != 0) ? round($sendNews / $allWorkReport * 100,2) : 0,
            'writeBulltan'  =>($allWorkReport != 0) ? round($writeBulltan / $allWorkReport * 100,2) : 0,
            'sendNewspaper' =>($allWorkReport != 0) ? round($sendNewspaper / $allWorkReport * 100,2) : 0,
            'programDevelop'=>($allWorkReport != 0) ? round($programDevelop / $allWorkReport * 100,2) : 0,
            'teaching'      =>($allWorkReport != 0) ? round($teaching / $allWorkReport * 100,2) : 0,
            'other'         =>($allWorkReport != 0) ? round($other / $allWorkReport * 100,2) : 0,
            'translate'     =>($allWorkReport != 0) ? round($translate / $allWorkReport * 100,2) : 0,
        ];

        $bestFindingPeople = WorkReport::select('employee_id', \DB::raw('sum(outcome) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'سوژه‌یابی')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();

        $identification = WorkReport::select('employee_id', \DB::raw('sum(outcome) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'شناسایی')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();                                                    
        $bestDocumentation = WorkReport::select('employee_id', \DB::raw('sum(outcome) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'مستندسازی')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();
        $bestSendNews = WorkReport::select('employee_id', \DB::raw('sum(outcome) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'ارسال خبر')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();
        $bestWriteBulltan = WorkReport::select('employee_id', \DB::raw('count(*) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'ارسال خبرنامه')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();                                                                                                                                                            
        $bestSendNewspaper = WorkReport::select('employee_id', \DB::raw('sum(outcome) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'ارسال بصر')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();
        $bestProgramDevelop = WorkReport::select('employee_id', \DB::raw('count(*) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'برنامه نویسی')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();
        $bestTranslate = WorkReport::select('employee_id', \DB::raw('count(*) as best_count'))
                                                    ->whereBetween('date', [$startDate, $endDate])
                                                    ->where('project_task', 'like', 'ترجمه')
                                                    ->whereHas('employee', function ($query) use ($departmentId) {
                                                        $query->whereHas('department_user', function ($query) use ($departmentId) {
                                                            $query->where('department_id', $departmentId);
                                                        });
                                                    })                                                    
                                                    ->groupBy('employee_id')
                                                    ->orderByDesc('best_count')
                                                    ->with(['employee:id,username'])
                                                    ->get();

        $users = User::select('id', 'username');
        $usernames = $users->get()->pluck('username')->toArray();
        $point = $users->count();
        $p = $point;
        $rankedUsers = array();
        foreach ($bestFindingPeople as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*4);
        }    
        $p = $point;
        foreach ($bestDocumentation as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
        }  
        $p = $point;
        foreach ($bestSendNews as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*10);
        }  
        $p = $point;
        foreach ($bestWriteBulltan as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
        }  
        $p = $point;
        foreach ($bestSendNewspaper as $key => $value) {
            $username = $value->employee->username;
            // $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*0);
            $rankedUsers['کاربر '.$username] = 0;
        }  
        $p = $point;
        foreach ($bestProgramDevelop as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*5);
        } 
        $p = $point;
        foreach ($bestTranslate as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*1);
        }    
        $p = $point;
        foreach ($identification as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
        }         
        arsort($rankedUsers);

        $process = WorkReport::select('date', DB::raw('count(*) as total_messages'))
                                ->whereBetween('date', [$startDate, $endDate]) // Add this line for date range
                                ->whereHas('employee', function ($query) use ($departmentId) {
                                    $query->whereHas('department_user', function ($query) use ($departmentId) {
                                        $query->where('department_id', $departmentId);
                                    });
                                })                                
                                ->groupBy('date')
                                ->orderBy('date','asc')
                                ->get();
        $yourprocess = WorkReport::select('date', DB::raw('count(*) as total_messages'))
                                ->whereBetween('date', [$startDate, $endDate]) // Add this line for date range
                                ->whereHas('employee', function ($query) use ($departmentId) {
                                    $query->whereHas('department_user', function ($query) use ($departmentId) {
                                        $query->where('department_id', $departmentId);
                                    });
                                })                                
                                ->where('employee_id',$currentUser->id)
                                ->groupBy('date')
                                ->orderBy('date','asc')
                                ->get();                                

        $number = $process->pluck('total_messages')->toArray();
        $yournumber = [];
        $lable = [];

        foreach ($process as $key => $value) {
            $yourprocess = WorkReport::select('date', DB::raw('count(*) as total_messages'))
                                    ->whereDate('date', $value->date) // Add this line for date range
                                    ->where('employee_id',$currentUser->id)
                                    ->groupBy('date')
                                    ->orderBy('date','asc')
                                    ->first();             
            $yournumber[] = $yourprocess->total_messages ?? 0;

            $vertaDateTime = Verta::instance($value->date);
            $shamsiDate = $vertaDateTime->format('Y/m/d');
            $lable[] = $shamsiDate;
        }
        
        $ReleaseProcess = [
            'lables' => $lable,
            'number' => $number,
            'yournumber' => $yournumber
        ];

        $lastReportQuery = WorkReport::whereDate('date', date('Y-m-d'))
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->with(['employee:id,username']);

        $lastReport = $lastReportQuery->orderBy('id','desc')->get();
          
        $theBest = [
            'bestFindingPeople'=>$bestFindingPeople->first(),
            'bestDocumentation'=>$bestDocumentation->first(),
            'bestSendNews'=>$bestSendNews->first(),
            'bestWriteBulltan'=>$bestWriteBulltan->first(),
            'bestSendNewspaper'=>$bestSendNewspaper->first(),
            'bestProgramDevelop'=>$bestProgramDevelop->first(),
            'bestTranslate'=>$bestTranslate->first(),  
            'identification'=>$identification->first()          
        ];

        $Newsletter = WorkReport::select('employee_id', 'outcome', DB::raw('COUNT(*) as count'))
        ->where('project_task','ارسال خبرنامه')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->groupBy('employee_id', 'outcome')
        ->orderby('outcome')
        ->with('employee')
        ->get();

        return response()->json([
            'worksPercent'=>$worksPercent,
            'works'=>$works,
            'rankedUsers'=>$rankedUsers,
            'theBest' => $theBest,
            'lastReport'=>$lastReport,
            'ReleaseProcess' => $ReleaseProcess,
            'allWorkReport' => $allWorkReport,
            'identificationSum' => $identificationSum,
            'identification' => $identification1,
            'identificationPercent' => ($identification1 != 0) ? round($identification1 / $allWorkReport * 100,2) : 0,
            'findingPeople' => $findingPeople,
            'findingPeoplePercent' => ($allWorkReport != 0) ? round($findingPeople / $allWorkReport * 100,2) : 0,
            'findingPeopleSum' => $findingPeopleSum,
            'Documentation' => $Documentation,
            'DocumentationPercent' => ($allWorkReport != 0) ? round($Documentation / $allWorkReport * 100,2) : 0,
            'DocumentationSum' => $DocumentationSum,
            'sendNews' => $sendNews,
            'sendNewsPercent' => ($allWorkReport != 0) ? round($sendNews / $allWorkReport * 100,2) : 0,
            'writeBulltan' => $writeBulltan,
            'writeBulltanPercent' => ($allWorkReport != 0) ? round($writeBulltan / $allWorkReport * 100,5) : 0,
            'sendNewspaper' => $sendNewspaper,
            'sendNewspaperPercent' => ($allWorkReport != 0) ? round($sendNewspaper / $allWorkReport * 100,2) : 0,
            'programDevelop' => $programDevelop,
            'programDevelopPercent' => ($allWorkReport != 0) ? round($programDevelop / $allWorkReport * 100,2) : 0,
            'teaching' => $teaching,
            'teachingPercent' => ($allWorkReport != 0) ? round($teaching / $allWorkReport * 100,2) : 0,
            'other' => $other,
            'otherPercent' => ($allWorkReport != 0) ? round($other / $allWorkReport * 100,2) : 0, 
            'translate' => $translate,
            'translatePercent' => ($allWorkReport != 0) ? round($translate / $allWorkReport * 100,2) : 0,  
            'Newsletter' => $Newsletter ?? []                                                                       
        ]);

    }

    public function userStatistics(Request $request)
    {
        if (!$this->isValidAction('userStatistics', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
    
        $userId = auth()->user()->id;
    
        $now = new Verta();
    
        $currentYear = $now->year;
        $currentMonth = $now->month;
        
        $firstDayOfMonth = Verta::parse("{$currentYear}-{$currentMonth}-01");
        $lastDayOfMonth = $firstDayOfMonth->addMonth()->subDay();
        
        $startDateCurrentMonth = $firstDayOfMonth->DateTime();
        $endDateCurrentMonth = $lastDayOfMonth->DateTime();
    
        $firstDayOfLastMonth = Verta::parse($firstDayOfMonth->subMonth()->format('Y-m-01'));
        $lastDayOfLastMonth = Verta::parse($firstDayOfLastMonth->addMonth()->subDay()->format('Y-m-d'));
    
        $startDateLastMonth = $firstDayOfLastMonth->DateTime();
        $endDateLastMonth = $lastDayOfLastMonth->DateTime();
        
        $startDateCurrentMonth = ($request->date_start == '') ? $startDateCurrentMonth : Verta::parse($request->date_start)->DateTime();
        $endDateCurrentMonth = ($request->date_end == '') ? $endDateCurrentMonth : Verta::parse($request->date_end)->DateTime();
    
        // محاسبه و نمایش آمار برای ماه جاری
        $currentMonthStats = $this->calculateStats($request,$userId, $startDateCurrentMonth, $endDateCurrentMonth, $request->project_task_search);
    
        // محاسبه و نمایش آمار برای ماه قبل
        $lastMonthStats = $this->calculateStats($request,$userId, $startDateLastMonth, $endDateLastMonth, $request->project_task_search);
    
        return response()->json([
            'currentMonth' => $currentMonthStats,
            'lastMonth' => $lastMonthStats,
        ]);
    }
    
    private function calculateStats($request,$userId, $startDate, $endDate, $projectTaskSearch)
    {
        $commonConditions = [
            ['column' => 'date', 'operator' => 'between', 'value' => [$startDate, $endDate]],
        ];
    
        if ($request->project_task_search) {
            $commonConditions[] = ['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }
    
        $userTaskConditions = array_merge(
            [['column' => 'employee_id', 'operator' => '=', 'value' => $userId]],
            $commonConditions
        );
    
        $userCount = $this->WorkReportRepository->Get([
            'conditions' => $userTaskConditions,
            'count' => true,
        ]);
    
        $allCount = $this->WorkReportRepository->Get([
            'conditions' => $commonConditions,
            'count' => true,
        ]);
    
        $projectPercent = $allCount ? number_format($userCount * 100 / $allCount, 2) : 0;
    
        $DepartmentUser = DepartmentUser::where('user_id',$userId)->first();
        $taskTypes = Project::where([['department_id',$DepartmentUser->department_id],['pid'.null]])->orderBy('id','desc')->get();
        $results = [];
        $results['userCount'] = ['name'=>'فعالیت','count'=>$userCount,'icon'=>'bx bx-hard-hat fs-4'];

        foreach ($taskTypes as $item) {
            if($item->result_type == 'int')
            {
                $options = [
                    'conditions' => [
                        ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                        ['column' => 'project_task', 'operator' => 'like', 'value' => $item->name],
                        [
                            'column' => 'date',
                            'operator' => 'between',
                            'value' => [$startDate, $endDate],
                        ],
                    ],
                    'sum' => [
                        ['column' => 'outcome', 'alias' => 'sum_outcome'],
                    ],            
                    'get' => true,
                ];
                if($request->project_task_search != '')
                {
                    $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
                }        
                $itemCount = $this->WorkReportRepository->Get($options)->first()->sum_outcome;
                $results[$item->en_name] = ['name'=>$item->name,'count'=>$itemCount,'icon'=>$item->icon];
            }
            else
            {
                $options = [
                    'conditions' => [
                        ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                        ['column' => 'project_task', 'operator' => 'like', 'value' => $item->name],
                        [
                            'column' => 'date',
                            'operator' => 'between',
                            'value' => [$startDate, $endDate],
                        ],
                    ],          
                    'count' => true,
                ];
                if($request->project_task_search != '')
                {
                    $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
                }        
                $itemCount = $this->WorkReportRepository->Get($options);   
                $results[$item->en_name] = ['name'=>$item->name,'count'=>$itemCount,'icon'=>$item->icon];
            }
        }
        
        return $results;
    }
    
    public function updateWorkReport(Request $request)
    {
        if (!$this->isValidAction('updateWorkReport', 'PUT')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date' => 'required',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'nullable|string',
            'outcome' => 'nullable',
            'project_task' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date)->toCarbon();
        $data['date'] =  $jalaliDate->format('Y-m-d');
        
        $WorkReportId = $request->WorkReportId;

        $updatedWorkReport = $this->WorkReportRepository->update($WorkReportId, $data);

        // ممکن است نیاز به ارسال پاسخ 200 OK یا هر پاسخ دیگری باشد
        return response()->json(['message' => 'Work Report updated successfully', 'data' => $updatedWorkReport]);
    }

    public function deleteWorkReport(Request $request)
    {
        if (!$this->isValidAction('deleteWorkReport', 'DELETE')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $workReportId = $request->id ?? 0;

        $this->WorkReportRepository->delete($workReportId);

        // ممکن است نیاز به ارسال پاسخ 200 OK یا هر پاسخ دیگری باشد
        return response()->json(['message' => 'Work Report deleted successfully']);
    }

    public function saveNewWorkReport($request)
    {
        if (!$this->isValidAction('saveNewWorkReport', 'POST')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }        

            // تنظیم مقدار employee_id از کاربر فعلی
            $employee_id = auth()->user()->id;

            // اعتبارسنجی داده‌های فرم
            $validatedData = $request->validate([
                'date'          => 'required',
                'start_time'    => 'nullable|date_format:H:i',
                'end_time'      => [
                    'nullable',
                    'date_format:H:i',
                    // 'after_or_equal:start_time',
                ],
                'description'   => 'required|string',
                'outcome'       => 'nullable',
                'project_task'  => 'required|string',
                'location'      => 'nullable|string',
            ]);

            $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date)->toCarbon();
            $validatedData['date'] = $jalaliDate;

            // تنظیم مقدار employee_id در آرایه validatedData
            $validatedData['employee_id'] = $employee_id;

            // ایجاد یک مورد جدید با استفاده از ریپازیتوری
            $this->WorkReportRepository->create($validatedData);

            return response()->json('Success', 200);   
    }

    private function isAdmin()
    {
        $currentUser = auth()->user();
        return (DepartmentUser::where([['user_id',$currentUser->id],['job_position','like','admin']])->count() > 0) ? true : false;
    }

    public function getProjectItemsForShow($request)
    {
        if (!$this->isValidAction('getProjectForShow', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $Project = Project::where([['department_id',$departmentId],['id',$request->id ?? 0]])->delete();

        return response()->json(['projectShow'=>$projectShow], 200);   
    }

    public function getProjectForShow($request)
    {
        if (!$this->isValidAction('getProjectForShow', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $projectShow = Project::where([['department_id',$departmentId],['pid', null],['name',$request->type == 'edit' ? $request->edit_project_task : $request->project_task]])->orderBy('sort', 'asc')->first();
        if($projectShow->result_type == 'select')
        {
            $items = Project::where([['department_id',$departmentId],['pid',$projectShow->id ?? 0]])->get();
        }
        return response()->json(['projectShow'=>$projectShow,'items'=>$items ?? []], 200);   
    }

    public function getProjectsForShow($request)
    {
        if (!$this->isValidAction('getProjectsForShow', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $currentUser = auth()->user();
        $DepartmentUser = DepartmentUser::where('user_id',$currentUser->id)->first();
        $departmentId = $DepartmentUser->department_id ?? 0;

        $Projects = Project::where([['department_id',$departmentId],['pid', null]])->orderBy('sort', 'asc')->get();

        return response()->json(['Projects'=>$Projects], 200);   
    }
    public function getWorkList($request)
    {
        if (!$this->isValidAction('getWorkList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $isAdmin = $this->isAdmin();
        
        $now = new Verta();

        $currentYear = $now->year;
        $currentMonth = $now->month;
        
        $firstDayOfMonth = Verta::parse("{$currentYear}-{$currentMonth}-01");
        $lastDayOfMonth = $firstDayOfMonth->addMonth()->subDay();
        
        $startDate = $firstDayOfMonth->DateTime();
        $endDate = $lastDayOfMonth->DateTime();
        
        $startDate = ($request->date_start == '') ? $startDate : Verta::parse($request->date_start)->DateTime();
        $endDate = ($request->date_end == '') ? $endDate : Verta::parse($request->date_end)->DateTime();

        $employee_id = auth()->user()->id;

        $options = [
            'page' => $request->page ?? 1,
            'sortings' => [
                [
                    'column' => 'date',
                    'order' => 'desc',
                ],
                [
                    'column' => 'id',
                    'order' => 'desc',
                ],                
            ],
            'with' => ['employee:username,id'],
            'conditions' => [
                [
                    'column' => 'employee_id',
                    'operator' => '=',
                    'value' => $employee_id,
                ],
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],
        ];       
        
        if($request->project_task_search != '')
        {
            $options['conditions'][3]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        } 

        $WorkList = $this->WorkReportRepository->Get($options,30);        

        return response()->json(['WorkList' => $WorkList , 'isAdmin'=>$isAdmin], 200);

    }

    public function getNewsletter($request)
    {
        if (!$this->isValidAction('getNewsletter', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $Newsletter = Newsletter::orderby('type')->get();
        return response()->json(['Newsletter' => $Newsletter], 200);
    }
}
