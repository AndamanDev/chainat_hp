Highcharts.setOptions([])

// Add a request interceptor
axios.interceptors.request.use(
  (config) => {
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Add a response interceptor
axios.interceptors.response.use(
  (response) => {
    if (_.get(response, 'data.data', null)) {
      return _.get(response, 'data.data', response)
    }
    return _.get(response, 'data', response)
  },
  (error) => {
    return Promise.reject(_.get(error, 'response.data', error))
  }
)

var app = new Vue({
  el: '#app',
  data: {
    services: [],
    queues: [],
    chart1: null,
    chart2: null,
    limit: 10,
    service_id: ""
  },
  computed: {
    serviceList() {
      if (!this.service_id) return this.services

      return _.filter(this.services, (item) => parseInt(item.serviceid) === parseInt(this.service_id))
    },
    categories() {
      return this.serviceList.map((item) => item.service_name)
    },
    countallservices() {
      const colors = ['primary', 'info', 'danger', 'success', 'warning']
      const countall = this.queues.length
      const countwaitall = _.filter(this.queues, (item) => item.service_status_id === 1).length

      const random = Math.floor(Math.random() * colors.length)
      const items = []
      items.push({
        service_name: 'จำนวนคิวทั้งหมด',
        count: countall,
        wait: countwaitall,
        color: colors[random],
      })

      for (let i = 0; i < this.serviceList.length; i++) {
        const service = this.serviceList[i]
        const total = _.filter(this.queues, (item) => item.serviceid === service.serviceid).length
        const wait = _.filter(
          this.queues,
          (item) => item.serviceid === service.serviceid && item.service_status_id === 1
        ).length
        const waitex = _.filter(
          this.queues,
          (item) => item.serviceid === service.serviceid && item.service_status_id === 4 && item.counter_service_id
        ).length

        const random = Math.floor(Math.random() * colors.length)

        items.push({
          service_name: service.service_name,
          count: total,
          wait: wait + waitex,
          color: colors[random],
        })
      }

      return items
    },
    seriesall() {
      const items = []
      for (let i = 0; i < this.serviceList.length; i++) {
        const service = this.serviceList[i]
        const total = _.filter(this.queues, (item) => item.serviceid === service.serviceid).length
        items.push(total)
      }

      return items
    },
    serieswait() {
      const items = []
      for (let i = 0; i < this.serviceList.length; i++) {
        const service = this.serviceList[i]
        const wait = _.filter(
          this.queues,
          (item) => item.serviceid === service.serviceid && item.service_status_id === 1
        ).length
        const waitex = _.filter(
          this.queues,
          (item) => item.serviceid === service.serviceid && item.service_status_id === 4 && item.counter_service_id
        ).length
        items.push(wait + waitex)
      }
      return items
    },
    slottimes() {
      const items = []

      let start = '06:00'
      for (let i = 0; i < 18; i++) {
        items.push({
          name: `${start}-${moment(start, 'HH:mm').add(1, 'hours').format('HH:mm')}`,
          start: start,
          end: moment(start, 'HH:mm').add(1, 'hours').format('HH:mm'),
        })
        start = moment(start, 'HH:mm').add(1, 'hours').format('HH:mm')
      }

      return items
    },
    series() {
      const items = []
      const subseries2 = []
      let start = '06:00'
      for (let i = 0; i < 18; i++) {
        const item = {
          name: `${start}-${moment(start, 'HH:mm').add(1, 'hours').format('HH:mm')}`,
          start: start,
          end: moment(start, 'HH:mm').add(1, 'hours').format('HH:mm'),
        }

        const count = _.filter(this.queues, (row) => {
          const a = parseFloat(moment(moment(row.queue_time, 'HH:mm:ss').format('HH:mm'), 'HH:mm').format('X'))
          const b = parseFloat(moment(item.start, 'HH:mm').format('X'))
          const c = parseFloat(moment(item.end, 'HH:mm').format('X'))

          return a >= b && a <= c
        }).length

        items.push({
          name: item.name,
          y: count,
          drilldown: item.name,
        })

        const drilldown = []
        for (let i = 0; i < this.serviceList.length; i++) {
          const service = this.serviceList[i]
          const count = _.filter(this.queues, (row) => {
            const a = parseFloat(moment(moment(row.queue_time, 'HH:mm:ss').format('HH:mm'), 'HH:mm').format('X'))
            const b = parseFloat(moment(item.start, 'HH:mm').format('X'))
            const c = parseFloat(moment(item.end, 'HH:mm').format('X'))

            return a >= b && a <= c && row.serviceid === service.serviceid
          }).length

          drilldown.push([service.service_name, count])
        }

        subseries2.push({
          name: item.name,
          id: item.name,
          data: drilldown,
        })

        start = moment(start, 'HH:mm').add(1, 'hours').format('HH:mm')
      }

      return {
        series2: items,
        subseries2: subseries2,
      }
    },
    filteredServices: function () {
      var limit = this.limit;
      return this.countallservices.slice(0, limit);
    }
  },
  beforeMount() { },
  watch: {
    countallservices(val) { },
    categories(val) { },
  },
  mounted() {
    const _this = this
    this.$nextTick(function () {
      _this.initChart1()
      _this.initChart2()
      _this.initSelect2()
    })

    _this.getServices()
    _this.getQueues()

    socket.on('register', (res) => {
      _this.getQueues()
    });
  },
  methods: {
    getServices: async function () {
      const _this = this
      try {
        const { data } = await axios.get(`/node/v1/service/list`)
        _this.services = data
        if (_this.chart1) {
          _this.chart1.update({
            xAxis: {
              categories: _this.categories,
            },
          })
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: _.get(error, 'message'),
        })
      }
    },
    getQueues: async function () {
      try {
        const data = await axios.get(`/node/api/kiosk/queue-list?access-token=${accesstoken}`)
        if (Array.isArray(data)) {
          this.queues = data
        }

        if (this.chart1) {
          this.chart1.series[0].setData(this.seriesall)
          this.chart1.series[1].setData(this.serieswait)
        }
        if (this.chart2) {
          this.chart2.series[0].setData(this.series.series2)
          this.chart2.update({
            drilldown: {
              series: this.series.subseries2,
            },
          })
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: _.get(error, 'message'),
        })
      }
    },
    initChart1() {
      const _this = this
      _this.chart1 = new Highcharts.Chart({
        chart: {
          renderTo: 'chart1',
          type: 'bar',
        },
        title: {
          text: 'กราฟแสดงผลจำนวนผู้ที่มาใช้บริการ',
        },
        xAxis: {
          categories: this.categories,
        },
        yAxis: {
          min: 0,
          title: {
            text: 'จำนวนคิว',
            align: 'high',
          },
          labels: {
            overflow: 'justify',
          },
        },
        tooltip: {
          valueSuffix: ' คิว',
        },
        legend: {
          layout: 'vertical',
          align: 'right',
          verticalAlign: 'top',
          x: -40,
          y: 80,
          floating: true,
          borderWidth: 1,
          backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
          shadow: true,
        },
        plotOptions: {
          bar: {
            dataLabels: {
              enabled: true,
            },
          },
        },
        credits: {
          enabled: false,
        },
        series: [
          {
            name: 'คิวทั้งหมด',
            data: [],
          },
          {
            name: 'คิวรอ',
            data: [],
          },
        ],
      })
    },
    initChart2() {
      const _this = this
      _this.chart2 = new Highcharts.Chart({
        chart: {
          renderTo: 'chart2',
          type: 'column',
        },
        title: {
          text: 'กราฟแสดงจำนวนผู้ที่มาใช้บริการตามช่วงเวลา',
        },
        subtitle: {
          text: 'สามารถคลิกที่แท่งกราฟเพื่อดูจำนวนแยกตามประเภทบริการได้',
        },
        xAxis: {
          type: 'category',
          labels: {
            rotation: 45,
          },
        },
        yAxis: {
          min: 0,
          title: {
            text: 'จำนวน',
          },
        },
        tooltip: {
          headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
          pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>จำนวน {point.y} คิว</b><br/>',
        },
        plotOptions: {
          series: {
            borderWidth: 0,
          },
        },
        series: [
          {
            name: 'ช่วงเวลา',
            colorByPoint: true,
            data: [],
          },
        ],
        legend: {
          enabled: false,
        },
        drilldown: {
          series: [],
        },
        credits: {
          enabled: false,
        },
      })
    },
    loadMore() {
      if (this.limit >= this.countallservices.length) return
      this.limit = this.limit + 10
    },
    onChangeServiceId() {
      const _this = this
      if (_this.chart1) {
        _this.chart1.update({
          xAxis: {
            categories: _this.categories,
          },
        })
      }
      if (this.chart1) {
        this.chart1.series[0].setData(this.seriesall)
        this.chart1.series[1].setData(this.serieswait)
      }
      if (this.chart2) {
        this.chart2.series[0].setData(this.series.series2)
        this.chart2.update({
          drilldown: {
            series: this.series.subseries2,
          },
        })
      }
    },
    initSelect2() {
      const _this = this
      $('#serviceid').select2({
        theme: "bootstrap",
        allowClear: true,
        language: "th",
        placeholder: "งานบริการทั้งหมด"
      });

      $('#serviceid').on('change', function(){
        _this.service_id = $(this).val()
        _this.onChangeServiceId()
      })
    }
  },
})
