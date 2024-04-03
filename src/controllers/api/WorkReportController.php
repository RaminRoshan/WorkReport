<?php

namespace Pishgaman\WorkReport\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\WorkReport\Database\Repository\WorkReportRepository;
use Pishgaman\WorkReport\Database\Models\WorkReport;
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
        'getStatistics'
    ];

    protected $validMethods = [
        'GET' => ['getWorkList','getNewsletter','userStatistics','getStatistics'], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => ['saveNewWorkReport'], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => ['updateWorkReport'],
        'DELETE' => ['deleteWorkReport']
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
                            
        $findingPeople = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','سوژه‌یابی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();

        $Documentation = WorkReport::whereBetween('date',[$startDate, $endDate])->where('project_task','like','مستندسازی')
        ->whereHas('employee', function ($query) use ($departmentId) {
            $query->whereHas('department_user', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        })
        ->count();
        
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
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
        }  
        $p = $point;
        foreach ($bestWriteBulltan as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
        }  
        $p = $point;
        foreach ($bestSendNewspaper as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*1);
        }  
        $p = $point;
        foreach ($bestProgramDevelop as $key => $value) {
            $username = $value->employee->username;
            $rankedUsers['کاربر '.$username] = ($rankedUsers['کاربر '.$username] ?? 0) + (($p--)*3);
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
            'findingPeople' => $findingPeople,
            'findingPeoplePercent' => ($allWorkReport != 0) ? round($findingPeople / $allWorkReport * 100,2) : 0,
            'Documentation' => $Documentation,
            'DocumentationPercent' => ($allWorkReport != 0) ? round($Documentation / $allWorkReport * 100,2) : 0,
            'sendNews' => $sendNews,
            'sendNewsPercent' => ($allWorkReport != 0) ? round($sendNews / $allWorkReport * 100,2) : 0,
            'writeBulltan' => $writeBulltan,
            'writeBulltanPercent' => ($allWorkReport != 0) ? round($writeBulltan / $allWorkReport * 100,2) : 0,
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
        
        $startDate = $firstDayOfMonth->DateTime();
        $endDate = $lastDayOfMonth->DateTime();
        
        $startDate = ($request->date_start == '') ? $startDate : Verta::parse($request->date_start)->DateTime();
        $endDate = ($request->date_end == '') ? $endDate : Verta::parse($request->date_end)->DateTime();

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
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
            $options['conditions'][2]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }

        $userCount = $this->WorkReportRepository->Get($options);

        $options = [
            'conditions' => [
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
            $options['conditions'][1]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }

        $allCount = $this->WorkReportRepository->Get($options);

        if($allCount == 0)
            $projectPercent = 0;
        else
            $projectPercent = number_format($userCount * 100 / $allCount,2);

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $findingPeople = $this->WorkReportRepository->Get($options)->first()->sum_outcome;

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $identification = $this->WorkReportRepository->Get($options)->first()->sum_outcome;        

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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $allidentification = $this->WorkReportRepository->Get($options)->first()->sum_outcome;

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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $allFindingPeople = $this->WorkReportRepository->Get($options)->first()->sum_outcome;
                
        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "سوژه‌یابی"],
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
        $findingPeopleProject = $this->WorkReportRepository->Get($options);  
        
        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "سوژه‌یابی"],
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
        $allFindingPeopleProject = $this->WorkReportRepository->Get($options);  


        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $Documentation = $this->WorkReportRepository->Get($options)->first()->sum_outcome;      

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
        if($request->project_task_search != '')
        {
            $options['conditions'][4]=['column' => 'project_task', 'operator' => 'like', 'value' => $request->project_task_search];
        }        
        $allDocumentation = $this->WorkReportRepository->Get($options)->first()->sum_outcome; 

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "مستندسازی"],
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
        $DocumentationProject = $this->WorkReportRepository->Get($options);

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "مستندسازی"],
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
        $allDocumentationProject = $this->WorkReportRepository->Get($options);

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال خبرنامه"],
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
        $writeBulltan = $this->WorkReportRepository->Get($options);  

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال خبرنامه"],
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
        $allwriteBulltan = $this->WorkReportRepository->Get($options);

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال خبر"],
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
        $sendNews = $this->WorkReportRepository->Get($options)->first()->sum_outcome; 

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال خبر"],
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
        $allsendNews = $this->WorkReportRepository->Get($options)->first()->sum_outcome;        

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال بصر"],
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
        $sendNewspaper = $this->WorkReportRepository->Get($options)->first()->sum_outcome;  

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ارسال بصر"],
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
        $allsendNewspaper = $this->WorkReportRepository->Get($options)->first()->sum_outcome;

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "برنامه نویسی"],
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
        $programDevelop = $this->WorkReportRepository->Get($options); 

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "برنامه نویسی"],
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
        $allprogramDevelop = $this->WorkReportRepository->Get($options);        

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "آموزش"],
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
        $teaching = $this->WorkReportRepository->Get($options); 

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "آموزش"],
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
        $allteaching = $this->WorkReportRepository->Get($options);

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "سایر"],
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
        $other = $this->WorkReportRepository->Get($options); 
        
        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "سایر"],
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
        $allother = $this->WorkReportRepository->Get($options); 

        $options = [
            'conditions' => [
                ['column' => 'employee_id', 'operator' => '=', 'value' => $userId],
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ترجمه"],
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
        $translate = $this->WorkReportRepository->Get($options); 

        $options = [
            'conditions' => [
                ['column' => 'project_task', 'operator' => 'like', 'value' => "ترجمه"],
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
        $alltranslate = $this->WorkReportRepository->Get($options); 

        $coefficients = [
            'سوژه‌یابی' => $findingPeopleProject,
            'مستندسازی' => $DocumentationProject,
            'ارسال خبرنامه' => $writeBulltan,
            'ارسال خبر' => $sendNews,
            'ارسال بصر' => $sendNewspaper,
            'برنامه نویسی' => $programDevelop,
            'آموزش' => $teaching,
            'سایر' => $other,
            'ترجمه' => $translate,
        ];
        
        $workPoints = WorkPoint::all();

        $totalScore = 0;
        
        foreach ($workPoints as $item) {
            switch ($item->name) {
                case 'سوژه‌یابی':
                    $totalScore = $totalScore + ($item->point * $coefficients['سوژه‌یابی']);
                    break;
                case 'مستندسازی':
                    $totalScore = $totalScore + ($item->point * $coefficients['مستندسازی']);
                    break;
                case 'ارسال خبرنامه':
                    $totalScore = $totalScore + ($item->point * $coefficients['ارسال خبرنامه']);
                    break;                                        
                case 'ارسال خبر':
                    $totalScore = $totalScore + ($item->point * $coefficients['ارسال خبر']);
                    break;
                case 'ارسال بصر':
                    $totalScore = $totalScore + ($item->point * $coefficients['ارسال بصر']);
                    break;                                        
                case 'برنامه نویسی':
                    $totalScore = $totalScore + ($item->point * $coefficients['برنامه نویسی']);
                    break;                    
                case 'آموزش':
                    $totalScore = $totalScore + ($item->point * $coefficients['آموزش']);
                    break; 
                case 'ترجمه':
                    $totalScore = $totalScore + ($item->point * $coefficients['ترجمه']);
                    break;                                          
                case 'سایر':
                    $totalScore = $totalScore + ($item->point * $coefficients['سایر']);
                    break;                    
                
            }
        }

        return response()->json([
            // 'allCount' => $allCount, 
            'userCount' => $userCount , 
            'projectPercent'=>$projectPercent, 
            'findingPeople'=>$findingPeople,
            'Documentation'=>$Documentation,
            'writeBulltan'=>$writeBulltan,
            'sendNews'=>$sendNews,
            'sendNewspaper'=>$sendNewspaper,
            'programDevelop'=>$programDevelop,
            'teaching'=>$teaching,
            'other'=>$other,
            'totalScore'=>$totalScore,
            'translate'=>$translate,
            // 'allfindingPeople'=>$allFindingPeople,
            // 'allDocumentation'=>$allDocumentation,
            // 'allwriteBulltan'=>$allwriteBulltan,
            // 'allsendNews'=>$allsendNews,
            // 'allsendNewspaper'=>$allsendNewspaper,
            // 'allprogramDevelop'=>$allprogramDevelop,
            // 'allteaching'=>$allteaching,
            // 'allother'=>$allother,
            // 'alltranslate'=>$alltranslate,
            // 'identification'=>$identification,
            // 'allidentification'=>$allidentification,
                                  
        ]);
    }

    public function updateWorkReport(Request $request)
    {
        if (!$this->isValidAction('updateWorkReport', 'PUT')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'nullable|string',
            'outcome' => 'nullable|string',
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
                'date'          => 'required|date',
                'start_time'    => 'nullable|date_format:H:i',
                'end_time'      => [
                    'nullable',
                    'date_format:H:i',
                    // 'after_or_equal:start_time',
                ],
                'description'   => 'required|string',
                'outcome'       => 'nullable|string',
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

    public function getWorkList($request)
    {
        if (!$this->isValidAction('getWorkList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

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

        return response()->json(['WorkList' => $WorkList], 200);

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
