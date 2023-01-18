const db = require('../config/db').mysql
const yup = require('yup')
const _ = require('lodash')
const moment = require('moment')
moment.locale(process.env.MOMENT_LOCALE)
const transformDatetime = require('../utils/transform-datetime')

class QueueTranModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_qtrans'
  }

  static attributeLabels() {
    return {
      ids: 'ไอดี',
      q_ids: '',
      servicegroupid: '',
      counter_service_id: '',
      doctor_id: '',
      checkin_date: '',
      checkout_date: '',
      service_status_id: '',
      created_at: '',
      updated_at: '',
      created_by: '',
      updated_by: ''
    }
  }

  static schemas() {
    return yup.object().shape({
      ids: yup.number().integer().nullable(),
      q_ids: yup.number().integer().nullable(),
      servicegroupid: yup.number().nullable().integer().notRequired(),
      doctor_id: yup.number().nullable().integer().notRequired(),
      // checkin_date: yup.string().nullable().notRequired(),
      checkout_date: yup.string().nullable().notRequired(),
      service_status_id: yup.number().nullable().integer().notRequired(),
      checkin_date: yup
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
      created_by: yup.number().nullable().integer().notRequired(),
      updated_by: yup.number().nullable().integer().notRequired(),
    })
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('ids', id).first()
  }

  static deleteById(id) {
    return this.delete({ ids: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

  save() {
    if (!this.attributes) throw new Error('attributes not set.')
    let attributes = _.omit(this.attributes, ['ids'])

    if (_.get(this.attributes, 'ids')) {
      attributes = Object.assign(attributes, {
        updated_at: moment().format('YYYY-MM-DD HH:mm:ss')
      })
      // update
      return this.db(this.tableName).where('ids', this.attributes.ids).update(_.omit(attributes, ['created_at', 'checkin_date', 'created_by']))
    } else {
      // create
      return this.db(this.tableName).insert(attributes)
    }
  }

}

QueueTranModel.tableName = 'tb_qtrans'
QueueTranModel.db = db

module.exports = QueueTranModel