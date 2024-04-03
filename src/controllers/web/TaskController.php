<?php

namespace Pishgaman\WorkReport\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hekmatinasser\Verta\Verta;

class TaskController extends Controller
{
    private $validActions = [
        'userTask',
        // 'other_action',  // Add other safe actions here
    ];

    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }    
    /**
     * Validate the requested action name.
     */
    private function isValidAction($functionName)
    {
        return in_array($functionName, $this->validActions);
    }

    public function userTask(Request $request)
    {
        // Execute the "index" method only if it is a valid action.
        if (!$this->isValidAction('userTask')) {
            return abort(404);
        }

        $now = new Verta();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $firstDayOfMonth = Verta::parse("{$currentYear}-{$currentMonth}-01");
        $lastDayOfMonth = $firstDayOfMonth->addMonth()->subDay();
        $startDate = $firstDayOfMonth->DateTime()->format('Y-m-d');
        $endDate = $lastDayOfMonth->DateTime()->format('Y-m-d');

        $mix = ['packages/pishgaman/WorkReport/src/resources/vue/Task/userTaskApp.js'];

        $breadcrumb = [
            __('WorkReportLang::RoutName.Kara')=>route('home') , 
            'وظایف من'=>route('home')
        ];

        return view('WorkReportView::task',['mix' => $mix,'breadcrumb'=>$breadcrumb,'startDate'=>$startDate,'endDate'=>$endDate]);
    }
}
