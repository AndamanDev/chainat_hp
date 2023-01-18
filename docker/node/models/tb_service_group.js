const db = require('../config/db').mysql

class ServiceGroupModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_servicegroup'
  }

  static attributeLabels() {
    return {
      servicegroupid: 'ไอดี',
      servicegroup_code: '',
      servicegroup_type_id: '',
      servicegroup_name: '',
      servicegroup_prefix: '',
      servicegroup_order: '',
      subservice_status: '',
      servicegroup_status: '',
      show_on_kiosk: '',
      show_on_mobile: '',
      servicestatus_default: '',
      servicegroup_clinic: '',
      servicegroup_pic: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('servicegroupid', id).first()
  }

  static deleteById(id) {
    return this.delete({ servicegroupid: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

ServiceGroupModel.tableName = 'tb_servicegroup'
ServiceGroupModel.db = db

module.exports = ServiceGroupModel