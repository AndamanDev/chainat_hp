const {
  SettingModel,
  TbSound,
  TbDisplay,
  TbCounterService,
  TbService,
  TbTicket,
  TbServiceProfile,
  TbSoundStation,
  TbCallingConfig
} = require('../../models')
const {
  SettingServiceGroupQuerier,
  TbSoundQuerier,
  DisplayQuerier,
  SettingCounterQuerier,
  TicketQuerier,
  ServiceProfileQuerier,
  SoundStationQuerier,
  CallingConfigQuerier
} = require('../../models/queryql')
const _ = require('lodash')

exports.getServiceGroups = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new SettingServiceGroupQuerier(params, SettingModel.getServiceGroups())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getSounds = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new TbSoundQuerier(params, TbSound.find())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getDisplays = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new DisplayQuerier(params, TbDisplay.find())
    const response = await querier.run()
    let rows = _.get(response, 'data', [])
    const items = []
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i]
      const counterids = String(row.counterservice_id).split(',')
      const serviceids = String(row.service_id).split(',')
      const counters = await TbCounterService.find().whereIn('counterserviceid', counterids)
      const services = await TbService.find().whereIn('serviceid', serviceids)
      items.push(
        _.assign(row, {
          DT_RowId: row.display_ids,
          services: services,
          counters: counters,
        })
      )
    }
    res.send({
      data: items,
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getCounters = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new SettingCounterQuerier(params, SettingModel.getCounters())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getTickets = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new TicketQuerier(params, TbTicket.find())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getServiceProfiles = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new ServiceProfileQuerier(params, SettingModel.getServiceProfiles())
    const response = await querier.run()
    const rows = _.get(response, 'data', [])
    const items = []

    for (let i = 0; i < rows.length; i++) {
      const row = rows[i]
      const serviceids = String(row.service_id).split(',')
      const services = await TbService.find().whereIn('serviceid', serviceids)
      items.push(
        _.assign(row, {
          services: services,
          serviceids: serviceids,
        })
      )
    }
    res.send({
      data: items,
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getSoundStations = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new SoundStationQuerier(params, TbSoundStation.find())
    const response = await querier.run()
    const rows = _.get(response, 'data', [])
    const items = []

    for (let i = 0; i < rows.length; i++) {
      const row = rows[i]
      const counterserviceids = String(row.counterserviceid).split(',')
      const counters = await TbCounterService.find().whereIn('counterserviceid', counterserviceids)
      items.push(
        _.assign(row, {
          counters: counters,
          counterserviceids: counterserviceids,
        })
      )
    }
    res.send({
      data: items,
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getCallingConfigs = async (req, res) => {
  try {
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    const querier = new CallingConfigQuerier(params, TbCallingConfig.find())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data']),
    })
  } catch (error) {
    res.error(error)
  }
}

exports.mapParams = (query) => {
  const { columns, start, length, order, search, draw, page } = query

  let params = {}
  let filter = {}
  let sort = {}

  if (columns) {
    for (let i = 0; i < columns.length; i++) {
      const column = columns[i]
      const data = _.get(column, 'data')
      const searchable = _.get(column, 'searchable', false)
      const value = _.get(column, 'search.value', '')
      if (data !== '' && data !== 'actions' && searchable && value) {
        filter[data] = value
      }
    }
  }

  if (order) {
    for (let i = 0; i < order.length; i++) {
      const orderdata = order[i]
      const column = _.get(columns, `[${orderdata.column}]`)
      if (column && _.get(column, 'data')) {
        sort[column.data] = _.get(orderdata, 'dir', 'asc')
      }
    }
  }

  if (_.get(search, 'value')) {
    filter['q'] = _.get(search, 'value')
  }
  if (filter) {
    params['filter'] = filter
  }
  if (page) {
    if (_.get(page, 'size') === -1) {
      params['page'] = _.omit(page, ['size'])
    } else {
      params['page'] = page
    }
  }
  if (sort) {
    params['sort'] = sort
  }

  return params
}
