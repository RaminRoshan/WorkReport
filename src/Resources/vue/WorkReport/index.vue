<template>
    <nav class="navbar navbar-expand-lg bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="javascript:void(0)"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-ex-6">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar-ex-6">
                <div class="navbar-nav me-auto">
                </div>
                <ul class="navbar-nav ms-lg-auto">
                    <li class="nav-item" v-if="isAdmin">
                        <a class=" btn btn-primary" @click="getSetting()" href="javascript:void(0);" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#workReportSetting" data-target="#workReportSetting" style="margin-left: 10px;">
                            <i class="fa fa-cogs navbar-icon menu-icon"></i>
                            تنظیمات گزارش کار
                        </a>
                    </li>                 
                    <li class="nav-item">
                        <a class=" btn btn-primary" href="javascript:void(0);" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#addNewTask" data-target="#addNewTask">
                            <i class="fa fa-plus navbar-icon menu-icon"></i>
                            افزودن گزارش کار
                        </a>
                    </li>                   
                </ul>
            </div>
        </div>
    </nav>
    <br>
    <div class="row">
        <template v-for="(item, key) in currentMonth" :key="key">
            <div class="col-6 col-md-2 col-lg-2 mb-2" v-if="item.count > 0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar mx-auto mb-2">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i :class="item.icon"></i>
                            </span>
                        </div>
                        <span class="d-block text-nowrap pt-1">{{ item.name }}</span>
                        <h2 class="mb-n3">
                            {{ item.count }}
                            <i :title="lastMonth[key]?.count" :class="(lastMonth[key]?.count > item.count) ? 'fa fa-arrow-circle-down bg-label-danger' : 'fa fa-arrow-circle-up bg-label-success'" style="font-size:20px"></i> 
                        </h2>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card" style="background: ghostwhite;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-6">
                            <div class="form-group">  
                                <h5>از: </h5> <date-picker v-model="date_start"></date-picker>
                            </div>
                        </div> 
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-6">
                            <div class="form-group">  
                                <h5>تا: </h5> <date-picker v-model="date_end"></date-picker>
                            </div>
                        </div>                                                  
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-6">
                            <div class="form-group">                    
                                <h5>پروژه:</h5>
                                <select class="form-select" aria-label="Default select example" v-model="project_task_search">
                                    <option value=""></option>
                                    <option value="سوژه‌یابی">سوژه‌یابی</option>
                                    <option value="شناسایی">شناسایی</option>
                                    <option value="مستندسازی">مستندسازی</option>
                                    <option value="ارسال خبرنامه">ارسال خبرنامه</option>
                                    <option value="ارسال خبر">ارسال خبر</option>
                                    <option value="ارسال بصر">ارسال بصر</option>
                                    <option value="برنامه نویسی">برنامه نویسی</option>
                                    <option value="آموزش">آموزش</option>                                                
                                    <option value="سایر">سایر</option>
                                </select>  
                            </div>
                        </div>                  
                        <div class="col-xl-2 col-lg-3 col-md-3 col-sm-12 col-6">
                            <div class="form-group"> 
                                <h5>ابزار</h5>
                                <button class="btn btn-info btn-sm" @click="runNewGet()" title="اعمال فیلتر"><i class="fa fa-search"></i></button>
                            </div>
                        </div>  
                    </div>                         
				</div>
            </div>
            <hr>
            <div class="col-sm-12">
                <nav aria-label="Page navigation" v-if="pagination.last_page != 1">
                    <ul class="pagination">
                        <li v-if="pagination.current_page > 1">
                            <a href="#" aria-label="Previous" class="page-link" @click.prevent="changePage(pagination.current_page - 1,orderbyValue)">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li v-for="page in pagesNumber"
                            :class="[ page == isActived ? 'page-item active' : '']">
                            <a href="#" @click.prevent="changePage(page,orderbyValue)" class="page-link">{{ page }}</a>
                        </li>
                        <li v-if="pagination.current_page < pagination.last_page">
                            <a href="#" aria-label="Next" class="page-link"
                                @click.prevent="changePage(pagination.current_page + 1,orderbyValue)">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>   
            <hr>
            <div class="row overflow-hidden">
                <div class="col-12">
                    <ul class="timeline timeline-center mt-5 mb-0">
                        <li class="timeline-item mb-md-4 mb-5" v-for="item in WorkList">
                            <span class="timeline-indicator timeline-indicator-primary" data-aos="zoom-in" data-aos-delay="200">
                                <i class="bx bx-paint"></i>
                            </span>
                            <div class="timeline-event card p-0" data-aos="fade-right">
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="card-title mb-0 mt-n1">{{item.project_task}}</h6>
                                    <div class="meta primary-font mt-n1">
                                        <span class="badge rounded-pill bg-label-info">{{item.outcome}}</span>
                                        <span class="badge rounded-pill bg-label-primary">{{item.start_time}}</span>
                                        <span class="badge rounded-pill bg-label-success">{{item.end_time}}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        {{item.description}}
                                    </p>
                                    <button class="btn btn-danger btn-sm" @click="deleteWorkReport(item.id)"><i class="fa fa-trash"></i></button>
                                    <button class="btn btn-info btn-sm" @click="WorkReportId = item.id;edit_date= convertDateToPersian(item.date);
                                        edit_employee_id = item.employee_id
                                        edit_start_time=item.start_time;           
                                        edit_end_time=item.end_time;
                                        edit_description=item.description;
                                        edit_outcome=item.outcome;
                                        edit_project_task=item.project_task;
                                        selectedOption = isValueInArray(item.project_task);
                                        getProjectForShow('edit');
                                        edit_location=item.location;" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#editNewTask" data-target="#editNewTask">
                                        <i class="fa fa-edit"></i>
                                    </button>                            
                                </div>
                                
                                <div class="timeline-event-time">{{item.employee.username}} <br> {{convertDateToPersian(item.date)}}</div>
                            </div>
                        </li>
                    </ul>
                </div>   
            </div>     
        </div>
        <div class="col-sm-12">
            <nav aria-label="Page navigation" v-if="pagination.last_page != 1">
                <ul class="pagination">
                    <li v-if="pagination.current_page > 1">
                        <a href="#" aria-label="Previous" class="page-link" @click.prevent="changePage(pagination.current_page - 1,orderbyValue)">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li v-for="page in pagesNumber" :class="[ page == isActived ? 'page-item active' : '']">
                        <a href="#" @click.prevent="changePage(page,orderbyValue)" class="page-link">{{ page }}</a>
                    </li>
                    <li v-if="pagination.current_page < pagination.last_page">
                        <a href="#" aria-label="Next" class="page-link" @click.prevent="changePage(pagination.current_page + 1,orderbyValue)">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>        
    </div>

    <div class="modal fade" id="workReportSetting" tabindex="-1" aria-labelledby="workReportSettingLabel" style="display: none;" aria-hidden="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="workReportSettingLabel">تنظیمات گزارش کار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="TabSetting" role="TabSetting">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="project-tab" data-bs-toggle="tab" data-bs-target="#project_tab" type="button" role="tab" aria-controls="project_tab" aria-selected="true">پروژه‌ها</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="select-tab" data-bs-toggle="tab" data-bs-target="#select_tab" type="button" role="tab" aria-controls="select_tab" aria-selected="false">مدیریت انتخابی</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="project_tab" role="tabpanel" aria-labelledby="select_tab">
                            <div class="row">
                                <div class="col-sm-3">
                                    <input class="form-control" v-model="project_name" placeholder="عنوان پروژه را وارد کنید">
                                </div>
                                <div class="col-sm-3">
                                    <input class="form-control" v-model="project_en_name" placeholder="عنوان انگلیسی پروژه را وارد کنید">
                                </div>    
                                <div class="col-sm-3">
                                    <input class="form-control" v-model="project_icon" placeholder="متن آیکن پروژه را وارد کنید">
                                </div>                                                              
                                <div class="col-sm-2">
                                    <select class="form-control" v-model="project_result">
                                        <option value="notUsed">بدون خروجی</option>
                                        <option value="int">عددی</option>
                                        <option value="text">متنی</option>
                                        <option value="select">انتخابی</option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button class="btn btn-primary" @click="saveNewProject()"><i class="fa fa-save"></i></button>
                                </div>
                                <div class="col-sm-12">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-center">عنوان فارسی</th>
                                                <th scope="col" class="text-center">عنوان انگلیسی</th>
                                                <th scope="col" class="text-center">آیکن</th>
                                                <th scope="col" class="text-center">نوع نتیجه</th>
                                                <th scope="col" class="text-center">ویرایش</th>
                                                <th scope="col" class="text-center">حذف</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in projects">
                                                <th scope="row"><input class="form-control" v-model="item.name"></th>
                                                <th scope="row"><input class="form-control" v-model="item.en_name"></th>
                                                <th scope="row"><input class="form-control" v-model="item.icon"></th>
                                                <td>
                                                    <select class="form-control" v-model="item.result_type">
                                                        <option value="notUsed">بدون خروجی</option>
                                                        <option value="int">عددی</option>
                                                        <option value="text">متنی</option>
                                                        <option value="select">انتخابی</option>
                                                    </select>
                                                </td>
                                                <td><button class="btn btn-info" @click="editProject(item)"><i class="fa fa-edit"></i></button></td>
                                                <td><button class="btn btn-danger" @click="deleteProject(item.id)"><i class="fa fa-trash"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="select_tab" role="tabpanel" aria-labelledby="select_tab">
                            <div class="row">
                                <div class="col-sm-4">
                                    <select class="form-control" v-model="select_item_id">
                                        <option v-for="item in selectedItem" :value="item.id">{{item.name}}</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-primary" @click="getSelectItem()"><i class="fa fa-search"></i></button>
                                </div>                                 
                                <div class="col-sm-4">
                                    <input class="form-control" v-model="select_name" placeholder="عنوان نتیجه را وارد کنید">
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-primary" @click="saveNewSelectItem()"><i class="fa fa-save"></i></button>
                                </div>  
                                <div class="col-sm-12">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">عنوان</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in subSelectItem">
                                                <td scope="row">{{item.name}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>                                                  
                            </div>                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addNewTask" tabindex="-1" aria-labelledby="addNewTaskLabel" style="display: none;" aria-hidden="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewTaskLabel">ایجاد گزارش کار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="date">تاریخ</label>
                                <date-picker v-model="date"></date-picker>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">  
                            <div class="form-group">
                                <label for="project_task">پروژه</label>
                                <select class="form-select" v-model="project_task" aria-label="Default select example" @change="getProjectForShow()">
                                    <option v-for="item in projectsShow"  :value="item.name">{{item.name}}</option>
                                </select>
                            </div>
                        </div>                        
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="start_time">زمان شروع</label>
                                <input type="time" class="form-control" id="start_time" v-model="start_time">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="end_time">زمان پایان</label>
                                <input type="time" class="form-control" id="end_time" v-model="end_time" >
                            </div>                                                                       
                        </div>                                                                        
                        <div class="col-sm-12 col-md-12">                                
                            <div class="form-group">
                                <label for="description">توضیحات</label>
                                <textarea class="form-control" id="description" v-model="description" style="min-height:150px;"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12" v-if="projectShow.result_type != 'notUsed'">  
                            <div class="form-group">
                                <label for="outcome">نتیجه</label>
                                <input type="number" placeholder="تعداد را وارد کنید" v-model="outcome" class="form-control" v-if="projectShow.result_type == 'int'">
                                <input type="text" placeholder="متن را وارد کنید" v-model="outcome" class="form-control" v-if="projectShow.result_type == 'text'">
                                <select class="form-select" v-model="outcome" @change="handleOptionChange" aria-label="Default select example" v-if="projectShow.result_type == 'select'">
                                    <option v-for="item in projectItemShow" :value="item.name">{{item.name}}</option>
                                </select>                                             
                            </div>
                        </div> 
                        <div class="col-sm-12 col-md-8"></div>

                        <div class="col-sm-12 col-md-2">  
                            <br>
                            <button type="button" class="btn btn-danger btn-block" data-bs-dismiss="modal" data-dismiss="modal" ><i class="fa fa-close menu-icon"></i> لغو</button> 
                        </div>
                        <div class="col-sm-12 col-md-2">  
                            <br>
                            <button type="button" @click="saveNewWorkReport()" class="btn btn-success btn-block" ><i class="fa fa-save menu-icon"></i> ایجاد</button>
                        </div>                            
                    </div>                                                          
                </div>
                <div class="modal-footer custom">
                </div>
            </div>
        </div>
    </div>      
    <div class="modal fade" id="editNewTask" aria-labelledby="editNewTaskLabel" tabindex="-1" style="display: none;" aria-hidden="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNewTaskLabel">ویرایش گزارش کار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="edit_date">تاریخ</label>
                                <date-picker v-model="edit_date"></date-picker>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">  
                            <div class="form-group">
                                <label for="project_task">پروژه</label>
                                <select class="form-select" v-model="edit_project_task" aria-label="Default select example" @change="getProjectForShow('edit')">
                                    <option v-for="item in projectsShow"  :value="item.name">{{item.name}}</option>
                                </select>
                            </div>
                        </div>                        
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="edit_start_time">زمان شروع</label>
                                <input type="time" class="form-control" id="edit_start_time" v-model="edit_start_time">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="edit_end_time">زمان پایان</label>
                                <input type="time" class="form-control" id="edit_end_time" v-model="edit_end_time" >
                            </div>                                                                       
                        </div>                                                                        
                        <div class="col-sm-12 col-md-12">                                
                            <div class="form-group">
                                <label for="edit_description">توضیحات</label>
                                <textarea class="form-control" id="edit_description" v-model="edit_description" style="min-height:150px;"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12" v-if="projectShow.result_type == 'int' || projectShow.result_type == 'text' || projectShow.result_type == 'select'">  
                            <div class="form-group">
                                <label for="outcome">نتیجه</label>
                                <input type="number" placeholder="تعداد را وارد کنید" v-model="edit_outcome" class="form-control" v-if="projectShow.result_type == 'int'">
                                <input type="text" placeholder="متن را وارد کنید" v-model="edit_outcome" class="form-control" v-if="projectShow.result_type == 'text'">
                                <select class="form-select" v-model="edit_outcome" @change="handleOptionChange" aria-label="Default select example" v-if="projectShow.result_type == 'select'">
                                    <option v-for="item in projectItemShow" :value="item.name">{{item.name}}</option>
                                </select>                                             
                            </div>
                        </div>                        
                        <div class="col-sm-12 col-md-8"></div>

                        <div class="col-sm-12 col-md-2">  
                            <br>
                            <button type="button" class="btn btn-danger btn-block" data-bs-dismiss="modal" data-dismiss="modal" ><i class="fa fa-close menu-icon"></i> لغو</button> 
                        </div>
                        <div class="col-sm-12 col-md-2">  
                            <br>
                            <button type="button" @click="saveEditWorkReport()" class="btn btn-success btn-block" ><i class="fa fa-save menu-icon"></i> ویرایش</button>
                        </div>                                                                                                                     
                    </div>                                                          
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import jalaliMoment from "jalali-moment";

export default {
    data() {
        return {
            WorkList:[],
            Newsletter:[],
            WorkReportId:'',
            date: '', 
            start_time:'',           
            end_time:'',  
            description:'',
            outcome:'',         
            project_task:'',   
            location:'',  
            edit_date: '', 
            edit_start_time:'',           
            edit_end_time:'',  
            edit_description:'',
            edit_outcome:'',         
            edit_project_task:'',   
            edit_location:'', 
            edit_employee_id:'',  
            selectedOption:'',            
            pagination: {
                total: 0,
                per_page: 2,
                from: 1,
                to: 0,
                current_page: 1,
                last_page: 1
            },
            offset:4,
            itemsPerPage:20, 
            allCount: 0,
            userCount: 0,
            projectPercent:0,
            findingPeople:0,
            Documentation:0,
            writeBulltan:0,
            sendNews:0,
            sendNewspaper:0,
            programDevelop:0,
            teaching:0,
            other:0,  
            totalScore:0,  
            translate:0,
            allfindingPeople:0,
            allDocumentation:0,
            allwriteBulltan:0,
            allsendNews:0,
            allsendNewspaper:0,
            allprogramDevelop:0,
            allteaching:0,
            allother:0,  
            alltranslate:0,            
            isReadOnly:'readonly', 
            date_start:'',  
            date_end:'',
            project_task_search:'',
            identification:0,
            allidentification:0,
            projects:[],
            selectedItem:[],
            subSelectItem:[],
            project_name:'',
            project_en_name:'',
            project_icon:'',
            project_result:'notUsed',
            result_name:'',
            select_item_id:'',
            select_name:'',
            isAdmin:false,
            projectsShow:[],
            projectShow:[],
            projectItemShow:[],
            currentMonth:[],
            lastMonth:[],
        }
    },
    components: {  },  
    mounted() {
        this.getProjectsForShow();
        this.getWorkList();
        this.getNewsletter();
        this.getUserStatistics();
    },
    methods: {  
        deleteProject(id)
        {
            Swal.fire({
                title: 'آیا اطمینان دارید؟',
                text: "این پروژه حذف خواهد شد!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'لغو'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                        const token = response.data.token;
                        const action = 'deleteProject';
                        axios.request({
                            method: 'DELETE', // از PUT برای ویرایش استفاده می‌کنیم
                            url: this.getAppUrl() + 'api/user/WorkReport',
                            headers: {'Authorization': `Bearer ${token}`},
                            data: { action, id }
                        }).then(response => {
                            Swal.fire(
                                'حذف شد!',
                                'پروژه با موفقیت حذف شد',
                                'success'
                            );
                            this.getSetting();   
                        }).catch(error => {
                            this.checkError(error);
                        });
                    }).catch(error => {
                        this.checkError(error);
                    });
                }
            }); 
        },
        saveNewSelectItem() {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                
                const token = response.data.token;
                const select_item_id = this.select_item_id
                const select_name = this.select_name;
                const action = 'saveNewSelectItem'; 

                axios.request({
                    method: 'POST',
                    url: this.getAppUrl() + 'api/user/WorkReport',
                    headers: {'Authorization': `Bearer ${token}`},
                    data: { select_item_id, select_name , action }
                }).then(response => {
                    this.select_name = "";
                    Swal.fire(
                        'اضافه شد!',
                        'آیتم با موفقیت اضافه شد',
                        'success'
                    );   
                    
                    this.getSelectItem();   
                }).catch(error => {
                    this.checkError(error);
                });        
            }).catch(error => {
                this.checkError(error);
            });
        }, 
        saveNewProject() {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                
                const token = response.data.token;
                const project_name = this.project_name
                const project_en_name = this.project_en_name
                const project_icon = this.project_icon
                const project_result = this.project_result;
                const action = 'saveNewProject'; 

                axios.request({
                    method: 'POST',
                    url: this.getAppUrl() + 'api/user/WorkReport',
                    headers: {'Authorization': `Bearer ${token}`},
                    data: { project_name,project_en_name,project_icon,project_result , action }
                }).then(response => {
                    this.project_name = ""
                    this.project_result = "notUsed";
                    Swal.fire(
                        'اضافه شد!',
                        'پروژه با موفقیت اضافه شد',
                        'success'
                    );   
                    this.getSetting();   
                }).catch(error => {
                    this.checkError(error);
                });        
            }).catch(error => {
                this.checkError(error);
            });
        },        
        editProject(item) {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                
                const token = response.data.token;
                const project_name = item.name
                const project_en_name = item.en_name
                const project_icon = item.icon
                const proId = item.id;
                const project_result = item.result_type;
                const action = 'editProject'; 

                axios.request({
                    method: 'PUT',
                    url: this.getAppUrl() + 'api/user/WorkReport',
                    headers: {'Authorization': `Bearer ${token}`},
                    data: { project_name,project_en_name,project_icon,proId, project_result , action }
                }).then(response => {
                    this.project_name = ""
                    this.project_result = "notUsed";
                    Swal.fire(
                        'ویرایش شد!',
                        'پروژه با موفقیت ویرایش شد',
                        'success'
                    );   
                    this.getSetting();   
                }).catch(error => {
                    this.checkError(error);
                });        
            }).catch(error => {
                this.checkError(error);
            });
        }, 
        getProjectForShow(type='')
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getProjectForShow&type='+type+'&edit_project_task='+this.edit_project_task+'&project_task='+this.project_task,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.projectShow = response.data.projectShow;   
                    this.projectItemShow = response.data.items;   
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            }); 
        },
        getProjectItemsForShow()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getProjectItemsForShow&id='+this.projectShow.id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.projectItemShow = response.data.projectShow;   
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            }); 
        },        
        getProjectsForShow()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getProjectsForShow',
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.projectsShow = response.data.Projects;   
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });            
        },                 
        getSetting()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getSetting',
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.projects = response.data.Projects;   
                    this.selectedItem = response.data.selectedItem;             
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });            
        },
        getSelectItem()
        {
            const id = this.select_item_id;
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getSelectItem&id='+id,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.subSelectItem = response.data.subSelectItem;             
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });            
        },        
        runNewGet()
        {
            this.getWorkList(1);
            this.getUserStatistics();
        },
        convertDateToPersian(date){
            return jalaliMoment(date, "YYYY-MM-DD").format("jYYYY/jMM/jDD");
        },
        convertDateToMiladi(date){
            console.log(date);
            return jalaliMoment.from(input, 'fa', 'YYYY/MM/DD').locale('en').format('YYYY/MM/DD');
        },
        getUserStatistics()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                const dateStart = this.date_start;
                const dateEnd = this.date_end;
                const project_task_search = this.project_task_search

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=userStatistics&date_start='+dateStart+'&date_end='+dateEnd+'&project_task_search='+project_task_search,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.currentMonth = response.data.currentMonth
                    this.lastMonth = response.data.lastMonth
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },        
        getNewsletter()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getNewsletter',
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.Newsletter = response.data.Newsletter;                
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },
        isValueInArray(value){
            var options = ["سوژه‌یابی","مستندسازی","ارسال خبرنامه","ارسال خبر","ارسال بصر","برنامه نویسی","آموزش"];
            if(options.includes(value))
            {
                return value;
            }
            else
            {
                return 6;
            }
        },
        handleOptionChange() {
            //this.isReadOnly =  'readonly';
            this.getSelectItem();
            //this.edit_project_task = this.selectedOption;
        },        
        deleteWorkReport(id){
            Swal.fire({
                title: 'آیا اطمینان دارید؟',
                text: "این کاربر به صورت موقت حذف خواهد شد!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'لغو'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                        const token = response.data.token;
                        const action = 'deleteWorkReport';
                        axios.request({
                            method: 'delete', // از PUT برای ویرایش استفاده می‌کنیم
                            url: this.getAppUrl() + 'api/user/WorkReport',
                            headers: {'Authorization': `Bearer ${token}`},
                            data: { action, id }
                        }).then(response => {
                            Swal.fire(
                                'حذف شد!',
                                'گزارش کار شما با موفقیت حذف شد',
                                'success'
                            );
                            this.searchQuery = '';
                            this.getWorkList(this.pagination.current_page);          
                        }).catch(error => {
                            this.checkError(error);
                        });
                    }).catch(error => {
                        this.checkError(error);
                    });
                }
            });        
        
        },
        saveEditWorkReport(){
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                
                const token = response.data.token;
                const WorkReportId = this.WorkReportId
                const employee_id = this.edit_employee_id;
                const date = this.edit_date
                const start_time = this.edit_start_time;
                const end_time = this.edit_end_time;
                const description = this.edit_description;
                const outcome = this.edit_outcome;
                const project_task = this.edit_project_task;
                const action = 'updateWorkReport'; 

                axios.request({
                    method: 'PUT',
                    url: this.getAppUrl() + 'api/user/WorkReport',
                    headers: {'Authorization': `Bearer ${token}`},
                    data: { WorkReportId , employee_id , date, start_time , end_time , description , outcome , project_task , action , action }
                }).then(response => {
                    Swal.fire(
                        'ویرایش شد!',
                        'گزارش کار شما با موفقیت ویرایش شد.',
                        'success'
                    );   
                    this.getWorkList(this.pagination.current_page);       
                }).catch(error => {
                    this.checkError(error);
                });        
            }).catch(error => {
                this.checkError(error);
            });        
        },
        saveNewWorkReport() {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                
                const token = response.data.token;
                const date = this.date
                const start_time = this.start_time;
                const end_time = this.end_time;
                const description = this.description;
                const outcome = this.outcome;
                const project_task = this.project_task;
                const action = 'saveNewWorkReport'; 

                axios.request({
                    method: 'POST',
                    url: this.getAppUrl() + 'api/user/WorkReport',
                    headers: {'Authorization': `Bearer ${token}`},
                    data: { date, start_time , end_time , description , outcome , project_task , action , action }
                }).then(response => {
                    this.date = ""
                    this.start_time = "";
                    this.end_time = "";
                    this.description = "";
                    this.outcome = "";
                    this.project_task = "";                    
                    Swal.fire(
                        'اضافه شد!',
                        'گزارش کار شما با موفقیت اضافه شد.',
                        'success'
                    );   
                    this.getWorkList();   
                    this.getUserStatistics();
                }).catch(error => {
                    this.checkError(error);
                });        
            }).catch(error => {
                this.checkError(error);
            });
        },    
        getWorkList(page = 1) {   
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                const dateStart = this.date_start;
                const dateEnd = this.date_end;
                const project_task_search = this.project_task_search

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getWorkList&page='+page+'&date_start='+dateStart+'&date_end='+dateEnd+'&project_task_search='+project_task_search,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.fetchPagesDetails(response.data.WorkList);        
                    this.WorkList = response.data.WorkList.data;                
                    this.isAdmin = response.data.isAdmin;                
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },    
        fetchPagesDetails: function (page) {
            this.pagination = {
                total: page['total'],
                per_page: page['per_page'],
                from: page['from'],
                to: page['to'],
                current_page: page['current_page'],
                last_page: page['last_page'],
            };
        },
        changePage: function (page,orderbyValue) {
            this.getWorkList(page,'');
        },         
    },
    computed: {
      isActived () {
        return this.pagination.current_page;
      },
      pagesNumber () {
            if (!this.pagination.to) {
                return [];
            }
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;     
        } 
    }    
}
</script>

<style>
.modal .modal-footer.custom .left-side, .modal .modal-footer.custom .right-side {
    width: 45%;
}
.swal2-container {
  z-index: 25000;
}
</style>


