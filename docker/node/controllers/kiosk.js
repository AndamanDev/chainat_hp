const _ = require('lodash')
const moment = require('moment')
const path = require('path')
const fs = require('fs')
const sprintf = require('sprintf-js').sprintf
const messageQueue = require('../queue/send-message')
const stringify = require('qs-stringify')
const { TbService, TbServiceGroup, TbServiceTsSlot, TbQueue, TbQueueTrans, TbServiceStatus, TbLastQueue, TbCaller } = require('../models')
const { QueueListQuerier } = require('../models/queryql')
moment.locale(process.env.MOMENT_LOCALE)

exports.postCreateQueue = async (req, res) => {
  let isNewRecord = false
  let q_ids = null
  let q_tran_ids = null
  let trx = null
  try {
    const body = req.body
    req.assert(body.patient_info, 400, `invalid patient_info.`)
    req.assert(body.servicegroupid, 400, `invalid servicegroupid.`)
    req.assert(body.serviceid, 400, `invalid serviceid.`)
    req.assert(body.created_from, 400, `invalid created_from.`)

    const patient_info = _.get(body, 'patient_info')
    const right = _.get(body, 'right')
    const appoint = _.get(body, 'appoint', null)
    const data_visit = _.get(body, 'patient_info.data_visit', null)
    const pt_name = _.get(patient_info, 'pt_name')
    const hn = _.get(patient_info, 'hn')
    const cid = _.get(patient_info, 'cid')
    let maininscl_name = _.get(right, 'cimaininscl_named')
    const appoint_id = _.get(appoint, 'appoint_id')
    const doctor_id = _.get(appoint, 'doctor_id')
    const doctor_name = _.get(appoint, 'doctor_name')
    const servicegroupid = _.get(body, 'servicegroupid')
    const serviceid = _.get(body, 'serviceid')
    const created_from = _.get(body, 'created_from')
    const picture = _.get(body, 'picture')
    const pt_visit_type_id = _.get(body, 'pt_visit_type_id')
    let u_id = _.get(body, 'u_id')
    const q_status_id = _.get(body, 'q_status_id', 1)
    const token = _.get(body, 'token')
    let age = _.get(patient_info, 'age', '').replace(/ /g, '').replace(/ปี/g, '')
    const countdrug = _.get(body, 'countdrug', 0)
    const qfinace = _.get(body, 'qfinace', 0)

    // trx = await TbService.db.transaction();
    // trx.isCompleted(); // false

    const modelService = await TbService.findOneById(serviceid)
    req.assert(modelService, 404, `ไม่พบข้อมูลแผนก.`)

    const modelServiceGroup = await TbServiceGroup.findOneById(servicegroupid)
    req.assert(modelServiceGroup, 404, `ไม่พบข้อมูลกลุ่มแผนก.`)

    let qn = null
    let vn = null
    if (Array.isArray(data_visit)) {
      const qnitem = data_visit.filter(item => !!_.get(item, 'qn')).find(item => item.main_dep === modelService.main_dep)
      const vnitem = data_visit.filter(item => !!_.get(item, 'vn')).find(item => item.main_dep === modelService.main_dep)
      qn = _.get(qnitem, 'qn', null)
      vn = _.get(vnitem, 'vn', null)
    }

    if (appoint) {
      maininscl_name = _.get(appoint, 'appoint_right')
    }

    const tslot = await TbServiceTsSlot.find().where({ serviceid: serviceid }).count('*', { as: 'count' }).first()
    const counttslot = _.get(tslot, 'count', 0)

    let tslotid = null
    if (counttslot) {
      let slot = await TbServiceTsSlot.find()
        .where({ serviceid: serviceid })
        .whereRaw('? >= t_slot_begin', [moment().format('HH:mm:00')])
        .whereRaw('? <= t_slot_end', [moment().format('HH:mm:00')])
        .orderBy('t_slot_begin', 'asc')
        .first()

      req.assert(slot, 400, `บริการ '${modelService.service_name}' ไม่ได้อยู่ในช่วงเวลาให้บริการ.`)
      tslotid = _.get(slot, 'tslotid')

      const countqueue = await TbQueue.db(TbQueue.tableName)
        .where({
          serviceid: serviceid,
          tslotid: tslotid
        })
        .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
        .count('q_ids', { as: 'count' })
        .first()
      const total = _.get(countqueue, 'count', 0)
      if (total > 0 && total >= _.get(slot, 'q_limitqty', 0) && slot.q_limit === 1) {
        req.assert(false, 400, `บริการ '${modelService.service_name}' คิวเต็ม!`)
      }
    }

    let modelQueue = await TbQueue.find()
      .where({
        serviceid: serviceid,
        servicegroupid: servicegroupid,
        q_hn: hn
      })
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .whereRaw('q_status_id <> ?', [4])
      .orderBy('q_ids', 'desc')
      .first()

    isNewRecord = !modelQueue

    let modelCaller = null

    const service_prefix = _.get(modelService, 'service_prefix', '1')
    const service_numdigit = _.get(modelService, 'service_numdigit', '4')

    let q_num = _.get(modelQueue, 'q_num', null)
    let lastQueue = await TbLastQueue.findOne({ queue_date: moment().format('YYYY-MM-DD'), service_id: serviceid })
    // is newrecord
    if (!modelQueue) {
      // const lastQueue = await TbQueue.find()
      //   .where({
      //     serviceid: serviceid,
      //     servicegroupid: servicegroupid,
      //   })
      //   .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      //   .whereRaw('q_status_id <> ?', [4])
      //   .orderBy('q_ids', 'desc')
      //   .first()
      q_num = this.generateQueueNumber({
        lastnum: _.get(lastQueue, 'queue_no', null),
        prefix: service_prefix,
        digit: service_numdigit
      })
    } else {
      modelCaller = await TbCaller.findOne({ q_ids: _.get(modelQueue, 'q_ids', null) })
      maininscl_name = _.get(modelQueue, 'maininscl_name')
      u_id = _.get(modelQueue, 'u_id')
      tslotid = _.get(modelQueue, 'tslotid')
    }

    let attributes = {
      q_ids: _.get(modelQueue, 'q_ids', null),
      q_num: q_num,
      cid: cid,
      q_hn: hn,
      q_vn: vn,
      q_qn: qn,
      pt_name: pt_name,
      appoint_id: appoint_id,
      servicegroupid: servicegroupid,
      serviceid: serviceid,
      created_from: created_from,
      q_status_id: modelCaller ? _.get(modelQueue, 'q_status_id') : q_status_id,
      doctor_id: doctor_id,
      doctor_name: doctor_name,
      maininscl_name: maininscl_name,
      pt_visit_type_id: pt_visit_type_id,
      tslotid: tslotid,
      quickly: 0,
      u_id: u_id,
      token: token,
      age: age,
      qfinace: qfinace,
      countdrug: countdrug,
      pt_pic: _.get(modelQueue, 'pt_pic'),
      created_at: _.get(modelQueue, 'created_at', moment().format("YYYY-MM-DD HH:mm:ss")),
      updated_at: _.get(modelQueue, 'updated_at', moment().format("YYYY-MM-DD HH:mm:ss")),
    }

    if (picture) {
      const data = await this.savePicture(picture, hn)
      attributes = Object.assign(attributes, {
        pt_pic: process.env.WEB_BASE_URL + data.path
      })
    }

    attributes = _.pickBy(attributes, (v) => v !== null && v !== undefined && v !== '')
    // validate
    const values = TbQueue.schemas().validateSync(attributes)
    const saved = await new TbQueue(values).save()

    q_ids = _.get(modelQueue, 'q_ids', _.get(saved, '[0]', null))
    let modelQtrans = await TbQueueTrans.find().where({ q_ids: q_ids }).first()

    const queue_left = await TbQueue.db(TbQueue.tableName)
      .where({
        serviceid: serviceid,
      })
      .whereRaw('q_timestp < ?', [moment().format('YYYY-MM-DD HH:mm:ss')])
      .whereRaw('queue_date = ?', [moment().format('YYYY-MM-DD')])
      .whereRaw('q_status_id <> ?', [4])
      .whereRaw('q_ids <> ?', [q_ids])
      .count('q_ids', { as: 'count' })
      .first()
    const count_queue_left = _.get(queue_left, 'count', 0)

    let queueTransAttributes = {
      ids: _.get(modelQtrans, 'ids', null),
      q_ids: q_ids,
      servicegroupid: servicegroupid,
      service_status_id: attributes.q_status_id,
      doctor_id: doctor_id
    }
    queueTransAttributes = _.pickBy(queueTransAttributes, (v) => v !== null && v !== undefined && v !== '')
    // validate
    const qtranvalues = TbQueueTrans.schemas().validateSync(queueTransAttributes)
    const qtranssaved = await new TbQueueTrans(qtranvalues).save()
    q_tran_ids = _.get(modelQtrans, 'ids', _.get(qtranssaved, '[0]', null))

    // save last queue
    const lastqueuevalues = TbLastQueue.schemas().validateSync({
      id: _.get(lastQueue, 'id', null),
      queue_no: q_num,
      queue_date: moment().format('YYYY-MM-DD'),
      service_id: serviceid,
      next_queue_no: this.generateQueueNumber({
        lastnum: q_num,
        prefix: service_prefix,
        digit: service_numdigit
      })
    })
    await new TbLastQueue(lastqueuevalues).save()

    // send notification
    if (token) {
      await messageQueue.add({
        data: {
          type: 'create-queue'
        },
        notification: {
          title: 'จองคิวสำเร็จ!',
          body: `หมายเลขคิวของคุณคือ ${q_num}`
        },
        token: token
      });
    }

    if (!modelQtrans) {
      modelQtrans = await TbQueueTrans.findOneById(q_tran_ids)
    }

    if (!modelQueue) {
      modelQueue = await TbQueue.findOneById(q_ids)
    }

    const serviceStatus = await TbServiceStatus.find().where({ service_status_id: attributes.q_status_id }).first()

    // await trx.commit();
    // trx.isCompleted(); // true
    const data = {
      modelQueue: modelQueue,
      modelQtrans: modelQtrans,
      service_status_name: _.get(serviceStatus, 'service_status_name'),
      queue_left: count_queue_left
    }

    if (isNewRecord) {
      req.io.emit("register", data);
    }

    res.send(data)
  } catch (error) {
    res.error(error)
  }
}

exports.getSlot = async (serviceid, tslotid = []) => {
  try {
    let slot = null
    if (tslotid) {
      slot = await TbServiceTsSlot.find()
        .where({ serviceid: serviceid })
        .whereRaw('t_slot_begin >= ?', [moment().format('HH:mm:00')])
        .whereNotIn('tslotid', tslotid)
        .first()
    } else {
      slot = await TbServiceTsSlot.find()
        .where({ serviceid: serviceid })
        .whereRaw('CURRENT_TIME >= tb_service_tslot.t_slot_begin', [])
        .whereRaw('CURRENT_TIME <= tb_service_tslot.t_slot_end', [])
        .first()
    }

    if (slot) {
      if (parseInt(_.get(slot, 'q_limit')) === 1) {

      }
    }
  } catch (error) {
    return Promise.reject(error)
  }
}

exports.generateQueueNumber = (params) => {
  const lastnum = _.get(params, 'lastnum', null)
  const prefix = _.get(params, 'prefix', '1')
  const digit = _.get(params, 'digit', 4)

  if (!lastnum) {
    return `${prefix}${sprintf(`%0${digit}d`, 1)}`
  }
  // is number
  if (/^\d+$/.test(lastnum)) {
    const number = String(lastnum).substring(String(prefix).length)
    return `${prefix}${sprintf(`%0${digit}d`, parseInt(number) + 1)}`
  } else {
    const number = String(lastnum).substring(String(prefix).length)
    const length = number.length
    if (/^\d+$/.test(number)) {
      return `${prefix}${sprintf(`%0${length}d`, parseInt(number) + 1)}`
    } else {
      return `${prefix}${sprintf(`%0${length}d`, 1)}`
    }
  }
}

exports.savePicture = async (picture, hn) => {
  try {
    const rootPath = path.dirname(__dirname)
    hn = sprintf(`%07d`, hn)
    const rootDir = String(hn).substring(0, 3)
    const subDir1 = String(hn).substring(3, 6)
    const subDir2 = String(hn).substring(6, 7)

    let isRootDir = fs.existsSync(path.join(rootPath, 'uploads', rootDir))
    if (!isRootDir) {
      fs.mkdirSync(path.join(rootPath, 'uploads', rootDir))
    }

    isRootDir = fs.existsSync(path.join(rootPath, 'uploads', rootDir))
    if (isRootDir && !fs.existsSync(path.join(rootPath, 'uploads', rootDir, subDir1))) {
      fs.mkdirSync(path.join(rootPath, 'uploads', rootDir, subDir1))
    }

    const isSubDir1 = fs.existsSync(path.join(rootPath, 'uploads', rootDir, subDir1))
    if (isRootDir && isSubDir1 && !fs.existsSync(path.join(rootPath, 'uploads', rootDir, subDir1, subDir2))) {
      fs.mkdirSync(path.join(rootPath, 'uploads', rootDir, subDir1, subDir2))
    }
    const isUploadDir = fs.existsSync(path.join(rootPath, 'uploads', rootDir, subDir1, subDir2))

    if (isUploadDir) {
      const base64Data = picture.replace(/^data:image\/png;base64,/, "");
      fs.writeFileSync(path.join(rootPath, 'uploads', rootDir, subDir1, subDir2, `${hn}.jpg`), base64Data, 'base64')
    }

    return {
      path: `/uploads/${rootDir}/${subDir1}/${subDir2}/${hn}.jpg`
    }
  } catch (error) {
    return Promise.reject(error)
  }
}

exports.getQueueList = async (req, res) => {
  try {
    const querier = new QueueListQuerier(this.mapParams(req.query), TbQueue.getQueueList(req.query))
    const response = await querier.run()
    res.send({
      data: _.get(response, 'data', []),
      draw: parseInt(_.get(req.query, 'draw', 0)),
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
  if (sort) {
    params['sort'] = sort
  }

  return params
  // return stringify(params)
}