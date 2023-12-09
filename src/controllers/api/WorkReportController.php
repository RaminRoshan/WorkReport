<?php

namespace Pishgaman\WorkReport\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\WorkReport\Database\Repository\WorkReportRepository;
use App\Http\Controllers\Controller;
use Pishgaman\WorkReport\Database\Models\Newsletter;
use Pishgaman\WorkReport\Database\Models\WorkPoint;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Log;

class WorkReportController extends Controller
{
    private $validActions = [
        'getWorkList',
        'saveNewWorkReport',
        'deleteWorkReport',
        'updateWorkReport',
        'getNewsletter',
        'userStatistics'
    ];

    protected $validMethods = [
        'GET' => ['getWorkList','getNewsletter','userStatistics'], // Added 'createAccessLevel' as a valid method-action pair
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
            // Log::error('functionName: ' . $functionName);

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

    public function userStatistics(Request $request)
    {
        if (!$this->isValidAction('userStatistics', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $userId = auth()->user()->id;

        $today = Carbon::now();
        $nextDay = $today->addDay();
        
        $jtoday = Jalalian::fromDateTime($today);
        $jyear = $jtoday->getYear();
        $jmounth = $jtoday->getMonth();
        $jday = 1;
        $startdate = (new Jalalian($jyear, $jmounth, $jday, 0, 0, 0))->toCarbon()->toDateTimeString();

        $startDate = ($request->date_start == '') ? $startdate : Jalalian::fromFormat('Y/m/d', $request->date_start)->toCarbon();
        $endDate = ($request->date_end == '') ? $nextDay->toDateString() : Jalalian::fromFormat('Y/m/d', $request->date_end)->toCarbon();

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
            'allCount' => $allCount, 
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
            'allfindingPeople'=>$allFindingPeople,
            'allDocumentation'=>$allDocumentation,
            'allwriteBulltan'=>$allwriteBulltan,
            'allsendNews'=>$allsendNews,
            'allsendNewspaper'=>$allsendNewspaper,
            'allprogramDevelop'=>$allprogramDevelop,
            'allteaching'=>$allteaching,
            'allother'=>$allother,
            'alltranslate'=>$alltranslate                        
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

        $today = Carbon::now();
        $nextDay = $today->addDay();

        $jtoday = Jalalian::fromDateTime($today);
        $jyear = $jtoday->getYear();
        $jmounth = $jtoday->getMonth();
        $jday = 1;
        $startdate = (new Jalalian($jyear, $jmounth, $jday, 0, 0, 0))->toCarbon()->toDateTimeString();

        $employee_id = auth()->user()->id;

        $startDate = ($request->date_start == '') ? $startdate : Jalalian::fromFormat('Y/m/d', $request->date_start)->toCarbon();
        $endDate = ($request->date_end == '') ? $nextDay->toDateString() : Jalalian::fromFormat('Y/m/d', $request->date_end)->toCarbon();
        
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

        $Newsletter = Newsletter::all();
        return response()->json(['Newsletter' => $Newsletter], 200);
    }
}
