const db = require('../config/db').mysql

class ServiceStatusModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_service_status'
  }

  static attributeLabels() {
    return {
      service_status_id: 'ไอดี',
      service_status_name: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('service_status_id', id).first()
  }

  static deleteById(id) {
    return this.delete({ service_status_id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

ServiceStatusModel.tableName = 'tb_service_status'
ServiceStatusModel.db = db

module.exports = ServiceStatusModel