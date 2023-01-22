const db = require('../config/db').mysql

class ServiceTypeModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_service_type'
  }

  static attributeLabels() {
    return {
      service_type_id: 'ไอดี',
      service_type_name: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('service_type_id', id).first()
  }

  static deleteById(id) {
    return this.delete({ service_type_id: id })
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

ServiceTypeModel.tableName = 'tb_service_type'
ServiceTypeModel.db = db

module.exports = ServiceTypeModel