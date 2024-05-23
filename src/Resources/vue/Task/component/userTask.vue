<template>
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
        <option value="taskDone">انجام شد</option>
        <option value="InProgress">در جریان</option>
        <option value="DelayToDo">تاخیر در انجام کار</option>
        <option value="lock">ثبت شده توسط مدیر</option>
      </select>
    </div>
    <div class="col-sm-12 col-md-1">
      <button class="btn btn-primary btn-sm mt-2 btn-toggle-sidebar" v-if="taskId == 0" @click="getTasks(1)">
        <i class="fa fa-search me-1"></i>
      </button> 
    </div>    
    <div class="col-sm-12 card mt-4 card">
      <table class="table">
        <thead>
            <tr>
              <th>#</th>
              <th>عنوان</th>
              <th>مسئول</th>
              <th>از</th>
              <th>تا</th>
              <th>مکان</th>
              <th>وضعیت</th>
              <th>زمان انجام</th>
            </tr>
        </thead>
        <tbody>
          <tr v-for="item in Tasks" :style="{ color: getDateColor(item.end_date,item.start_date,item.status) }">
            <td></td>
            <td>{{ item.title }}</td>
            <td>{{ item.employee.username }}</td>
            <td>{{ convertDateToPersian(item.start_date) }}</td>
            <td>{{ convertDateToPersian(item.end_date) }}</td>
            <td>{{ item.location }}</td>
            <td>{{ translateText(item.status) }}</td>
            <td>{{ convertDateToPersian(item.done_at) }}</td>
          </tr>
        </tbody>
      </table>
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
  <div class="row" v-if="calendershow == true">
    <div class="col-sm-12">
      <button class="btn btn-warning btn-toggle-sidebar" v-if="taskId == 0" @click="calendershow=false">
          <i class="bx bx-bell me-1"></i>
          <span class="align-middle">
            فهرست وظایف 
            <span class="badge bg-white text-primary ms-1" v-if="todayTask > 0">{{todayTask}}</span>
          </span>
      </button> 
      <button id="addEventSidebarBtn" class="btn btn-primary btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar" v-if="taskId == 0" style="margin-right:5px">
        <i class="bx bx-plus me-1"></i>
        <span class="align-middle">افزودن وظیفه</span>
      </button>   
      <button @click="deleteTask()" class="btn btn-danger btn-toggle-sidebar" v-if="taskId != 0" style="min-width:160px">
        <i class="bx bx-trash me-1"></i>
        <span class="align-middle">حذف وظیفه</span>
      </button>             
      <button id="addEventSidebarBtn" class="btn btn-info btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#EditEventSidebar" aria-controls="EditEventSidebar" v-if="taskId != 0" style="margin-right:5px;min-width:160px;">
        <i class="bx bx-edit me-1"></i>
        <span class="align-middle">ویرایش وظیفه</span>
      </button>  
      <button @click="taskDone()" class="btn btn-success btn-toggle-sidebar" v-if="taskId != 0" style="margin-right:5px;min-width:160px;">
        <i class="bx bx-check me-1"></i>
        <span class="align-middle">انجام شد</span>
      </button> 
      <button id="addEventSidebarBtn" class="btn btn-danger btn-toggle-sidebar" v-if="taskId != 0" @click="taskId=0" style="margin-right:5px;min-width:160px;">
        <i class="fa fa-close me-1"></i>
        <span class="align-middle">انصراف</span>
      </button>           
    </div>
    <div class="col-sm-12" v-if="Task.title && taskId != 0 && calendershow == true" >
      <p>
        <br>
        <b>عنوان: <span style="color: blue;">{{ Task.title }}</span></b> 
         - 
        <b> توضیحات: <span style="color: blue;">{{ Task.description }}</span> </b> - 
        <b> تمام روز: <span style="color: blue;">{{ Task.all_day ? 'بله' : 'خیر' }}</span></b> - 
        <b> زمان: <small style="color: blue;">{{ Task.start_date }} از {{ Task.end_date }}</small></b> - 
        <b> مکان: <span style="color: blue;">{{ Task.location }}</span></b> - 
      </p>
    </div>
  </div>
  <div class="card app-calendar-wrapper" v-if="calendershow == true" style="margin-top:12px">
    <div class="row g-0">
      <div class="col app-calendar-content">
        <div class="card shadow-none border-0">
          <div class="card-body pb-0">
            <FullCalendar :options='calendarOptions' />
          </div>
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
        <label class="form-label" for="eventStartDate">تاریخ شروع</label>
        <date-picker v-model="eventStartDate" disable="1403-01-17"></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
        <label class="form-label" for="eventEndDate">تاریخ پایان</label>
        <date-picker v-model="eventEndDate"></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="switch">
          <input type="checkbox" class="switch-input allDay-switch" v-model="allDay">
          <span class="switch-toggle-slider">
            <span class="switch-on"></span>
            <span class="switch-off"></span>
          </span>
          <span class="switch-label">تمام روز</span>
        </label>
      </div>
      <div class="mb-3">
        <label class="switch">
          <input type="checkbox" class="switch-input allDay-switch" v-model="repTask">
          <span class="switch-toggle-slider">
            <span class="switch-on"></span>
            <span class="switch-off"></span>
          </span>
          <span class="switch-label">تکرار در هر روز</span>
        </label>
      </div>      
      <div class="mb-3">
        <label class="form-label" for="eventLocation">مکان</label>
        <input type="text" class="form-control" id="eventLocation" v-model="eventLocation" placeholder="موقعیت را وارد کنید">
      </div>
      <div class="mb-3">
        <label class="form-label" for="eventDescription">توضیحات</label>
        <textarea class="form-control" v-model="eventDescription" id="eventDescription"></textarea>
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
        <label class="form-label" for="eventStartDate">تاریخ شروع</label>
        <date-picker v-model="eventStartDateEdit" disable="1403-01-17"></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
        <label class="form-label" for="eventEndDate">تاریخ پایان</label>
        <date-picker v-model="eventEndDateEdit"></date-picker>
        <div class="fv-plugins-message-container invalid-feedback"></div>
      </div>
      <div class="mb-3">
        <label class="switch">
          <input type="checkbox" class="switch-input allDay-switch" v-model="allDayEdit">
          <span class="switch-toggle-slider">
            <span class="switch-on"></span>
            <span class="switch-off"></span>
          </span>
          <span class="switch-label">تمام روز</span>
        </label>
      </div>
      <div class="mb-3">
        <label class="form-label" for="eventLocation">مکان</label>
        <input type="text" class="form-control" id="eventLocation" v-model="eventLocationEdit" placeholder="موقعیت را وارد کنید">
      </div>
      <div class="mb-3">
        <label class="form-label" for="eventDescription">توضیحات</label>
        <textarea class="form-control" v-model="eventDescriptionEdit" id="eventDescription"></textarea>
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
        eventLevel:'',
        eventStartDate:'',
        eventEndDate:'',
        allDay:'',
        repTask:0,
        eventLocation:'',
        eventDescription:'',
  
        eventTitleEdit:'',
        eventLevelEdit:'',
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
      }
    },
    components: {
      FullCalendar // make the <FullCalendar> tag available
    },
    mounted() {
      this.getTasksInProgress();
      this.getTasks();
    },
    methods:
    {
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
          return moment(date, "YYYY-MM-DD H:i:s").locale("fa").format("jYYYY/jMM/jDD H:mm:ss");
        else
          return '';

      },
      taskDone()
      {
        axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
            
            const token = response.data.token;
            const taskId = this.taskId;
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
              url: this.getAppUrl() + 'api/user/tasks?action=getTasks&page='+page+'&status='+this.task_status,
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
      calculateDaysDifference(startDate, endDate) {
        const oneDay = 24 * 60 * 60 * 1000; // یک روز به میلی‌ثانیه
        const start = new Date(startDate);
        const end = new Date(endDate);
        const differenceMs = Math.abs(end - start);
        const differenceDays = Math.round(differenceMs / oneDay);
        return differenceDays;
      },         
getTasksInProgress() {
    axios.get(this.getAppUrl() + 'sanctum/getToken')
        .then(response => {
            const token = response.data.token;

            axios.request({
                method: 'GET',
                url: this.getAppUrl() + 'api/user/tasks?action=getTasksInProgress',
                headers: { 'Authorization': `Bearer ${token}` }
            }).then(response => {
                this.isAdmin = response.data.isAdmin;
                const tasksInProgress = response.data.TasksInProgress;
                this.todayTask = response.data.todayTask;

                const today = new Date();
                today.setHours(0, 0, 0, 0); // تنظیم ساعت به 00:00:00:000 برای مقایسه فقط تاریخ

                const events = tasksInProgress.map(task => {
                    const startDate = new Date(task.start_date);
                    let endDate = new Date(task.end_date);
                    // اگر تاریخ پایان کمتر از امروز است، آن را به امروز تغییر دهید
                    let dayDiffTitle = ""
                    if (endDate < today) {
                        const differenceDays = this.calculateDaysDifference(endDate,today)
                        dayDiffTitle = differenceDays.toString() + ' - ';
                        endDate = today;
                        today.setHours(23, 59, 59, 0);
                    }
      
                    return {
                        id: task.id,
                        title: ' - ' + dayDiffTitle + task.employee.username + ' - ' + task.title,
                        start: startDate,
                        end: endDate,
                        backgroundColor: this.FullCalendarbackgroundColor(task.level),
                        textColor: '#000000'
                    };
                });

                this.calendarOptions.events = events;
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
            return '#FFFFE0';
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
</style>
  
  