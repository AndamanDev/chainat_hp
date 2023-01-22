const db = require('../config/db').mysql

class TicketModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_ticket'
  }

  static attributeLabels() {
    return {
      ids: 'ไอดี',
      hos_name_th: '',
      hos_name_en: '',
      template: '',
      default_template: '',
      logo_path: '',
      logo_base_url: '',
      barcode_type: '',
      status: ''
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('ids', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ ids: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

TicketModel.tableName = 'tb_ticket'
TicketModel.db = db

module.exports = TicketModel