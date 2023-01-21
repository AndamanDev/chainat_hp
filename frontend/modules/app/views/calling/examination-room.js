$(function () {
  //hidden menu
  $('body').addClass('hide-sidebar')
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

  $('#callingform-qnum').keyup(function () {
    this.value = this.value.toUpperCase()
  })

  // Toastr options
  toastr.options = {
    debug: false,
    newestOnTop: false,
    positionClass: 'toast-top-center',
    closeButton: true,
    toastClass: 'animated fadeInDown',
  }

  // Initialize iCheck plugin
  var input = $('.i-checks').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_square-green',
  })

  //Checkbox Event
  $(input).on('ifChecked', function (event) {
    var key = $(this).val()
    Queue.setDataSession()
    if (!app.form.counter_service_ids.includes(parseInt(key))) {
      var arr = []
      arr.push(...app.form.counter_service_ids)
      arr.push(parseInt(key))
      app.setState({
        key: 'counter_service_ids',
        value: arr,
      })
    }
  })

  $(input).on('ifUnchecked', function (event) {
    var key = $(this).val()
    if (app.form.counter_service_ids.includes(parseInt(key))) {
      var arr = []
      arr.push(...app.form.counter_service_ids)
      arr = arr.filter((v) => v !== parseInt(key))
      app.setState({
        key: 'counter_service_ids',
        value: arr,
      })
    }
    Queue.setDataSession()
  })
})

Queue = {
  setDataSession: function () {
    var $form = $('#calling-form')
    var data = $form.serialize()
    $.ajax({
      method: 'POST',
      url: '/app/calling/set-counter-session',
      data: data,
      dataType: 'json',
      beforeSend: function (jqXHR, settings) {
        swal({
          title: 'Loading...',
          text: '',
          onOpen: () => {
            swal.showLoading()
          },
        }).then((result) => { })
      },
      success: function (res) {
        swal.close()
        // location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        Queue.ajaxAlertError(errorThrown)
      },
    })
  },
  handleEventClick: function () {
    var self = this
    //เรียกคิวรอ
    $('#tb-waiting tbody').on('click', 'tr td a.btn-calling', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbwaiting.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbwaiting.row(tr).data()
      swal({
        title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
        html: '<p><i class="fa fa-user"></i> ' + data.pt_name + '</p>',
        input: 'select',
        type: 'question',
        inputOptions: app.counterOpts,
        inputPlaceholder: 'เลือกห้องตรวจ',
        inputValue: data.counter_service_id || '',
        inputClass: 'form-control m-b',
        showCancelButton: true,
        confirmButtonText: 'เรียกคิว',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        inputValidator: (value) => {
          if (!value) {
            return 'คุณไม่ได้เลือกห้องตรวจ!'
          }
        },
        preConfirm: (value) => {
          return new Promise((resolve) => {
            $.ajax({
              method: 'POST',
              url: `/node/api/v1/calling/call?id=${data.q_ids}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: value, //select value
              },
              success: function (res) {
                if (res.status == 200) {
                  self.reloadTbWaiting() //โหลดข้อมูลคิวรอพบแพทย์ใหม่
                  self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                  self.toastrSuccess('CALL ' + data.q_num)
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
      })
      jQuery('.swal2-select').select2({
        allowClear: true,
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'เลือกห้องตรวจ...',
        language: 'th',
        sorter: function (data) {
          return data.sort(function (a, b) {
            return a.text < b.text ? -1 : a.text > b.text ? 1 : 0
          })
        },
      })
      $('select.swal2-select, span.select2').addClass('input-lg')
      $('#swal2-content').css('padding-bottom', '15px')
    })

    //เรียกคิวซ้ำ
    $('#tb-calling tbody').on('click', 'tr td a.btn-recall', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var url = $(this).attr('href')
      var data = app.tbcalling.row(tr).data()
      swal({
        title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.pt_name +
          '</p>',
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'เรียกคิว',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        onOpen: () => {
          swal.clickConfirm()
        },
        preConfirm: function () {
          return new Promise(function (resolve, reject) {
            $.ajax({
              method: 'POST',
              url: `${url}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: data.counter_service_id,
                // modelForm: modelForm, //Data Model CallingForm
                // modelProfile: modelProfile,
              },
              success: function (res) {
                if (res.status == 200) {
                  app.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
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
    })

    //พักคิว
    $('#tb-calling tbody').on('click', 'tr td a.btn-hold', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var url = $(this).attr('href')
      var data = app.tbcalling.row(tr).data()
      swal({
        title: 'ยืนยันพักคิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.pt_name +
          '</p>',
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
              url: `${url}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: data.counter_service_id,
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
    })

    $('#tb-calling tbody').on('click', 'tr td a.btn-transfer', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbcalling.row(tr).data()
      swal({
        title: 'ส่งคิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.pt_name +
          '</p>',
        input: 'select',
        type: 'question',
        inputOptions: select2Data,
        inputPlaceholder: 'เลือกห้องตรวจ',
        inputValue: data.counter_service_id || '',
        inputClass: 'form-control m-b',
        showCancelButton: true,
        confirmButtonText: 'ส่งคิว',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        inputValidator: (value) => {
          if (!value) {
            return 'คุณไม่ได้เลือกห้องตรวจ!'
          }
        },
        preConfirm: function (value) {
          return new Promise(function (resolve, reject) {
            $.ajax({
              method: 'POST',
              url: '/app/calling/transfer-examination-room',
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                modelForm: modelForm, //Data Model CallingForm
                modelProfile: modelProfile,
                value: value, //select value
              },
              success: function (res) {
                if (res.status == 200) {
                  //success
                  self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                  // self.reloadTbHold();//โหลดข้อมูลพักคิวใหม่
                  self.toastrSuccess('Transfer ' + data.q_num)
                  socket.emit('transfer-examination-room', res) //sending data
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
      jQuery('.swal2-select').select2({
        allowClear: true,
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'เลือกห้องตรวจ...',
        language: 'th',
        sorter: function (data) {
          return data.sort(function (a, b) {
            return a.text < b.text ? -1 : a.text > b.text ? 1 : 0
          })
        },
      })
      $('select.swal2-select, span.select2').addClass('input-lg')
      $('#swal2-content').css('padding-bottom', '15px')
    })

    //เรียกคิว hold
    $('#tb-hold tbody').on('click', 'tr td a.btn-calling', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var url = $(this).attr('href')
      var data = app.tbhold.row(tr).data()
      swal({
        title: 'ยืนยันเรียกคิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.pt_name +
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
              url: `/node/api/v1/calling/recall?id=${data.caller_ids}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: data.counter_service_id,
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
    })

    //End คิว hold
    $('#tb-hold tbody').on('click', 'tr td a.btn-end', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var url = $(this).attr('href')
      var data = app.tbhold.row(tr).data()
      swal({
        title: 'ยืนยัน End คิว ' + data.q_num + ' ?',
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
              url: `${url}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: data.counter_service_id,
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
    })

    $('#tb-hold tbody').on('click', 'tr td a.btn-transfer', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbhold.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbhold.row(tr).data()
      swal({
        title: 'ส่งคิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html:
          '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
          '<p><i class="fa fa-user"></i>' +
          data.pt_name +
          '</p>',
        input: 'select',
        type: 'question',
        inputOptions: select2Data,
        inputPlaceholder: 'เลือกห้องตรวจ',
        inputValue: data.counter_service_id || '',
        inputClass: 'form-control m-b',
        showCancelButton: true,
        confirmButtonText: 'ส่งคิว',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        inputValidator: (value) => {
          if (!value) {
            return 'คุณไม่ได้เลือกห้องตรวจ!'
          }
        },
        preConfirm: function (value) {
          return new Promise(function (resolve, reject) {
            $.ajax({
              method: 'POST',
              url: baseUrl + '/app/calling/transfer-examination-room',
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                modelForm: modelForm, //Data Model CallingForm
                modelProfile: modelProfile,
                value: value, //select value
              },
              success: function (res) {
                if (res.status == 200) {
                  //success
                  self.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                  self.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
                  self.toastrSuccess('Transfer ' + data.q_num)
                  socket.emit('transfer-examination-room', res) //sending data
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
      jQuery('.swal2-select').select2({
        allowClear: true,
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'เลือกห้องตรวจ...',
        language: 'th',
        sorter: function (data) {
          return data.sort(function (a, b) {
            return a.text < b.text ? -1 : a.text > b.text ? 1 : 0
          })
        },
      })
      $('select.swal2-select, span.select2').addClass('input-lg')
      $('#swal2-content').css('padding-bottom', '15px')
    })

    //End คิว
    $('#tb-calling tbody').on('click', 'tr td a.btn-end', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbcalling.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var url = $(this).attr('href')
      var data = app.tbcalling.row(tr).data()
      swal({
        title: 'ยืนยัน End คิว ' + data.q_num + ' ?',
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
              url: `${url}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: data.counter_service_id,
              },
              success: function (res) {
                if (res.status == 200) {
                  //success
                  self.reloadTbCalling() //โหลดข้อมูล
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
    })

    $('#tb-waiting tbody').on('click', 'tr td a.btn-end', function (event) {
      event.preventDefault()
      var tr = $(this).closest('tr')
      if (tr.hasClass('child') && typeof app.tbwaiting.row(tr).data() === 'undefined') {
        tr = $(this).closest('tr').prev()
      }
      var key = tr.data('key')
      var data = app.tbwaiting.row(tr).data()
      swal({
        title: 'ยืนยัน End คิว ' + data.q_num + ' ?',
        text: data.pt_name,
        html: '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>',
        input: 'select',
        type: 'question',
        inputOptions: app.counterOpts,
        inputPlaceholder: 'เลือกห้องตรวจ',
        inputValue: data.counter_service_id || '',
        inputClass: 'form-control m-b',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        inputValidator: (value) => {
          if (!value) {
            return 'คุณไม่ได้เลือกห้องตรวจ!'
          }
        },
        preConfirm: (value) => {
          return new Promise((resolve) => {
            $.ajax({
              method: 'POST',
              url: `${url}&access-token=${accesstoken}`,
              dataType: 'json',
              data: {
                data: data, //Data in column Datatable
                ...app.form,
                counter_service_id: value,
                // modelForm: modelForm, //Data Model CallingForm
                // modelProfile: modelProfile,
                // value: value, //select value
              },
              success: function (res) {
                if (res.status == 200) {
                  self.reloadTbWaiting() //โหลดข้อมูลคิวรอพบแพทย์ใหม่
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
      })
      jQuery('.swal2-select').select2({
        allowClear: true,
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'เลือกห้องตรวจ...',
        language: 'th',
        sorter: function (data) {
          return data.sort(function (a, b) {
            return a.text < b.text ? -1 : a.text > b.text ? 1 : 0
          })
        },
      })
      $('select.swal2-select, span.select2').addClass('input-lg')
      $('#swal2-content').css('padding-bottom', '15px')
    })
  },
  init: function () {
    var self = this
    self.handleEventClick()
  },
  reloadTbWaiting: function () {
    if (!app.waitingLoading && app.tbwaiting) {
      app.tbwaiting.ajax.reload() //โหลดข้อมูลคิวรอ
    }
  },
  reloadTbCalling: function () {
    if (!app.callingLoading && app.tbcalling) {
      app.tbcalling.ajax.reload() //โหลดข้อมูลกำลังเรียก
    }
  },
  reloadTbHold: function () {
    if (!app.holdLoading && app.tbhold) {
      app.tbhold.ajax.reload() //โหลดข้อมูลพักคิวใหม่
    }
  },
  toastrSuccess: function (msg) {
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
    if (modelForm.service_profile == null) {
      var title = 'กรุณาเลือกโปรไฟล์'
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

//socket event
$(function () {
  socket
    .on('finish', (res) => {
      var services = String(_.get(app.modelServiceProfile, 'service_id')).split(',')
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
        Queue.toastrWarning('ผู้ป่วยลงทะเบียนใหม่!', res.modelQueue.pt_name)
      } else {
        Queue.reloadTbWaiting() //โหลดข้อมูลรอเรียก
      }
    })
    .on('call', (res) => {
      //เรียกคิวรอ
      if (res.eventOn === 'tb-waiting' && res.state === 'call') {
        //เรียกคิวจาก table คิวรอพบแพทย์
        Queue.reloadTbWaiting() //โหลดข้อมูลคิวรอพบแพทย์ใหม่
        var counters = app.form.counter_service_ids //modelForm.counter_service
        if (jQuery.inArray(res.counter.counterserviceid, counters) != -1) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
        }
        swal.close()
      } else if (res.eventOn === 'tb-hold' && res.state === 'call-hold') {
        //เรียกคิวจาก table พักคิว
        var counters = app.form.counter_service_ids // modelForm.counter_service
        if (jQuery.inArray(res.counter.counterserviceid, counters) != -1) {
          Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
          Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
        }
      }
    })
    .on('hold', (res) => {
      //Hold คิวห้องตรวจ /kiosk/calling/examination-room
      var counters = modelForm.counter_service
      if (jQuery.inArray(res.counter.counterserviceid.toString(), counters) != -1) {
        Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
        Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
      }
    })
    .on('transfer-examination-room', (res) => {
      Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
      Queue.reloadTbHold() //โหลดข้อมูลพักคิวใหม่
    })
    .on('finish', (res) => {
      //จบ Process q /kiosk/calling/examination-room
      var counters = app.form.counter_service_ids // modelForm.counter_service
      if (jQuery.inArray(res.counter.counterserviceid, counters) != -1) {
        Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
      }
    })
    .on('display', (res) => {
      setTimeout(function () {
        app.tbcalling.rows().every(function (rowIdx, tableLoop, rowLoop) {
          var data = this.data()
          if (data.q_ids === res.artist.modelQueue.q_ids) {
            $('#tb-calling').find('tr.success').removeClass('success')
            $('#last-queue').html(data.q_num)
            app.tbcalling.$('tr#' + res.artist.data.DT_RowId).addClass('success')
            Queue.toastrWarning('', '<i class="pe-7s-speaker"></i> กำลังเรียกคิว #' + data.q_num)
          }
        })
      }, 500)
    })
})

//CallNext
$('a.activity-callnext').on('click', function (e) {
  e.preventDefault()
  var data = app.tbwaiting.rows(0).data()
  if (data.length > 0) {
    swal({
      title: 'ยืนยันเรียกคิว ' + data[0].q_num + ' ?',
      html: '<p><i class="fa fa-user"></i> ' + data[0].pt_name + '</p>',
      input: 'select',
      type: 'question',
      inputOptions: app.counterOpts,
      inputPlaceholder: 'เลือกห้องตรวจ',
      inputValue: data[0].counter_service_id || '',
      inputClass: 'form-control m-b',
      showCancelButton: true,
      confirmButtonText: 'เรียกคิว',
      cancelButtonText: 'ยกเลิก',
      allowOutsideClick: false,
      inputValidator: (value) => {
        if (!value) {
          return 'คุณไม่ได้เลือกห้องตรวจ!'
        }
      },
      preConfirm: (value) => {
        return new Promise((resolve) => {
          $.ajax({
            method: 'POST',
            url: `/node/api/v1/calling/call?id=${data[0].q_ids}&access-token=${accesstoken}`,
            dataType: 'json',
            data: {
              data: data[0], //Data in column Datatable
              ...app.form,
              counter_service_id: value,
            },
            success: function (res) {
              if (res.status == 200) {
                Queue.reloadTbWaiting() //โหลดข้อมูลคิวรอพบแพทย์ใหม่
                Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                Queue.toastrSuccess('CALL ' + data[0].q_num)
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
    })
    jQuery('.swal2-select').select2({
      allowClear: true,
      theme: 'bootstrap',
      width: '100%',
      placeholder: 'เลือกห้องตรวจ...',
      language: 'th',
    })
    $('select.swal2-select, span.select2').addClass('input-lg')
    $('#swal2-content').css('padding-bottom', '15px')
  } else {
    swal({
      type: 'warning',
      title: 'ไม่พบหมายเลขคิว',
      showConfirmButton: false,
      timer: 1500,
    })
  }
})

var $form = $('#calling-form')
$form.on('beforeSubmit', function () {
  var dataObj = {}
  var qcall

  $form.serializeArray().map(function (field) {
    dataObj[field.name] = field.value
  })

  if (dataObj['CallingForm[qnum]'] != null && dataObj['CallingForm[qnum]'] != '') {
    //ข้อมูลกำลังเรียก
    app.tbcalling.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = { data: data, tbkey: 'tbcalling' }
      }
    })
    //ข้อมูลคิวรอพบแพทย์
    app.tbwaiting.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = { data: data, tbkey: 'tbwaiting' }
      }
    })
    //ข้อมูลพักคิว
    app.tbhold.rows().every(function (rowIdx, tableLoop, rowLoop) {
      var data = this.data()
      if (data.q_num === dataObj['CallingForm[qnum]']) {
        qcall = { data: data, tbkey: 'tbhold' }
      }
    })

    if (qcall === undefined) {
      toastr.error(dataObj['CallingForm[qnum]'], 'ไม่พบข้อมูล!', { timeOut: 3000, positionClass: 'toast-top-right' })
    } else {
      if (qcall.tbkey === 'tbcalling') {
        swal({
          title: 'ยืนยันเรียกคิว ' + qcall.data.q_num + ' ?',
          text: qcall.data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p><i class="fa fa-user"></i>' +
            qcall.data.pt_name +
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
                  counter_service_id: qcall.data.counter_service_id
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
          text: qcall.data.pt_name,
          html:
            '<small class="text-danger" style="font-size: 13px;">กด Enter เพื่อยืนยัน / กด Esc เพื่อยกเลิก</small>' +
            '<p><i class="fa fa-user"></i>' +
            qcall.data.pt_name +
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
                  counter_service_id: qcall.data.counter_service_id
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
          html: '<p><i class="fa fa-user"></i> ' + qcall.data.pt_name + '</p>',
          input: 'select',
          type: 'question',
          inputOptions: app.counterOpts,
          inputPlaceholder: 'เลือกห้องตรวจ',
          inputValue: qcall.data.counter_service_id || '',
          inputClass: 'form-control m-b',
          showCancelButton: true,
          confirmButtonText: 'เรียกคิว',
          cancelButtonText: 'ยกเลิก',
          allowOutsideClick: false,
          showLoaderOnConfirm: true,
          inputValidator: (value) => {
            if (!value) {
              return 'คุณไม่ได้เลือกห้องตรวจ!'
            }
          },
          preConfirm: (value) => {
            return new Promise((resolve) => {
              $.ajax({
                method: 'POST',
                url: `/node/api/v1/calling/call?id=${qcall.data.q_ids}&access-token=${accesstoken}`,
                dataType: 'json',
                data: {
                  data: qcall.data, //Data in column Datatable
                  ...app.form,
                  counter_service_id: value
                  // modelForm: modelForm, //Data Model CallingForm
                  // modelProfile: modelProfile,
                  // value: value, //select value
                },
                success: function (res) {
                  if (res.status == 200) {
                    Queue.reloadTbWaiting() //โหลดข้อมูลคิวรอพบแพทย์ใหม่
                    Queue.reloadTbCalling() //โหลดข้อมูลกำลังเรียกใหม่
                    Queue.toastrSuccess('CALL ' + qcall.data.q_num)
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
        })
        jQuery('.swal2-select').select2({
          allowClear: true,
          theme: 'bootstrap',
          width: '100%',
          placeholder: 'เลือกห้องตรวจ...',
          language: 'th',
        })
        $('select.swal2-select, span.select2').addClass('input-lg')
        $('#swal2-content').css('padding-bottom', '15px')
      }
    }
  } else {
    toastr.error(dataObj['CallingForm[qnum]'], 'ไม่พบข้อมูล!', { timeOut: 3000, positionClass: 'toast-top-right' })
  }
  $('input#callingform-qnum').val(null) //clear data
  return false
})

Queue.init()

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

  if (localStorage.getItem('examination-tablet-mode') == 'true') {
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
    var counternames = []
    $('#callingform-counter_service input[type="checkbox"]').each(function (index, value) {
      var el = $(this)
      if (el.is(':checked')) {
        counternames.push(el.closest('label').text())
      }
    })
    $('div.panel-form .panel-heading-text').html(' | ' + profilename + ': ' + counternames.join(' , '))
    $('#tablet-mode').prop('checked', true)
    $('#tab-menu-default,#tab-menu-default1,.small-header').css('display', 'none')
    $('.footer-tabs,.call-next-tablet-mode,.text-tablet-mode').css('display', '')
    $('#tab-watting .panel-body,#tab-calling .panel-body,#tab-hold .panel-body').css('border-top', '1px solid #e4e5e7')
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
    $('#tab-menu-default,#tab-menu-default1,.small-header').css('display', '')
    $('#tab-watting .panel-body,#tab-calling .panel-body,#tab-hold .panel-body').css('border-top', '0')
  }
}

$(document).ready(function () {
  $('#tablet-mode').on('click', function () {
    if ($(this).is(':checked')) {
      localStorage.setItem('examination-tablet-mode', 'true')
    } else {
      localStorage.setItem('examination-tablet-mode', 'false')
    }
    setTabletmode()
  })
})

setTabletmode()

$('#callingform-service_profile').on('change', function () {
  var val = $(this).val()
  app.setState({ key: 'service_profile_id', value: val })
  app.fetchDataServiceProfile()
})

Vue.use(Vuex)

const { mapActions, mapGetters } = Vuex

const store = new Vuex.Store({
  state: {
    service_profile_id: null,
    counter_service_ids: [],
  },
  getters: {
    form: (state) => {
      return {
        service_profile_id: state.service_profile_id,
        counter_service_ids: state.counter_service_ids,
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
  plugins: [
    createPersistedState({
      key: 'vuex-examination',
    }),
  ],
})

var app = new Vue({
  el: '#app',
  store,
  data: {
    modelServiceProfile: null,
    counters: [],

    tbwaiting: null,
    tbcalling: null,
    tbhold: null,

    waitingLoading: false,
    callingLoading: false,
    holdLoading: false,
  },
  computed: {
    ...mapGetters({
      form: 'form',
    }),
    serviceids() {
      return String(_.get(this.modelServiceProfile, 'service_id', '')).split(',')
    },
    counterOpts() {
      const selectopts = {}
      const counters = this.counters.filter((row) =>
        this.form.counter_service_ids.includes(parseInt(row.counterserviceid))
      )
      for (let i = 0; i < counters.length; i++) {
        const counter = counters[i]
        selectopts[counter.counterserviceid] = counter.counterservice_name
      }
      return selectopts
    },
  },
  mounted() {
    this.$nextTick(function () {
      var ids = []
      $.each($("input[name='CallingForm[counter_service][]']:checked"), function () {
        var id = $(this).val()
        ids.push(parseInt(id))
      })
      app.setState({
        key: 'counter_service_ids',
        value: ids,
      })
      if ($('#callingform-service_profile').val()) {
        // $('#callingform-service_profile').val(this.form.service_profile_id).trigger('change')
        // $('#callingform-service_profile').trigger('select2:select')
        this.setState({ key: 'service_profile_id', value: $('#callingform-service_profile').val() })
        this.fetchDataServiceProfile()
      }
      // this.initTableCalling()
      // this.initTableHold()
      //
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
          id: _this.form.service_profile_id,
        },
        success: async function (res) {
          _this.modelServiceProfile = res
          await _this.fetchDataCounterservices()
          _this.initTableWaiting()
          _this.initTableCalling()
          _this.initTableHold()
        },
        error: function (jqXHR, textStatus, errorThrown) {
          Queue.ajaxAlertError(errorThrown)
        },
      })
    },
    fetchDataCounterservices: function () {
      const _this = this
      $.ajax({
        method: 'GET',
        url: `/app/calling/counter-services`,
        dataType: 'json',
        data: {
          counterservice_typeid: _.get(_this.modelServiceProfile, 'counterservice_typeid'),
        },
        success: function (res) {
          _this.counters = res
        },
        error: function (jqXHR, textStatus, errorThrown) {
          Queue.ajaxAlertError(errorThrown)
        },
      })
    },

    // waiting
    initTableWaiting: function () {
      const _this = this
      if(_this.tbwaiting) {
        _this.tbwaiting.destroy();
      }
      var tbwaiting = jQuery('#tb-waiting').DataTable({
        ajax: {
          url: '/node/api/v1/examination/waiting-list',
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
                filter: {
                  service_id: {
                    in: _this.serviceids,
                  },
                  // counter_service_id: {
                  //   in: _this.form.counter_service_ids
                  // },
                  service_status_id: 5,
                },
                form: _this.form,
                'access-token': accesstoken,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              filter: {
                service_id: {
                  in: _this.serviceids,
                },
                // counter_service_id: {
                //   in: _this.form.counter_service_ids
                // },
                service_status_id: 5,
              },
              'access-token': accesstoken,
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
        responsive: false,
        processing: true,
        serverSide: true,
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
          { data: 'service_name', className: 'dt-body-left dt-head-nowrap', title: 'ประเภท' },
          {
            data: 'pt_name',
            className: 'dt-body-left dt-head-nowrap',
            title: '<i class="fa fa-user"></i> ชื่อ-นามสกุล',
          },
          {
            data: 'checkin_date',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-clock-o"></i> เวลามาถึง',
            visible: false,
          },
          {
            data: 'counterservice_name',
            className: 'dt-body-center dt-head-nowrap',
            title: 'จุดบริการ',
            visible: false,
          },
          { data: 'service_status_name', className: 'dt-body-center dt-head-nowrap', title: 'สถานะ' },
          { data: 'service_prefix', className: 'dt-body-center dt-head-nowrap', title: 'prefix', visible: false },
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-nowrap',
            orderable: false,
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            responsivePriority: 1,
            render: function (data, type, row, meta) {
              return `<a href="/node/api/v1/calling/call?id=${row.q_ids}" class="btn btn-success btn-calling">เรียกคิว</a>
            <a href="/node/api/v1/calling/end?id=${row.q_ids}" class="btn btn-danger btn-end">เสร็จสิ้น</a>`
            },
          },
        ],
        columnDefs: [{ targets: [2, 3, 4, 6, 7, 8, 9], visible: false }],
        buttons: [
          { extend: 'colvis', text: '<i class="glyphicon glyphicon-list"></i> ' },
          {
            text: '<i class="glyphicon glyphicon-refresh"></i> ',
            action: function (e, dt, node, config) {
              dt.ajax.reload()
            },
          },
        ],
        drawCallback: function (settings) {
          var api = this.api()
          var count = api.data().count()
          $('.count-waiting').html(count)
        }
      })
      jQuery('#tb-waiting')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          swal({ title: 'Error...!', html: '<small>' + message + '</small>', type: 'error' })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.waitingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('.count-waiting').html(json.total || 0)
          _this.waitingLoading = false
        })

      this.tbwaiting = tbwaiting
    },

    // calling
    initTableCalling: function () {
      const _this = this
      if(_this.tbcalling) {
        _this.tbcalling.destroy();
      }
      var tbcalling = jQuery('#tb-calling').DataTable({
        ajax: {
          url: `/node/api/v1/examination/calling-list`,
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
                filter: {
                  service_id: {
                    in: _this.serviceids,
                  },
                  counter_service_id: {
                    in: _this.form.counter_service_ids,
                  },
                  service_status_id: 2,
                },
                form: _this.form,
                'access-token': accesstoken,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              filter: {
                service_id: {
                  in: _this.serviceids,
                },
                // counter_service_id: {
                //   in: _this.form.counter_service_ids
                // },
                service_status_id: 5,
              },
              'access-token': accesstoken,
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
        responsive: false,
        processing: true,
        serverSide: true,
        order: [[9, 'asc']],
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
          var count = api.data().count()
          $('.count-calling').html(count)

          var rows = api.rows({ page: 'current' }).nodes()
          var columns = api.columns().nodes()
          var last = null
          api
            .column(3, { page: 'current' })
            .data()
            .each(function (group, i) {
              var data = api.rows(i).data()
              if (last !== group) {
                $(rows)
                  .eq(i)
                  .before(
                    '<tr class=""><td style="text-align: left;font-size: 16px;" colspan="' +
                    columns.length +
                    '">' +
                    group +
                    '</td></tr>'
                  )
                last = group
              }
            })
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
          { data: 'service_name', className: 'dt-body-left dt-head-nowrap', title: 'ประเภท' },
          {
            data: 'pt_name',
            className: 'dt-body-left dt-head-nowrap',
            title: '<i class="fa fa-user"></i> ชื่อ-นามสกุล',
          },
          { data: 'counterservice_name', className: 'dt-body-center dt-head-nowrap', title: 'จุดบริการ' },
          {
            data: 'checkin_date',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-clock-o"></i> เวลามาถึง',
          },
          { data: 'service_status_name', className: 'dt-body-center dt-head-nowrap', title: 'สถานะ' },
          { data: 'service_prefix', className: 'dt-body-center dt-head-nowrap', title: 'prefix' },
          {
            data: null,
            defaultContent: '',
            className: 'dt-center dt-nowrap',
            orderable: false,
            title: '<i class="fa fa-cogs"></i> ดำเนินการ',
            responsivePriority: 1,
            render: function (data, type, row, meta) {
              return `<a href="/node/api/v1/calling/recall?id=${row.caller_ids}" class="btn btn-success btn-recall">เรียกคิว</a>
              <a href="/node/api/v1/calling/hold?id=${row.caller_ids}" class="btn btn-warning btn-hold">พักคิว</a>
              <a href="/node/api/v1/calling/end?id=${row.caller_ids}" class="btn btn-danger btn-end">เสร็จสิ้น</a>`
            },
          },
        ],
        columnDefs: [{ targets: [2, 3, 6, 7, 8, 9], visible: false }],
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
      jQuery('#tb-calling')
        .on('error.dt', function (e, settings, techNote, message) {
          e.preventDefault()
          swal({ title: 'Error...!', html: '<small>' + message + '</small>', type: 'error' })
        })
        .on('preXhr.dt', function (e, settings, data) {
          _this.callingLoading = true
        })
        .on('xhr.dt', function (e, settings, json, xhr) {
          $('.count-calling').html(json.total || 0)
          _this.callingLoading = false
        })

      _this.tbcalling = tbcalling
    },

    // hold
    initTableHold: function () {
      const _this = this
      if(_this.tbhold) {
        _this.tbhold.destroy();
      }
      var tbhold = jQuery('#tb-hold').DataTable({
        ajax: {
          url: `/node/api/v1/examination/hold-list`,
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
                filter: {
                  service_id: {
                    in: _this.serviceids,
                  },
                  counter_service_id: {
                    in: _this.form.counter_service_ids,
                  },
                  service_status_id: 3,
                },
                form: _this.form,
                'access-token': accesstoken,
              })
            }
            return $.extend({}, d, {
              form: _this.form,
              filter: {
                service_id: {
                  in: _this.serviceids,
                },
                // counter_service_id: {
                //   in: _this.form.counter_service_ids
                // },
                service_status_id: 5,
              },
              'access-token': accesstoken,
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
        responsive: false,
        processing: true,
        serverSide: true,
        order: [[9, 'asc']],
        drawCallback: function (settings) {
          var api = this.api()
          dtFnc.initConfirm(api)
          var count = api.data().count()
          $('.count-hold').html(count)

          var rows = api.rows({ page: 'current' }).nodes()
          var columns = api.columns().nodes()
          var last = null
          api
            .column(3, { page: 'current' })
            .data()
            .each(function (group, i) {
              var data = api.rows(i).data()
              if (last !== group) {
                $(rows)
                  .eq(i)
                  .before(
                    '<tr class=""><td style="text-align: center;font-size: 16px;" colspan="' +
                    columns.length +
                    '">' +
                    group +
                    '</td></tr>'
                  )
                last = group
              }
            })
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
          { data: 'service_name', className: 'dt-body-left dt-head-nowrap', title: 'ประเภท' },
          {
            data: 'pt_name',
            className: 'dt-body-left dt-head-nowrap',
            title: '<i class="fa fa-user"></i> ชื่อ-นามสกุล',
          },
          { data: 'counterservice_name', className: 'dt-body-center dt-head-nowrap', title: 'จุดบริการ' },
          {
            data: 'checkin_date',
            className: 'dt-body-center dt-head-nowrap',
            title: '<i class="fa fa-clock-o"></i> เวลามาถึง',
          },
          { data: 'service_status_name', className: 'dt-body-center dt-head-nowrap', title: 'สถานะ' },
          { data: 'service_prefix', className: 'dt-body-center dt-head-nowrap', title: 'prefix' },
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
        columnDefs: [{ targets: [2, 3, 6, 7, 8, 9], visible: false }],
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
          $('.count-hold').html(json.total || 0)
          _this.holdLoading = false
        })

      _this.tbhold = tbhold
    },
  },
})
