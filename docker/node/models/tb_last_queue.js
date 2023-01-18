const db = require('../config/db').mysql
const _ = require('lodash')
const yup = require('yup')
const moment = require('moment')
moment.locale(process.env.MOMENT_LOCALE)
const transformDatetime = require('../utils/transform-datetime')

class LastQueueModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_last_queue'
  }

  static attributeLabels() {
    return {
      id: 'ไอดี',
      queue_date: '',
      queue_no: '',
      service_id: '',
      next_queue_no: '',
      created_at: '',
      updated_at: ''
    }
  }

  static schemas() {
    return yup.object().shape({
      id: yup.number().integer().nullable(),
      queue_date: yup.string().required('invalid queue_date.'),
      queue_no: yup.string().required('invalid queue_no.'),
      service_id: yup.number().nullable().integer().notRequired(),
      next_queue_no: yup.string().nullable().notRequired(),
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
    })
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('id', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

  save() {
    if (!this.attributes) throw new Error('attributes not set.')
    let attributes = _.omit(this.attributes, ['id'])

    if (_.get(this.attributes, 'id')) {
      attributes = Object.assign(attributes, {
        updated_at: moment().format('YYYY-MM-DD HH:mm:ss')
      })
      // update
      return this.db(this.tableName).where('id', this.attributes.id).update(_.omit(attributes, ['created_at']))
    } else {
      // create
      return this.db(this.tableName).insert(attributes)
    }
  }

}

LastQueueModel.tableName = 'tb_last_queue'
LastQueueModel.db = db

module.exports = LastQueueModel