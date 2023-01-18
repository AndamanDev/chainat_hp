const db = require('../config/db').mysql

class DrugConfigModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'tb_drug_config'
  }

  static attributeLabels() {
    return {
      id: 'ไอดี',
      drug_qty: '',
      pharmacy_drug: '',
      drug_elderly: '',
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('id', id).first()
  }

  static deleteById(id) {
    return this.delete({ id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

DrugConfigModel.tableName = 'tb_drug_config'
DrugConfigModel.db = db

module.exports = DrugConfigModel