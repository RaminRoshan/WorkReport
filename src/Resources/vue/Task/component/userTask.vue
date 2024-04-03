<template>
<div class="card app-calendar-wrapper">
  <div class="row g-0">
    <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
      <div class="border-bottom p-4 my-sm-0 mb-3">
        <div class="d-grid">
          <button class="btn btn-primary btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
              <i class="bx bx-plus me-1"></i>
              <span class="align-middle">افزودن رویداد</span>
          </button>
        </div>
      </div>
    </div>
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
                        <h6 class="offcanvas-title" id="addEventSidebarLabel">افزودن رویداد</h6>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                      </div>
                      <div class="offcanvas-body">
                        <form class="event-form pt-0 fv-plugins-bootstrap5 fv-plugins-framework" id="eventForm" onsubmit="return false" novalidate="novalidate">
                          <div class="mb-3 fv-plugins-icon-container">
                            <label class="form-label" for="eventTitle">عنوان</label>
                            <input type="text" class="form-control" id="eventTitle" name="eventTitle" placeholder="عنوان رویداد">
                          <div class="fv-plugins-message-container invalid-feedback"></div></div>
                          <div class="mb-3">
                            <label class="form-label" for="eventLabel">برچسب</label>
                            <div class="position-relative"><select class="select2 select-event-label form-select select2-hidden-accessible" id="eventLabel" name="eventLabel" data-select2-id="eventLabel" tabindex="-1" aria-hidden="true">
                              <option data-label="primary" value="Business" selected="" data-select2-id="2">کسب و کار</option>
                              <option data-label="danger" value="Personal">شخصی</option>
                              <option data-label="warning" value="Family">خانواده</option>
                              <option data-label="success" value="Holiday">تعطیلات</option>
                              <option data-label="info" value="ETC">سایر</option>
                            </select><span class="select2 select2-container select2-container--default" dir="rtl" data-select2-id="1" style="width: 335px;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-eventLabel-container"><span class="select2-selection__rendered" id="select2-eventLabel-container" role="textbox" aria-readonly="true" title="کسب و کار"><span class="badge badge-dot bg-primary me-2"> </span>کسب و کار</span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span></div>
                          </div>
                          <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
                            <label class="form-label" for="eventStartDate">تاریخ شروع</label>
                            <input type="hidden" class="form-control flatpickr-input" id="eventStartDate" name="eventStartDate" placeholder="تاریخ شروع" value="1403-01-14 12:51"><input class="form-control form-control input" placeholder="تاریخ شروع" tabindex="0" type="text" readonly="readonly">
                          <div class="fv-plugins-message-container invalid-feedback"></div></div>
                          <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
                            <label class="form-label" for="eventEndDate">تاریخ پایان</label>
                            <input type="hidden" class="form-control flatpickr-input" id="eventEndDate" name="eventEndDate" placeholder="تاریخ پایان" value="1403-01-14 12:51"><input class="form-control form-control input" placeholder="تاریخ پایان" tabindex="0" type="text" readonly="readonly">
                          <div class="fv-plugins-message-container invalid-feedback"></div></div>
                          <div class="mb-3">
                            <label class="switch">
                              <input type="checkbox" class="switch-input allDay-switch">
                              <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                              </span>
                              <span class="switch-label">تمام روز</span>
                            </label>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="eventURL">آدرس URL رویداد</label>
                            <input type="url" class="form-control" id="eventURL" name="eventURL" placeholder="https://www.google.com">
                          </div>
                          <div class="mb-3 select2-primary">
                            <label class="form-label" for="eventGuests">افزودن مهمانان</label>
                            <div class="position-relative"><select class="select2 select-event-guests form-select select2-hidden-accessible" id="eventGuests" name="eventGuests" multiple="" data-select2-id="eventGuests" tabindex="-1" aria-hidden="true">
                              <option data-avatar="1.png" value="Jane Foster">تونی استارک</option>
                              <option data-avatar="3.png" value="Donna Frank">پیتر پارکر</option>
                              <option data-avatar="5.png" value="Gabrielle Robertson">استیو راجرز</option>
                              <option data-avatar="7.png" value="Lori Spears">اولیور کویین</option>
                              <option data-avatar="9.png" value="Sandy Vega">بروس وین</option>
                              <option data-avatar="11.png" value="Cheryl May">کلینت بارتون</option>
                            </select><span class="select2 select2-container select2-container--default" dir="rtl" data-select2-id="3" style="width: 335px;"><span class="selection"><span class="select2-selection select2-selection--multiple" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="-1" aria-disabled="false"><ul class="select2-selection__rendered"><li class="select2-search select2-search--inline"><input class="select2-search__field" type="search" tabindex="0" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" role="searchbox" aria-autocomplete="list" placeholder="انتخاب" style="width: 321px;"></li></ul></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span></div>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="eventLocation">مکان</label>
                            <input type="text" class="form-control" id="eventLocation" name="eventLocation" placeholder="موقعیت را وارد کنید">
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="eventDescription">توضیحات</label>
                            <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
                          </div>
                          <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
                            <div>
                              <button type="submit" class="btn btn-primary btn-add-event me-sm-3 me-1">افزودن</button>
                              <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1" data-bs-dismiss="offcanvas">
                                انصراف
                              </button>
                            </div>
                            <div><button class="btn btn-label-danger btn-delete-event d-none ms-1">حذف</button></div>
                          </div>
                        <input type="hidden"></form>
                      </div>
                    </div>
</template>
<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import persian from '@fullcalendar/core/locales/fa';

export default {
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },
  methods:{
    calculateFirstDayOfMonth(){
      
    }
  },  
  data: function() {
    var firstDay = document.getElementById('TaskApp').getAttribute("class").split('-')[2]
    console.log(firstDay)
    return {
      calendarOptions: {
        locale: persian,
        plugins: [dayGridPlugin],
        initialView: 'dayGridMonth',
        weekends: true,
        defaultDate: new Date(document.getElementById('TaskApp').getAttribute("class")),
        // editable: true,
        events: [
          { title: 'Meeting', start: new Date(document.getElementById('TaskApp').getAttribute("class")) , end: new Date()}
        ],
      }
    }
  }
}
</script>


