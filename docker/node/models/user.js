const db = require('../config/db').mysql

class UserModel {
  constructor(attributes) {
    this.attributes = attributes
    this.db = db
    this.tableName = 'user'
  }

  static attributeLabels() {
    return {
    }
  }

  static find(columns = '*') {
    return this.db.select(columns).from(this.tableName)
  }

  static findOneById = (id) => {
    return this.find().where('id', id).first()
  }

  static findOne = (condition) => {
    return this.find().where(condition).first()
  }

  static deleteById(id) {
    return this.delete({ id: id })
  }

  static delete(params) {
    return this.db(this.tableName).where(params).del()
  }

}

UserModel.tableName = 'user'
UserModel.db = db

module.exports = UserModel