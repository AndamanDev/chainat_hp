Vue.use(Vuex)

const { mapActions, mapGetters, mapState } = Vuex

const store = new Vuex.Store({
  state: {
    form: {
      service_profile_id: null,
      service_ids: [],
      counter_service_ids: [],
    }
  },
  mutations: {
    SET_FORM_DATA(state, payload) {
      state.form = payload
    },
  },
  actions: {
    setFormData({ commit }, payload) {
      commit("SET_FORM_DATA", payload)
    },
  },
  plugins: [createPersistedState({
    key: 'vuex-store',
  })],
})

// moment locale
moment.locale("th")

axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common["X-CSRF-Token"] = window.yii.getCsrfToken()
// Add a request interceptor
axios.interceptors.request.use(
  (config) => {
    const query = yii.getQueryParams(config.url)
    if (app && app.authKey && !_.get(query, "access-token")) {
      config.url += (config.url.match(/[\?]/g) ? "&" : "?") + "access-token=" + app.authKey
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Add a response interceptor
axios.interceptors.response.use(
  (response) => {
    return _.get(response, "data", response)
  },
  (error) => {
    return Promise.reject(_.get(error, "response.data", error))
  }
)

const updateObject = (oldObject, updatedProperties) => {
  return {
    ...oldObject,
    ...updatedProperties,
  }
}

var app = new Vue({
  el: '#app',
  store,
  vuetify: new Vuetify(),
  data: {
    config: null,
    service_profiles: [],
    counter_services: [],

    loading_profile: false,

    tbwaiting: null,
    tbcalling: null,
    tbhold: null,

    waitingLoading: false,
    callingLoading: false,
    holdLoading: false,

    waiting_items: [],
    calling_items: [],
    hold_items: [],
  },
  computed: {
    authKey() {
      return _.get(this.config, "auth_key")
    },
    ...mapState({
      form: (state) => state.form,
    }),
    serviceProfile() {
      return this.service_profiles.find(item => item.service_profile_id === this.form.service_profile_id)
    },
    services() {
      return _.get(this.serviceProfile, 'services', [])
    },
    counterServiceOpts() {
      return _.filter(this.counter_services, (counter) => counter.counterservice_type === _.get(this.serviceProfile, 'counterservice_typeid'))
    },
    filteredCounters() {
      // if (!this.form.counter_service_ids.length) return this.counterServiceOpts
      let counters = this.counterServiceOpts
      if (this.form.counter_service_ids.length) {
        counters = _.filter(this.counterServiceOpts, (item) => this.form.counter_service_ids.includes(item.counterserviceid))
      }

      let rows = []
      const data = {
        q_num: '-',
        caller_ids: null,
        q_ids: null,
        qtran_ids: null,
        pt_name: '-'
      }
      for (let i = 0; i < counters.length; i++) {
        const counter = counters[i];
        const row = _.find(this.calling_items, (row) => row.counter_service_id === counter.counterserviceid)
        if (row) {
          const showBtnPharmacy = row.serviceid === 12 && row.serviceid !== 11 && row.countdrug > 0 && parseInt(this.form.service_profile_id) !== 21 && row.total_drug === 0
          const showBtnDoctor = ![11, 12, 13].includes(row.serviceid)
          rows.push(_.assign(counter, {
            data: row,
            disabled_btn_call: false,
            disabled_btn_hold: false,
            disabled_btn_finish: false,
            disabled_btn_doctor: false,
            disabled_btn_pharmacy: false,
            show_btn_pharmacy: showBtnPharmacy,
            show_btn_doctor: showBtnDoctor
          }))
        } else {
          rows.push(_.assign(counter, {
            data: data,
            disabled_btn_call: false,
            disabled_btn_hold: true,
            disabled_btn_finish: true,
            disabled_btn_doctor: true,
            disabled_btn_pharmacy: true,
            show_btn_pharmacy: false,
            show_btn_doctor: true
          }))
        }
      }

      return rows
    },
    counterInputOpts() {
      const opts = {}
      for (let i = 0; i < this.filteredCounters.length; i++) {
        const row = this.filteredCounters[i];
        if (!row.data.caller_ids) {
          opts[row.counterserviceid] = row.counterservice_name
        }
      }
      return opts
    }
  },
  beforeMount() {
    this.getConfig();
  },
  mounted() {
    $("body").toggleClass("hide-sidebar");
  },
  methods: {
    ...mapActions({
      setFormData: "setFormData",
    }),
    // การตั้งค่า
    getConfig: async function () {
      const _this = this
      try {
        const config = await axios.get(`/app/calling/config`)
        _this.config = config
        _this.$nextTick(function () {
          _this.getCounters()
          _this.getServiceProfiles()
          _this.initTableWaiting()
          _this.initTableCalling()
          _this.initTableHold()
        })
      } catch (err) {
        _this.showMessageError(err)
      }
    },
    // counters
    getCounters: async function () {
      const _this = this
      try {
        const counters = await axios.get(`/node/api/get_counterservice_list`)
        _this.counter_services = counters
      } catch (err) {
        _this.showMessageError(err)
      }
    },
    // service profiles
    getServiceProfiles: async function () {
      const _this = this
      try {
        _this.loading_profile = true
        const { data } = await axios.get(`/node/api/v1/setting/service-profiles`)
        _this.service_profiles = data
        _this.loading_profile = false
      } catch (err) {
        _this.loading_profile = false
        _this.showMessageError(err)
      }
    },
    // calling
    getDataCalling: async function () {
      const _this = this
      try {
        // var info = _this.tbcalling.page.info()
        var page = {
          number: 1 //info.page + 1,
          // size: info.length,
        }
        const params = decodeURIComponent($.param({
          page: page,
          form: _this.form,
          sort: {
            call_timestp: 'asc'
          }
        }))
        const { data } = await axios.get(`/node/api/v1/calling/calling-list?${params}`)
        _this.calling_items = data
      } catch (err) {
        _this.showMessageError(err)
      }
    },
    // show error
    showMessageError: function (error) {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: _.get(error, "message", error),
        confirmButtonText: "ปิด",
      })
    },

    updateProfileInput(value) {
      this.setFormData(updateObject(this.form, { service_profile_id: parseInt(value) }))
    },
    updateServiceInput(value) {
      this.setFormData(updateObject(this.form, { service_ids: value }))
    },
    updateCounterInput(value) {
      this.setFormData(updateObject(this.form, { counter_service_ids: value }))
    },

    initTableWaiting: function () {
      const _this = this
      var tbwaiting = jQuery('#tb-waiting').DataTable({
        ajax: {
          url: '/node/api/v1/calling/waiting-list',
          data: function (d, settings) {
            var api = new $.fn.dataTable.Api(settings)
            var info = api.page.info()
            var page = {
              number: info.page + 1,
              size: info.length,
            }
            if (info.length !== -1) {
              return $.extend({}, d, {
                page: page,
                form: _this.form,
                'access-token': _this.authKey,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              'access-token': _this.authKey,
            })
          },
          type: 'GET',
        },
        dom: "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
        language: {
          loadingRecords: 'กำลังดำเนินการ...',
          zeroRecords: '',
          lengthMenu: '_MENU_',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว',
          infoEmpty: 'แสดง 0 ถึง 0 จาก 0 แถว',
          infoFiltered: '(กรองข้อมูล _MAX_ ทุกแถว)',
          emptyTable: 'ไม่พบข้อมูล',
          oPaginate: {
            sFirst: 'หน้าแรก',
            sPrevious: 'ก่อนหน้า',
            sNext: 'ถัดไป',
            sLast: 'หน้าสุดท้าย',
          },
          search: '_INPUT_ ',
          searchPlaceholder: 'ค้นหา...',
        },
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 75, 100, -1],
          [10, 25, 50, 75, 100, 'All'],
        ],
        autoWidth: false,
        deferRender: true,
        searchHighlight: true,
        responsive: false,
        processing: true,
        serverSide: true,
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
        },
        initComplete: function () {
          var api = this.api()
          dtFnc.initResponsive(api)
        },
        columns: [
          {
            data: null,
            defaultContent: '',
            className: 'text-center',
            render: function (data, type, row, meta) {
              return meta.row + 1
            },
          },
          {
            data: 'q_num',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-money"></i> คิว',
            render: function (data, type, row, meta) {
              return `<span class="badge badge-success">${row.q_num}</span>`
            },
          },
          {
            data: 'q_hn',
            className: 'dt-body-center dt-head-nowrap',
            title: 'HN',
          },
          {
            data: 'q_qn',
            className: 'dt-body-center dt-head-nowrap',
            title: 'QN',
          },
          {
            data: 'pt_name',
            className: 'dt-body-left dt-head-nowrap',
            title: 'ชื่อ',
          },
          {
            data: 'service_name',
            className: 'dt-body-left dt-head-nowrap',
            title: 'ประเภท',
            render: function (data, type, row, meta) {
              if (row.quickly === 1) {
                return `คิวด่วน`
              }
              return row.service_name
            },
          },
          {
            data: 'service_status_name',
            className: 'dt-body-center dt-head-nowrap',
            title: 'สถานะ',
          },
          {
            data: 'queue_date',
            className: 'dt-body-center dt-head-nowrap',
            title: 'วันที่',
            render: function (data, type, row, meta) {
              return moment(row.queue_date).format('DD/MM/YYYY')
            },
          },
          {
            data: 'queue_time',
            className: 'dt-body-center dt-head-nowrap',
            title: 'เวลา',
            render: function (data, type, row, meta) {
              return moment(row.queue_time, 'HH:mm:ss').format('HH:mm')
            },
          },
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-nowrap',
            orderable: false,
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            render: function (data, type, row, meta) {
              return `<a href="/node/api/v1/calling/call?id=${row.q_ids}" class="btn btn-info btn-calling">เรียกคิว</a>`
            },
          },
        ],
        columnDefs: [
          {
            targets: [3, 5, 6, 7],
            visible: false,
          },
        ],
        buttons: [
          {
            extend: 'colvis',
            text: '<i class="glyphicon glyphicon-list"></i> ',
          },
          {
            text: '<i class="glyphicon glyphicon-refresh"></i> ',
            action: function (e, dt, node, config) {
              dt.ajax.reload()
            },
          },
        ],
        order: [[8, 'asc']],
      })

      jQuery('#tb-waiting')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonText: 'ปิด'
          })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.waitingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('#total-wait').html(_.get(json, 'total', 0))
          _this.waitingLoading = false
        })
        .on('draw.dt', function () {
          var info = tbwaiting.page.info();
          tbwaiting.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
          });

          var last = null
          var rows = tbwaiting
            .rows({
              page: 'current',
            })
            .nodes()
          var columns = tbwaiting.columns().nodes()
          tbwaiting
            .column(5, {
              page: 'current',
            })
            .data()
            .each(function (group, i) {
              var data = tbwaiting.rows(i).data()
              if (last !== group) {
                var clss = ''
                if (data[0].quickly === 1) {
                  group = 'คิวด่วน'
                  clss = 'warning'
                }
                $(rows)
                  .eq(i)
                  .before(
                    '<tr class="' +
                    clss +
                    '"><td style="text-align: left;font-size:16px" colspan="' +
                    columns.length +
                    '">' +
                    group +
                    '</td></tr>'
                  )
                last = group
              }
            })
        })

      this.tbwaiting = tbwaiting
    },

    // calling
    initTableCalling: function () {
      const _this = this
      var tbcalling = jQuery('#tb-calling').DataTable({
        ajax: {
          url: '/node/api/v1/calling/calling-list',
          data: function (d, settings) {
            var api = new $.fn.dataTable.Api(settings)
            var info = api.page.info()
            var page = {
              number: info.page + 1,
              size: info.length,
            }
            if (info.length !== -1) {
              return $.extend({}, d, {
                page: page,
                form: _this.form,
                'access-token': _this.authKey,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              'access-token': _this.authKey,
            })
          },
          type: 'GET',
        },
        dom: "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
        language: {
          loadingRecords: 'กำลังดำเนินการ...',
          zeroRecords: '',
          lengthMenu: '_MENU_',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว',
          infoEmpty: 'แสดง 0 ถึง 0 จาก 0 แถว',
          infoFiltered: '(กรองข้อมูล _MAX_ ทุกแถว)',
          emptyTable: 'ไม่พบข้อมูล',
          oPaginate: {
            sFirst: 'หน้าแรก',
            sPrevious: 'ก่อนหน้า',
            sNext: 'ถัดไป',
            sLast: 'หน้าสุดท้าย',
          },
          search: '_INPUT_ ',
          searchPlaceholder: 'ค้นหา...',
        },
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 75, 100, -1],
          [10, 25, 50, 75, 100, 'All'],
        ],
        autoWidth: false,
        deferRender: true,
        searchHighlight: true,
        responsive: true,
        processing: true,
        serverSide: true,
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
        },
        initComplete: function () {
          var api = this.api()
          dtFnc.initResponsive(api)
          // dtFnc.initColumnIndex(api)
        },
        columns: [
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-head-nowrap',
            title: '#',
            orderable: false,
          },
          {
            data: 'q_num',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-money"></i> คิว',
            render: function (data, type, row, meta) {
              return `<span class="badge badge-success">${row.q_num}</span>`
            },
          },
          {
            data: 'q_hn',
            className: 'dt-body-center dt-head-nowrap',
            title: 'HN',
          },
          {
            data: 'q_qn',
            className: 'dt-body-center dt-head-nowrap',
            title: 'QN',
          },
          {
            data: 'pt_name',
            className: 'dt-body-left dt-head-nowrap',
            title: 'ชื่อ',
          },
          {
            data: 'service_name',
            className: 'dt-body-left dt-head-nowrap',
            title: 'ประเภท',
            render: function (data, type, row, meta) {
              if (row.quickly === 1) {
                return `คิวด่วน`
              }
              return row.service_name
            },
          },
          {
            data: 'counterservice_name',
            className: 'dt-body-center dt-head-nowrap',
            title: 'จุดบริการ',
          },
          {
            data: 'call_timestp',
            className: 'dt-body-center dt-head-nowrap',
            title: 'เวลาเรียกคิว',
            render: function (data, type, row, meta) {
              return moment(row.call_timestp, 'YYYY-MM-DD HH:mm:ss').format('HH:mm')
            },
          },
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-nowrap',
            orderable: false,
            visible: false,
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            responsivePriority: 1,
            render: function (data, type, row, meta) {
              let waitingbtn = ``
              if (row.serviceid === 12 && row.serviceid !== 11 && row.countdrug > 0 && parseInt(_this.form.service_profile_id) !== 21 && row.total_drug === 0) {
                waitingbtn = `<a href="/node/api/v1/calling/waiting-pharmacy?id=${row.caller_ids}" class="btn btn-info btn-send-to-pharmacy" title="ส่งห้องยา">ส่งห้องยา</a>`
              } else if (![11, 12, 13].includes(row.serviceid)) {
                waitingbtn = `<a href="/node/api/v1/calling/waiting-doctor?id=${row.caller_ids}" class="btn btn-info btn-send-to-doctor" title="ส่งห้องแพทย์">ส่งห้องแพทย์</a>`
              }
              return `<a href="/node/api/v1/calling/recall?id=${row.caller_ids}" class="btn btn-success btn-recall">เรียกซ้ำ</a>
            <a href="/node/api/v1/calling/hold?id=${row.caller_ids}" class="btn btn-warning btn-hold">พักคิว</a>
            <a href="/node/api/v1/calling/end?id=${row.caller_ids}" class="btn btn-danger btn-end">เสร็จสิ้น</a>
            ${waitingbtn}`
            },
          },
        ],
        columnDefs: [
          {
            targets: [2, 3, 5, 6],
            visible: false,
          },
        ],
        buttons: [
          {
            extend: 'colvis',
            text: '<i class="glyphicon glyphicon-list"></i> ',
          },
          {
            text: '<i class="glyphicon glyphicon-refresh"></i> ',
            action: function (e, dt, node, config) {
              dt.ajax.reload()
            },
          },
        ],
      })

      jQuery('#tb-calling')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonText: 'ปิด'
          })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.callingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('#total-calling').html(_.get(json, 'total', 0))
          _this.callingLoading = false
          _this.getDataCalling()
        })
        .on('draw.dt', function () {
          var info = tbcalling.page.info();
          tbcalling.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
          });

          var last = null
          var rows = tbcalling.rows({ page: 'current' }).nodes()
          var columns = tbcalling.columns().nodes()
          tbcalling
            .column(2, { page: 'current' })
            .data()
            .each(function (group, i) {
              var data = tbcalling.rows(i).data()
              if (last !== group) {
                var clss = ''
                if (data[0].quickly === 1) {
                  group = 'คิวด่วน'
                  clss = 'warning'
                }
                $(rows)
                  .eq(i)
                  .before(
                    '<tr class="' +
                    clss +
                    '"><td style="text-align: left;font-size:16px" colspan="' +
                    columns.length +
                    '">' +
                    group +
                    '</td></tr>'
                  )
                last = group
              }
            })
        })

      _this.tbcalling = tbcalling
    },

    // hold
    initTableHold: function () {
      const _this = this
      var tbhold = jQuery('#tb-hold').DataTable({
        ajax: {
          url: '/node/api/v1/calling/hold-list',
          data: function (d) {
            var table = $('#tb-hold').DataTable()
            var info = table.page.info()
            var page = {
              number: info.page + 1,
              size: info.length,
            }
            if (info.length !== -1) {
              return $.extend({}, d, {
                page: page,
                form: _this.form,
                'access-token': _this.authKey,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              'access-token': _this.authKey,
            })
          },
          type: 'GET',
        },
        dom: "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
        language: {
          loadingRecords: 'กำลังดำเนินการ...',
          zeroRecords: '',
          lengthMenu: '_MENU_',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว',
          infoEmpty: 'แสดง 0 ถึง 0 จาก 0 แถว',
          infoFiltered: '(กรองข้อมูล _MAX_ ทุกแถว)',
          emptyTable: 'ไม่พบข้อมูล',
          oPaginate: { sFirst: 'หน้าแรก', sPrevious: 'ก่อนหน้า', sNext: 'ถัดไป', sLast: 'หน้าสุดท้าย' },
          search: '_INPUT_ ',
          searchPlaceholder: 'ค้นหา...',
        },
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 75, 100, -1],
          [10, 25, 50, 75, 100, 'All'],
        ],
        autoWidth: false,
        deferRender: true,
        searchHighlight: true,
        responsive: true,
        processing: true,
        serverSide: true,
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
        },
        initComplete: function () {
          var api = this.api()
          dtFnc.initResponsive(api)
        },
        columns: [
          { data: null, defaultContent: '', className: 'dt-center dt-head-nowrap', title: '#', orderable: false },
          {
            data: 'q_num',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-money"></i> คิว',
            render: function (data, type, row, meta) {
              return `<span class="badge badge-success">${row.q_num}</span>`
            },
          },
          { data: 'q_hn', className: 'dt-body-center dt-head-nowrap', title: 'HN' },
          { data: 'q_qn', className: 'dt-body-center dt-head-nowrap', title: 'QN' },
          { data: 'pt_name', className: 'dt-body-left dt-head-nowrap', title: 'ชื่อ' },
          {
            data: 'service_name',
            className: 'dt-body-left dt-head-nowrap',
            title: 'ประเภท',
            render: function (data, type, row, meta) {
              if (row.quickly === 1) {
                return `คิวด่วน`
              }
              return row.service_name
            },
          },
          { data: 'counterservice_name', className: 'dt-body-center dt-head-nowrap', title: 'จุดบริการ' },
          // { data: 'service_status_name', className: 'dt-body-center dt-head-nowrap', title: 'สถานะ' },
          {
            data: 'queue_date',
            className: 'dt-body-center dt-head-nowrap',
            title: 'วันที่',
            render: function (data, type, row, meta) {
              return moment(row.queue_date).format('DD/MM/YYYY')
            },
            visible: false,
          },
          {
            data: 'queue_time',
            className: 'dt-body-center dt-head-nowrap',
            title: 'เวลา',
            render: function (data, type, row, meta) {
              return moment(row.queue_time, 'HH:mm:ss').format('HH:mm')
            },
            visible: false,
          },
          {
            data: 'hold_time',
            className: 'dt-body-center dt-head-nowrap',
            title: 'เวลาพักคิว',
            render: function (data, type, row, meta) {
              return moment(row.hold_time, 'YYYY-MM-DD HH:mm:ss').format('HH:mm')
            },
          },
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-nowrap',
            orderable: false,
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            render: function (data, type, row, meta) {
              return `<a href="/node/api/v1/calling/recall?id=${row.caller_ids}" class="btn btn-info btn-calling">เรียกคิว</a>
            <a href="/node/api/v1/calling/end?id=${row.caller_ids}" class="btn btn-success btn-end">เสร็จสิ้น</a>`
            },
          },
        ],
        columnDefs: [{ targets: [2, 3, 5, 6, 7], visible: false }],
        buttons: [
          { extend: 'colvis', text: '<i class="glyphicon glyphicon-list"></i> ' },
          {
            text: '<i class="glyphicon glyphicon-refresh"></i> ',
            action: function (e, dt, node, config) {
              dt.ajax.reload()
            },
          },
        ],
      })
      jQuery('#tb-hold')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonText: 'ปิด'
          })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.holdLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('#total-hold').html(_.get(json, 'total', 0))
          _this.holdLoading = false
        })
        .on('draw.dt', function () {
          var info = tbhold.page.info();
          tbhold.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
          });

          var last = null
          var rows = tbhold.rows({ page: 'current' }).nodes()
          var columns = tbhold.columns().nodes()
          tbhold
            .column(2, { page: 'current' })
            .data()
            .each(function (group, i) {
              var data = tbhold.rows(i).data()
              if (last !== group) {
                var clss = ''
                if (data[0].quickly === 1) {
                  group = 'คิวด่วน'
                  clss = 'warning'
                }
                $(rows)
                  .eq(i)
                  .before(
                    '<tr class="' +
                    clss +
                    '"><td style="text-align: left;font-size:16px" colspan="' +
                    columns.length +
                    '">' +
                    group +
                    '</td></tr>'
                  )
                last = group
              }
            })
        })

      _this.tbhold = tbhold
    },

    onCallWaiting: async function ({ data, key, url }) {
      const _this = this
      try {
        const { value: response } = await Swal.fire({
          text: 'เลือกช่องบริการ',
          icon: 'question',
          input: 'select',
          title: `เรียกคิว ${data.q_num}`,
          inputOptions: _this.counterInputOpts,
          inputPlaceholder: 'ช่องบริการ',
          showCancelButton: true,
          allowOutsideClick: false,
          allowEscapeKey: false,
          inputAttributes: {
            class: 'form-control'
          },
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          inputValidator: (value) => {
            return new Promise((resolve) => {
              if (!value) {
                resolve('กรุณาเลือกช่องบริการ')
              } else {
                resolve()
              }
            })
          },
          showLoaderOnConfirm: true,
          preConfirm: async function (value) {
            try {
              const response = await axios.post(url, {
                data: data, //Data in column Datatable
                ..._this.form,
                counter_service_id: value,
              })
              return response
            } catch (error) {
              _this.showMessageError(error)
            }
          }
        })

        if (response) {
          socket.emit('call', response) //sending data
          _this.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
          _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
          // _this.getDataCalling()
          setTimeout(() => {
            $(`#queue-${data.q_num}`).addClass('blink')
            setTimeout(() => {
              $(`#queue-${data.q_num}`).removeClass('blink')
            }, 7000);
          }, 500);
        }
      } catch (error) {
        _this.showMessageError(error)
      }
    },

    onCall(counter) {
      const _this = this
      if (!counter.data.caller_ids) {
        _this.onCallNext(counter)
      } else {
        _this.onReCall(counter)
      }
    },

    async onCallNext(counter) {
      const _this = this
      const data = _.get(_this.tbwaiting.rows(0).data(), '[0]', null)
      if (!data) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `เรียกคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'เรียกคิว',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/call?id=${data.q_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('call', response) //sending data
            _this.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
            setTimeout(() => {
              $(`#queue-${data.q_num}`).addClass('blink')
              setTimeout(() => {
                $(`#queue-${data.q_num}`).removeClass('blink')
              }, 7000);
            }, 500);
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onReCall(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `เรียกคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'เรียกคิว',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            didOpen: () => {
              Swal.clickConfirm()
            },
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/recall?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('call', response) //sending data
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
            _this.tbhold.ajax.reload() //โหลดข้อมูลคิวพัก
            setTimeout(() => {
              $(`#queue-${data.q_num}`).addClass('blink')
              setTimeout(() => {
                $(`#queue-${data.q_num}`).removeClass('blink')
              }, 7000);
            }, 500);
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onHold(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `พักคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'พักคิว',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            didOpen: () => {
              Swal.clickConfirm()
            },
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/hold?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('hold', response) //sending data
            _this.tbhold.ajax.reload() //โหลดข้อมูลคิวพัก
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onFinish(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `เสร็จสิ้นคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'เสร็จสิ้น',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/end?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('finish', response) //sending data
            _this.tbhold.ajax.reload() //โหลดข้อมูลคิวพัก
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onSendToDoctor(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `ยืนยันส่งคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ส่งคิว',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/waiting-doctor?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('finish', response) //sending data
            _this.tbhold.ajax.reload() //โหลดข้อมูลคิวพัก
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onCallHold(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            text: 'เลือกช่องบริการ',
            icon: 'question',
            input: 'select',
            title: `เรียกคิว ${data.q_num}`,
            inputOptions: _this.counterInputOpts,
            inputPlaceholder: 'ช่องบริการ',
            inputValue: counter.counterserviceid,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            inputAttributes: {
              class: 'form-control'
            },
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'เรียกคิว',
            cancelButtonText: 'ยกเลิก',
            inputValidator: (value) => {
              return new Promise((resolve) => {
                if (!value) {
                  resolve('กรุณาเลือกช่องบริการ')
                } else {
                  resolve()
                }
              })
            },
            showLoaderOnConfirm: true,
            preConfirm: async function (value) {
              try {
                const response = await axios.post(`/node/api/v1/calling/recall?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: value,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('call', response) //sending data
            _this.tbcalling.ajax.reload() //โหลดข้อมูลคิวรอ
            _this.tbhold.ajax.reload() //โหลดข้อมูลกำลังเรียก
            setTimeout(() => {
              $(`#queue-${data.q_num}`).addClass('blink')
              setTimeout(() => {
                $(`#queue-${data.q_num}`).removeClass('blink')
              }, 7000);
            }, 1000);
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    async onSendToPharmacy(counter) {
      const _this = this
      const data = _.get(counter, 'data', null)
      if (!_.get(data, 'caller_ids', null)) {
        Swal.fire({
          icon: 'warning',
          title: 'ไม่พบรายการคิว',
          text: '',
          showConfirmButton: false,
          timer: 1500
        })
      } else {
        try {
          const { value: response } = await Swal.fire({
            icon: 'question',
            title: `ยืนยันส่งคิว ${data.q_num} ?`,
            html: `<p class="mb-0">${data.pt_name}</p><p class="m-0"><small>${counter.counterservice_name}</small></p>`,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ส่งคิว',
            cancelButtonText: 'ยกเลิก',
            showLoaderOnConfirm: true,
            preConfirm: async function () {
              try {
                const response = await axios.post(`/node/api/v1/calling/waiting-pharmacy?id=${data.caller_ids}`, {
                  data: data, //Data in column Datatable
                  ..._this.form,
                  counter_service_id: counter.counterserviceid,
                })
                return response
              } catch (error) {
                _this.showMessageError(error)
              }
            }
          })
          if (response) {
            socket.emit('finish', response) //sending data
            _this.tbhold.ajax.reload() //โหลดข้อมูลคิวพัก
            _this.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
          }
        } catch (error) {
          _this.showMessageError(error)
        }
      }
    },

    onChangeServiceProfile() {
      setTimeout(() => {
        this.tbwaiting.ajax.reload()
        this.tbcalling.ajax.reload()
        this.tbhold.ajax.reload()
      }, 1000);
      this.setFormData(updateObject(this.form, { service_ids: [], counter_service_ids: [] }))
    },
    onChangeService() {
      setTimeout(() => {
        this.tbwaiting.ajax.reload()
        this.tbcalling.ajax.reload()
        this.tbhold.ajax.reload()
      }, 100);
      this.setFormData(updateObject(this.form, { counter_service_ids: [] }))
    },
    onChangeCounter() {
      setTimeout(() => {
        this.tbwaiting.ajax.reload()
        this.tbcalling.ajax.reload()
        this.tbhold.ajax.reload()
      }, 100);
    },
  },
})

//เรียกคิว
$('#tb-waiting tbody').on('click', 'tr td a.btn-calling', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof app.tbwaiting.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = app.tbwaiting.row(tr).data()
  app.onCallWaiting({ data, key, url })
});

//เรียกคิว hold
$('#tb-hold tbody').on('click', 'tr td a.btn-calling', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = app.tbhold.row(tr).data()
  // const counter = _.find(app.filteredCounters, (item) => item.data.caller_ids && item.counterserviceid === data.counter_service_id)
  // if (counter) {
  //   Swal.fire({
  //     icon: 'warning',
  //     title: 'ช่องบริการไม่ว่าง',
  //     text: data.counterservice_name,
  //     showConfirmButton: false,
  //     timer: 2000
  //   })
  // } else {

  // }
  app.onCallHold({
    counterserviceid: data.counter_service_id,
    counterservice_name: data.counterservice_name,
    data: data
  })
});

//End คิว hold
$('#tb-hold tbody').on('click', 'tr td a.btn-end', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = app.tbhold.row(tr).data()
  app.onFinish({
    counterserviceid: data.counter_service_id,
    counterservice_name: data.counterservice_name,
    data: data
  })
});

$('#tb-calling tbody').on('click', 'tr td a.btn-send-to-doctor', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = app.tbhold.row(tr).data()
  app.onSendToDoctor({
    counterserviceid: data.counter_service_id,
    counterservice_name: data.counterservice_name,
    data: data
  })
});

$('#tb-calling tbody').on('click', 'tr td a.btn-send-to-pharmacy', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = app.tbhold.row(tr).data()
  app.onSendToPharmacy({
    counterserviceid: data.counter_service_id,
    counterservice_name: data.counterservice_name,
    data: data
  })
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$('body').tooltip({
  selector: "[data-toggle=tooltip]"
});

socket
  .on('register', (res) => {
    //ออกบัตรคิว
    var services = String(_.get(app.serviceProfile, 'service_id')).split(',')
    // dt_tbqdata.ajax.reload()
    if (jQuery.inArray(String(res.modelQueue.serviceid).toString(), services) !== -1) {
      $('#jplayer_notify').jPlayer({
        ready: function () {
          $(this)
            .jPlayer('setMedia', {
              mp3: '/media/alert.mp3',
            })
            .jPlayer('play')
        },
        supplied: 'mp3',
        ended: function () {
          // The $.jPlayer.event.ended event
          $(this).jPlayer('stop') // Repeat the media
        },
      })
      $('#jplayer_notify').jPlayer('play')

      app.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
      toastr.warning(res.modelQueue.q_num, 'คิวใหม่!', {
        timeOut: 7000,
        positionClass: 'toast-top-right',
        progressBar: true,
        closeButton: true,
      })
    }
  })
  .on('call', (res) => {
    var t1 = app.tbwaiting
    var t2 = app.tbcalling
    var t3 = app.tbhold

    t1.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.q_ids) === parseInt(_.get(res, 'modelQueue.q_ids')) ||
        parseInt(data.q_ids) === parseInt(_.get(res, 'modelQ.q_ids'))
      ) {
        app.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
        app.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
      }
    })
    t2.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbcalling.ajax.reload()
      }
    })
    t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbhold.ajax.reload()
        app.tbcalling.ajax.reload()
      }
    })
  })
  .on('hold', (res) => {
    var t2 = app.tbcalling
    var t3 = app.tbhold
    t2.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbcalling.ajax.reload()
        app.tbhold.ajax.reload()
      }
    })
    t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbcalling.ajax.reload()
        app.tbhold.ajax.reload()
      }
    })
  })
  .on('finish', (res) => {
    var t2 = app.tbcalling
    var t3 = app.tbhold
    var services = String(_.get(app.serviceProfile, 'service_id')).split(',')
    if (jQuery.inArray(String(res.modelQueue.serviceid).toString(), services) != -1) {
      app.tbwaiting.ajax.reload()
    }
    t2.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbcalling.ajax.reload()
        app.tbhold.ajax.reload()
      }
    })
    t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (
        parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
        parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
      ) {
        app.tbcalling.ajax.reload()
        app.tbhold.ajax.reload()
      }
    })
  })
  .on('display', (res) => {
    setTimeout(function () {
      app.tbcalling.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (data.q_num == res.title) {
          $('#tb-calling').find('tr.success').removeClass('success')
          $('#last-queue').html(data.q_num)
          app.tbcalling.$('tr#' + res.artist.data.DT_RowId).addClass('success')
          toastr.warning(data.q_num, '<i class="pe-7s-speaker"></i> กำลังเรียกคิว', {
            timeOut: 7000,
            positionClass: 'toast-top-right',
            progressBar: true,
            closeButton: true,
          })
        }
      })
    }, 500)
  })