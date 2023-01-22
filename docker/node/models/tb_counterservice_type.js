const db = require('../config/db').mysql

class CounterServiceTypeModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_counterservice_type'
  }

  static attributeLabels() {
    return {
      counterservice_typeid: 'ไอดี',
      counterservice_type: '',
      sound_id: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('counterservice_typeid', id).first()
  }

  static deleteById(id) {
    return this.delete({ counterservice_typeid: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

CounterServiceTypeModel.tableName = 'tb_counterservice_type'
CounterServiceTypeModel.db = db

module.exports = CounterServiceTypeModel