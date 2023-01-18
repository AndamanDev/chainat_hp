const db = require('../config/db').mysql

class ServiceModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_service'
  }

  static attributeLabels() {
    return {
      serviceid: 'ไอดี',
      service_name: 'ชื่องานบริการ',
      service_groupid: '',
      service_route: '',
      prn_profileid: '',
      prn_profileid_quickly: '',
      prn_copyqty: '',
      service_prefix: '',
      service_numdigit: '',
      service_status: '',
      service_md_name_id: '',
      print_by_hn: '',
      quickly: '',
      show_on_kiosk: '',
      show_on_mobile: '',
      btn_kiosk_name: '',
      main_dep: '',
      service_type_id: '',
      service_pic: ''
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('serviceid', id).first()
  }

  static deleteById(id) {
    return this.delete({ serviceid: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

ServiceModel.tableName = 'tb_service'
ServiceModel.db = db

module.exports = ServiceModel