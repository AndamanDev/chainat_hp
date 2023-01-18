Vue.use(Vuex)

const { mapActions, mapGetters } = Vuex

const store = new Vuex.Store({
  state: {
    service_profile_id: null,
    counter_service_id: null,
  },
  getters: {
    form: (state) => {
      return {
        service_profile_id: state.service_profile_id,
        counter_service_id: state.counter_service_id,
      }
    },
  },
  mutations: {
    SET_STATE(state, { key, value }) {
      state[key] = value
    },
  },
  actions: {
    setState({ commit }, payload) {
      commit('SET_STATE', payload)
    },
  },
  plugins: [createPersistedState({
    key: 'vuex-medical',
  })],
})

var app = new Vue({
  el: '#app',
  store,
  data: {
    // form: {
    //   service_profile_id: null,
    //   counter_service_id: null,
    // },
    tbwaiting: null,
    tbcalling: null,
    tbhold: null,

    waitingLoading: false,
    callingLoading: false,
    holdLoading: false,

    modelServiceProfile: null,
  },
  computed: {
    ...mapGetters({
      form: 'form',
    }),
  },
  watch: {
    form: {
      handler: function (val, oldVal) {

      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(function () {
      $('#counter_service_id').val(this.form.counter_service_id)
      $('#callingform-service_profile').val(this.form.service_profile_id).trigger('change')
      $('#callingform-service_profile').trigger('select2:select')
      this.initTableWaiting()
      this.initTableCalling()
      this.initTableHold()
    })
  },
  methods: {
    ...mapActions({
      setState: 'setState',
    }),
    fetchDataServiceProfile: function () {
      const _this = this
      $.ajax({
        method: 'GET',
        url: `/app/calling/service-profile`,
        dataType: 'json',
        data: {
          id: _this.form.service_profile_id
        },
        success: function (res) {
          _this.modelServiceProfile = res
        },
        error: function (jqXHR, textStatus, errorThrown) {
          Queue.ajaxAlertError(errorThrown)
        },
      })
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
              })
            }
            return $.extend({}, d, {
              form: _this.form,
            })
          },
          type: 'POST',
          complete: function (jqXHR, textStatus) { },
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
          var count = api.data().count()
          // $('.count-waiting').html(count)

          // if (keySelected.length > 0 && keySelected != undefined && localStorage.getItem("medical-tablet-mode") == "true") {
          //   var indexRemove = [];
          //   $.each(keySelected, function (index, value) {
          //     var tr = $("#tb-waiting").find("tr#" + value);
          //     if (tr.length == 1) {
          //       $("#checkbox-" + value).prop("checked", true);
          //       $("#tb-waiting tr#" + value).addClass("success");
          //     } else {
          //       indexRemove.push(index);
          //     }
          //   });
          //   $.each(indexRemove, function (i, k) {
          //     keySelected.splice(k, 1);
          //   });
          //   $('.count-selected').html('(' + keySelected.length + ')');
          //   if (keySelected.length == 0) {
          //     $('button.on-call-selected').prop('disabled', true);
          //   }
          // }
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
            data: 'checkbox',
            defaultContent: '',
            className: 'dt-center dt-head-nowrap',
            title: '#',
            orderable: false,
            visible: false,
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
              return `<a href="/node/api/v1/calling/call?id=${row.q_ids}" class="btn btn-success btn-calling">เรียกคิว</a>`
            },
          },
        ],
        columnDefs: [
          {
            targets: [4, 6, 7],
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
        order: [[9, 'asc']],
      })

      jQuery('#tb-waiting')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          swal({
            title: 'Error...!',
            html: '<small>' + message + '</small>',
            type: 'error',
          })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.waitingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('.count-waiting').html(_.get(json, 'total', 0))
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
            .column(6, {
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
          data: function (d) {
            var table = $('#tb-calling').DataTable()
            var info = table.page.info()
            var page = {
              number: info.page + 1,
              size: info.length,
            }
            if (info.length !== -1) {
              return $.extend({}, d, {
                page: page,
                form: _this.form,
                // modelForm: modelForm,
                // modelProfile: modelProfile,
              })
            }
            return $.extend({}, d, {
              // modelForm: modelForm,
              // modelProfile: modelProfile,
              form: _this.form,
            })
          },
          type: 'POST',
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
          var count = api.data().count()
          // var rows = api.rows( {page:"current"} ).nodes();
          // var columns = api.columns().nodes();
          // var last=null;
          // api.column(2, {page:"current"} ).data().each( function ( group, i ) {
          //     var data = api.rows(i).data();
          //     if ( last !== group ) {
          //         if(data[0].quickly == "1"){
          //             $(rows).eq( i ).before(
          //                 '<tr class="warning"><td style="text-align: left;font-size:16px" colspan="'+columns.length+'">'+group +'</td></tr>'
          //             );
          //         }else{
          //             $(rows).eq( i ).before(
          //                 '<tr class=""><td style="text-align: left;font-size:16px" colspan="'+columns.length+'">'+group +'</td></tr>'
          //             );
          //         }

          //         last = group;
          //     }
          // } );
        },
        initComplete: function () {
          var api = this.api()
          dtFnc.initResponsive(api)
          dtFnc.initColumnIndex(api)
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
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            render: function (data, type, row, meta) {
              let waitingbtn = ``
              if (row.serviceid === 12 && row.serviceid !== 11 && row.countdrug > 0 && parseInt(_this.form.service_profile_id) !== 21 && row.total_drug === 0) {
                waitingbtn = `<a href="/node/api/v1/calling/waiting-pharmacy?id=${row.caller_ids}" class="btn btn-info btn-waiting" title="ส่งห้องยา">ส่งห้องยา</a>`
              } else if (![11, 12, 13].includes(row.serviceid)) {
                waitingbtn = `<a href="/node/api/v1/calling/waiting-doctor?id=${row.caller_ids}" class="btn btn-info btn-waiting" title="ส่งห้องแพทย์">ส่งห้องแพทย์</a>`
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
            targets: [2, 3, 5, 7],
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
          swal({
            title: 'Error...!',
            html: '<small>' + message + '</small>',
            type: 'error',
          })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.callingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('.count-calling').html(_.get(json, 'total', 0))
          _this.callingLoading = false
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
                // modelForm: modelForm,
                // modelProfile: modelProfile,
              })
            }
            return $.extend({}, d, {
              // modelForm: modelForm,
              // modelProfile: modelProfile,
              form: _this.form,
            })
          },
          type: 'POST',
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
        responsive: false,
        processing: true,
        serverSide: true,
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
          var count = api.data().count()

          // var rows = api.rows({ page: 'current' }).nodes()
          // var columns = api.columns().nodes()
          // var last = null
          // api
          //   .column(2, { page: 'current' })
          //   .data()
          //   .each(function (group, i) {
          //     var data = api.rows(i).data()
          //     if (last !== group) {
          //       if (data[0].quickly == '1') {
          //         $(rows)
          //           .eq(i)
          //           .before(
          //             '<tr class="warning"><td style="text-align: left;font-size:16px" colspan="' +
          //               columns.length +
          //               '">' +
          //               group +
          //               '</td></tr>'
          //           )
          //       } else {
          //         $(rows)
          //           .eq(i)
          //           .before(
          //             '<tr class=""><td style="text-align: left;font-size:16px" colspan="' +
          //               columns.length +
          //               '">' +
          //               group +
          //               '</td></tr>'
          //           )
          //       }

          //       last = group
          //     }
          //   })
        },
        initComplete: function () {
          var api = this.api()
          dtFnc.initResponsive(api)
          dtFnc.initColumnIndex(api)
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
              return `<a href="/node/api/v1/calling/recall?id=${row.caller_ids}" class="btn btn-success btn-calling">เรียกคิว</a>
            <a href="/node/api/v1/calling/end?id=${row.caller_ids}" class="btn btn-danger btn-end">เสร็จสิ้น</a>`
            },
          },
        ],
        columnDefs: [{ targets: [2, 3, 5, 7], visible: false }],
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
          swal({ title: 'Error...!', html: '<small>' + message + '</small>', type: 'error' })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.holdLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('.count-hold').html(_.get(json, 'total', 0))
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
  },
})

$('#callingform-service_profile').on('change', function (e) {
  var val = $(this).val()
  app.setState({ key: 'service_profile_id', value: val })
  app.fetchDataServiceProfile()
  // if (app.tbwaiting) {
  //   app.tbwaiting.ajax.reload()
  // }
  // if (app.tbcalling) {
  //   app.tbcalling.ajax.reload()
  // }
})
$('#callingform-counter_service').on('depdrop:change', function (e) {
  var val = $(this).val()
  app.setState({ key: 'counter_service_id', value: val })
})
$('#callingform-counter_service').on('change', function (e) {
  var val = $(this).val()
  app.setState({ key: 'counter_service_id', value: val })
  if (app.tbwaiting && !app.waitingLoading) {
    app.tbwaiting.ajax.reload()
  }
  if (app.tbcalling && !app.callingLoading) {
    app.tbcalling.ajax.reload()
  }
})
//$('input[type="search"]').removeClass('input-sm').addClass('input-lg');
$('input[type="search"]').focus(function () {
  //animate
  $(this).animate(
    {
      width: '250px',
    },
    400
  )
})

$('input[type="search"]').blur(function () {
  $(this).animate(
    {
      width: '160px',
    },
    500
  )
})
// Toastr options
toastr.options = {
  debug: false,
  newestOnTop: false,
  positionClass: 'toast-top-center',
  closeButton: true,
  toastClass: 'animated fadeInDown',
}

Queue = {
  handleEventClick: function () {
    var self = this
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
      var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          input: 'radio',
          // inputOptions: {
          //     0 : "ต้องการ END ทันที",
          //     1 : "ไม่ต้องการ END ทันที"
          // },
          // inputValue: 1,
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function (value) {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  // if (res.status == 200) {
                  $('li.tab-watting, #tab-watting').removeClass('active')
                  $('li.tab-calling, #tab-calling').addClass('active')
                  self.reloadTbWaiting() //โหลดข้อมูลรอเรียก
                  self.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                  self.toastrSuccess('CALL ' + data.q_num)
                  //$("html, body").animate({ scrollTop: 0 }, "slow");
                  socket.emit('call', res) //sending data
                  if (value == 0) {
                    setTimeout(function () {
                      var tr = $('#tb-calling tr#' + res.modelCaller.caller_ids),
                        url = '/node/api/v1/calling?id=' + res.modelCaller.caller_ids
                      var key = tr.data('key')
                      var data = app.tbcalling.row(tr).data()
                      $.ajax({
                        method: 'POST',
                        url: `${url}&access-token=${accesstoken}`,
                        dataType: 'json',
                        data: {
                          data: data, //Data in column Datatable
                          ...app.form,
                          // modelForm: modelForm, //Data Model CallingForm
                          // modelProfile: modelProfile,
                        },
                        success: function (res) {
                          if (res.status == '200') {
                            self.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                            self.toastrSuccess('END ' + data.q_num)
                            socket.emit('finish', res) //sending data
                            resolve()
                          } else {
                            self.ajaxAlertWarning()
                          }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                          self.ajaxAlertError(errorThrown)
                        },
                      })
                    }, 500)
                  } else {
                    resolve()
                  }
                  // } else {
                  //   self.ajaxAlertWarning();
                  // }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
          }
        })
      }
    })
    //End คิว hold
    $('#tb-waiting tbody').on('click', 'tr td a.btn-end', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr'),
        url = $(this).attr('href')
      if (tr.hasClass('child') && typeof app.tbwaiting.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbwaiting.row(tr).data()
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยัน END คิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html: '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: baseUrl + url,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    //success
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                    self.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
                    self.toastrSuccess('END ' + data.q_num)
                    socket.emit('finish', res) //sending data
                    resolve()
                  } else {
                    //error
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })
    //เรียกคิวซ้ำ
    $('#tb-calling tbody').on('click', 'tr td a.btn-recall', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr'),
        url = $(this).attr('href')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbcalling.row(tr).data()
      var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                    self.toastrSuccess('RECALL ' + data.q_num)
                    socket.emit('call', res) //sending data
                    resolve()
                  } else {
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })
    //พักคิว
    $('#tb-calling tbody').on('click', 'tr td a.btn-hold', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr'),
        url = $(this).attr('href')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbcalling.row(tr).data()
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยันพักคิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html: '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'พักคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    //success
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                    self.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
                    self.toastrSuccess('HOLD ' + data.q_num)
                    socket.emit('hold', res) //sending data
                    resolve()
                  } else {
                    //error
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })

    //End คิว
    $('#tb-calling tbody').on('click', 'tr td a.btn-end', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr'),
        url = $(this).attr('href')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbcalling.row(tr).data()
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยัน END คิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p><i class="fa fa-user"></i>' +
            data.pt_name +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == '200') {
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                    self.toastrSuccess('END ' + data.q_num)
                    socket.emit('finish', res) //sending data
                    resolve()
                  } else {
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })

    //
    $('#tb-calling tbody').on('click', 'tr td a.btn-waiting', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr'),
        url = $(this).attr('href')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbcalling.row(tr).data()
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยันส่งคิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p><i class="fa fa-user"></i>' +
            data.pt_name +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == '200') {
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                    self.toastrSuccess('END ' + data.q_num)
                    socket.emit('finish', res) //sending data
                    resolve()
                  } else {
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })

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
      var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    //success
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                    self.reloadTbHold() //โหลดข้อมูลพักคิว
                    self.toastrSuccess('CALL ' + data.q_num)
                    socket.emit('call', res) //sending data
                    resolve()
                  } else {
                    //error
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })

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
      if (self.checkCounter()) {
        swal({
          title: 'ยืนยัน END คิว ' + data.q_num + ' ?',
          text: data.pt_name,
          html: '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: url + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    //success
                    self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                    self.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
                    self.toastrSuccess('END ' + data.q_num)
                    socket.emit('finish', res) //sending data
                    resolve()
                  } else {
                    //error
                    self.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  self.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    })

    $('#tb-patients-ist tbody').on('click', 'tr td a', function (event) {
      event.preventDefault()
      var table = $('#tb-patients-ist').DataTable()
      var tr = $(this).closest('tr'),
        serviceid = $(this).attr('data-key'),
        groupid = $(this).attr('data-group')
      if (tr.hasClass('child') && typeof table.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = table.row(tr).data()
      var txt = $(this).text()
      swal({
        title: 'ยืนยัน?',
        text: data.fullname,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.fullname +
          '</p>' +
          '<p><i class="fa fa-angle-double-down"></i></p><p>' +
          txt +
          '</p>',
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        showLoaderOnConfirm: true,
        preConfirm: function () {
          return new Promise(function (resolve) {
            $.ajax({
              url: baseUrl + '/app/kiosk/register',
              type: 'POST',
              data: $.extend(data, {
                groupid: groupid,
                serviceid: serviceid,
              }),
              dataType: 'JSON',
              success: function (res) {
                if (res.status == '200') {
                  toastr.success(res.modelQ.pt_name, 'Printing #' + res.modelQ.q_num, {
                    timeOut: 3000,
                    positionClass: 'toast-top-right',
                  })
                  window.open(res.url, 'myPrint', 'width=800, height=600')
                  table.ajax.reload()
                  dt_tbqdata.ajax.reload()
                  socket.emit('register', res) //sending data
                  resolve()
                } else {
                  swal('Oops...', 'เกิดข้อผิดพลาด!', 'error')
                }
              },
              error: function (jqXHR, errMsg) {
                swal('Oops...', errMsg, 'error')
              },
            })
          })
        },
      }).then((result) => {
        if (result.value) {
          swal.close()
        }
      })
    })
  },
  init: function () {
    var self = this
    self.handleEventClick()
  },
  reloadTbWaiting: function () {
    if (app.tbwaiting && !app.waitingLoading) {
      app.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
    }
  },
  reloadTbCalling: function () {
    if (app.tbcalling && !app.callingLoading) {
      app.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
    }
  },
  reloadTbHold: function () {
    if (app.tbhold && !app.holdLoading) {
      app.tbhold.ajax.reload() //โหลดข้อมูลพักคิวใหม่
    }
  },
  toastrSuccess: function (msg = '') {
    if (localStorage.getItem('disablealert-pagecalling') == 'on') {
      toastr.success(msg, 'Success!', {
        timeOut: 3000,
        positionClass: 'toast-top-right',
        progressBar: true,
        closeButton: true,
      })
    }
  },
  toastrWarning: function (title = 'Warning!', msg = '') {
    if (localStorage.getItem('disablealert-pagecalling') == 'on') {
      toastr.success(msg, title, {
        timeOut: 5000,
        positionClass: 'toast-top-right',
        progressBar: true,
        closeButton: true,
      })
    }
  },
  ajaxAlertError: function (msg) {
    swal({
      type: 'error',
      title: msg,
      showConfirmButton: false,
      timer: 1500,
    })
  },
  ajaxAlertWarning: function () {
    swal({
      type: 'error',
      title: 'เกิดข้อผิดพลาด!!',
      showConfirmButton: false,
      timer: 1500,
    })
  },
  checkCounter: function () {
    if (app.form.service_profile_id == null || app.form.counter_service_id == null) {
      var title = app.form.service_profile_id == null ? 'กรุณาเลือกโปรไฟล์' : 'กรุณาเลือกช่องบริการ'
      swal({
        type: 'warning',
        title: title,
        showConfirmButton: false,
        timer: 1500,
      })
      return false
    } else {
      return true
    }
  },
}

//Socket Events
$(function () {
  socket
    .on('register', (res) => {
      //ออกบัตรคิว
      var services = String(_.get(app.modelServiceProfile, 'service_id')).split(',')
      dt_tbqdata.ajax.reload()
      if (jQuery.inArray(res.modelQueue.serviceid.toString(), services) != -1) {
        //ถ้าคิวมีใน service profile
        if (localStorage.getItem('playsound-pagecalling') == 'on') {
          var player = $('#jplayer_notify').jPlayer({
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
        }

        Queue.reloadTbWaiting() //โหลดข้อมูลรอเรียก
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
          Queue.reloadTbWaiting() //โหลดข้อมูลคิวรอ
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          dt_tbqdata.ajax.reload()
        }
      })
      t2.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (
          parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
          parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
        ) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          dt_tbqdata.ajax.reload()
        }
      })
      t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (
          parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
          parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
        ) {
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          dt_tbqdata.ajax.reload()
        }
      })
      // if (res.eventOn === "tb-waiting" && res.state === "call") {
      //   Queue.reloadTbWaiting(); //โหลดข้อมูลคิวรอ
      //   if (
      //     res.modelProfile.service_profile_id == modelProfile.service_profile_id &&
      //     modelForm.counter_service == res.counter.counterserviceid.toString()
      //   ) {
      //     Queue.reloadTbCalling(); //โหลดข้อมูลกำลังเรียก
      //   }
      //   swal.close();
      // } else if (res.eventOn === "tb-hold" && res.state === "call-hold") {
      //   if (
      //     res.modelProfile.service_profile_id == modelProfile.service_profile_id &&
      //     modelForm.counter_service == res.counter.counterserviceid.toString()
      //   ) {
      //     Queue.reloadTbCalling(); //โหลดข้อมูลกำลังเรียกใหม่
      //     Queue.reloadTbHold(); //โหลดข้อมูลพักคิวใหม่
      //   }
      // }
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
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
          dt_tbqdata.ajax.reload()
        }
      })
      t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (
          parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
          parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
        ) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
          dt_tbqdata.ajax.reload()
        }
      })
      // if (
      //   res.modelProfile.service_profile_id == modelProfile.service_profile_id &&
      //   modelForm.counter_service == res.counter.counterserviceid.toString()
      // ) {
      //   Queue.reloadTbCalling(); //โหลดข้อมูลกำลังเรียกใหม่
      //   Queue.reloadTbHold(); //โหลดข้อมูลพักคิวใหม่
      // }
    })
    .on('finish', (res) => {
      var t2 = app.tbcalling
      var t3 = app.tbhold
      var services = String(_.get(app.modelServiceProfile, 'service_id')).split(',')
      if (jQuery.inArray(res.modelQueue.serviceid.toString(), services) != -1) {
        Queue.reloadTbWaiting()
      }
      t2.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (
          parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
          parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
        ) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
          dt_tbqdata.ajax.reload()
        }
      })
      t3.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data()
        if (
          parseInt(data.caller_ids) === parseInt(_.get(res, 'modelCaller.caller_ids')) ||
          parseInt(data.caller_ids) === parseInt(_.get(res, 'model.caller_ids'))
        ) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
          dt_tbqdata.ajax.reload()
        }
      })
      // if (
      //   res.modelProfile.service_profile_id == modelProfile.service_profile_id &&
      //   modelForm.counter_service == res.counter.counterserviceid.toString()
      // ) {
      //   Queue.reloadTbCalling(); //โหลดข้อมูลกำลังเรียกใหม่
      //   Queue.reloadTbHold(); //โหลดข้อมูลพักคิวใหม่
      // }
    })
    .on('display', (res) => {
      setTimeout(function () {
        app.tbcalling.rows().every(function (rowIdx, tableLoop, rowLoop) {
          var data = this.data()
          if (data.q_num == res.title) {
            $('#tb-calling').find('tr.success').removeClass('success')
            $('#last-queue').html(data.q_num)
            app.tbcalling.$('tr#' + res.artist.data.DT_RowId).addClass('success')
            Queue.toastrWarning('', '<i class="pe-7s-speaker"></i> กำลังเรียกคิว #' + data.q_num)
          }
        })
      }, 500)
    })
  //set draggable
  /* $( window ).resize(function() {
        if($(window).width() > 992){
            $('.call-next').draggable();
        }
    });
    if($(window).width() > 992){
        $('.call-next').draggable();
    } */
  //แปลงเป็นตัวอักษรตัวใหญ่
  $('#callingform-qnum').keyup(function () {
    this.value = this.value.toUpperCase()
  })
})

var $form = $('#calling-form')
$form.on('beforeSubmit', function () {
  var dataObj = {}
  var qcall
  var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''

  $form.serializeArray().map(function (field) {
    dataObj[field.name] = field.value
  })

  if (dataObj['CallingForm[qnum]'] != null && dataObj['CallingForm[qnum]'] != '') {
    //ข้อมูลกำลังเรียก
    app.tbcalling.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = {
          data: data,
          tbkey: 'tbcalling',
        }
      }
    })
    //ข้อมูลรอเรียก
    app.tbwaiting.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = {
          data: data,
          tbkey: 'tbwaiting',
        }
      }
    })
    //ข้อมูลพักคิว
    app.tbhold.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = {
          data: data,
          tbkey: 'tbhold',
        }
      }
    })

    if (qcall === undefined) {
      toastr.error(dataObj['CallingForm[qnum]'], 'ไม่พบข้อมูล!', {
        timeOut: 3000,
        positionClass: 'toast-top-center',
      })
    } else {
      if (qcall.tbkey === 'tbcalling') {
        swal({
          title: 'ยืนยันเรียกคิว ' + qcall.data.q_num + ' ?',
          text: '',
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: `/node/api/v1/calling/recall?id=${qcall.data.caller_ids}&access-token=${accesstoken}`,
                dataType: 'json',
                data: {
                  data: qcall.data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    Queue.reloadTbCalling()
                    Queue.toastrSuccess('RECALL ' + qcall.data.q_num)
                    socket.emit('call', res) //sending data
                    resolve()
                  } else {
                    Queue.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  Queue.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      } else if (qcall.tbkey === 'tbhold') {
        swal({
          title: 'ยืนยันเรียกคิว ' + qcall.data.q_num + ' ?',
          text: '',
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: `/node/api/v1/calling/recall?id=${qcall.data.caller_ids}&access-token=${accesstoken}`,
                dataType: 'json',
                data: {
                  data: qcall.data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    //success
                    Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                    Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
                    Queue.toastrSuccess('CALL ' + qcall.data.q_num)
                    socket.emit('call', res) //sending data
                    resolve()
                  } else {
                    //error
                    Queue.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  Queue.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      } else if (qcall.tbkey === 'tbwaiting') {
        swal({
          title: 'ยืนยันเรียกคิว ' + qcall.data.q_num + ' ?',
          text: '',
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p>' +
            countername +
            '</p>',
          type: 'question',
          input: 'radio',
          inputOptions: {
            0: 'ต้องการ END ทันที',
            1: 'ไม่ต้องการ END ทันที',
          },
          inputValue: 1,
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          preConfirm: function (value) {
            return new Promise(function (resolve, reject) {
              $.ajax({
                method: 'POST',
                url: '/node/api/v1/calling/call?id=' + qcall.data.q_ids + '&access-token=' + accesstoken,
                dataType: 'json',
                data: {
                  data: qcall.data, //Data in column Datatable
                  ...app.form,
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                },
                success: function (res) {
                  if (res.status == 200) {
                    Queue.reloadTbWaiting() //โหลดข้อมูลรอเรียกคัดกรองใหม่
                    Queue.reloadTbCalling() //โหลดข้อมูลกำลัวเรียกใหม่
                    Queue.toastrSuccess('CALL ' + qcall.data.q_num)
                    //$("html, body").animate({ scrollTop: 0 }, "slow");
                    socket.emit('call', res) //sending data
                    if (value == 0) {
                      setTimeout(function () {
                        var tr = $('#tb-calling tr#' + res.modelCaller.caller_ids),
                          url = '/node/api/v1/calling/end?id=' + res.modelCaller.caller_ids + '&access-token=' + accesstoken
                        var key = tr.data('key')
                        var data = app.tbcalling.row(tr).data()
                        $.ajax({
                          method: 'POST',
                          url: baseUrl + url,
                          dataType: 'json',
                          data: {
                            data: data, //Data in column Datatable
                            ...app.form,
                            // modelForm: modelForm, //Data Model CallingForm
                            // modelProfile: modelProfile,
                          },
                          success: function (res) {
                            if (res.status == '200') {
                              Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                              Queue.toastrSuccess('END ' + data.q_num)
                              socket.emit('finish', res) //sending data
                              resolve()
                            } else {
                              Queue.ajaxAlertWarning()
                            }
                          },
                          error: function (jqXHR, textStatus, errorThrown) {
                            Queue.ajaxAlertError(errorThrown)
                          },
                        })
                      }, 500)
                    } else {
                      resolve()
                    }
                  } else {
                    Queue.ajaxAlertWarning()
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  Queue.ajaxAlertError(errorThrown)
                },
              })
            })
          },
        }).then((result) => {
          if (result.value) {
            //Confirm
            swal.close()
          }
        })
      }
    }
  } else {
    toastr.error(dataObj['CallingForm[qnum]'], 'ไม่พบข้อมูล!', {
      timeOut: 3000,
      positionClass: 'toast-top-center',
    })
  }
  $('input#callingform-qnum').val(null) //clear data
  return false
})

$('a.activity-callnext').on('click', function (e) {
  e.preventDefault()
  var data = app.tbwaiting.rows(0).data(),
    url = $(this).attr('data-url')
  var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
  if (data.length > 0) {
    if (Queue.checkCounter()) {
      swal({
        title: 'CALL NEXT ' + data[0].q_num + ' ?',
        text: data[0].q_num,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p>' +
          countername +
          '</p>',
        type: 'question',
        input: 'radio',
        inputOptions: {
          0: 'ต้องการ END ทันที',
          1: 'ไม่ต้องการ END ทันที',
        },
        inputValue: 1,
        showCancelButton: true,
        confirmButtonText: 'เรียกคิว',
        cancelButtonText: 'ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: function (value) {
          return new Promise(function (resolve, reject) {
            $.ajax({
              method: 'POST',
              url: '/node/api/v1/calling/call?id=' + data[0].q_ids + '&access-token=' + accesstoken,
              dataType: 'json',
              data: {
                data: data[0], //Data in column Datatable
                ...app.form,
                // modelForm: modelForm, //Data Model CallingForm
                // modelProfile: modelProfile,
              },
              success: function (res) {
                if (res.status == 200) {
                  $('li.tab-watting, #tab-watting').removeClass('active')
                  $('li.tab-calling, #tab-calling').addClass('active')
                  Queue.reloadTbWaiting() //โหลดข้อมูลรอเรียก
                  Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                  Queue.toastrSuccess('CALL ' + data[0].q_num)
                  //$("html, body").animate({ scrollTop: 0 }, "slow");
                  socket.emit('call', res) //sending data
                  if (value == 0) {
                    setTimeout(function () {
                      var tr = $('#tb-calling tr#' + res.modelCaller.caller_ids),
                        url = '/node/api/v1/calling/end?id=' + res.modelCaller.caller_ids + '&access-token=' + accesstoken
                      var key = tr.data('key')
                      var data = app.tbcalling.row(tr).data()
                      $.ajax({
                        method: 'POST',
                        url: url,
                        dataType: 'json',
                        data: {
                          data: data, //Data in column Datatable
                          ...app.form,
                          // modelForm: modelForm, //Data Model CallingForm
                          // modelProfile: modelProfile,
                        },
                        success: function (res) {
                          if (res.status == '200') {
                            Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                            Queue.toastrSuccess('END ' + res.modelQueue.q_num)
                            socket.emit('finish', res) //sending data
                            resolve()
                          } else {
                            Queue.ajaxAlertWarning()
                          }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                          Queue.ajaxAlertError(errorThrown)
                        },
                      })
                    }, 500)
                  } else {
                    resolve()
                  }
                } else {
                  Queue.ajaxAlertWarning()
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                Queue.ajaxAlertError(errorThrown)
              },
            })
          })
        },
      }).then((result) => {
        if (result.value) {
          swal.close()
        }
      })
    }
  } else {
    swal({
      type: 'warning',
      title: 'ไม่พบหมายเลขคิว',
      showConfirmButton: false,
      timer: 1500,
    })
  }
})

//hidden menu
$('body').addClass('hide-sidebar')

$('#fullscreen-toggler').on('click', function (e) {
  setFullScreen()
})

function setFullScreen() {
  var element = document.documentElement
  if (!$('body').hasClass('full-screen')) {
    $('body').addClass('full-screen')
    $('#fullscreen-toggler').addClass('active')
    localStorage.setItem('medical-fullscreen', 'true')
    if (element.requestFullscreen) {
      element.requestFullscreen()
    } else if (element.mozRequestFullScreen) {
      element.mozRequestFullScreen()
    } else if (element.webkitRequestFullscreen) {
      element.webkitRequestFullscreen()
    } else if (element.msRequestFullscreen) {
      element.msRequestFullscreen()
    }
  } else {
    $('body').removeClass('full-screen')
    $('#fullscreen-toggler').removeClass('active')
    localStorage.setItem('medical-fullscreen', 'false')
    if (document.exitFullscreen) {
      document.exitFullscreen()
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen()
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen()
    }
  }
}

function setTabletmode() {
  var hpanel = $('div.panel-form')
  var icon = $('div.panel-form').find('i:first')
  var body = hpanel.find('div.panel-body')
  var footer = hpanel.find('div.panel-footer')

  if (localStorage.getItem('medical-tablet-mode') == 'true') {
    body.slideToggle(300)
    footer.slideToggle(200)
    // Toggle icon from up to down
    icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down')
    hpanel.toggleClass('').toggleClass('panel-collapse')
    setTimeout(function () {
      hpanel.resize()
      hpanel.find('[id^=map-]').resize()
    }, 50)
    var profilename = $('#callingform-service_profile').select2('data')[0]['text'] || ''
    var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
    $('div.panel-form .panel-heading-text').html(' | ' + profilename + ': ' + countername)
    $('#tablet-mode').prop('checked', true)
    $('#tab-menu-default,.small-header').css('display', 'none')
    $('.footer-tabs,.call-next-tablet-mode,.text-tablet-mode').css('display', '')
    $('#tab-watting .panel-body,#tab-calling .panel-body,#tab-hold .panel-body').css('border-top', '1px solid #e4e5e7')
    // app.tbwaiting.column(1).visible(true)
  } else {
    if (hpanel.hasClass('panel-collapse')) {
      body.slideToggle(300)
      footer.slideToggle(200)
      // Toggle icon from up to down
      icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down')
      hpanel.toggleClass('').toggleClass('panel-collapse')
      setTimeout(function () {
        hpanel.resize()
        hpanel.find('[id^=map-]').resize()
      }, 50)
    }
    $('div.panel-form .panel-heading-text').html('&nbsp;')
    $('.footer-tabs,.call-next-tablet-mode,.text-tablet-mode').css('display', 'none')
    $('#tab-menu-default,.small-header').css('display', '')
    $('#tab-watting .panel-body,#tab-calling .panel-body,#tab-hold .panel-body').css('border-top', '0')
    // app.tbwaiting.column(1).visible(false)
  }
}

$(document).ready(function () {
  $('#tablet-mode').on('click', function () {
    if ($(this).is(':checked')) {
      localStorage.setItem('medical-tablet-mode', 'true')
    } else {
      localStorage.setItem('medical-tablet-mode', 'false')
    }
    setTabletmode()
  })
})

setTabletmode()

$('#tb-waiting tbody').on('change', 'input[name="selection[]"]', function () {
  var tr = $(this).closest('tr')
  var value = $(this).val()
  if (this.checked) {
    $(tr).addClass('success')
  } else {
    $(tr).removeClass('success')
  }
  // If checkbox is not checked
  if (this.checked) {
    keySelected.push(value)
  } else {
    if (jQuery.inArray(value, keySelected) !== -1) {
      $.each(keySelected, function (index, data) {
        if (value == data) {
          keySelected.splice(index, 1)
        }
      })
    }
  }
  if (keySelected.length > 0) {
    $('button.on-call-selected').prop('disabled', false)
  } else {
    $('button.on-call-selected').prop('disabled', true)
  }
  $('.count-selected').html('(' + keySelected.length + ')')
})

$('button.on-call-selected').on('click', function (e) {
  e.preventDefault()
  var url = $(this).data('url')
  var selectedData = []
  var queNumber = []
  $.each(keySelected, function (index, value) {
    var data = app.tbwaiting.row('#' + value).data()
    if (data != undefined) {
      selectedData.push(data)
      queNumber.push(data.q_num)
    }
  })
  if (selectedData.length > 0) {
    if (Queue.checkCounter()) {
      var countername = $('#callingform-counter_service').select2('data')[0]['text'] || ''
      swal({
        title: 'ยืนยันเรียกคิว?',
        text: '',
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small><br><p>' +
          queNumber.join(', ') +
          '</p><p>' +
          countername +
          '</p>',
        type: 'question',
        input: 'radio',
        inputOptions: {
          0: 'ต้องการ END ทันที',
          1: 'ไม่ต้องการ END ทันที',
        },
        inputValue: 1,
        showCancelButton: true,
        confirmButtonText: 'เรียกคิว',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        preConfirm: function (value) {
          return new Promise(function (resolve, reject) {
            var timer = 1000
            $.ajax({
              method: 'POST',
              url: baseUrl + url,
              dataType: 'json',
              data: {
                selectedData: selectedData, //Data in column Datatable
                ...app.form,
                // modelForm: modelForm, //Data Model CallingForm
                // modelProfile: modelProfile,
                autoend: value,
              },
              success: function (res) {
                $('li.tab-watting, #tab-watting').removeClass('active')
                $('li.tab-calling, #tab-calling').addClass('active')
                $('.count-selected').html('(0)')
                $('button.on-call-selected').prop('disabled', true)
                Queue.reloadTbWaiting() //โหลดข้อมูลรอเรียก
                Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียก
                $.each(res.call_result, function (index, data) {
                  setTimeout(function () {
                    Queue.toastrSuccess('CALL ' + data.data.q_num)
                    socket.emit('call', data) //sending data
                  }, timer)
                  timer = timer + 1500
                })
                if (value == 0) {
                  var timer2 = 1000
                  $.each(res.end_result, function (index, data) {
                    setTimeout(function () {
                      Queue.toastrSuccess('END ' + data.data.q_num)
                      socket.emit('finish', data) //sending data
                    }, timer2)
                    timer2 = timer2 + 1500
                  })
                }
                keySelected = []
                resolve()
              },
              error: function (jqXHR, textStatus, errorThrown) {
                Queue.ajaxAlertError(errorThrown)
              },
            })
          })
        },
      }).then((result) => {
        if (result.value) {
          //Confirm
        }
      })
    }
  }
})

Queue.init()

dt_tbqdata.on('xhr.dt', function (e, settings, json, xhr) {
  $("#count-qdata").html(_.get(json, 'total', 0));
})

$('#tb-qdata tbody').on('click', 'tr td a.btn-del', function (event) {
  event.preventDefault()
  var tr = $(this).closest('tr'),
    url = $(this).attr('href')
  if (tr.hasClass('child') && typeof dt_tbqdata.row(tr).data() === 'undefined') {
    tr = $(this).closest('tr').prev()
  }
  var key = tr.data('key')
  var data = dt_tbqdata.row(tr).data()
  swal({
    title: 'ยืนยันลบคิว ' + data.q_num + ' ?',
    text: '',
    type: 'question',
    showCancelButton: true,
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    allowOutsideClick: false,
    showLoaderOnConfirm: true,
    preConfirm: function () {
      return new Promise(function (resolve, reject) {
        $.ajax({
          method: 'DELETE',
          url: url,
          dataType: 'json',
          success: function (res) {
            dt_tbqdata.ajax.reload()
            Queue.toastrSuccess('ลบรายการคิวสำเร็จ')
            resolve()
          },
          error: function (jqXHR, textStatus, errorThrown) {
            Queue.ajaxAlertError(errorThrown)
          },
        })
      })
    },
  }).then((result) => {
    if (result.value) {
      //Confirm
      swal.close()
    }
  })
})