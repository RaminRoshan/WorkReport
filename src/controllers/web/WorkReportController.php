<?php

namespace Pishgaman\WorkReport\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkReportController extends Controller
{
    private $validActions = [
        'index',
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

    /**
     * Handle the "index" action.
     */
    public function index(Request $request)
    {
        // Execute the "index" method only if it is a valid action.
        if (!$this->isValidAction('index')) {
            return abort(404);
        }

        $mix = ['packages/pishgaman/WorkReport/src/resources/vue/WorkReportApp.js'];

        $breadcrumb = [
            __('WorkReportLang::RoutName.Kara')=>route('home') , 
            'مدیریت گزارش کار'=>route('home')
        ];

        return view('WorkReportView::index',['mix' => $mix,'breadcrumb'=>$breadcrumb]);
    }
}
