const db = require('../config/db').mysql

class CallingConfigModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_calling_config'
  }

  static attributeLabels() {
    return {
      calling_id: 'ไอดี',
      notice_queue: '',
      notice_queue_status: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('calling_id', id).first()
  }

  static deleteById(id) {
    return this.delete({ calling_id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

CallingConfigModel.tableName = 'tb_calling_config'
CallingConfigModel.db = db

module.exports = CallingConfigModel