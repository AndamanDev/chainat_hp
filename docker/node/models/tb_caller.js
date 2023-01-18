const db = require('../config/db').mysql
const _ = require('lodash')
const yup = require('yup')
const moment = require('moment')
moment.locale(process.env.MOMENT_LOCALE)
const transformDatetime = require('../utils/transform-datetime')

class CallerModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_caller'
  }

  static attributeLabels() {
    return {
      caller_ids: 'ไอดี',
      q_ids: '',
      qtran_ids: '',
      servicegroupid: '',
      counter_service_id: '',
      call_timestp: '',
      call_time: '',
      hold_time: '',
      end_time: '',
      created_by: '',
      created_at: '',
      updated_by: '',
      updated_at: '',
      call_status: '',
    }
  }

  static schemas() {
    return yup.object().shape({
      caller_ids: yup.number().integer().nullable(),
      q_ids: yup.number().integer().nullable(),
      qtran_ids: yup.number().integer().nullable(),
      servicegroupid: yup.number().integer().nullable(),
      counter_service_id: yup.number().integer().nullable(),
      call_timestp: yup
        .string()
        .notRequired()
        .transform(transformDatetime)
        .default(() => moment().format('YYYY-MM-DD HH:mm:ss')),
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
      created_by: yup.number().integer().nullable(),
      updated_by: yup.number().integer().nullable(),
      call_status: yup.string().nullable().notRequired(),
      call_time: yup.string().nullable().notRequired(),
      hold_time: yup.string().nullable().notRequired(),
      end_time: yup.string().nullable().notRequired(),
    })
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('caller_ids', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ caller_ids: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

  save() {
    if (!this.attributes) throw new Error('attributes not set.')
    let attributes = _.omit(this.attributes, ['caller_ids'])

    if (_.get(this.attributes, 'caller_ids')) {
      attributes = Object.assign(attributes, {
        updated_at: moment().format('YYYY-MM-DD HH:mm:ss')
      })
      // update
      return this.db(this.tableName).where('caller_ids', this.attributes.caller_ids).update(_.omit(attributes, ['created_at', 'created_by']))
    } else {
      // create
      return this.db(this.tableName).insert(attributes)
    }
  }

}

CallerModel.tableName = 'tb_caller'
CallerModel.db = db
CallerModel.STATUS_CALLING = 'calling'
CallerModel.STATUS_HOLD = 'hold'
CallerModel.STATUS_CALLEND = 'callend'
CallerModel.STATUS_FINISHED = 'finished'
CallerModel.STATUS_END = 'end'

module.exports = CallerModel