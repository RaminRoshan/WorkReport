<template>
      <div class="modal fade" id="downloadPDF" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title secondary-font" id="exampleModalLabel1">دانلود</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col mb-12" v-if="linkActive">
                  <a :href="download_link" target="blank"><i class="fa fa-download"></i> دانلود </a>
                </div>
                <div class="col mb-12" v-else>
                  <p>
                    <div class="spinner-grow text-info" role="status">
                      <span class="visually-hidden">در حال بارگذاری ...</span>
                    </div>
                    درحال آماده سازی فایل، لطفا شکیبا باشید ...</p>             
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  <div class="row " v-if="calendershow != true">
    <div class="col-sm-12 col-md-2">
      <div class="d-grid mt-2">
        <button class="btn btn-warning btn-toggle-sidebar" v-if="taskId == 0" @click="calendershow=true">
          <i class="fa fa-arrow-right me-1"></i>
          <span class="align-middle">نمایش تقویم</span>
        </button> 
      </div>
    </div>
    <div class="col-sm-12 col-md-2">
      <select class="form-control mt-2" v-model="task_status">
        <option value="">همه</option>
        <option value="taskDone">انجام شده</option>
        <option value="InProgress">در جریان</option>
        <option value="DelayToDo">تاخیر در انجام کار</option>
        <option value="lock">ثبت شده توسط مدیر</option>
      </select>
    </div>
    <div class="col-sm-12 col-md-2 mt-2">
      <date-picker v-model="reportExportStart" placeholder="از"></date-picker>
    </div> 
    <div class="col-sm-12 col-md-2 mt-2">
      <date-picker v-model="reportExportEnd" placeholder="تا" ></date-picker>
    </div>        
    <div class="col-sm-12 col-md-2">
      <button class="btn btn-primary btn-sm mt-2 btn-toggle-sidebar" v-if="taskId == 0" @click="getTasks(1)" title="ایجاد فیلتر از گزارش">
        <i class="fa fa-search" style="font-size:14px"></i>
      </button> 
      <button 
        class="btn btn-info btn-sm mt-2 btn-toggle-sidebar" 
        v-if="taskId == 0" 
        @click="handleExport"
        :title="linkIconActive ? 'ایجاد فایل PDF گزارش ماهانه' : 'در حال ایجاد فایل PDF گزارش ماهانه...'"
        data-bs-toggle="modal" 
        data-bs-target="#downloadPDF"
      >
        <i :class="linkIconActive ? 'fa-solid fa-file-word' : 'fa-solid fa-check'" style="font-size:14px"></i>
      </button>          
    </div>  
    <div class="col-sm-12 card mt-4 card">
      <table class="table">
        <thead>
            <tr>
              <th>#</th>
              <th>عنوان</th>
              <th>مسئول</th>
              <th>تاریخ تنظیم شده</th>
              <th>وضعیت</th>
              <th>تاریخ انجام</th>
              <th>اضافه کردن به گزارش کار</th>
            </tr>
        </thead>
        <tbody>
          <tr v-for="item in Tasks" :style="{ color: getDateColor(item.end_date,item.start_date,item.status) }">
            <td></td>
            <td>{{ item.title }}</td>
            <td>{{ item.employee.username }}</td>
            <td>{{ convertDateToPersian(item.start_date) }}</td>
            <td>{{ translateText(item.status) }}</td>
            <td>{{ convertDateToPersian(item.done_at) }}</td>
            <td>
              <button v-if="item.done_at != null" class="btn btn-primary btn-sm" @click="projectShow.result_type = 'notUsed';start_time='';end_time='';outcome='';project_task='';description=item.description;date=item.done_at" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#addNewTask" data-target="#addNewTask">
                <i class="fa fa-plus"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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
    </div>
    <div class="col-sm-12  card mt-4 card">
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
<div class="container my-3 card" style="padding-top:20px;">
  <div class="row align-items-center " v-if="calendershow">
    <div class="col-sm-5 d-flex align-items-center ">
      <button class="btn btn-warning me-2" v-if="taskId == 0" @click="calendershow = false">
        <i class="bx bx-bell me-1"></i>
        <span class="align-middle">فهرست وظایف 
          <span class="badge bg-white text-primary ms-1" v-if="todayTask > 0">{{ todayTask }}</span>
        </span>
      </button>
      <button id="addEventSidebarBtn" class="btn btn-primary me-2" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar" v-if="taskId == 0">
        <i class="bx bx-plus me-1"></i>
        <span class="align-middle">افزودن وظیفه</span>
      </button>
      <button @click="deleteTask()" class="btn btn-danger me-2" v-if="taskId != 0" style="min-width: 160px;">
        <i class="bx bx-trash me-1"></i>
        <span class="align-middle">حذف وظیفه</span>
      </button>
      <button id="addEventSidebarBtn" class="btn btn-info me-2" data-bs-toggle="offcanvas" data-bs-target="#EditEventSidebar" aria-controls="EditEventSidebar" v-if="taskId != 0" style="min-width: 160px;">
        <i class="bx bx-edit me-1"></i>
        <span class="align-middle">ویرایش وظیفه</span>
      </button>
      <button id="addEventSidebarBtn" class="btn btn-danger me-2" v-if="taskId != 0" @click="taskId = 0" style="min-width: 160px;">
        <i class="fa fa-close me-1"></i>
        <span class="align-middle">انصراف</span>
      </button>
    </div>
    <div class="col-sm-2">
      <date-picker v-model="selectDateIn" placeholder="تغییر هفته" class="floralwhite"></date-picker>
    </div>
    <div class="col-sm-2" v-if="isAdmin">
      <select class="form-control floralwhite" v-model="filterUsername">
        <option value="">انتخاب کاربر</option>
        <option v-for="(item, index) in EmployeesUsername" :key="index" :value="item">
          {{ item }}
        </option>
      </select>
    </div>
    <div class="col-sm-1">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="Friday" v-model="Friday" @change="showFriday">
        <label class="form-check-label" for="Friday">جمعه</label>
      </div>
    </div>    
    <div class="col-sm-2 d-flex">
      <button class="btn btn-primary me-2" @click="getTasksInProgress()">
        <i class="fa fa-search"></i>
      </button>
      <button class="btn btn-info" v-if="taskId == 0" @click="getTasksInProgressWord(); linkActive = false" title="ایجاد فایل PDF گزارش هفتگی" data-bs-toggle="modal" data-bs-target="#downloadPDF">
        <i class="fa-solid fa-file-word" style="font-size: 14px;"></i>
      </button>
    </div>
    <div class="col-sm-12 mt-3">
      <p v-if="Task.title && taskId != 0">
        <b>عنوان: <span style="color: blue;">{{ Task.title }}</span></b>
        -
        <b>توضیحات: <span style="color: blue;">{{ Task.description }}</span></b>
        -
        <b>تاریخ برنامه‌ریزی شده: <span style="color: blue;">{{ convertDateToPersian(Task.start_date) }}</span></b>
        -
        <b>تاریخ انجام: <small style="color: blue;">{{ convertDateToPersian(Task.done_at) }}</small></b>
      </p>
    </div>
  </div>
</div>

  <div class="card app-calendar-wrapper" v-if="calendershow == true" style="margin-top:12px">
    <div class="row g-0">
      <div class="col app-calendar-content">
        <div class="card shadow-none border-0">
          <table class="table table-bordered">
            <thead>
              <tr class="text-center table-secondary">
                <th :style="getStyle(firstDayOfWeek)">
                  <i v-if="hasDelayTasks(todayGregorian, 0)" class="bx bx-bell me-1" style="color:red"></i> شنبه<br>{{ firstDayOfWeek }}
                </th>
                <th :style="getStyle(printDate(firstDayOfWeek, 1))">
                  <i v-if="hasDelayTasks(todayGregorian, 1)" class="bx bx-bell me-1" style="color:red"></i> یکشنبه<br>{{ printDate(firstDayOfWeek, 1) }}
                </th>
                <th :style="getStyle(printDate(firstDayOfWeek, 2))">
                  <i v-if="hasDelayTasks(todayGregorian, 2)" class="bx bx-bell me-1" style="color:red"></i> دوشنبه<br>{{ printDate(firstDayOfWeek, 2) }}
                </th>
                <th :style="getStyle(printDate(firstDayOfWeek, 3))">
                  <i v-if="hasDelayTasks(todayGregorian, 3)" class="bx bx-bell me-1" style="color:red"></i> سه‌شنبه<br>{{ printDate(firstDayOfWeek, 3) }}
                </th>
                <th :style="getStyle(printDate(firstDayOfWeek, 4))">
                  <i v-if="hasDelayTasks(todayGregorian, 4)" class="bx bx-bell me-1" style="color:red"></i> چهارشنبه<br>{{ printDate(firstDayOfWeek, 4) }}
                </th>
                <th :style="getStyle(printDate(firstDayOfWeek, 5))">
                  <i v-if="hasDelayTasks(todayGregorian, 5)" class="bx bx-bell me-1" style="color:red"></i> پنجشنبه<br>{{ printDate(firstDayOfWeek, 5) }}
                </th>
                <th :style="getStyle(1)" v-if="Friday">
                  <i v-if="hasDelayTasks(todayGregorian, 6)" class="bx bx-bell me-1" style="color:red"></i> جمعه<br>{{ printDate(firstDayOfWeek, 6) }}
                </th>                
              </tr>
            </thead>
            <tbody>
              <tr>
                <td v-for="dayOffset in dayOffsetin" :key="dayOffset" style="height: 380px;text-align: right; vertical-align: top;">
                    <template v-for="item in filteredTasks(dayOffset)" :key="item.id" >
                      <div v-if="!checkTakhirInDone(item)" class="form-check" style="padding-right: 0.5em;">
                        <input class="form-check-input" type="checkbox" @click="taskDone(item.id)" :checked="item.done_at !== null"  :disabled="currentUserId !== item.employee_id">
                        <label class="form-check-label" for="flexCheckDefault" @click="getTask(item.id)">
                          <i class="fa fa-circle" aria-hidden="true" :style="'font-size:10px;Color:'+ FullCalendarbackgroundColor(item.level)"></i>
                          {{(currentUserId == item.employee_id ) ? '' : item.employee.username}} - {{ item.title }}
                        </label>
                      </div>
                    </template>
                </td>
              </tr>
            </tbody>
            <tbody>
              <tr class="table-danger">
                <td v-for="dayOffset in dayOffsetin" :key="dayOffset" style="height: 200px;text-align: right; vertical-align: top;min-height:200px">
                    <template v-for="item in filteredDellayTasks(dayOffset)" :key="item.id" >
                        <div class="form-check" style="padding-right: 0.5em;">
                            <input class="form-check-input" type="checkbox" @click="taskDone(item.id)" :checked="item.done_at !== null"  :disabled="currentUserId !== item.employee_id">
                            <label class="form-check-label" for="flexCheckDefault" @click="getTask(item.id)">
                                <i class="fa fa-circle" aria-hidden="true" :style="'font-size:10px;Color:'+ FullCalendarbackgroundColor(item.level)"></i>
                                 - {{printTakhirNum(item)}} {{(currentUserId == item.employee_id ) ? '' : '- ' + item.employee.username}} - {{ item.title }}
                            </label>
                        </div>
                    </template>
                </td>
              </tr>
            </tbody>            
          </table>
        </div>
      </div>
    </div>     
  </div>
  <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header border-bottom">
      <h6 class="offcanvas-title" id="addEventSidebarLabel">افزودن وظیفه</h6>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="mb-3 fv-plugins-icon-container">
        <label class="form-label" for="eventTitle">عنوان</label>
        <input type="text" class="form-control" id="eventTitle" v-model="eventTitle" placeholder="عنوان وظیفه را در این قسمت وارد کنید">
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3" v-if="isAdmin">
        <label class="form-label" for="eventLocation">مسئول</label>
        <input type="text" class="form-control" id="eventLocation" v-model="employee_id" placeholder="نام کاربری مسئول انجام وظیفه را وارد کنید">
      </div>       
      <div class="mb-3 fv-plugins-icon-container">
        <label class="form-label" for="eventTitle">درجه اهمیت</label>
        <select v-model="eventLevel" class="form-control">
          <option value="0">عادی</option>
          <option value="1">متوسط</option>
          <option value="2">مهم</option>
          <option value="3">خیلی مهم</option>
        </select>
      </div> 
      <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
        <label class="form-label" for="eventStartDate">تاریخ انجام</label>
        <date-picker v-model="eventStartDate" placeholder="تاریخ انجام وظیفه را وارد کنید."></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="eventDescription">توضیحات</label>
        <textarea class="form-control" v-model="eventDescription" id="eventDescription" placeholder="توضیحات وظیفه را وارد کنید"></textarea>
      </div>
      <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
        <div>
          <button class="btn btn-primary btn-add-event me-sm-3 me-1" @click="saveNewTask">افزودن</button>
          <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-3 me-1" data-bs-dismiss="offcanvas">انصراف</button>
        </div>
      </div>
    </div>
  </div>
  <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="EditEventSidebar" aria-labelledby="EditEventSidebarLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header border-bottom">
      <h6 class="offcanvas-title" id="EditEventSidebarLabel">ویرایش وظیفه</h6>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="mb-3 fv-plugins-icon-container">
        <label class="form-label" for="eventTitle">عنوان</label>
        <input type="text" class="form-control" id="eventTitle" v-model="eventTitleEdit" placeholder="عنوان وظیفه را در این قسمت وارد کنید">
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3" v-if="isAdmin">
        <label class="form-label" for="eventLocation">مسئول</label>
        <input type="text" class="form-control" id="eventLocation" v-model="employee_idEdit" placeholder="نام کاربری مسئول انجام وظیفه را وارد کنید">
      </div>      
      <div class="mb-3 fv-plugins-icon-container">
        <label class="form-label" for="eventTitle">درجه اهمیت</label>
        <select v-model="eventLevelEdit" class="form-control">
          <option value="0">عادی</option>
          <option value="1">متوسط</option>
          <option value="2">مهم</option>
          <option value="3">خیلی مهم</option>
        </select>
      </div>
      <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
        <label class="form-label" for="eventStartDate">تاریخ انجام</label>
        <date-picker v-model="eventStartDateEdit" placeholder="تاریخ انجام وظیفه را وارد کنید."></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="eventDescription">توضیحات</label>
        <textarea class="form-control" v-model="eventDescriptionEdit" id="eventDescription" placeholder="توضیحات وظیفه را وارد کنید"></textarea>
      </div>
      <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
        <div>
          <button class="btn btn-primary btn-add-event me-sm-3 me-1" @click="saveEditTask">ویرایش</button>
          <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-3 me-1" data-bs-dismiss="offcanvas">انصراف</button>
        </div>
      </div>
    </div>
  </div>
  
  </template>
  <script>
  import FullCalendar from '@fullcalendar/vue3';
  import dayGridPlugin from '@fullcalendar/daygrid';
  import timeGridPlugin from '@fullcalendar/timegrid';
  import interactionPlugin from '@fullcalendar/interaction';
  import persian from '@fullcalendar/core/locales/fa';
  import Swal from 'sweetalert2';
  import moment from "jalali-moment";
  
  export default {
    data() {
      return {
        calendershow:true,
        taskId:0,

        calendarOptions: {
          locale: persian,
          plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },          
          hiddenDays: [5],
          weekends: true,
          // editable: true,
          events: [],
          eventTimeFormat: { // Format options for displaying time in events
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
          },
          slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
          },
          allDaySlot: false,          
          eventClick: function(info) {
            this.getTask(info.event.id);
          }.bind(this)        
        },   

        eventTitle:'',
        eventLevel:0,
        eventStartDate:'',
        eventEndDate:'',
        allDay:'',
        repTask:0,
        eventLocation:'',
        eventDescription:'',
  
        eventTitleEdit:'',
        eventLevelEdit:0,
        eventStartDateEdit:'',
        eventEndDateEdit:'',
        allDayEdit:'',
        eventLocationEdit:'',
        eventDescriptionEdit:'',  
  
        TasksInProgress:[], 
        Task:[],
        Tasks:[],
        isAdmin:false,

        employee_id:'',
        employee_idEdit:'',

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
        
        task_status:'',
        todayTask:0,

        linkActive:false,
        download_link:'',

        firstDayOfWeek:'',
        todayGregorian:'',

        delayTasks:[],
        selectDateIn:'',

        reportExportStart:'',
        reportExportEnd:'',

        projectsShow:[],
        projectShow:[],
        projectItemShow:[],  

        date: '', 
        start_time:'',           
        end_time:'',  
        description:'',
        outcome:'',         
        project_task:'',   
        location:'',   
        Holiday:[],  
        currentUserId:0,  
        EmployeesUsername:[],   

        filterUsername:'',
        linkIconActive: true,   

        Friday:false,
        dayOffsetin:6,
        width:16.66,
      }
    },
    components: {
      FullCalendar // make the <FullCalendar> tag available
    },
    mounted() {
      this.getTasksInProgress();
      this.getTasks();
      this.getProjectsForShow();
    },
    methods:
    {
      showFriday() {
        if(this.Friday)
        {
          this.dayOffsetin = 7;
          this.width = 14.28;
        }
        else
        {
          this.dayOffsetin = 6;
          this.width = 16.66;
        }
      },
      printDate(firstDayOfWeek,addNum)
      {
        const jalaliDate = moment(firstDayOfWeek, 'jYYYY/jMM/jDD');
        jalaliDate.add(addNum, 'days');
        return jalaliDate.format('jYYYY/jMM/jDD');
      }, 
      printDateMiladi(firstDayOfWeek,addNum)
      {
        const jalaliDate = moment(firstDayOfWeek, 'YYYY/MM/DD');
        jalaliDate.add(addNum, 'days');
        return jalaliDate.format('YYYY/MM/DD');
      },      
      formatDate(startDate) {
        const date = moment(startDate, 'YYYY-MM-DD HH:mm:ss').format('YYYY/MM/DD');
        return date;
      },   
      filteredTasks(dayOffset) {
        const targetDate = this.printDateMiladi(this.todayGregorian, dayOffset-1);
        return this.TasksInProgress.filter(item => {
          return this.formatDate(item.start_date) === targetDate && this.checkTakhir(item);
        });
      },   
      checkTakhir(item)
      {
        const start_date = new Date(item.start_date); // تبدیل رشته به شیء Date
        const today = new Date();

        start_date.setHours(0, 0, 0, 0);
        today.setHours(0, 0, 0, 0);

        if (start_date < today) {
          if(item.done_at !== null)
          {
            return true;
          }
          return false;
        } else {
            return true;
        }
      },             
      handleExport() {
        this.linkIconActive = false;
        this.exportMonthlyReportWord;
        setTimeout(() => {
          this.linkIconActive = true;
        }, 3000); // مدت زمان در میلی‌ثانیه
      },      
      getStyle(date) {
        return (this.Holiday[date] || date == 1) ? 'width:'+this.width+'%;color:red' : 'width:'+this.width+'%';
      },
      hasDelayTasks(today, offset) {
        return this.delayTasks.length !== 0 && this.checkTodayGregorian(this.printDate(today, offset));
      },  
      filteredDellayTasks(dayOffset) {
          const targetDate = this.printDateMiladi(this.todayGregorian, dayOffset - 1);
          return this.delayTasks.filter(item => {
              if (item.done_at === null) {
                  // وظایف با done_at خالی در روز جاری نمایش داده می‌شوند
                  return moment().format('YYYY/MM/DD') === targetDate;
              } else {
                  // وظایف با done_at پر در روزی که done_at شده نمایش داده می‌شوند
                  return this.formatDate(item.done_at) === targetDate;
              }
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
      printTakhirNum(item) 
      {
        let date1;
        if (item.done_at) {
          date1 = moment(item.done_at, 'YYYY-MM-DD');
        } else {
          date1 = moment(new Date(), 'YYYY-MM-DD');
        }
        const date2 = moment(new Date(item.start_date), 'YYYY-MM-DD');
        const differenceInDays = date2.diff(date1, 'days');
        return differenceInDays * (-1);
      },
      saveNewWorkReport() {
          axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
              
              const token = response.data.token;
              const date = this.convertDateToPersian(this.date)
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
              }).catch(error => {
                  this.checkError(error);
              });        
          }).catch(error => {
              this.checkError(error);
          });
      }, 
      checkTodayGregorian(date) {
          const start_date = new Date(date);
          const today = new Date();

          start_date.setHours(0, 0, 0, 0);
          today.setHours(0, 0, 0, 0);

          if (start_date.getTime() !== today.getTime()) {
              return false;
          } else {
              return true;
          }        
      },
      checkTakhirInDone(item) {
          const start_date = new Date(item.start_date); // تبدیل رشته به شیء Date
          const done_at = new Date(item.done_at); // تبدیل رشته به شیء Date

          start_date.setHours(0, 0, 0, 0);
          done_at.setHours(0, 0, 0, 0);

          if (start_date < done_at) {
              return true; // کار با تاخیر انجام شده است
          } else {
              return false; // کار به موقع انجام شده است یا در همان روز انجام شده است
          }
      },

    
      exportMonthlyReportWord()
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
          const token = response.data.token;
          const task_status = this.task_status;
          const reportExportStart = this.reportExportStart;
          const reportExportEnd = this.reportExportEnd;
          axios.request({
              method: 'GET',
              url: this.getAppUrl() + 'api/user/tasks?action=exportMonthlyReportWord&task_status='+task_status + '&reportExportEnd=' + reportExportEnd + '&reportExportStart=' + reportExportStart,
              headers: {'Authorization': `Bearer ${token}`}
          }).then(response => {
            this.linkActive = true;  
            this.download_link =  response.data.download_link;     
          }).catch(error => {
              this.checkError(error);
          });
        }).catch(error => {
            this.checkError(error);
        });
      },
      getTasksInProgressWord()
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
          const token = response.data.token;
          const selectDateIn = this.selectDateIn;
          axios.request({
              method: 'GET',
              url: this.getAppUrl() + 'api/user/tasks?action=getTasksInProgressWord&selectDateIn='+selectDateIn,
              headers: {'Authorization': `Bearer ${token}`}
          }).then(response => {
            this.linkActive = true;  
            this.download_link =  response.data.download_link;     
          }).catch(error => {
              this.checkError(error);
          });
        }).catch(error => {
            this.checkError(error);
        });
      },
      getDateColor(end_date,start_date,status) {
        let today = new Date();
        
        if (new Date(end_date) >= today && new Date(start_date) <= today) {
          if(status=='InProgress')
            return 'blue';
        } 
        if(new Date(end_date) < today && new Date(start_date) < today)
        {
          if(status=='InProgress')
            return 'red';
        }


        return 'black';

      },      
      convertDateToPersian(date){
        if(date)
          return moment(date, "YYYY-MM-DD H:i:s").locale("fa").format("jYYYY/jMM/jDD");
        else
          return '';
      },
      taskDone(taskId)
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
            
            const token = response.data.token;
            const action = 'taskDone'; 
  
            axios.request({
                method: 'PUT',
                url: this.getAppUrl() + 'api/user/tasks',
                headers: {'Authorization': `Bearer ${token}`},
                data: { taskId , action }
            }).then(response => {
              this.taskId = 0;
              this.getTasksInProgress();   
            }).catch(error => {
                this.checkError(error);
            });        
        }).catch(error => {
            this.checkError(error);
        });
      },
      saveEditTask() 
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
            const token = response.data.token;
            const eventTitle = this.eventTitleEdit;
            const eventStartDate = this.eventStartDateEdit;
            const eventEndDate = this.eventEndDateEdit;
            const allDay = this.allDayEdit;
            const eventLocation = this.eventLocationEdit;
            const eventDescription = this.eventDescriptionEdit;
            const eventLevel = this.eventLevelEdit;
            const id = this.taskId;
            const employee_idEdit = this.employee_idEdit;
            const action = 'saveEditTask'; 
  
            axios.request({
                method: 'PUT',
                url: this.getAppUrl() + 'api/user/tasks',
                headers: {'Authorization': `Bearer ${token}`},
                data: { eventTitle, eventStartDate , eventEndDate , allDay , eventLocation , eventDescription , action , id , eventLevel , employee_idEdit}
            }).then(response => {
                this.eventTitleEdit = ""
                this.eventStartDateEdit = "";
                this.eventEndDateEdit = "";
                this.allDayEdit = "";
                this.eventLocationEdit = "";
                this.eventDescriptionEdit = "";  
                this.taskId = 0;                  
                this.getTasksInProgress();   
            }).catch(error => {
                this.checkError(error);
            });        
        }).catch(error => {
            this.checkError(error);
        });
      },    
      saveNewTask() 
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
            
            const token = response.data.token;
            const eventTitle = this.eventTitle
            const eventLevel = this.eventLevel
            const eventStartDate = this.eventStartDate;
            const eventEndDate = this.eventEndDate;
            const allDay = this.allDay;
            const repTask = this.repTask;
            const eventLocation = this.eventLocation;
            const eventDescription = this.eventDescription;
            const employee_id = this.employee_id;
            const action = 'saveNewTask'; 
  
            axios.request({
                method: 'POST',
                url: this.getAppUrl() + 'api/user/tasks',
                headers: {'Authorization': `Bearer ${token}`},
                data: { eventTitle, eventLevel , eventStartDate , eventEndDate , allDay , repTask , eventLocation , eventDescription , action , employee_id }
            }).then(response => {
                this.eventTitle = ""
                this.eventStartDate = "";
                this.eventEndDate = "";
                this.allDay = "";
                this.eventLocation = "";
                this.eventDescription = "";    
                this.employee_id = "";                 
                this.getTasksInProgress();   
            }).catch(error => {
                this.checkError(error);
            });        
        }).catch(error => {
            this.checkError(error);
        });
      }, 
      deleteTask()
      {
        Swal.fire({
                title: 'آیا اطمینان دارید؟',
                text: "این وظیفه حذف خواهد شد!",
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
                        const action = 'deleteTask';
                        const id = this.taskId;
                        axios.request({
                            method: 'DELETE', // از PUT برای ویرایش استفاده می‌کنیم
                            url: this.getAppUrl() + 'api/user/tasks',
                            headers: {'Authorization': `Bearer ${token}`},
                            data: { action, id }
                        }).then(response => {
                            Swal.fire(
                                'حذف شد!',
                                'وظیفه شما با موفقیت حذف شد',
                                'success'
                            );
                            this.getTasksInProgress();  
                            this.taskId = 0;        
                        }).catch(error => {
                            this.checkError(error);
                        });
                    }).catch(error => {
                        this.checkError(error);
                    });
                }
            });  
      },
      getTasks(page=1)
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
          const token = response.data.token;
          
          axios.request({
              method: 'GET',
              url: this.getAppUrl() + 'api/user/tasks?action=getTasks&page='+page+'&status='+this.task_status + '&reportExportEnd=' + this.reportExportEnd + '&reportExportStart=' + this.reportExportStart,
              headers: {'Authorization': `Bearer ${token}`}
          }).then(response => {
            this.Tasks = response.data.Tasks.data;
            this.fetchPagesDetails(response.data.Tasks);        
          }).catch(error => {
              this.checkError(error);
          });
        }).catch(error => {
            this.checkError(error);
        });
      },      
      getTask(id)
      {
        this.taskId=id
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
          const token = response.data.token;
          
          axios.request({
              method: 'GET',
              url: this.getAppUrl() + 'api/user/tasks?action=getTask&id='+id,
              headers: {'Authorization': `Bearer ${token}`}
          }).then(response => {
            this.Task = response.data.Task;  
            this.eventTitleEdit = this.Task.title;
            this.eventLevelEdit = this.Task.level;
            this.eventStartDateEdit = this.Task.start_date;
            this.eventEndDateEdit = this.Task.end_date;
            this.allDayEdit = this.Task.all_day;
            this.eventLocationEdit = this.Task.location;
            this.eventDescriptionEdit = this.Task.description;                              
          }).catch(error => {
              this.checkError(error);
          });
        }).catch(error => {
            this.checkError(error);
        });
      }, 
       
      getTasksInProgress() {
          axios.get(this.getAppUrl() + 'sanctum/getToken')
              .then(response => {
                  const token = response.data.token;
                  axios.request({
                      method: 'GET',
                      url: `${this.getAppUrl()}api/user/tasks?action=getTasksInProgress&selectDateIn=${this.selectDateIn}&filterUsername=${this.filterUsername}`,
                      headers: { 'Authorization': `Bearer ${token}` }
                  }).then(response => {
                      this.isAdmin = response.data.isAdmin;
                      this.TasksInProgress = response.data.TasksInProgress;
                      const tasksInProgress = response.data.TasksInProgress;
                      this.delayTasks = response.data.delayTasks;
                      this.todayTask = response.data.todayTask;
                      this.todayGregorian = response.data.gregorianDate;
                      this.firstDayOfWeek = response.data.firstDayOfWeek; 
                      this.Holiday = response.data.Holiday; 
                      this.currentUserId = response.data.currentUserId
                      this.EmployeesUsername = response.data.EmployeesUsername
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
      FullCalendarbackgroundColor(level)
      {
        switch (level) {
          case "1":
            return 'rgb(255, 255, 100)';
            break;
          case "2":
            return '#FFD580';
            break;  
          case "3":
            return '#FF7F7F';
            break;                  
          default:
            return '#90EE90';
            break;
        }
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
          this.getTasks(page);
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
.container-xxl, .container-xl, .container-lg, .container-md, .container-sm, .container {max-width: 99.5%;}  
.floralwhite {background-color: floralwhite;}
</style>
  
  