const db = require('../config/db').mysql

class ServiceTsSlotModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_service_tslot'
  }

  static attributeLabels() {
    return {
      tslotid: 'ไอดี',
      serviceid: '',
      t_slot_begin: '',
      t_slot_end: '',
      q_limit: '',
      q_limitqty: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('tslotid', id).first()
  }

  static deleteById(id) {
    return this.delete({ tslotid: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

  static count(field, conditions) {
    if(conditions) {
      return this.db(this.tableName).where(conditions).count(field, { as: 'count' })
    }
    return this.db(this.tableName).count(field, { as: 'count' })
  }

}

ServiceTsSlotModel.tableName = 'tb_service_tslot'
ServiceTsSlotModel.db = db

module.exports = ServiceTsSlotModel