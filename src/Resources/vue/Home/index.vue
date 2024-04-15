<template>
<div class="row">
    <div class="col-md-8 col-lg-8 col-xl-8 mb-xl-0">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-3">نمودار فعالیت روزانه</h5>
            </div>
            <div class="card-body">
                <Line :key="chartKey" ref="myChart" :data="data" :options="options" style="height: 350px;" />             
            </div>
        </div>
    </div>    
    <div class="col-md-4 col-lg-4 col-xl-4 mb-2 mb-xl-0">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-3">کل فعالیت این ماه</h5>
                <h1 class="display-6 fw-normal mb-0 primary-font">{{allWorkReport}}</h1>
            </div>
            <div class="card-body">
                <span class="d-block mb-2">فعالیت کنونی</span>
                <div class="progress progress-stacked mb-3 mb-xl-5" style="height: 8px">
                    <div class="progress-bar bg-info" role="progressbar" :style="'width: '+writeBulltanPercent+'%'" :aria-valuenow="writeBulltanPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-primary" role="progressbar" :style="'width: '+sendNewsPercent+'%'" :aria-valuenow="sendNewsPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-warning" role="progressbar" :style="'width: '+sendNewspaperPercent+'%'" :aria-valuenow="sendNewspaperPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-success" role="progressbar" :style="'width: '+findingPeoplePercent+'%'" :aria-valuenow="findingPeoplePercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-danger" role="progressbar" :style="'width: '+DocumentationPercent+'%'" :aria-valuenow="DocumentationPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-info" role="progressbar" :style="'width: '+programDevelopPercent+'%'" :aria-valuenow="programDevelopPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-primary" role="progressbar" :style="'width: '+teachingPercent+'%'" :aria-valuenow="teachingPercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-warning" role="progressbar" :style="'width: '+translatePercent+'%'" :aria-valuenow="translatePercent" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-success" role="progressbar" :style="'width: '+otherPercent+'%'" :aria-valuenow="otherPercent" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <ul class="p-0 m-0">
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-info me-2"></span> خبرنامه
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{writeBulltan}}</span>
                            <span class="fw-semibold">{{writeBulltanPercent}}%</span>
                        </div>
                    </li>    
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-primary me-2"></span> خبر
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{sendNews}}</span>
                            <span class="fw-semibold">{{sendNewsPercent}}%</span>
                        </div>
                    </li>  
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-warning me-2"></span> بصر
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{sendNewspaper}}</span>
                            <span class="fw-semibold">{{sendNewspaperPercent}}%</span>
                        </div>
                    </li>   
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-success me-2"></span> شناسایی
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{identification}}</span>
                            <span class="fw-semibold">{{identificationPercent}}% ({{ identificationSum }})</span>
                        </div>
                    </li>                                                                       
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-success me-2"></span> سوژه‌یابی
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{findingPeople}}</span>
                            <span class="fw-semibold">{{findingPeoplePercent}}% ({{ findingPeopleSum }})</span>
                        </div>
                    </li>
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-danger me-2"></span> مستندسازی
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{Documentation}}</span>
                            <span class="fw-semibold">{{DocumentationPercent}}% ({{DocumentationSum}})</span>
                        </div>
                    </li>
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-info me-2"></span> برنامه نویسی
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{programDevelop}}</span>
                            <span class="fw-semibold">{{programDevelopPercent}}%</span>
                        </div>
                    </li>   
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-primary me-2"></span> آموزش
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{teaching}}</span>
                            <span class="fw-semibold">{{teachingPercent}}%</span>
                        </div>
                    </li>    
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-warning me-2"></span> ترجمه
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{translate}}</span>
                            <span class="fw-semibold">{{translatePercent}}%</span>
                        </div>
                    </li>                      
                    <li class="mb-3 d-flex justify-content-between">
                        <div class="d-flex align-items-center me-3">
                            <span class="badge badge-dot bg-primary me-2"></span> سایر
                        </div>
                        <div class="d-flex gap-3">
                            <span>{{other}}</span>
                            <span class="fw-semibold">{{otherPercent}}%</span>
                        </div>
                    </li>                                          
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-xl-12 mb-2 mb-xl-0 mt-3">
        <div class="alert alert-warning">
            <p>امتیازها بر اساس فعالیت در مجموعه محاسبه شده‌اند، جزئیات در مورد کیفیت فعالیت در این گزارش ذکر نشده است.</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-6 col-xl-6 mb-2 mb-xl-0">
        <div class="card h-100 mt-3">
            <div class="card-header">
                <h5 class="card-title mb-3">کاربران برتر در هر موضوع</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestWriteBulltan ? theBest.bestWriteBulltan.employee.username : 'N/A'}}
                        </td>
                        <td>برترین ارسال کننده خبرنامه ({{ theBest && theBest.bestWriteBulltan ? theBest.bestWriteBulltan.best_count : 0 }})</td>

                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestFindingPeople ? theBest.bestFindingPeople.employee.username : 'N/A'}}
                        </td>
                        <td>برترین سوژه‌یاب ({{ theBest && theBest.bestFindingPeople ? theBest.bestFindingPeople.best_count : 0 }})</td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestSendNews ? theBest.bestSendNews.employee.username : 'N/A'}}
                        </td>
                        <td>برترین ارسال کننده خبر ({{ theBest && theBest.bestSendNews ? theBest.bestSendNews.best_count : 0 }})</td>

                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.identification ? theBest.identification.employee.username : 'N/A'}}
                        </td>
                        <td>برترین در شناسایی ({{ theBest && theBest.identification ? theBest.identification.best_count : 0 }})</td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestSendNewspaper ? theBest.bestSendNewspaper.employee.username : 'N/A'}}
                        </td>
                        <td>برترین ارسال کننده بصر ({{ theBest && theBest.bestSendNewspaper ? theBest.bestSendNewspaper.best_count : 0 }})</td>

                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestDocumentation ? theBest.bestDocumentation.employee.username : 'N/A'}}
                        </td>
                        <td>برترین مستندساز ({{ theBest && theBest.bestDocumentation ? theBest.bestDocumentation.best_count : 0 }})</td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestTranslate ? theBest.bestTranslate.employee.username : 'N/A'}}
                        </td>
                        <td>برترین مترجم ({{ theBest && theBest.bestTranslate ? theBest.bestTranslate.best_count : 0 }})</td>

                        <td>
                            <i class="fa fa-star" style="color: gold;"></i>
                            {{theBest && theBest.bestProgramDevelop ? theBest.bestProgramDevelop.employee.username : 'N/A'}}
                        </td>
                        <td>برترین برنامه‌نویس ({{ theBest && theBest.bestProgramDevelop ? theBest.bestProgramDevelop.best_count : 0 }})</td>                        
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-6 col-xl-6 mb-2 mb-xl-0">
        <div class="card h-100 mt-3">
            <div class="card-header">
                <h5 class="card-title mb-3">کاربران برتر</h5>
            </div>
            <div class="card-body text-center">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-secondary">
                            <th>کاربر</th>
                            <th>امتیاز</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in Object.entries(rankedUsers)">
                            <td>{{ item[0] }}</td>
                            <td>{{ item[1] }}</td>          
                        </tr>
                    </tbody>
                </table>
                <img :src="this.getAppUrl() +'Templates/first/assets/img/illustrations/prize-light.png'" width="140" height="150" class="rounded-start" alt="View Sales" data-app-light-img="illustrations/prize-light.png" data-app-dark-img="illustrations/prize-dark.png">
            </div>
        </div>        
    </div>
    <div class="col-md-12 col-lg-12 col-xl-12 col-xl-8 mb-4">
        <div class="card h-100 mt-5">
            <div class="card-header d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title mb-0">تعداد خبرنامه‌ها به تفکیک موضوع</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover text-center">
                    <thead>
                        <tr class="table-success">
                            <th>#</th>
                            <th v-for="user in users" :key="user.id">{{ user.name }}</th>
                            <th>جمع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(outcomeCount, outcome) in outcomes" :key="outcome">
                            <td>{{ outcome }}</td>
                            <td v-for="user in users" :key="user.id">
                                {{ getOutcomeCount(outcome, user.id) }}
                            </td>
                            <td class="table-info">{{ getTotal(outcome) }}</td>
                        </tr>
                        <tr class="table-info">
                            <td>جمع</td>
                            <td v-for="user in users" :key="user.id">
                                {{ getColumnTotal(user.id) }}
                            </td>
                            <td>{{ getRowTotal() }}</td> 
                        </tr>                        
                    </tbody>
                </table> 
            </div>    
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-xl-12 col-xl-8 mb-4">
        <div class="card h-100 mt-5">
            <div class="card-header d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title mb-0">فعالیت‌های امروز</h5>
            </div>
            <div class="card-body">
                <ul class="p-0 m-0">
                    <li class="d-flex mb-3 align-items-center" v-for="item in lastReport">
                        <div class="avatar avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-cube"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <p class="mb-0">{{ item.description }}</p>
                                <small class="text-muted">{{item.employee.username}}</small>
                            </div>
                            <div class="item-progress">{{item.date}}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>    
    </div>    
</div>
</template>

<script>
import {Chart as ChartJS,CategoryScale,LinearScale,PointElement,LineElement,Title,Tooltip,Legend,BarElement} from 'chart.js'

import { Line } from 'vue-chartjs'
import { Bar } from 'vue-chartjs'
ChartJS.register(CategoryScale,LinearScale,PointElement,LineElement,Title,Tooltip,Legend, BarElement)

export default {
    data() {
        return {
            theBest:[],
            lastReport:[],
            rankedUsers:[],
            outcomes: {},
            users: [],
            newsletter: [],            
            dateStart:'',
            dateEnd:'',
            project_task_search:'',
            allWorkReport:0,
            findingPeople:0,
            findingPeoplePercent:0,
            Documentation:0,
            DocumentationPercent:0,
            writeBulltan:0,
            writeBulltanPercent:0,
            sendNews:0,
            sendNewsPercent:0,
            sendNewspaper:0,
            sendNewspaperPercent:0,   
            programDevelop:0,
            programDevelopPercent:0,  
            teaching:0,
            teachingPercent:0,  
            other:0,
            otherPercent:0, 
            translate:0,
            translatePercent:0,
            chartKey:0,
            DocumentationSum:0,
            findingPeopleSum:0,
            identificationPercent:0,
            identification:0,
            identificationSum:0,
            data : {
                labels: [],
                datasets: [
                    {
                        label: 'روند فعالیت مجموعه',
                        backgroundColor: '#f87979',
                        data: []
                    },
                    {
                        label: 'روند فعالیت شما',
                        backgroundColor: 'blue',
                        data: []
                    }
                ]
            },
            options : {
                responsive: true,
                maintainAspectRatio: false
            },                                               
        }
    },
    components: { Line , Bar  }, 
    mounted() {
        this.getStatistics();
    },
    methods: {  
        getStatistics()
        {
            axios.get(this.getAppUrl() + 'sanctum/getToken').then(response => {
                const token = response.data.token;
                const dateStart = this.date_start;
                const dateEnd = this.date_end;
                const project_task_search = this.project_task_search

                axios.request({
                    method: 'GET',
                    url: this.getAppUrl() + 'api/user/WorkReport?action=getStatistics&date_start='+dateStart+'&date_end='+dateEnd+'&project_task_search='+project_task_search,
                    headers: {'Authorization': `Bearer ${token}`}
                }).then(response => {
                    this.rankedUsers = response.data.rankedUsers;                
                    this.allWorkReport = response.data.allWorkReport;                
                    this.theBest = response.data.theBest;                
                    this.identification = response.data.identification; 
                    this.identificationSum = response.data.identificationSum; 
                    this.identificationPercent = response.data.identificationPercent; 
                    this.findingPeople = response.data.findingPeople; 
                    this.findingPeoplePercent = response.data.findingPeoplePercent;   
                    this.findingPeopleSum = response.data.findingPeopleSum;           
                    this.Documentation = response.data.Documentation; 
                    this.DocumentationPercent = response.data.DocumentationPercent;  
                    this.DocumentationSum = response.data.DocumentationSum; 
                    this.writeBulltan = response.data.writeBulltan; 
                    this.writeBulltanPercent = response.data.writeBulltanPercent;     
                    this.sendNews = response.data.sendNews; 
                    this.sendNewsPercent = response.data.sendNewsPercent;  
                    this.sendNewspaper = response.data.sendNewspaper; 
                    this.sendNewspaperPercent = response.data.sendNewspaperPercent; 
                    this.programDevelop = response.data.programDevelop;  
                    this.programDevelopPercent = response.data.programDevelopPercent; 
                    this.teaching = response.data.teaching;  
                    this.teachingPercent = response.data.teachingPercent; 
                    this.other = response.data.other;                                                                                                   
                    this.otherPercent = response.data.otherPercent;   
                    this.translate = response.data.translate;                                                                                                   
                    this.translatePercent = response.data.translatePercent;   
                    this.data.labels = response.data.ReleaseProcess.lables;                    
                    this.data.datasets[0].data = response.data.ReleaseProcess.number; // اطمینان حاصل شود که به datasets[0].data دسترسی دارید
                    this.data.datasets[1].data = response.data.ReleaseProcess.yournumber; 
                    this.chartKey++;  
                    this.lastReport = response.data.lastReport;   
                    this.newsletter = response.data.Newsletter;    
                    this.processData();                                                                                                                              
                }).catch(error => {
                    this.checkError(error);
                });
            }).catch(error => {
                this.checkError(error);
            });
        },  
        processData() {
            this.newsletter.forEach(item => {
                if (!this.outcomes[item.outcome]) {
                    this.outcomes[item.outcome] = {};
                }
                this.outcomes[item.outcome][item.employee_id] = item.count;
                if (!this.users.find(user => user.id === item.employee_id)) {
                    this.users.push({ id: item.employee_id, name: item.employee.username });
                }
            });
        },   
        getOutcomeCount(outcome, userId) {
            return this.outcomes[outcome] && this.outcomes[outcome][userId] ? this.outcomes[outcome][userId] : 0;
        },    
        getTotal(outcome) {
            let total = 0;
            for (let user of this.users) {
                total += this.getOutcomeCount(outcome, user.id);
            }
            return total;
        },
        getColumnTotal(userId) {
            let total = 0;
            for (let outcome in this.outcomes) {
                total += this.getOutcomeCount(outcome, userId);
            }
            return total;
        },
        getRowTotal() {
            let total = 0;
            for (let user of this.users) {
                total += this.getColumnTotal(user.id);
            }
            return total;
        }        
    },
    computed: {
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
