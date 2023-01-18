const { QueueExWaitingQuerier, QueueExCallingQuerier, QueueExHoldQuerier } = require('../models/queryql')
const { TbServiceProfile, TbQueue } = require('../models')
const _ = require('lodash')

exports.getQueueWaitingList = async (req, res) => {
  try {
    // const modelProfile = await TbServiceProfile.findOneById(_.get(req.body, 'form.service_profile_id', null))
    // const serviceids = String(_.get(modelProfile, 'service_id')).split(",");

    const querier = new QueueExWaitingQuerier(this.mapParams(req), TbQueue.getExaminationWaiting())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data'])
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getQueueCallingList = async (req, res) => {
  try {
    const querier = new QueueExCallingQuerier(this.mapParams(req), TbQueue.getExaminationCalling())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data'])
    })
  } catch (error) {
    res.error(error)
  }
}

exports.getQueueHoldList = async (req, res) => {
  try {
    const querier = new QueueExHoldQuerier(this.mapParams(req), TbQueue.getExaminationHold())
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.body, 'draw', 0)),
      recordsFiltered: _.get(response, 'total', 0),
      recordsTotal: _.get(response, 'total', 0),
      ..._.omit(response, ['data'])
    })
  } catch (error) {
    res.error(error)
  }
}

exports.mapParams = (req) => {
  const { columns, start, length, order, search, draw, page } = req.query

  let params = {}
  let filter = {}
  let sort = {}

  if (req.query.filter) {
    filter = _.assign(filter, req.query.filter)
  }

  if (columns) {
    for (let i = 0; i < columns.length; i++) {
      const column = columns[i];
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
      const orderdata = order[i];
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