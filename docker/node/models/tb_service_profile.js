const db = require('../config/db').mysql

class ServiceProfileModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_service_profile'
  }

  static attributeLabels() {
    return {
      service_profile_id: 'ไอดี',
      service_name: '',
      counterservice_typeid: '',
      service_id: '',
      service_profile_status: '',
      counter_service_ids: '',
      service_status_id: ''
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('service_profile_id', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ service_profile_id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

ServiceProfileModel.tableName = 'tb_service_profile'
ServiceProfileModel.db = db

module.exports = ServiceProfileModel