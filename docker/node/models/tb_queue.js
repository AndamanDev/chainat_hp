const db = require('../config/db').mysql
const _ = require('lodash')
const yup = require('yup')
const moment = require('moment')
moment.locale(process.env.MOMENT_LOCALE)
const transformDatetime = require('../utils/transform-datetime')
const transformDate = require('../utils/transform-date')
const transformTime = require('../utils/transform-time')

class QueueModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_quequ'
  }

  static attributeLabels() {
    return {
      q_ids: 'ไอดี',
      q_num: '',
      q_timestp: '',
      q_arrive_time: '',
      q_appoint_time: '',
      cid: '',
      q_vn: '',
      q_hn: '',
      q_qn: '',
      rx_q: '',
      pt_name: '',
      pt_visit_type_id: '',
      appoint_id: '',
      servicegroupid: '',
      quickly: '',
      serviceid: '',
      created_from: '',
      q_status_id: '',
      doctor_id: '',
      doctor_name: '',
      counterserviceid: '',
      tslotid: '',
      created_at: '',
      updated_at: '',
      pt_pic: '',
      pt_sound: '',
      maininscl_name: '',
      u_id: '',
      token: '',
      age: '',
      countdrug: '',
      qfinace: '',
      queue_date: '',
      queue_time: ''
    }
  }

  static schemas() {
    return yup.object().shape({
      q_ids: yup.number().integer(),
      q_num: yup.string().nullable().notRequired(),
      q_timestp: yup
        .string()
        .notRequired()
        .transform(transformDatetime)
        .default(() => moment().format('YYYY-MM-DD HH:mm:ss')),
      q_arrive_time: yup.number().nullable().integer().notRequired(),
      q_appoint_time: yup.number().nullable().integer().notRequired(),
      cid: yup.string().nullable().notRequired(),
      q_vn: yup.string().nullable().notRequired(),
      q_hn: yup.string().nullable().notRequired(),
      q_qn: yup.string().nullable().notRequired(),
      rx_q: yup.string().nullable().notRequired(),
      pt_name: yup.string().nullable().notRequired(),
      pt_visit_type_id: yup.number().nullable().integer().notRequired(),
      appoint_id: yup.number().nullable().integer().notRequired(),
      servicegroupid: yup.number().nullable().integer().notRequired(),
      quickly: yup.number().nullable().integer().notRequired(),
      serviceid: yup.number().nullable().integer().notRequired(),
      created_from: yup.number().nullable().integer().notRequired(),
      q_status_id: yup.number().nullable().integer().notRequired(),
      doctor_id: yup.number().nullable().integer().notRequired(),
      doctor_name: yup.string().nullable().notRequired(),
      counterserviceid: yup.number().nullable().integer().notRequired(),
      tslotid: yup.number().nullable().integer().notRequired(),
      created_at: yup
        .string()
        .notRequired()
        .transform(transformDatetime)
        .default(() => moment().format('YYYY-MM-DD HH:mm:ss')),
      updated_at: yup
        .string()
        .notRequired()
        .transform(transformDatetime)
        .default(() => moment().format('YYYY-MM-DD HH:mm:ss')),
      pt_pic: yup.string().nullable().notRequired(),
      pt_sound: yup.string().nullable().notRequired(),
      maininscl_name: yup.string().nullable().notRequired(),
      u_id: yup.string().nullable().notRequired(),
      token: yup.string().nullable().notRequired(),
      age: yup.string().nullable().notRequired(),
      countdrug: yup.number().nullable().integer().notRequired(),
      qfinace: yup.number().nullable().integer().notRequired(),
      queue_date: yup
        .string()
        .notRequired()
        .transform(transformDate)
        .default(() => moment().format('YYYY-MM-DD')),
      queue_time: yup
        .string()
        .notRequired()
        .transform(transformTime)
        .default(() => moment().format('HH:mm:ss')),
    })
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('q_ids', id).first()
  }

  static deleteById(id) {
    return this.delete({ q_ids: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

  save() {
    if (!this.attributes) throw new Error('attributes not set.')
    let attributes = _.omit(this.attributes, ['q_ids'])

    if (_.get(this.attributes, 'q_ids')) {
      attributes = Object.assign(attributes, {
        updated_at: moment().format('YYYY-MM-DD HH:mm:ss')
      })
      // update
      return this.db(this.tableName).where('q_ids', this.attributes.q_ids).update(_.omit(attributes, ['created_at', 'q_timestp', 'queue_date', 'queue_time']))
    } else {
      // create
      return this.db(this.tableName).insert(attributes)
    }
  }

  static getQueueList(query) {
    const qdate = _.get(query, 'qdate', moment().format('YYYY-MM-DD'))
    return this.db
      .select([
        db.raw(`tb_quequ.q_ids as DT_RowId`),
        'tb_quequ.*',
        'tb_service.*',
        'tb_counterservice.counterservice_name',
        'tb_service_status.service_status_name'
      ])
      .from(this.tableName)
      .innerJoin('tb_service', 'tb_quequ.serviceid', 'tb_service.serviceid')
      .innerJoin('tb_qtrans', 'tb_quequ.q_ids', 'tb_qtrans.q_ids')
      .innerJoin('tb_service_status', 'tb_quequ.q_status_id', 'tb_service_status.service_status_id')
      .leftJoin('tb_caller', 'tb_qtrans.ids', 'tb_caller.qtran_ids')
      .leftJoin('tb_counterservice', 'tb_caller.counter_service_id', 'tb_counterservice.counterserviceid')
      .whereRaw('tb_quequ.queue_date = ?', [qdate])
  }

  static getDataWaiting(serviceids) {
    return this.db.select([
      db.raw(`tb_quequ.q_ids as DT_RowId`),
      'tb_qtrans.ids',
      'tb_qtrans.q_ids',
      'tb_qtrans.counter_service_id',
      db.raw(`DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR), '%H:%i:%s') as checkin_date`),
      'tb_qtrans.service_status_id',
      'tb_quequ.q_num',
      'tb_quequ.q_hn',
      'tb_quequ.q_vn',
      'tb_quequ.q_qn',
      'tb_quequ.pt_name',
      'tb_counterservice.counterservice_name',
      'tb_service_status.service_status_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix',
      'tb_quequ.quickly',
      'tb_quequ.queue_date',
      'tb_quequ.queue_time'
    ])
      .from('tb_qtrans')
      .innerJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_qtrans.q_ids')
      .leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_qtrans.counter_service_id')
      .leftJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .leftJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .whereIn('tb_quequ.serviceid', serviceids)
      .whereIn('tb_qtrans.service_status_id', [1, 5, 11, 12, 13])
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('tb_quequ.quickly', 'desc')
  }

  static getDataCalling(serviceids) {
    return this.db.select([
      db.raw(`tb_caller.caller_ids as DT_RowId`),
      'tb_caller.caller_ids',
      'tb_caller.q_ids',
      'tb_caller.qtran_ids',
      // db.raw(`DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),'%H:%i:%s') as checkin_date`),
      db.raw(`(SELECT COUNT(tb_drug_dispensing.dispensing_id) FROM tb_drug_dispensing WHERE tb_drug_dispensing.HN = tb_quequ.q_hn AND DATE(tb_drug_dispensing.created_at) = CURRENT_DATE) AS total_drug`),
      'tb_caller.servicegroupid',
      'tb_caller.counter_service_id',
      'tb_caller.call_timestp',
      'tb_caller.call_time',
      'tb_caller.hold_time',
      'tb_caller.end_time',
      'tb_quequ.q_num',
      'tb_quequ.q_hn',
      'tb_quequ.q_qn',
      'tb_quequ.pt_name',
      'tb_quequ.countdrug',
      'tb_quequ.qfinace',
      // 'tb_service_status.service_status_name',
      'tb_counterservice.counterservice_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix',
      'tb_quequ.quickly',
      // 'tb_qtrans.ids',
      'tb_quequ.queue_date',
      'tb_quequ.queue_time'
    ])
      .from('tb_caller')
      .innerJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_caller.q_ids')
      // .innerJoin('tb_qtrans', 'tb_qtrans.ids', 'tb_caller.qtran_ids')
      // .innerJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_caller.counter_service_id')
      .innerJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .whereIn('tb_quequ.serviceid', serviceids)
      .whereIn('tb_caller.call_status', ['calling', 'callend'])
      .whereIn('tb_quequ.q_status_id', [2, 11, 12, 13])
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      // .whereRaw('DATE(tb_qtrans.created_at) = ?', [moment().format('YYYY-MM-DD')])
      .whereRaw('DATE(tb_caller.created_at) = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('tb_quequ.quickly', 'desc')
  }

  static getDataHold(serviceids) {
    return this.db.select([
      db.raw(`tb_caller.caller_ids as DT_RowId`),
      'tb_caller.caller_ids',
      'tb_caller.q_ids',
      'tb_caller.qtran_ids',
      // db.raw(`DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),'%H:%i:%s') as checkin_date`),
      'tb_caller.servicegroupid',
      'tb_caller.counter_service_id',
      'tb_caller.call_timestp',
      'tb_caller.call_time',
      'tb_caller.hold_time',
      'tb_caller.end_time',
      'tb_quequ.q_num',
      'tb_quequ.q_hn',
      'tb_quequ.q_qn',
      'tb_quequ.pt_name',
      'tb_quequ.countdrug',
      'tb_quequ.qfinace',
      // 'tb_service_status.service_status_name',
      'tb_counterservice.counterservice_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix',
      'tb_quequ.quickly',
      // 'tb_qtrans.ids',
      'tb_quequ.queue_date',
      'tb_quequ.queue_time'
    ])
      .from('tb_caller')
      .innerJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_caller.q_ids')
      // .innerJoin('tb_qtrans', 'tb_qtrans.ids', 'tb_caller.qtran_ids')
      // .innerJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_caller.counter_service_id')
      .innerJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .whereIn('tb_quequ.serviceid', serviceids)
      .whereIn('tb_caller.call_status', ['hold'])
      .whereIn('tb_quequ.q_status_id', [3, 11, 12, 13])
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      // .whereRaw('DATE(tb_qtrans.created_at) = ?', [moment().format('YYYY-MM-DD')])
      .whereRaw('DATE(tb_caller.created_at) = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('tb_quequ.quickly', 'desc')
  }

  static getExaminationWaiting() {
    return this.db.select([
      db.raw(`tb_qtrans.q_ids as DT_RowId`),
      'tb_qtrans.ids',
      'tb_qtrans.q_ids',
      'tb_qtrans.counter_service_id',
      db.raw(`DATE_FORMAT(SEC_TO_TIME(tb_quequ.q_appoint_time),'%H:%i') as checkin_date`),
      'tb_qtrans.service_status_id',
      'tb_quequ.q_num',
      'tb_quequ.q_hn',
      'tb_quequ.q_qn',
      'tb_quequ.q_vn as VN',
      'tb_quequ.pt_visit_type_id',
      'tb_quequ.pt_name',
      'tb_counterservice.counterservice_name',
      'tb_service_status.service_status_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix',
      db.raw(`SEC_TO_TIME(tb_quequ.q_appoint_time) as appoint_time`),
      'tb_quequ.queue_date',
      'tb_quequ.queue_time'
    ])
      .from('tb_qtrans')
      .innerJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_qtrans.q_ids')
      .leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_qtrans.counter_service_id')
      .leftJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .leftJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('tb_counterservice.counterserviceid', 'asc')
      .orderBy('checkin_date', 'asc')
  }

  static getExaminationCalling() {
    return this.db.select([
      db.raw(`tb_caller.caller_ids as DT_RowId`),
      'tb_caller.caller_ids',
      'tb_caller.q_ids',
      'tb_caller.qtran_ids',
      db.raw(`DATE_FORMAT(SEC_TO_TIME(tb_quequ.q_appoint_time),'%H:%i') as checkin_date`),
      'tb_caller.servicegroupid',
      'tb_caller.counter_service_id',
      'tb_caller.call_timestp',
      'tb_caller.call_time',
      'tb_caller.hold_time',
      'tb_caller.end_time',
      'tb_quequ.q_num',
      'tb_quequ.q_vn as VN',
      'tb_quequ.q_hn',
      'tb_quequ.q_qn',
      'tb_quequ.pt_name',
      'tb_service_status.service_status_name',
      'tb_counterservice.counterservice_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix',
      'tb_quequ.queue_date',
      'tb_quequ.queue_time'
    ])
      .from('tb_caller')
      .leftJoin('tb_qtrans', 'tb_qtrans.ids', 'tb_caller.qtran_ids')
      .leftJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_qtrans.q_ids')
      .leftJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_caller.counter_service_id')
      .leftJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .whereIn('tb_caller.call_status', ['calling', 'callend'])
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('tb_quequ.q_appoint_time', 'asc')
  }

  static getExaminationHold() {
    return this.db.select([
      db.raw(`tb_caller.caller_ids as DT_RowId`),
      'tb_caller.caller_ids',
      'tb_caller.q_ids',
      'tb_caller.qtran_ids',
      // 'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
      db.raw(`DATE_FORMAT(SEC_TO_TIME(tb_quequ.q_appoint_time),'%H:%i') as checkin_date`),
      'tb_caller.servicegroupid',
      'tb_caller.counter_service_id',
      'tb_caller.call_timestp',
      'tb_quequ.q_num',
      'tb_quequ.q_hn',
      'tb_quequ.q_qn',
      'tb_quequ.q_vn as VN',
      'tb_quequ.pt_name',
      'tb_service_status.service_status_name',
      'tb_counterservice.counterservice_name',
      'tb_service.service_name',
      'tb_service.serviceid',
      'tb_service.service_prefix'
    ])
      .from('tb_caller')
      .innerJoin('tb_qtrans', 'tb_qtrans.ids', 'tb_caller.qtran_ids')
      .innerJoin('tb_quequ', 'tb_quequ.q_ids', 'tb_qtrans.q_ids')
      .innerJoin('tb_service_status', 'tb_service_status.service_status_id', 'tb_qtrans.service_status_id')
      .innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid', 'tb_caller.counter_service_id')
      .leftJoin('tb_service', 'tb_service.serviceid', 'tb_quequ.serviceid')
      .where({ 'tb_caller.call_status': 'hold' })
      .orderBy('tb_quequ.q_appoint_time', 'asc')
  }

  static getNextQueue(service_id) {
    return this.db
      .select(['*'])
      .from(this.tableName).where({
        serviceid: service_id,
        q_status_id: 1
      })
      .whereRaw('tb_quequ.queue_date = ?', [moment().format('YYYY-MM-DD')])
      .orderBy('created_at', 'asc')
      .first()
  }

}

QueueModel.tableName = 'tb_quequ'
QueueModel.db = db

module.exports = QueueModel
