const db = require('../config/db').mysql

class CounterServiceModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_counterservice'
  }

  static attributeLabels() {
    return {
      counterserviceid: 'ไอดี',
      counterservice_name: '',
      counterservice_callnumber: '',
      counterservice_type: '',
      servicegroupid: '',
      userid: '',
      serviceid: '',
      sound_stationid: '',
      sound_id: '',
      counterservice_status: '',
      sound_service_id: '',
      service_order: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('counterserviceid', id).first()
  }

  static deleteById(id) {
    return this.delete({ counterserviceid: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

CounterServiceModel.tableName = 'tb_counterservice'
CounterServiceModel.db = db

module.exports = CounterServiceModel