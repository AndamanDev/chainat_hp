const { QueueWaitingQuerier, QueueCallingQuerier, QueueHoldQuerier } = require('../models/queryql')
const messageQueue = require('../queue/send-message')
const { TbQueue, TbServiceProfile, TbCounterService, TbService, TbCaller, TbQueueTrans, TbCallingConfig, TbSound } = require('../models')
const _ = require('lodash')
const fs = require('fs')
const path = require('path')
const moment = require('moment')
const audioconcat = require('audioconcat')
const ffmpegPath = require("@ffmpeg-installer/ffmpeg").path
const ffmpeg = require("fluent-ffmpeg")
ffmpeg.setFfmpegPath(ffmpegPath)
moment.locale(process.env.MOMENT_LOCALE)

exports.getQueueWaitingList = async (req, res) => {
  try {
    // const modelProfile = _.get(req.body, 'modelProfile')
    const modelProfile = await TbServiceProfile.findOneById(_.get(req.query, 'form.service_profile_id', null))
    const serviceids = String(_.get(modelProfile, 'service_id')).split(",");
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)
    

    const querier = new QueueWaitingQuerier(params, TbQueue.getDataWaiting(serviceids))
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
    const modelProfile = await TbServiceProfile.findOneById(_.get(req.query, 'form.service_profile_id', null))
    // const modelProfile = _.get(req.body, 'modelProfile')
    const serviceids = String(_.get(modelProfile, 'service_id')).split(",");
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)

    const querier = new QueueCallingQuerier(params, TbQueue.getDataCalling(serviceids))
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
    const modelProfile = await TbServiceProfile.findOneById(_.get(req.query, 'form.service_profile_id', null))
    // const modelProfile = _.get(req.body, 'modelProfile')
    const serviceids = String(_.get(modelProfile, 'service_id')).split(",");
    let params = _.assign(req.body, req.query)
    params = this.mapParams(params)

    const querier = new QueueHoldQuerier(params, TbQueue.getDataHold(serviceids))
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

exports.mapParams = (query) => {
  const { columns, start, length, order, search, draw, page } = query

  let params = {}
  let filter = {}
  let sort = {}

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
  if(query.sort) {
    sort = _.assign(sort, query.sort)
  }
  if (sort) {
    params['sort'] = sort
  }

  return params
}

// เรียกคิว
exports.postCall = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`)
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    // req.assert(req.body.modelProfile, 400, `invalid modelProfile.`)
    // req.assert(req.body.modelForm, 400, `invalid modelForm.`)
    // req.assert(req.body.data, 400, `invalid data.`)

    let data = _.get(req.body, 'data')

    const modelQueue = await TbQueue.findOneById(req.query.id)
    req.assert(modelQueue, 404, `queue not found.`)
    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)
    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)
    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)
    const modelQueueTrans = await TbQueueTrans.findOneById(data.ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)
    let modelCaller = await TbCaller.findOne({ q_ids: modelQueue.q_ids, qtran_ids: modelQueueTrans.ids })

    data = _.assign(data, { counter_service_id: modelCounterService.counterserviceid })

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      q_ids: modelQueue.q_ids,
      qtran_ids: modelQueueTrans.ids,
      counter_service_id: modelCounterService.counterserviceid,
      call_timestp: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_CALLING,
      created_by: _.get(req, 'user.id'),
      updated_by: _.get(req, 'user.id'),
    }

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 2,
      updated_by: _.get(req, 'user.id')
    }

    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const callersaved = await new TbCaller(callervalues).save()
    if (!modelCaller) {
      modelCaller = await TbCaller.findOneById(_.get(callersaved, '[0]'))
    } else {
      modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    }

    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 2,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelService.serviceid, modelQueue.q_ids)

    // send notification
    if (_.get(modelQueue, 'token')) {
      await messageQueue.add({
        data: {
          type: 'call-queue'
        },
        notification: {
          title: 'ถึงคิวของคุณแล้ว!',
          body: `หมายเลขคิวของคุณคือ ${modelQueue.q_num}`
        },
        token: _.get(modelQueue, 'token')
      });
    }

    const { sound, soungs } = await getMediaSound(modelQueue.q_num, modelCounterService)

    res.send({
      status: 200,
      message: 'success',
      sound: sound,
      audio: soungs.success ? soungs.audio : null,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-waiting',
      state: 'call'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.postRecall = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`) // caller_ids
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)

    let data = _.get(req.body, 'data')
    let modelCaller = await TbCaller.findOneById(req.query.id)
    req.assert(modelCaller, 404, `caller not found.`)

    const modelQueue = await TbQueue.findOneById(modelCaller.q_ids)
    req.assert(modelQueue, 404, `queue not found.`)

    const modelQueueTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)

    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)

    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      counter_service_id: req.body.counter_service_id,
      call_timestp: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_CALLING,
      updated_by: _.get(req, 'user.id', modelCaller.updated_by),
    }
    modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    await new TbCaller(callervalues).save()

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 2,
      updated_by: _.get(req, 'user.id')
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 2,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    // send notification
    if (_.get(modelQueue, 'token')) {
      await messageQueue.add({
        data: {
          type: 'call-queue'
        },
        notification: {
          title: 'ถึงคิวของคุณแล้ว!',
          body: `หมายเลขคิวของคุณคือ ${modelQueue.q_num}`
        },
        token: _.get(modelQueue, 'token')
      });
    }

    const { sound, soungs } = await getMediaSound(modelQueue.q_num, modelCounterService)

    res.send({
      status: 200,
      message: 'success',
      sound: sound,
      audio: soungs.success ? soungs.audio : null,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-calling',
      state: 'recall'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.postHold = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`) // caller_ids
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)

    let data = _.get(req.body, 'data')
    let modelCaller = await TbCaller.findOneById(req.query.id)
    req.assert(modelCaller, 404, `caller not found.`)

    const modelQueue = await TbQueue.findOneById(modelCaller.q_ids)
    req.assert(modelQueue, 404, `queue not found.`)

    const modelQueueTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)

    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)

    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      counter_service_id: req.body.counter_service_id,
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      hold_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_HOLD,
      updated_by: _.get(req, 'user.id', modelCaller.updated_by),
    }
    modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    await new TbCaller(callervalues).save()

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 3,
      updated_by: _.get(req, 'user.id')
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 3,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    // send notification
    if (_.get(modelQueue, 'token')) {
      await messageQueue.add({
        data: {
          type: 'call-queue'
        },
        notification: {
          title: 'คิวของคุณเรียกผ่านไปแล้ว!',
          body: `หมายเลขคิวของคุณคือ ${modelQueue.q_num}`
        },
        token: _.get(modelQueue, 'token')
      });
    }

    const { sound, soungs } = await getMediaSound(modelQueue.q_num, modelCounterService)

    res.send({
      status: 200,
      message: 'success',
      sound: sound,
      audio: soungs.success ? soungs.audio : null,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-calling',
      state: 'hold'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.postEnd = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`) // caller_ids
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)

    let data = _.get(req.body, 'data')
    let modelCaller = await TbCaller.findOneById(req.query.id)
    req.assert(modelCaller, 404, `caller not found.`)

    const modelQueue = await TbQueue.findOneById(modelCaller.q_ids)
    req.assert(modelQueue, 404, `queue not found.`)

    const modelQueueTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)

    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)

    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      counter_service_id: req.body.counter_service_id,
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED,
      updated_by: _.get(req, 'user.id', modelCaller.updated_by),
    }
    modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    await new TbCaller(callervalues).save()

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 4,
      counter_service_id: req.body.counter_service_id,
      updated_by: _.get(req, 'user.id')
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 4,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    // const sound = await getMediaSound(modelQueue.q_num, modelCounterService)

    res.send({
      status: 200,
      message: 'success',
      // sound: sound,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-calling',
      state: 'end'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.postWaittingDoctor = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`) // caller_ids
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)

    let data = _.get(req.body, 'data')
    let modelCaller = await TbCaller.findOneById(req.query.id)
    req.assert(modelCaller, 404, `caller not found.`)

    const modelQueue = await TbQueue.findOneById(modelCaller.q_ids)
    req.assert(modelQueue, 404, `queue not found.`)

    const modelQueueTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)

    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)

    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)

    const modelServiceEx = await TbService.find()
      .where({
        main_dep: modelService.main_dep,
        service_type_id: 2,
        service_groupid: modelService.service_groupid
      })
      .first()
    req.assert(modelServiceEx, 404, `ไม่พบการตั้งค่าห้องตรวจ.`)

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      counter_service_id: req.body.counter_service_id,
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED,
      updated_by: _.get(req, 'user.id', modelCaller.updated_by),
    }
    modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    await new TbCaller(callervalues).save()

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 4,
      counter_service_id: req.body.counter_service_id,
      updated_by: _.get(req, 'user.id')
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 4,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    // create new record
    const newqvalues = TbQueue.schemas().validateSync(_.assign(_.omit(modelQueue, ['serviceid', 'q_ids', 'q_status_id', 'created_at', 'updated_at', 'queue_time', 'q_timestp']), {
      q_status_id: 5,
      serviceid: modelServiceEx.serviceid,
      q_timestp: moment().format('YYYY-MM-DD HH:mm:ss'),
      queue_time: moment().format('HH:mm:ss'),
    }))
    const newq = await new TbQueue(newqvalues).save()
    const newQueueId = _.get(newq, '[0]')

    const newtransvalues = TbQueueTrans.schemas().validateSync({
      q_ids: newQueueId,
      servicegroupid: modelQueue.servicegroupid,
      doctor_id: modelQueueTrans.doctor_id,
      checkin_date: moment().format('YYYY-MM-DD HH:mm:ss'),
      checkout_date: modelQueueTrans.checkout_date,
      service_status_id: 5,
      created_by: _.get(req, 'user.id'),
      updated_by: _.get(req, 'user.id'),
    })
    await new TbQueueTrans(newtransvalues).save()

    res.send({
      status: 200,
      message: 'success',
      // sound: sound,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-calling',
      state: 'waiting-doctor'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.postWaitingPharmacy = async (req, res) => {
  try {
    req.assert(req.query.id, 400, `invalid id.`) // caller_ids
    req.assert(req.body.service_profile_id, 400, `invalid service_profile_id.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)

    let data = _.get(req.body, 'data')
    let modelCaller = await TbCaller.findOneById(req.query.id)
    req.assert(modelCaller, 404, `caller not found.`)

    const modelQueue = await TbQueue.findOneById(modelCaller.q_ids)
    req.assert(modelQueue, 404, `queue not found.`)

    const modelQueueTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    req.assert(modelQueueTrans, 404, `queue trans not found.`)

    const modelServiceProfile = await TbServiceProfile.findOneById(req.body.service_profile_id)
    req.assert(modelServiceProfile, 404, `service profile not found.`)

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter service not found.`)

    const modelService = await TbService.findOneById(modelQueue.serviceid)
    req.assert(modelService, 404, `service not found.`)

    const modelServiceEx = await TbService.find()
      .where({
        service_type_id: 4,
      })
      .first()
    req.assert(modelServiceEx, 404, `ไม่พบการตั้งค่าห้องยา.`)

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      counter_service_id: req.body.counter_service_id,
      updated_at: moment().format('YYYY-MM-DD HH:mm:ss'),
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED,
      updated_by: _.get(req, 'user.id', modelCaller.updated_by),
    }
    modelCaller = _.assign(modelCaller, _.omit(callerattrs, ['qtran_ids', 'caller_ids', 'q_ids']))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    await new TbCaller(callervalues).save()

    const transattrs = {
      ids: modelQueueTrans.ids,
      service_status_id: 4,
      counter_service_id: req.body.counter_service_id,
      updated_by: _.get(req, 'user.id')
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 4,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    // create new record
    const newqvalues = TbQueue.schemas().validateSync(_.assign(_.omit(modelQueue, ['serviceid', 'q_ids', 'q_status_id', 'created_at', 'updated_at', 'queue_time', 'q_timestp']), {
      q_status_id: 12,
      serviceid: modelServiceEx.serviceid,
      q_timestp: moment().format('YYYY-MM-DD HH:mm:ss'),
      queue_time: moment().format('HH:mm:ss'),
    }))
    const newq = await new TbQueue(newqvalues).save()
    const newQueueId = _.get(newq, '[0]')

    const newtransvalues = TbQueueTrans.schemas().validateSync({
      q_ids: newQueueId,
      servicegroupid: modelQueue.servicegroupid,
      doctor_id: modelQueueTrans.doctor_id,
      checkin_date: moment().format('YYYY-MM-DD HH:mm:ss'),
      checkout_date: modelQueueTrans.checkout_date,
      service_status_id: 12,
      created_by: _.get(req, 'user.id'),
      updated_by: _.get(req, 'user.id'),
    })
    await new TbQueueTrans(newtransvalues).save()

    res.send({
      status: 200,
      message: 'success',
      // sound: sound,
      data: data,
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelProfile: modelServiceProfile,
      counter: modelCounterService,
      service: modelService,
      eventOn: 'tb-calling',
      state: 'waiting-pharmacy'
    })
  } catch (error) {
    res.error(error)
  }
}

exports.sendMessage = async (serviceid, qids) => {
  try {
    const config = await TbCallingConfig.find().where({ notice_queue_status: 1 }).first();
    let limit = 4
    if (config) {
      limit = parseInt(config.notice_queue) + 1
    }
    const rows = await TbQueue.find()
      .where({ serviceid: serviceid, queue_date: moment().format('YYYY-MM-DD'), q_status_id: 1 })
      .whereRaw('q_ids <> ?', [qids])
      .limit(limit)
    if (limit === rows.length) {
      const last = _.last(rows)
      if (last && _.get(last, 'token')) {
        await messageQueue.add({
          data: {
            type: 'create-queue'
          },
          notification: {
            title: `อีก ${limit - 1} คิว ถึงคิวคุณ!`,
            body: `โปรดรอที่จุดบริการ!`
          },
          token: _.get(last, 'token')
        });
      }
    }
  } catch (error) {
    return Promise.reject(error)
  }
}

const getMediaSound = async (queueno, counter) => {
  try {
    const qnum = String(queueno).split('')
    const modelSound = await TbSound.findOne({ sound_id: _.get(counter, 'sound_id') })
    const SoundService = await TbSound.findOne({ sound_id: _.get(counter, 'sound_service_id') })
    const soungs = await this.concatAudioFile({
      prompt: SoundService.sound_path_name,
      text: queueno,
      service: SoundService.sound_name,
      counter: counter.counterservice_callnumber
    })

    let basePath = `/media/${modelSound.sound_path_name}`
    let begin = [`${basePath}/please.wav`]
    let end = [
      `/media/${SoundService.sound_path_name}/${SoundService.sound_name}`,
      `${basePath}/${modelSound.sound_name}`,
      `${basePath}/${modelSound.sound_path_name}_Sir.wav`
    ]
    let sound = []
    sound.push(...begin)
    for (let i = 0; i < qnum.length; i++) {
      const str = qnum[i];
      sound.push(`${basePath}/${modelSound.sound_path_name}_${str}.wav`)
    }
    sound.push(...end)
    return { sound, soungs }
  } catch (error) {
    return Promise.reject(error)
  }
}

exports.getNextQueue = async (req, res) => {
  try {
    req.assert(req.query.service_id, 400, `invalid service_id.`)
    const queue = await TbQueue.getNextQueue(req.query.service_id)
    // req.assert(queue, 404, `invalid service_id.`)
    res.success(queue)
  } catch (error) {
    res.error(error)
  }
}

exports.postCallingQueue = async (req, res) => {
  try {
    req.assert(req.body.q, 400, `invalid q.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    req.assert(req.body.service_id, 400, `invalid service_id.`)

    const modelService = await TbService.findOneById(req.body.service_id)
    req.assert(modelService, 404, `service not found.`)

    const modelQueue = await TbQueue.find()
      .where({
        // q_num: String(req.body.q).toUpperCase(),
        serviceid: req.body.service_id
      })
      .where(function () {
        this.where('q_num', String(req.body.q).toUpperCase()).orWhere('q_qn', req.body.q)
      })
      // .orWhere({ q_qn: req.body.q })
      .whereIn('q_status_id', [1, 2, 3, 5, 11, 12, 13])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('q_ids', 'desc')
      .first()
    req.assert(modelQueue, 404, `ไม่พบรายการคิว.`)
    if (modelQueue.q_status_id === 4) {
      req.assert(false, 400, `คิวนี้เสร็จสิ้นไปแล้ว.`)
    }

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter not found.`)

    let modelCaller = await TbCaller.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('call_status', ['calling', 'hold', 'callend'])
      .whereRaw('DATE(created_at) = ?', [moment().format('YYYY-MM-DD')])
      .first()

    let modelQTrans = await TbQueueTrans.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('service_status_id', [1, 2, 3, 5, 11, 12, 13])
      .first()
    const isNewCaller = !modelCaller

    if (modelCaller) {
      modelQTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    }

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      qtran_ids: _.get(modelQTrans, 'ids', null),
      q_ids: modelQueue.q_ids,
      counter_service_id: req.body.counter_service_id,
      call_timestp: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_CALLING
    }

    modelCaller = _.assign(modelCaller, _.omit(callerattrs, []))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const savedcaller = await new TbCaller(callervalues).save()
    if (isNewCaller) {
      modelCaller = _.assign(modelCaller, {
        caller_ids: _.get(savedcaller, '[0]')
      })
    }

    const transattrs = {
      ids: _.get(modelQTrans, 'ids', null),
      service_status_id: 2,
      q_ids: modelQueue.q_ids,
      servicegroupid: modelQueue.servicegroupid
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 2,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    // send notification
    if (_.get(modelQueue, 'token')) {
      await messageQueue.add({
        data: {
          type: 'calling-queue'
        },
        notification: {
          title: 'ถึงคิวของคุณแล้ว!',
          body: `หมายเลขคิวของคุณคือ ${modelQueue.q_num}`
        },
        token: _.get(modelQueue, 'token')
      });
    }

    const { sound, soungs } = await getMediaSound(modelQueue.q_num, modelCounterService)

    const response = {
      status: 200,
      message: 'success',
      sound: sound,
      audio: soungs.success ? soungs.audio : null,
      data: {
        counter_service_id: req.body.counter_service_id,
        qnumber: modelQueue.q_num,
        ...modelQueue
      },
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelQTrans: modelQTrans,
      counter: modelCounterService,
      service: modelService,
    }
    req.io.emit("call", response)

    res.send(response)
  } catch (error) {
    res.error(error)
  }
}

exports.postHoldQueue = async (req, res) => {
  try {
    req.assert(req.body.q, 400, `invalid q.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    req.assert(req.body.service_id, 400, `invalid service_id.`)

    const modelService = await TbService.findOneById(req.body.service_id)
    req.assert(modelService, 404, `service not found.`)

    const modelQueue = await TbQueue.find()
      .where({
        // q_num: String(req.body.q).toUpperCase(),
        serviceid: req.body.service_id
      })
      .where(function () {
        this.where('q_num', String(req.body.q).toUpperCase()).orWhere('q_qn', req.body.q)
      })
      // .orWhere({ q_qn: req.body.q })
      .whereIn('q_status_id', [2])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .first()
    req.assert(modelQueue, 404, `ไม่พบรายการคิว.`)
    if (modelQueue.q_status_id === 4) {
      req.assert(false, 400, `คิวนี้เสร็จสิ้นไปแล้ว.`)
    }

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter not found.`)

    let modelCaller = await TbCaller.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('call_status', ['calling', 'callend'])
      .whereRaw('DATE(created_at) = ?', [moment().format('YYYY-MM-DD')])
      .first()

    let modelQTrans = await TbQueueTrans.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('service_status_id', [1, 2, 3, 5])
      .first()

    if (modelCaller) {
      modelQTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    }

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      qtran_ids: _.get(modelQTrans, 'ids', null),
      q_ids: modelQueue.q_ids,
      counter_service_id: req.body.counter_service_id,
      hold_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_HOLD
    }

    modelCaller = _.assign(modelCaller, _.omit(callerattrs, []))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const savedcaller = await new TbCaller(callervalues).save()

    const transattrs = {
      ids: _.get(modelQTrans, 'ids', null),
      service_status_id: 3,
      q_ids: modelQueue.q_ids,
      servicegroupid: modelQueue.servicegroupid
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 3,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    // send notification
    if (_.get(modelQueue, 'token')) {
      await messageQueue.add({
        data: {
          type: 'calling-queue'
        },
        notification: {
          title: 'คิวของคุณเรียกผ่านไปแล้ว!',
          body: `หมายเลขคิวของคุณคือ ${modelQueue.q_num}`
        },
        token: _.get(modelQueue, 'token')
      });
    }

    const response = {
      status: 200,
      message: 'success',
      data: {
        counter_service_id: req.body.counter_service_id,
        qnumber: modelQueue.q_num,
        ...modelQueue
      },
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelQTrans: modelQTrans,
      counter: modelCounterService,
      service: modelService,
    }
    req.io.emit("hold", response)

    res.send(response)
  } catch (error) {
    res.error(error)
  }
}

exports.postEndQueue = async (req, res) => {
  try {
    req.assert(req.body.q, 400, `invalid q.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    req.assert(req.body.service_id, 400, `invalid service_id.`)

    const modelService = await TbService.findOneById(req.body.service_id)
    req.assert(modelService, 404, `service not found.`)

    const modelQueue = await TbQueue.find()
      .where({
        // q_num: String(req.body.q).toUpperCase(),
        serviceid: req.body.service_id
      })
      .where(function () {
        this.where('q_num', String(req.body.q).toUpperCase()).orWhere('q_qn', req.body.q)
      })
      // .orWhere({ q_qn: req.body.q })
      .whereIn('q_status_id', [2, 3, 5])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .first()
    req.assert(modelQueue, 404, `ไม่พบรายการคิว.`)
    if (modelQueue.q_status_id === 4) {
      req.assert(false, 400, `คิวนี้เสร็จสิ้นไปแล้ว.`)
    }

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter not found.`)

    let modelCaller = await TbCaller.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('call_status', ['calling', 'hold', 'callend'])
      .whereRaw('DATE(created_at) = ?', [moment().format('YYYY-MM-DD')])
      .first()

    let modelQTrans = await TbQueueTrans.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('service_status_id', [1, 2, 3, 5])
      .first()

    if (modelCaller) {
      modelQTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    }

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      qtran_ids: _.get(modelQTrans, 'ids', null),
      q_ids: modelQueue.q_ids,
      counter_service_id: req.body.counter_service_id,
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED
    }

    modelCaller = _.assign(modelCaller, _.omit(callerattrs, []))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const savedcaller = await new TbCaller(callervalues).save()

    const transattrs = {
      ids: _.get(modelQTrans, 'ids', null),
      service_status_id: 4,
      q_ids: modelQueue.q_ids,
      servicegroupid: modelQueue.servicegroupid
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 4,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    if (modelService.service_type_id === 1) {
      const modelServiceEx = await TbService.find()
        .where({
          main_dep: modelService.main_dep,
          service_type_id: 2,
          service_groupid: modelService.service_groupid
        })
        .first()
      req.assert(modelServiceEx, 400, `ไม่พบการตั้งค่าห้องตรวจ.`)

      // สร้างคิวห้องตรวจ
      const qvalues = TbQueue.schemas().validateSync({
        serviceid: modelServiceEx.serviceid,
        q_status_id: 5,
        queue_date: moment().format('YYYY-MM-DD'),
        queue_time: moment().format('HH:mm:ss'),
        ..._.omit(modelQueue, ['q_ids', 'serviceid', 'q_status_id', 'queue_date', 'queue_time'])
      })
      const saved = await new TbQueue(qvalues).save()

      const transvalues = TbQueueTrans.schemas().validateSync({
        q_ids: _.get(saved, '[0]'),
        servicegroupid: modelServiceEx.service_groupid,
        doctor_id: modelQTrans.doctor_id,
        checkin_date: modelQTrans.checkin_date,
        checkout_date: modelQTrans.checkout_date,
        service_status_id: 5
      })
      await new TbQueueTrans(transvalues).save()
    } else if (modelService.service_type_id === 3 && modelQueue.countdrug > 0) {
      const modelServiceEx = await TbService.find().where({ service_type_id: 4 }).first()
      req.assert(modelServiceEx, 400, `ไม่พบการตั้งค่าห้องยา.`)

      // สร้างคิวห้องตรวจ
      const qvalues = TbQueue.schemas().validateSync({
        serviceid: modelServiceEx.serviceid,
        servicegroupid: modelServiceEx.service_groupid,
        q_status_id: 12,
        queue_date: moment().format('YYYY-MM-DD'),
        queue_time: moment().format('HH:mm:ss'),
        ..._.omit(modelQueue, ['q_ids', 'serviceid', 'q_status_id', 'queue_date', 'queue_time', 'service_groupid'])
      })
      const saved = await new TbQueue(qvalues).save()

      const transvalues = TbQueueTrans.schemas().validateSync({
        q_ids: _.get(saved, '[0]'),
        servicegroupid: modelServiceEx.service_groupid,
        doctor_id: modelQTrans.doctor_id,
        checkin_date: modelQTrans.checkin_date,
        checkout_date: modelQTrans.checkout_date,
        service_status_id: 12
      })
      await new TbQueueTrans(transvalues).save()
    }

    await this.sendMessage(modelQueue.serviceid, modelQueue.q_ids)

    const response = {
      status: 200,
      message: 'success',
      data: {
        counter_service_id: req.body.counter_service_id,
        qnumber: modelQueue.q_num,
        ...modelQueue
      },
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelQTrans: modelQTrans,
      counter: modelCounterService,
      service: modelService,
    }
    req.io.emit("finish", response)

    res.send(response)
  } catch (error) {
    res.error(error)
  }
}

exports.postWaitingDoctorQueue = async (req, res) => {
  try {
    req.assert(req.body.q, 400, `invalid q.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    req.assert(req.body.service_id, 400, `invalid service_id.`)

    const modelService = await TbService.findOneById(req.body.service_id)
    req.assert(modelService, 404, `service not found.`)

    const modelQueue = await TbQueue.find()
      .where({
        serviceid: req.body.service_id
      })
      .where(function () {
        this.where('q_num', String(req.body.q).toUpperCase()).orWhere('q_qn', req.body.q)
      })
      .whereIn('q_status_id', [2, 3, 5])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('q_ids', 'desc')
      .first()
    req.assert(modelQueue, 404, `ไม่พบรายการคิว.`)
    if (modelQueue.q_status_id === 4) {
      req.assert(false, 400, `คิวนี้เสร็จสิ้นไปแล้ว.`)
    }

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter not found.`)

    let modelCaller = await TbCaller.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('call_status', ['calling', 'hold', 'callend'])
      .whereRaw('DATE(created_at) = ?', [moment().format('YYYY-MM-DD')])
      .first()

    let modelQTrans = await TbQueueTrans.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('service_status_id', [1, 2, 3, 5])
      .first()

    if (modelCaller) {
      modelQTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    }

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      qtran_ids: _.get(modelQTrans, 'ids', null),
      q_ids: modelQueue.q_ids,
      counter_service_id: req.body.counter_service_id,
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED
    }

    modelCaller = _.assign(modelCaller, _.omit(callerattrs, []))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const savedcaller = await new TbCaller(callervalues).save()

    const transattrs = {
      ids: _.get(modelQTrans, 'ids', null),
      service_status_id: 4,
      q_ids: modelQueue.q_ids,
      // servicegroupid: modelQueue.servicegroupid
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 4,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    const modelServiceEx = await TbService.find()
      .where({
        main_dep: modelService.main_dep,
        service_type_id: 2,
        service_groupid: modelService.service_groupid
      })
      .first()
    req.assert(modelServiceEx, 404, `ไม่พบการตั้งค่าห้องตรวจ.`)

    // สร้างคิวห้องตรวจ
    const newqueuevalues = TbQueue.schemas().validateSync({
      serviceid: modelServiceEx.serviceid,
      q_status_id: 5,
      queue_date: moment().format('YYYY-MM-DD'),
      queue_time: moment().format('HH:mm:ss'),
      ..._.omit(modelQueue, ['q_ids', 'serviceid', 'q_status_id', 'queue_date', 'queue_time', 'service_groupid'])
    })
    const saved = await new TbQueue(newqueuevalues).save()

    const newtransvalues = TbQueueTrans.schemas().validateSync({
      q_ids: _.get(saved, '[0]'),
      servicegroupid: modelServiceEx.service_groupid,
      doctor_id: modelQTrans.doctor_id,
      checkin_date: modelQTrans.checkin_date,
      checkout_date: modelQTrans.checkout_date,
      service_status_id: 5
    })
    await new TbQueueTrans(newtransvalues).save()

    const response = {
      status: 200,
      message: 'success',
      data: {
        counter_service_id: req.body.counter_service_id,
        qnumber: modelQueue.q_num,
        ...modelQueue
      },
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelQTrans: modelQTrans,
      counter: modelCounterService,
      service: modelService,
    }
    req.io.emit("finish", response)

    res.send(response)
  } catch (error) {
    res.error(error)
  }
}

exports.postWaitingPharmacyQueue = async (req, res) => {
  try {
    req.assert(req.body.q, 400, `invalid q.`)
    req.assert(req.body.counter_service_id, 400, `invalid counter_service_id.`)
    req.assert(req.body.service_id, 400, `invalid service_id.`)

    const modelService = await TbService.findOneById(req.body.service_id)
    req.assert(modelService, 404, `service not found.`)

    const modelQueue = await TbQueue.find()
      .where({
        serviceid: req.body.service_id
      })
      .where(function () {
        this.where('q_num', String(req.body.q).toUpperCase()).orWhere('q_qn', req.body.q)
      })
      .whereIn('q_status_id', [2, 3, 5])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('q_ids', 'desc')
      .first()
    req.assert(modelQueue, 404, `ไม่พบรายการคิว.`)
    if (modelQueue.q_status_id === 4) {
      req.assert(false, 400, `คิวนี้เสร็จสิ้นไปแล้ว.`)
    }

    const modelCounterService = await TbCounterService.findOneById(req.body.counter_service_id)
    req.assert(modelCounterService, 404, `counter not found.`)

    let modelCaller = await TbCaller.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('call_status', ['calling', 'hold', 'callend'])
      .whereRaw('DATE(created_at) = ?', [moment().format('YYYY-MM-DD')])
      .first()

    let modelQTrans = await TbQueueTrans.find()
      .where({
        q_ids: modelQueue.q_ids
      })
      .whereIn('service_status_id', [1, 2, 3, 5])
      .first()

    if (modelCaller) {
      modelQTrans = await TbQueueTrans.findOneById(modelCaller.qtran_ids)
    }

    let callerattrs = {
      caller_ids: _.get(modelCaller, 'caller_ids', null),
      qtran_ids: _.get(modelQTrans, 'ids', null),
      q_ids: modelQueue.q_ids,
      counter_service_id: req.body.counter_service_id,
      end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
      call_status: TbCaller.STATUS_FINISHED
    }

    modelCaller = _.assign(modelCaller, _.omit(callerattrs, []))
    const callervalues = TbCaller.schemas().validateSync(callerattrs)
    const savedcaller = await new TbCaller(callervalues).save()

    const transattrs = {
      ids: _.get(modelQTrans, 'ids', null),
      service_status_id: 4,
      q_ids: modelQueue.q_ids,
    }
    const transvalues = TbQueueTrans.schemas().validateSync(transattrs)
    await new TbQueueTrans(transvalues).save()

    const qattrs = {
      q_ids: modelQueue.q_ids,
      q_status_id: 12,
    }
    const qvalues = TbQueue.schemas().validateSync(qattrs)
    await new TbQueue(qvalues).save()

    const modelServiceEx = await TbService.find().where({ service_type_id: 4 }).first()
    req.assert(modelServiceEx, 400, `ไม่พบการตั้งค่าห้องยา.`)

    // สร้างคิวห้องตรวจ
    const newqvalues = TbQueue.schemas().validateSync({
      serviceid: modelServiceEx.serviceid,
      servicegroupid: modelServiceEx.service_groupid,
      q_status_id: 12,
      queue_date: moment().format('YYYY-MM-DD'),
      queue_time: moment().format('HH:mm:ss'),
      ..._.omit(modelQueue, ['q_ids', 'serviceid', 'q_status_id', 'queue_date', 'queue_time', 'service_groupid'])
    })
    const saved = await new TbQueue(newqvalues).save()

    const newtransvalues = TbQueueTrans.schemas().validateSync({
      q_ids: _.get(saved, '[0]'),
      servicegroupid: modelServiceEx.service_groupid,
      doctor_id: modelQTrans.doctor_id,
      checkin_date: modelQTrans.checkin_date,
      checkout_date: modelQTrans.checkout_date,
      service_status_id: 12
    })
    await new TbQueueTrans(newtransvalues).save()

    const response = {
      status: 200,
      message: 'success',
      data: {
        counter_service_id: req.body.counter_service_id,
        qnumber: modelQueue.q_num,
        ...modelQueue
      },
      modelCaller: modelCaller,
      modelQueue: modelQueue,
      modelQTrans: modelQTrans,
      counter: modelCounterService,
      service: modelService,
    }
    req.io.emit("finish", response)

    res.send(response)
  } catch (error) {
    res.error(error)
  }
}


// files path
let p1 = path.join(path.dirname(__dirname), "public", "media", "Prompt1", "mp3").replace(/\\/g, "/")
let p2 = path.join(path.dirname(__dirname), "public", "media", "Prompt2", "mp3").replace(/\\/g, "/")
exports.concatAudioFile = async (params) => {
  try {
    const rootPath = path.dirname(__dirname)
    let { prompt, text, service, counter, playCounter } = params
    let songs1 = []
    let songs2 = []
    let baseUrl = process.env.BASE_URL_AUDIO

    const chars = String(text).replace(/\s/g, "").split("")

    if (prompt === "Prompt1") {
      service = String(service).replace("Prompt1_", "").replace("_", "").replace(".wav", "").replace(".mp3", "")
      songs1.push(`${p1}/Prompt1_Please.mp3`) // เชิญหมายเลข
      songs1 = songs1.concat(chars.map((c) => `${p1}/Prompt1_${c}.mp3`))
      songs1.push(`${p1}/Prompt1_${service}.mp3`) // ที่ช่อง ห้อง โต๊ะ เตียง
      songs1.push(`${p1}/Prompt1_${counter}.mp3`) // หมายเลขโต๊ะ เตียง ช่องบริการ
      songs1.push(`${p1}/Prompt1_Sir.mp3`)

      songs2.push(`${p1}/Prompt1_Please.mp3`) // เชิญหมายเลข
      songs2 = songs2.concat(chars.map((c) => `${p1}/Prompt1_${c}.mp3`))
      songs2.push(`${p1}/Prompt1_${service}.mp3`) // ที่ช่อง ห้อง โต๊ะ เตียง
      // songs2.push(`${p1}/Prompt1_${counter}.mp3`) // หมายเลขโต๊ะ เตียง ช่องบริการ
      songs2.push(`${p1}/Prompt1_Sir.mp3`)
    }
    if (prompt === "Prompt2") {
      service = String(service).replace("Prompt2_", "").replace("_", "").replace(".wav", "").replace(".mp3", "")
      songs1.push(`${p2}/Prompt2_Please.mp3`) // เชิญหมายเลข
      songs1 = songs1.concat(chars.map((c) => `${p2}/Prompt2_${c}.mp3`))
      songs1.push(`${p2}/Prompt2_${service}.mp3`) // ที่ช่อง ห้อง โต๊ะ เตียง
      songs1.push(`${p2}/Prompt2_${counter}.mp3`) // หมายเลขโต๊ะ เตียง ช่องบริการ
      songs1.push(`${p2}/Prompt2_Sir.mp3`)

      songs2.push(`${p2}/Prompt2_Please.mp3`) // เชิญหมายเลข
      songs2 = songs2.concat(chars.map((c) => `${p2}/Prompt2_${c}.mp3`))
      songs2.push(`${p2}/Prompt2_${service}.mp3`) // ที่ช่อง ห้อง โต๊ะ เตียง
      // songs2.push(`${p2}/Prompt2_${counter}.mp3`) // หมายเลขโต๊ะ เตียง ช่องบริการ
      songs2.push(`${p2}/Prompt2_Sir.mp3`)
    }
    let isConcatSoung1 = true
    for (let i = 0; i < songs1.length; i++) {
      const song = songs1[i]
      if (!fs.existsSync(song)) {
        isConcatSoung1 = false
        // throw new Error("File not found.")
      }
    }
    let isConcatSoung2 = true
    for (let i = 0; i < songs2.length; i++) {
      const song = songs2[i]
      if (!fs.existsSync(song)) {
        isConcatSoung2 = false
        // throw new Error("File not found.")
      }
    }

    let output1 = `please-p1-${text}-${String(service).toLowerCase()}-${counter}-sir.mp3`
    let output2 = `please-p1-${text}-${String(service).toLowerCase()}-sir.mp3`
    if(prompt === "Prompt2") {
      output1 = `please-p2-${text}-${String(service).toLowerCase()}-${counter}-sir.mp3`
      output2 = `please-p2-${text}-${String(service).toLowerCase()}-sir.mp3`
    }
    // if (playCounter === "1") {
    // 	output = `please-${text}-${String(service).toLowerCase()}-sir.mp3`
    // }
    // const files = await glob.globAsync("./public/media/**/*.mp3")
    // const result = await audioQueue.add({ songs: songs, output: output })
    const pathOutput1 = path.join(
      rootPath,
      "public",
      "media",
      "files",
      output1
    )
    const pathOutput2 = path.join(
      rootPath,
      "public",
      "media",
      "files",
      output2
    )
    if (!fs.existsSync(pathOutput1) || !fs.existsSync(pathOutput2)) {
      if (isConcatSoung1) {
        await new Promise((resolve, reject) => {
          audioconcat(songs1)
            .concat(pathOutput1)
            .on("error", function (err, stdout, stderr) {
              console.error("Error:", err)
              console.error("ffmpeg stderr:", stderr)
              // next(err)
              reject(err)
            })
            .on("end", function (data) {
              console.error("Audio created in:", data)
              resolve(pathOutput1)
            })
        });
      }

      if (isConcatSoung2) {
        await new Promise((resolve, reject) => {
          audioconcat(songs2)
            .concat(pathOutput2)
            .on("error", function (err, stdout, stderr) {
              console.error("Error:", err)
              console.error("ffmpeg stderr:", stderr)
              // next(err)
              reject(err)
            })
            .on("end", function (data) {
              console.error("Audio created in:", data)
              resolve(pathOutput2)
            })
        })
      }

      if (!fs.existsSync(pathOutput1) || !fs.existsSync(pathOutput2)) {
        return {
          success: false
        }
      } else {
        fs.chmodSync(pathOutput1, 0777)
        fs.chmodSync(pathOutput2, 0777)
      }
      // const files = await Promise.all([
      //   audioconcat(songs1)
      //     .concat(pathOutput1)
      //     .on("error", function (err, stdout, stderr) {
      //       console.error("Error:", err)
      //       console.error("ffmpeg stderr:", stderr)
      //       // next(err)
      //       Promise.reject(err)
      //     })
      //     .on("end", function (data) {
      //       console.error("Audio created in:", data)
      //       Promise.resolve(pathOutput1)
      //     }),
      //   audioconcat(songs2)
      //     .concat(pathOutput2)
      //     .on("error", function (err, stdout, stderr) {
      //       console.error("Error:", err)
      //       console.error("ffmpeg stderr:", stderr)
      //       // next(err)
      //       Promise.reject(err)
      //     })
      //     .on("end", function (data) {
      //       console.error("Audio created in:", data)
      //       Promise.resolve(pathOutput2)
      //     }),
      // ])
      return {
        success: true,
        audio: {
          file1: `${baseUrl}/files/${output1}`,
          file2: `${baseUrl}/files/${output2}`,
        },
        // path: `/files/${output}`,
        // url: `${baseUrl}/files/${output}`,
        songs: {
          songs1: songs1,
          songs2: songs2,
        },
      }
    } else {
      return {
        success: true,
        audio: {
          file1: `${baseUrl}/files/${output1}`,
          file2: `${baseUrl}/files/${output2}`,
        },
        // path: `/files/${output}`,
        // url: `${baseUrl}/files/${output}`,
        songs: {
          songs1: songs1,
          songs2: songs2,
        },
      }
    }
  } catch (error) {
    return Promise.reject(error)
  }
}